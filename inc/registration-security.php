<?php
/**
 * Piyasa Vizyon public registration hardening.
 *
 * Keeps registration open while replacing BirFinans' legacy AJAX registration
 * handler with a rate-limited implementation. Cloudflare Turnstile can be
 * enabled from Settings > PV Registration Security without editing theme files.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'pv_registration_security_option' ) ) {
    function pv_registration_security_option( $key, $default = '' ) {
        $constant_map = array(
            'enabled'    => 'PV_TURNSTILE_ENABLED',
            'site_key'   => 'PV_TURNSTILE_SITE_KEY',
            'secret_key' => 'PV_TURNSTILE_SECRET_KEY',
        );

        if ( isset( $constant_map[ $key ] ) && defined( $constant_map[ $key ] ) ) {
            return constant( $constant_map[ $key ] );
        }

        return get_option( 'pv_registration_' . $key, $default );
    }
}

if ( ! function_exists( 'pv_registration_turnstile_enabled' ) ) {
    function pv_registration_turnstile_enabled() {
        $enabled    = (bool) pv_registration_security_option( 'enabled', false );
        $site_key   = trim( (string) pv_registration_security_option( 'site_key', '' ) );
        $secret_key = trim( (string) pv_registration_security_option( 'secret_key', '' ) );

        return $enabled && $site_key !== '' && $secret_key !== '';
    }
}

if ( ! function_exists( 'pv_registration_client_ip' ) ) {
    function pv_registration_client_ip() {
        $candidates = array(
            isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) : '',
            isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '',
        );

        foreach ( $candidates as $candidate ) {
            $candidate = trim( (string) $candidate );
            if ( filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
                return $candidate;
            }
        }

        return '0.0.0.0';
    }
}

if ( ! function_exists( 'pv_registration_rate_key' ) ) {
    function pv_registration_rate_key( $scope, $identity, $bucket ) {
        $hash = hash_hmac( 'sha256', $identity . '|' . $bucket, wp_salt( 'nonce' ) );
        return 'pv_reg_' . sanitize_key( $scope ) . '_' . substr( $hash, 0, 32 );
    }
}

if ( ! function_exists( 'pv_registration_bump_counter' ) ) {
    function pv_registration_bump_counter( $scope, $identity, $window_seconds ) {
        $window_seconds = max( 60, (int) $window_seconds );
        $bucket         = (int) floor( time() / $window_seconds );
        $key            = pv_registration_rate_key( $scope, $identity, $bucket );
        $count          = (int) get_transient( $key );
        $count++;
        set_transient( $key, $count, $window_seconds + 120 );
        return $count;
    }
}

if ( ! function_exists( 'pv_registration_rate_limit_check' ) ) {
    function pv_registration_rate_limit_check() {
        $ip = pv_registration_client_ip();

        $short_count = pv_registration_bump_counter( 'ip15', $ip, 15 * MINUTE_IN_SECONDS );
        if ( $short_count > 5 ) {
            return new WP_Error( 'pv_registration_rate_short', __( 'Çok kısa sürede fazla kayıt denemesi yapıldı. Lütfen 15 dakika sonra tekrar deneyin.', 'piyasavizyon-v7' ) );
        }

        $daily_count = pv_registration_bump_counter( 'ipday', $ip, DAY_IN_SECONDS );
        if ( $daily_count > 20 ) {
            return new WP_Error( 'pv_registration_rate_daily', __( 'Bu bağlantıdan günlük kayıt denemesi sınırına ulaşıldı. Lütfen daha sonra tekrar deneyin.', 'piyasavizyon-v7' ) );
        }

        $global_count = pv_registration_bump_counter( 'global10', 'piyasavizyon', 10 * MINUTE_IN_SECONDS );
        if ( $global_count > 100 ) {
            return new WP_Error( 'pv_registration_rate_global', __( 'Kayıt sistemi geçici olarak yoğun. Lütfen birkaç dakika sonra tekrar deneyin.', 'piyasavizyon-v7' ) );
        }

        return true;
    }
}

if ( ! function_exists( 'pv_registration_verify_turnstile' ) ) {
    function pv_registration_verify_turnstile( $token ) {
        if ( ! pv_registration_turnstile_enabled() ) {
            return true;
        }

        $token = trim( (string) $token );
        if ( $token === '' ) {
            return new WP_Error( 'pv_turnstile_missing', __( 'Lütfen güvenlik doğrulamasını tamamlayın.', 'piyasavizyon-v7' ) );
        }

        $response = wp_remote_post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            array(
                'timeout' => 8,
                'body'    => array(
                    'secret'   => (string) pv_registration_security_option( 'secret_key', '' ),
                    'response' => $token,
                    'remoteip' => pv_registration_client_ip(),
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'pv_turnstile_unavailable', __( 'Güvenlik doğrulaması şu anda tamamlanamadı. Lütfen tekrar deneyin.', 'piyasavizyon-v7' ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $body ) || empty( $body['success'] ) ) {
            return new WP_Error( 'pv_turnstile_failed', __( 'Güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.', 'piyasavizyon-v7' ) );
        }

        return true;
    }
}

if ( ! function_exists( 'pv_registration_json_error' ) ) {
    function pv_registration_json_error( $message ) {
        wp_send_json( array( 'loggedin' => false, 'message' => wp_strip_all_tags( (string) $message ) ) );
    }
}

if ( ! function_exists( 'pv_secure_ajax_register' ) ) {
    function pv_secure_ajax_register() {
        if ( ! check_ajax_referer( 'ajax-register-nonce', 'security', false ) ) {
            pv_registration_json_error( __( 'Güvenlik doğrulaması geçersiz. Sayfayı yenileyip tekrar deneyin.', 'piyasavizyon-v7' ) );
        }

        if ( ! get_option( 'users_can_register' ) ) {
            pv_registration_json_error( __( 'Yeni kullanıcı kaydı şu anda kullanılamıyor.', 'piyasavizyon-v7' ) );
        }

        $honeypot = isset( $_POST['pv_website'] ) ? trim( (string) wp_unslash( $_POST['pv_website'] ) ) : '';
        if ( $honeypot !== '' ) {
            pv_registration_json_error( __( 'Kayıt isteği doğrulanamadı.', 'piyasavizyon-v7' ) );
        }

        $rate_check = pv_registration_rate_limit_check();
        if ( is_wp_error( $rate_check ) ) {
            pv_registration_json_error( $rate_check->get_error_message() );
        }

        $turnstile_token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
        $turnstile_check = pv_registration_verify_turnstile( $turnstile_token );
        if ( is_wp_error( $turnstile_check ) ) {
            pv_registration_json_error( $turnstile_check->get_error_message() );
        }

        $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ), true ) : '';
        $password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
        $email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        if ( $username === '' || $password === '' || $email === '' ) {
            pv_registration_json_error( __( 'Kullanıcı adı, e-posta ve şifre alanları zorunludur.', 'piyasavizyon-v7' ) );
        }
        if ( ! validate_username( $username ) ) {
            pv_registration_json_error( __( 'Geçerli bir kullanıcı adı girin.', 'piyasavizyon-v7' ) );
        }
        if ( ! is_email( $email ) ) {
            pv_registration_json_error( __( 'Geçerli bir e-posta adresi girin.', 'piyasavizyon-v7' ) );
        }
        if ( username_exists( $username ) ) {
            pv_registration_json_error( __( 'Bu kullanıcı adı kullanılıyor.', 'piyasavizyon-v7' ) );
        }
        if ( email_exists( $email ) ) {
            pv_registration_json_error( __( 'Bu e-posta adresi kullanılıyor.', 'piyasavizyon-v7' ) );
        }

        $user_id = wp_insert_user(
            array(
                'user_login'    => $username,
                'user_nicename' => sanitize_title( $username ),
                'nickname'      => $username,
                'display_name'  => $username,
                'first_name'    => $username,
                'user_pass'     => $password,
                'user_email'    => $email,
                'role'          => 'subscriber',
            )
        );

        if ( is_wp_error( $user_id ) ) {
            pv_registration_json_error( $user_id->get_error_message() );
        }

        $signon = wp_signon(
            array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => true,
            ),
            is_ssl()
        );

        if ( is_wp_error( $signon ) ) {
            wp_send_json( array( 'loggedin' => false, 'message' => __( 'Kayıt oluşturuldu. Lütfen kullanıcı bilgilerinizle giriş yapın.', 'piyasavizyon-v7' ) ) );
        }

        wp_set_current_user( $signon->ID );
        wp_send_json( array( 'loggedin' => true, 'message' => __( 'Kayıt başarılı, yönlendiriliyorsunuz...', 'piyasavizyon-v7' ) ) );
    }
}

if ( ! function_exists( 'pv_registration_replace_parent_ajax_handler' ) ) {
    function pv_registration_replace_parent_ajax_handler() {
        remove_action( 'wp_ajax_nopriv_ajaxregister', 'ajax_register' );
        remove_action( 'wp_ajax_nopriv_ajaxregister', 'pv_secure_ajax_register' );
        add_action( 'wp_ajax_nopriv_ajaxregister', 'pv_secure_ajax_register' );
    }
}
add_action( 'init', 'pv_registration_replace_parent_ajax_handler', 999 );

if ( ! function_exists( 'pv_registration_enqueue_turnstile' ) ) {
    function pv_registration_enqueue_turnstile() {
        if ( ! pv_registration_turnstile_enabled() ) {
            return;
        }
        if ( ! wp_script_is( 'pv-turnstile', 'registered' ) ) {
            wp_register_script( 'pv-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit', array(), null, true );
        }
        wp_enqueue_script( 'pv-turnstile' );
    }
}

if ( ! function_exists( 'pv_registration_replace_parent_auth_script' ) ) {
    function pv_registration_replace_parent_auth_script() {
        if ( ! wp_script_is( 'ajax-auth-script', 'enqueued' ) && ! wp_script_is( 'ajax-auth-script', 'registered' ) ) {
            return;
        }

        pv_registration_enqueue_turnstile();
        wp_dequeue_script( 'ajax-auth-script' );
        wp_deregister_script( 'ajax-auth-script' );

        $script_path = get_stylesheet_directory() . '/assets/js/pv-auth-security.js';
        $version     = is_file( $script_path ) ? (string) filemtime( $script_path ) : '1.0.0';

        wp_register_script( 'ajax-auth-script', get_stylesheet_directory_uri() . '/assets/js/pv-auth-security.js', array( 'jquery' ), $version, true );
        wp_enqueue_script( 'ajax-auth-script' );

        wp_localize_script(
            'ajax-auth-script',
            'ajax_auth_object',
            array(
                'ajaxurl'        => admin_url( 'admin-ajax.php' ),
                'redirecturl'    => home_url(),
                'loadingmessage' => __( 'Bilgiler gönderiliyor, lütfen bekleyin...', 'piyasavizyon-v7' ),
            )
        );
        wp_localize_script(
            'ajax-auth-script',
            'pvAuthSecurity',
            array(
                'turnstileEnabled' => pv_registration_turnstile_enabled(),
                'turnstileSiteKey' => (string) pv_registration_security_option( 'site_key', '' ),
                'turnstileMessage' => __( 'Lütfen güvenlik doğrulamasını tamamlayın.', 'piyasavizyon-v7' ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'pv_registration_replace_parent_auth_script', 999 );

if ( ! function_exists( 'pv_registration_native_form_turnstile' ) ) {
    function pv_registration_native_form_turnstile() {
        if ( pv_registration_turnstile_enabled() ) {
            echo '<div id="pv-native-turnstile" style="margin:16px 0"></div>';
        }
    }
}
add_action( 'register_form', 'pv_registration_native_form_turnstile' );

if ( ! function_exists( 'pv_registration_native_login_assets' ) ) {
    function pv_registration_native_login_assets() {
        if ( pv_registration_turnstile_enabled() ) {
            pv_registration_enqueue_turnstile();
        }
    }
}
add_action( 'login_enqueue_scripts', 'pv_registration_native_login_assets' );

if ( ! function_exists( 'pv_registration_native_login_footer' ) ) {
    function pv_registration_native_login_footer() {
        if ( ! pv_registration_turnstile_enabled() ) {
            return;
        }
        $site_key = wp_json_encode( (string) pv_registration_security_option( 'site_key', '' ) );
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('pv-native-turnstile');
            if (!el || !window.turnstile) return;
            window.turnstile.render(el, {sitekey: <?php echo $site_key; ?>});
        });
        </script>
        <?php
    }
}
add_action( 'login_footer', 'pv_registration_native_login_footer' );

if ( ! function_exists( 'pv_registration_native_errors' ) ) {
    function pv_registration_native_errors( $errors, $sanitized_user_login, $user_email ) {
        unset( $sanitized_user_login, $user_email );
        if ( strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? (string) $_SERVER['REQUEST_METHOD'] : '' ) !== 'POST' ) {
            return $errors;
        }
        $rate_check = pv_registration_rate_limit_check();
        if ( is_wp_error( $rate_check ) ) {
            $errors->add( $rate_check->get_error_code(), $rate_check->get_error_message() );
            return $errors;
        }
        $turnstile_token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
        $turnstile_check = pv_registration_verify_turnstile( $turnstile_token );
        if ( is_wp_error( $turnstile_check ) ) {
            $errors->add( $turnstile_check->get_error_code(), $turnstile_check->get_error_message() );
        }
        return $errors;
    }
}
add_filter( 'registration_errors', 'pv_registration_native_errors', 20, 3 );

if ( ! function_exists( 'pv_registration_security_admin_menu' ) ) {
    function pv_registration_security_admin_menu() {
        add_options_page( __( 'PV Registration Security', 'piyasavizyon-v7' ), __( 'PV Registration Security', 'piyasavizyon-v7' ), 'manage_options', 'pv-registration-security', 'pv_registration_security_admin_page' );
    }
}
add_action( 'admin_menu', 'pv_registration_security_admin_menu' );

if ( ! function_exists( 'pv_registration_security_save_settings' ) ) {
    function pv_registration_security_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bu işlem için yetkiniz yok.', 'piyasavizyon-v7' ) );
        }
        check_admin_referer( 'pv_registration_security_save' );

        $enabled  = isset( $_POST['pv_registration_enabled'] ) ? '1' : '0';
        $site_key = isset( $_POST['pv_registration_site_key'] ) ? sanitize_text_field( wp_unslash( $_POST['pv_registration_site_key'] ) ) : '';
        $secret   = isset( $_POST['pv_registration_secret_key'] ) ? sanitize_text_field( wp_unslash( $_POST['pv_registration_secret_key'] ) ) : '';
        $clear    = ! empty( $_POST['pv_registration_clear_secret'] );

        update_option( 'pv_registration_enabled', $enabled, false );
        update_option( 'pv_registration_site_key', $site_key, false );
        if ( $clear ) {
            delete_option( 'pv_registration_secret_key' );
        } elseif ( $secret !== '' ) {
            update_option( 'pv_registration_secret_key', $secret, false );
        }

        wp_safe_redirect( add_query_arg( array( 'page' => 'pv-registration-security', 'updated' => '1' ), admin_url( 'options-general.php' ) ) );
        exit;
    }
}
add_action( 'admin_post_pv_registration_security_save', 'pv_registration_security_save_settings' );

if ( ! function_exists( 'pv_registration_security_admin_page' ) ) {
    function pv_registration_security_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $enabled      = (bool) pv_registration_security_option( 'enabled', false );
        $site_key     = (string) pv_registration_security_option( 'site_key', '' );
        $has_secret   = trim( (string) pv_registration_security_option( 'secret_key', '' ) ) !== '';
        $fully_active = pv_registration_turnstile_enabled();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'PV Registration Security', 'piyasavizyon-v7' ); ?></h1>
            <p><?php esc_html_e( 'Kullanıcı kaydı açık kalır. Bu katman BirFinans kayıt uç noktasına oran sınırlama ekler ve isteğe bağlı Cloudflare Turnstile doğrulaması uygular.', 'piyasavizyon-v7' ); ?></p>
            <?php if ( isset( $_GET['updated'] ) ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Ayarlar kaydedildi.', 'piyasavizyon-v7' ); ?></p></div><?php endif; ?>
            <?php if ( $fully_active ) : ?>
                <div class="notice notice-success"><p><strong><?php esc_html_e( 'Turnstile aktif.', 'piyasavizyon-v7' ); ?></strong> <?php esc_html_e( 'Kayıtlar bot doğrulamasından geçiyor.', 'piyasavizyon-v7' ); ?></p></div>
            <?php elseif ( $enabled ) : ?>
                <div class="notice notice-warning"><p><strong><?php esc_html_e( 'Turnstile henüz aktif değil.', 'piyasavizyon-v7' ); ?></strong> <?php esc_html_e( 'Site key ve secret key birlikte girilene kadar yalnızca oran sınırlama çalışır.', 'piyasavizyon-v7' ); ?></p></div>
            <?php else : ?>
                <div class="notice notice-info"><p><?php esc_html_e( 'Şu anda oran sınırlama aktiftir; Turnstile isteğe bağlıdır.', 'piyasavizyon-v7' ); ?></p></div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="pv_registration_security_save">
                <?php wp_nonce_field( 'pv_registration_security_save' ); ?>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><?php esc_html_e( 'Cloudflare Turnstile', 'piyasavizyon-v7' ); ?></th><td><label><input type="checkbox" name="pv_registration_enabled" value="1" <?php checked( $enabled ); ?>> <?php esc_html_e( 'Kayıtlarda Turnstile doğrulamasını etkinleştir', 'piyasavizyon-v7' ); ?></label></td></tr>
                    <tr><th scope="row"><label for="pv-registration-site-key"><?php esc_html_e( 'Site key', 'piyasavizyon-v7' ); ?></label></th><td><input id="pv-registration-site-key" class="regular-text" type="text" name="pv_registration_site_key" value="<?php echo esc_attr( $site_key ); ?>" autocomplete="off"></td></tr>
                    <tr><th scope="row"><label for="pv-registration-secret-key"><?php esc_html_e( 'Secret key', 'piyasavizyon-v7' ); ?></label></th><td><input id="pv-registration-secret-key" class="regular-text" type="password" name="pv_registration_secret_key" value="" autocomplete="new-password" placeholder="<?php echo esc_attr( $has_secret ? '••••••••••••••••' : '' ); ?>"><p class="description"><?php esc_html_e( 'Mevcut secret key gösterilmez. Alanı boş bırakırsanız mevcut değer korunur.', 'piyasavizyon-v7' ); ?></p><?php if ( $has_secret ) : ?><label><input type="checkbox" name="pv_registration_clear_secret" value="1"> <?php esc_html_e( 'Kayıtlı secret key değerini sil', 'piyasavizyon-v7' ); ?></label><?php endif; ?></td></tr>
                </table>
                <p><strong><?php esc_html_e( 'Sabit korumalar:', 'piyasavizyon-v7' ); ?></strong> <?php esc_html_e( 'IP başına 15 dakikada 5 deneme, günlük 20 deneme ve site genelinde 10 dakikada 100 kayıt denemesi.', 'piyasavizyon-v7' ); ?></p>
                <?php submit_button( __( 'Ayarları Kaydet', 'piyasavizyon-v7' ) ); ?>
            </form>
        </div>
        <?php
    }
}
