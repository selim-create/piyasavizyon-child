<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Category/menu sponsorship runtime.
 *
 * This module owns sponsor-specific navigation branding, contextual page skins,
 * a contextual 300x250 creative and privileged third-party impression tags.
 * No sponsorship is visible until the feature is explicitly enabled in the
 * Piyasa Vizyon > Reklam Yönetimi panel.
 */

function pv_sponsorship_targets() {
    return array(
        'gundem' => array(
            'label'          => 'Gündem',
            'nav_label'      => 'Gündem',
            'paths'          => array( 'gundem', 'dunya', 'son-dakika' ),
            'category_slugs' => array( 'gundem', 'dunya', 'son-dakika' ),
        ),
        'ekonomi' => array(
            'label'          => 'Ekonomi',
            'nav_label'      => 'Ekonomi',
            'paths'          => array( 'ekonomi', 'piyasalar/emlak', 'piyasalar/enerji-habeleri' ),
            'category_slugs' => array( 'ekonomi' ),
        ),
        'borsa' => array(
            'label'          => 'Borsa',
            'nav_label'      => 'Borsa',
            'paths'          => array( 'borsa', 'canli-borsa', 'tum-hisseler', 'hisse', 'tum-endeksler', 'endeks', 'tum-pariteler', 'ekonomik-takvim', 'piyasalar/borsa-haberleri' ),
            'category_slugs' => array( 'borsa-haberleri' ),
        ),
        'doviz' => array(
            'label'          => 'Döviz',
            'nav_label'      => 'Döviz',
            'paths'          => array( 'doviz-kurlari', 'doviz-cevirici', 'doviz-arsiv', 'doviz', 'piyasalar/doviz-haberleri' ),
            'category_slugs' => array( 'doviz-haberleri' ),
        ),
        'altin' => array(
            'label'          => 'Altın',
            'nav_label'      => 'Altın',
            'paths'          => array( 'altin-fiyatlari', 'altin', 'piyasalar/altin-haberleri' ),
            'category_slugs' => array( 'altin-haberleri' ),
        ),
        'finans' => array(
            'label'          => 'Finans',
            'nav_label'      => 'Finans',
            'paths'          => array( 'finans', 'kredi', 'kredi-hesapla', 'ihtiyac-kredisi', 'konut-kredisi', 'tasit-kredisi', 'kobi-kredisi', 'faiz-oranlari', 'mevduat-oranlari' ),
            'category_slugs' => array( 'finans', 'bankacilik', 'kredi-haberleri', 'faiz' ),
        ),
        'halka-arz' => array(
            'label'          => 'Halka Arz',
            'nav_label'      => 'Halka Arz',
            'paths'          => array( 'halka-arz', 'halka-arz-takvimi', 'gelecek-halka-arzlar', 'finans/halka-arz-haberleri' ),
            'category_slugs' => array( 'halka-arz-haberleri' ),
        ),
        'crypto' => array(
            'label'          => 'Kripto Para',
            'nav_label'      => 'Kripto Para',
            'paths'          => array( 'kripto-para', 'kripto-paralar', 'kriptoparalar', 'coin', 'piyasalar/kripto-para-haberleri' ),
            'category_slugs' => array( 'kripto-para', 'kripto-para-haberleri' ),
        ),
    );
}

function pv_sponsorship_defaults() {
    return array(
        'enabled'          => 0,
        'target'           => 'crypto',
        'brand'            => 'Staplex',
        'logo_url'         => '',
        'click_url'        => '',
        'skin_url'         => '',
        'banner_300_url'   => '',
        'banner_click_url' => '',
        'tracking_code'    => '',
    );
}

function pv_sponsorship_settings() {
    $saved = get_option( 'pv_sponsorship_settings', array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }
    return wp_parse_args( $saved, pv_sponsorship_defaults() );
}

function pv_sponsorship_is_enabled() {
    $settings = pv_sponsorship_settings();
    return ! empty( $settings['enabled'] );
}

function pv_sponsorship_request_path() {
    if ( empty( $_SERVER['REQUEST_URI'] ) ) {
        return '';
    }

    $path = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
    return trim( (string) $path, '/' );
}

function pv_sponsorship_path_matches( $request_path, $candidate ) {
    $candidate = trim( (string) $candidate, '/' );
    if ( $candidate === '' || $request_path === '' ) {
        return false;
    }

    return $request_path === $candidate || strpos( $request_path, $candidate . '/' ) === 0;
}

function pv_sponsorship_queried_category_slugs() {
    $slugs = array();

    if ( is_category() ) {
        $term = get_queried_object();
        if ( $term instanceof WP_Term ) {
            $slugs[] = $term->slug;
            $ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );
            foreach ( $ancestors as $ancestor_id ) {
                $ancestor = get_term( $ancestor_id, 'category' );
                if ( $ancestor instanceof WP_Term ) {
                    $slugs[] = $ancestor->slug;
                }
            }
        }
    }

    if ( is_single() ) {
        $post_id = get_queried_object_id();
        if ( $post_id ) {
            $terms = get_the_category( $post_id );
            foreach ( $terms as $term ) {
                if ( ! $term instanceof WP_Term ) {
                    continue;
                }
                $slugs[] = $term->slug;
                $ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );
                foreach ( $ancestors as $ancestor_id ) {
                    $ancestor = get_term( $ancestor_id, 'category' );
                    if ( $ancestor instanceof WP_Term ) {
                        $slugs[] = $ancestor->slug;
                    }
                }
            }
        }
    }

    return array_values( array_unique( array_filter( $slugs ) ) );
}

function pv_sponsorship_is_context() {
    if ( ! pv_sponsorship_is_enabled() || is_admin() || is_404() || is_search() ) {
        return false;
    }

    $settings = pv_sponsorship_settings();
    $targets  = pv_sponsorship_targets();
    $target   = isset( $targets[ $settings['target'] ] ) ? $targets[ $settings['target'] ] : null;

    if ( ! $target ) {
        return false;
    }

    $request_path = pv_sponsorship_request_path();
    foreach ( $target['paths'] as $path ) {
        if ( pv_sponsorship_path_matches( $request_path, $path ) ) {
            return true;
        }
    }

    $category_slugs = pv_sponsorship_queried_category_slugs();
    if ( array_intersect( $target['category_slugs'], $category_slugs ) ) {
        return true;
    }

    return false;
}

function pv_sponsorship_menu_logo( $nav_label ) {
    if ( ! pv_sponsorship_is_enabled() ) {
        return;
    }

    $settings = pv_sponsorship_settings();
    $targets  = pv_sponsorship_targets();
    if ( empty( $settings['logo_url'] ) || empty( $targets[ $settings['target'] ] ) ) {
        return;
    }

    $target = $targets[ $settings['target'] ];
    if ( (string) $target['nav_label'] !== (string) $nav_label ) {
        return;
    }

    $brand = trim( (string) $settings['brand'] );
    $title = $brand !== '' ? sprintf( '%s sponsorluğunda', $brand ) : 'Sponsorlu';
    $logo  = '<img src="' . esc_url( $settings['logo_url'] ) . '" alt="' . esc_attr( $brand ) . '" loading="eager" decoding="async">';

    if ( ! empty( $settings['click_url'] ) ) {
        echo '<span class="pv-nav-sponsor-logo" title="' . esc_attr( $title ) . '"><a href="' . esc_url( $settings['click_url'] ) . '" target="_blank" rel="sponsored noopener noreferrer" aria-label="' . esc_attr( $title ) . '">' . $logo . '</a></span>';
        return;
    }

    echo '<span class="pv-nav-sponsor-logo" title="' . esc_attr( $title ) . '">' . $logo . '</span>';
}

function pv_sponsorship_body_classes( $classes ) {
    if ( pv_sponsorship_is_context() ) {
        $settings  = pv_sponsorship_settings();
        $classes[] = 'pv-sponsorship-active';
        $classes[] = 'pv-sponsorship-' . sanitize_html_class( $settings['target'] );
    }

    return array_values( array_unique( $classes ) );
}
add_filter( 'body_class', 'pv_sponsorship_body_classes', 40 );

function pv_sponsorship_enqueue_assets() {
    if ( ! pv_sponsorship_is_enabled() ) {
        return;
    }

    $path = get_stylesheet_directory() . '/assets/css/pv-sponsorship.css';
    wp_enqueue_style(
        'pv-sponsorship',
        get_stylesheet_directory_uri() . '/assets/css/pv-sponsorship.css',
        array( 'pv-header-v260' ),
        is_file( $path ) ? filemtime( $path ) : '1.0.0'
    );

    if ( pv_sponsorship_is_context() ) {
        $settings = pv_sponsorship_settings();
        if ( ! empty( $settings['skin_url'] ) ) {
            $skin_url = esc_url_raw( $settings['skin_url'] );
            $css = 'body.pv-sponsorship-active{background-image:url("' . esc_url( $skin_url ) . '")!important;background-position:50% 0!important;background-repeat:no-repeat!important;background-attachment:fixed!important;background-size:1920px auto!important;}';
            wp_add_inline_style( 'pv-sponsorship', $css );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'pv_sponsorship_enqueue_assets', 70 );

function pv_sponsorship_render_skin_click_targets() {
    if ( ! pv_sponsorship_is_context() ) {
        return;
    }

    $settings = pv_sponsorship_settings();
    if ( empty( $settings['skin_url'] ) || empty( $settings['click_url'] ) ) {
        return;
    }

    $brand = trim( (string) $settings['brand'] );
    $label = $brand !== '' ? sprintf( '%s sponsor sayfası', $brand ) : 'Sponsor sayfası';
    $url   = esc_url( $settings['click_url'] );

    echo '<div class="pv-sponsor-skin-clicks">';
    echo '<a class="pv-sponsor-skin-wing pv-sponsor-skin-wing--left" href="' . $url . '" target="_blank" rel="sponsored noopener noreferrer" aria-label="' . esc_attr( $label ) . '"></a>';
    echo '<a class="pv-sponsor-skin-wing pv-sponsor-skin-wing--right" href="' . $url . '" target="_blank" rel="sponsored noopener noreferrer" aria-label="' . esc_attr( $label ) . '"></a>';
    echo '</div>';
}
add_action( 'wp_body_open', 'pv_sponsorship_render_skin_click_targets', 25 );

function pv_sponsorship_render_sidebar_ad() {
    if ( ! pv_sponsorship_is_context() ) {
        return false;
    }

    $settings = pv_sponsorship_settings();
    if ( empty( $settings['banner_300_url'] ) ) {
        return false;
    }

    $brand = trim( (string) $settings['brand'] );
    $alt   = $brand !== '' ? $brand . ' reklamı' : 'Sponsor reklamı';
    $img   = '<img src="' . esc_url( $settings['banner_300_url'] ) . '" alt="' . esc_attr( $alt ) . '" width="300" height="250" loading="lazy" decoding="async">';

    echo '<div class="pv-sponsor-300x250">';
    echo '<span class="pv-sponsor-ad-label">Sponsorlu' . ( $brand !== '' ? ' · ' . esc_html( $brand ) : '' ) . '</span>';

    if ( ! empty( $settings['banner_click_url'] ) ) {
        echo '<a href="' . esc_url( $settings['banner_click_url'] ) . '" target="_blank" rel="sponsored noopener noreferrer">' . $img . '</a>';
    } else {
        echo $img;
    }

    echo '</div>';
    return true;
}

function pv_sponsorship_render_tracking_code() {
    if ( ! pv_sponsorship_is_context() ) {
        return;
    }

    $settings = pv_sponsorship_settings();
    $code     = trim( (string) $settings['tracking_code'] );
    if ( $code === '' ) {
        return;
    }

    // Deliberately unescaped: only manage_options users can save this privileged
    // GAM/third-party tracking field, mirroring the theme's existing global-code panel.
    echo "\n<!-- Piyasa Vizyon contextual sponsorship tracking -->\n" . $code . "\n";
}
add_action( 'wp_footer', 'pv_sponsorship_render_tracking_code', 1 );

function pv_sponsorship_save_settings() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Bu işlem için yetkiniz yok.', 'piyasavizyon-v7' ) );
    }

    check_admin_referer( 'pv_sponsorship_save' );

    $targets = pv_sponsorship_targets();
    $target  = isset( $_POST['pv_sponsor_target'] ) ? sanitize_key( wp_unslash( $_POST['pv_sponsor_target'] ) ) : 'crypto';
    if ( ! isset( $targets[ $target ] ) ) {
        $target = 'crypto';
    }

    $settings = array(
        'enabled'          => isset( $_POST['pv_sponsor_enabled'] ) ? 1 : 0,
        'target'           => $target,
        'brand'            => isset( $_POST['pv_sponsor_brand'] ) ? sanitize_text_field( wp_unslash( $_POST['pv_sponsor_brand'] ) ) : '',
        'logo_url'         => isset( $_POST['pv_sponsor_logo_url'] ) ? esc_url_raw( wp_unslash( $_POST['pv_sponsor_logo_url'] ) ) : '',
        'click_url'        => isset( $_POST['pv_sponsor_click_url'] ) ? esc_url_raw( wp_unslash( $_POST['pv_sponsor_click_url'] ) ) : '',
        'skin_url'         => isset( $_POST['pv_sponsor_skin_url'] ) ? esc_url_raw( wp_unslash( $_POST['pv_sponsor_skin_url'] ) ) : '',
        'banner_300_url'   => isset( $_POST['pv_sponsor_banner_300_url'] ) ? esc_url_raw( wp_unslash( $_POST['pv_sponsor_banner_300_url'] ) ) : '',
        'banner_click_url' => isset( $_POST['pv_sponsor_banner_click_url'] ) ? esc_url_raw( wp_unslash( $_POST['pv_sponsor_banner_click_url'] ) ) : '',
        'tracking_code'    => isset( $_POST['pv_sponsor_tracking_code'] ) ? wp_unslash( $_POST['pv_sponsor_tracking_code'] ) : '',
    );

    update_option( 'pv_sponsorship_settings', $settings, false );

    $redirect = function_exists( 'pv_control_center_page_url' )
        ? pv_control_center_page_url( 'ads' )
        : admin_url( 'admin.php?page=pv-control-center&tab=ads' );

    wp_safe_redirect( add_query_arg( 'pv_sponsorship_updated', '1', $redirect ) );
    exit;
}
add_action( 'admin_post_pv_sponsorship_save', 'pv_sponsorship_save_settings' );

function pv_sponsorship_admin_panel() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $settings = pv_sponsorship_settings();
    $targets  = pv_sponsorship_targets();
    ?>
    <?php if ( isset( $_GET['pv_sponsorship_updated'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Sponsorluk ayarları kaydedildi.', 'piyasavizyon-v7' ); ?></p></div>
    <?php endif; ?>

    <div class="pvcc-grid pvcc-grid--2 pvcc-sponsorship-grid">
        <section class="pvcc-card">
            <div class="pvcc-card-head">
                <div>
                    <span class="pvcc-eyebrow"><?php esc_html_e( 'Contextual branding', 'piyasavizyon-v7' ); ?></span>
                    <h2><?php esc_html_e( 'Menü & kategori sponsorluğu', 'piyasavizyon-v7' ); ?></h2>
                    <p><?php esc_html_e( 'Seçilen menüde sponsor logosunu gösterir; ilgili kategori, piyasa sayfası ve o kategorideki yazılarda giydirme + 300x250 kreatifi devreye alır.', 'piyasavizyon-v7' ); ?></p>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pvcc-form pvcc-sponsor-form">
                <input type="hidden" name="action" value="pv_sponsorship_save">
                <?php wp_nonce_field( 'pv_sponsorship_save' ); ?>

                <label class="pvcc-switch-row">
                    <span><strong><?php esc_html_e( 'Sponsorluk yayında', 'piyasavizyon-v7' ); ?></strong><small><?php esc_html_e( 'Kapalıyken hiçbir sponsor öğesi frontend’de görünmez.', 'piyasavizyon-v7' ); ?></small></span>
                    <span class="pvcc-switch"><input type="checkbox" name="pv_sponsor_enabled" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>><span></span></span>
                </label>

                <label>
                    <span><?php esc_html_e( 'Sponsor olunan menü', 'piyasavizyon-v7' ); ?></span>
                    <select name="pv_sponsor_target">
                        <?php foreach ( $targets as $key => $target ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['target'], $key ); ?>><?php echo esc_html( $target['label'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label><span><?php esc_html_e( 'Marka adı', 'piyasavizyon-v7' ); ?></span><input type="text" name="pv_sponsor_brand" value="<?php echo esc_attr( $settings['brand'] ); ?>" placeholder="Staplex"></label>
                <label><span><?php esc_html_e( 'Menü logo URL', 'piyasavizyon-v7' ); ?></span><input type="url" name="pv_sponsor_logo_url" value="<?php echo esc_attr( $settings['logo_url'] ); ?>" placeholder="https://.../staplex-logo.webp"><small><?php esc_html_e( 'Şeffaf PNG/WebP önerilir. Menüde maksimum 54×18 px gösterilir.', 'piyasavizyon-v7' ); ?></small></label>
                <label><span><?php esc_html_e( 'Ana tıklama URL', 'piyasavizyon-v7' ); ?></span><input type="url" name="pv_sponsor_click_url" value="<?php echo esc_attr( $settings['click_url'] ); ?>" placeholder="https://www.staplex..."></label>
                <label><span><?php esc_html_e( 'Desktop giydirme URL', 'piyasavizyon-v7' ); ?></span><input type="url" name="pv_sponsor_skin_url" value="<?php echo esc_attr( $settings['skin_url'] ); ?>" placeholder="https://.../staplex-skin.webp"><small><?php esc_html_e( 'PNG/JPG/GIF/animated WebP. 1280 px altındaki ekranlarda gösterilmez.', 'piyasavizyon-v7' ); ?></small></label>
                <label><span><?php esc_html_e( '300×250 kreatif URL', 'piyasavizyon-v7' ); ?></span><input type="url" name="pv_sponsor_banner_300_url" value="<?php echo esc_attr( $settings['banner_300_url'] ); ?>" placeholder="https://.../staplex-300x250.webp"></label>
                <label><span><?php esc_html_e( '300×250 tıklama URL', 'piyasavizyon-v7' ); ?></span><input type="url" name="pv_sponsor_banner_click_url" value="<?php echo esc_attr( $settings['banner_click_url'] ); ?>" placeholder="https://www.staplex..."></label>
                <label>
                    <span><?php esc_html_e( 'GAM 1×1 / impression tracking kodları', 'piyasavizyon-v7' ); ?></span>
                    <textarea name="pv_sponsor_tracking_code" rows="8" class="large-text code" placeholder="<script>...</script>"><?php echo esc_textarea( $settings['tracking_code'] ); ?></textarea>
                    <small><?php esc_html_e( 'Birden fazla 1×1 / üçüncü taraf impression etiketi aynı alana eklenebilir. Kod yalnızca sponsorlu bağlamda bir kez footer’a basılır.', 'piyasavizyon-v7' ); ?></small>
                </label>

                <?php submit_button( __( 'Sponsorluk Ayarlarını Kaydet', 'piyasavizyon-v7' ), 'primary', 'submit', false ); ?>
            </form>
        </section>

        <section class="pvcc-card pvcc-card--dark">
            <div class="pvcc-card-head">
                <div>
                    <span class="pvcc-eyebrow"><?php esc_html_e( 'Creative spec', 'piyasavizyon-v7' ); ?></span>
                    <h2><?php esc_html_e( 'Markaya gönderilecek teknik ölçüler', 'piyasavizyon-v7' ); ?></h2>
                    <p><?php esc_html_e( 'Staplex / Kripto Para sahiplenmesi için önerilen üretim standardı.', 'piyasavizyon-v7' ); ?></p>
                </div>
            </div>
            <div class="pvcc-system-list">
                <div><span><?php esc_html_e( 'Menü logosu', 'piyasavizyon-v7' ); ?></span><strong>480×160 px @2x</strong></div>
                <div><span><?php esc_html_e( 'Giydirme master', 'piyasavizyon-v7' ); ?></span><strong>1920×1080 px</strong></div>
                <div><span><?php esc_html_e( 'Merkez güvenli alan', 'piyasavizyon-v7' ); ?></span><strong>1240 px boş</strong></div>
                <div><span><?php esc_html_e( 'Display', 'piyasavizyon-v7' ); ?></span><strong>300×250 px</strong></div>
                <div><span><?php esc_html_e( 'Animasyon', 'piyasavizyon-v7' ); ?></span><strong>GIF / animated WebP</strong></div>
                <div><span><?php esc_html_e( 'Tracking', 'piyasavizyon-v7' ); ?></span><strong>GAM / 1×1 tags</strong></div>
            </div>
            <div class="pvcc-note pvcc-note--dark">
                <span class="dashicons dashicons-info-outline"></span>
                <p><?php esc_html_e( 'Giydirme 1920 px merkezlenir; Piyasa Vizyon içerik alanı 1240 px olduğundan kreatifin orta 1240 px bölümü metin/logo açısından boş tutulmalıdır. Kritik marka öğeleri yan kanatlarda kenarlara fazla yaklaşmamalıdır.', 'piyasavizyon-v7' ); ?></p>
            </div>
        </section>
    </div>
    <?php
}
