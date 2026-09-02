<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_member_login_url() { return home_url( '/giris-kayit-sayfasi/' ); }
function pv_member_url( $slug ) { return home_url( '/' . trim( $slug, '/' ) . '/' ); }
function pv_member_ajax_nonce() { return wp_create_nonce( 'pv-member-action' ); }

function pv_member_require_login() {
    if ( is_user_logged_in() ) { return; }
    wp_safe_redirect( pv_member_login_url() );
    exit;
}

function pv_member_nav( $active = '' ) {
    $user = wp_get_current_user();
    $items = array(
        'profile'  => array( 'Profil Ayarlarım', pv_member_url( 'uye-profili' ), 'fa-regular fa-user' ),
        'photo'    => array( 'Fotoğrafım', pv_member_url( 'uye-profil-fotografi' ), 'fa-regular fa-image' ),
        'password' => array( 'Şifre Değiştir', pv_member_url( 'uye-sifre-degistir' ), 'fa-solid fa-key' ),
        'alarms'   => array( 'Alarmlarım', pv_member_url( 'uye-alarm-sayfasi' ), 'fa-regular fa-bell' ),
        'list'     => array( 'Listelerim', pv_member_url( 'uye-listesi' ), 'fa-regular fa-bookmark' ),
        'social'   => array( 'Sosyal Medya', pv_member_url( 'uye-sosyal-medya' ), 'fa-solid fa-share-nodes' ),
    );
    echo '<nav class="pv-member-nav" aria-label="Üye menüsü">';
    foreach ( $items as $key => $item ) {
        printf( '<a class="%1$s" href="%2$s"><i class="%3$s" aria-hidden="true"></i><span>%4$s</span></a>', $active === $key ? 'is-active' : '', esc_url( $item[1] ), esc_attr( $item[2] ), esc_html( $item[0] ) );
    }
    if ( $user->ID ) {
        printf( '<a href="%1$s"><i class="fa-regular fa-heart" aria-hidden="true"></i><span>Favorilerim</span></a>', esc_url( get_author_posts_url( $user->ID ) ) );
    }
    printf( '<a href="%1$s"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Çıkış Yap</span></a>', esc_url( wp_logout_url( home_url( '/' ) ) ) );
    echo '</nav>';
}

function pv_member_profile_photo_url( $user_id ) {
    $url = esc_url_raw( (string) get_user_meta( $user_id, 'profil_pic_url', true ) );
    return $url !== '' ? $url : get_avatar_url( $user_id, array( 'size' => 256 ) );
}

function pv_member_enqueue_assets() {
    $slug = get_page_template_slug();
    $templates = array( 'login.php', 'uye-profili.php', 'uye-profil-fotograf.php', 'uye-sifre-degistir.php', 'uye-profil-sosyal.php', 'uye-alarm-kur.php', 'uye-liste.php' );
    if ( ! in_array( $slug, $templates, true ) ) { return; }

    $css = get_stylesheet_directory() . '/assets/css/pv-member.css';
    wp_enqueue_style( 'pv-member', get_stylesheet_directory_uri() . '/assets/css/pv-member.css', array( 'pv-v7-main' ), is_file( $css ) ? filemtime( $css ) : '1' );

    if ( $slug === 'login.php' ) {
        if ( function_exists( 'pv_registration_enqueue_turnstile' ) ) { pv_registration_enqueue_turnstile(); }
        $js = get_stylesheet_directory() . '/assets/js/pv-auth-security.js';
        wp_enqueue_script( 'ajax-auth-script', get_stylesheet_directory_uri() . '/assets/js/pv-auth-security.js', array( 'jquery' ), is_file( $js ) ? filemtime( $js ) : '1', true );
        wp_localize_script( 'ajax-auth-script', 'ajax_auth_object', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'redirecturl' => home_url( '/' ),
            'loadingmessage' => __( 'Bilgiler gönderiliyor, lütfen bekleyin...', 'piyasavizyon-v7' ),
        ) );
        wp_localize_script( 'ajax-auth-script', 'pvAuthSecurity', array(
            'turnstileEnabled' => function_exists( 'pv_registration_turnstile_enabled' ) && pv_registration_turnstile_enabled(),
            'turnstileSiteKey' => function_exists( 'pv_registration_security_option' ) ? (string) pv_registration_security_option( 'site_key', '' ) : '',
            'turnstileMessage' => __( 'Lütfen güvenlik doğrulamasını tamamlayın.', 'piyasavizyon-v7' ),
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'pv_member_enqueue_assets', 1200 );

function pv_member_json_error( $message ) { wp_send_json( array( 'loggedin' => false, 'success' => false, 'message' => wp_strip_all_tags( (string) $message ) ) ); }
function pv_member_json_ok( $message = 'Ok' ) { wp_send_json( array( 'loggedin' => true, 'success' => true, 'message' => $message ) ); }

function pv_member_ajax_login() {
    if ( ! check_ajax_referer( 'ajax-login-nonce', 'security', false ) ) { pv_member_json_error( 'Güvenlik doğrulaması geçersiz. Sayfayı yenileyip tekrar deneyin.' ); }
    $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ), true ) : '';
    $password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
    if ( $username === '' || $password === '' ) { pv_member_json_error( 'Kullanıcı adı ve şifre alanları zorunludur.' ); }
    $user = wp_signon( array( 'user_login' => $username, 'user_password' => $password, 'remember' => true ), is_ssl() );
    if ( is_wp_error( $user ) ) { pv_member_json_error( 'Kullanıcı adı veya şifre hatalı.' ); }
    wp_set_current_user( $user->ID );
    wp_send_json( array( 'loggedin' => true, 'success' => true, 'message' => 'Giriş başarılı, yönlendiriliyorsunuz...' ) );
}

function pv_member_replace_parent_login() {
    remove_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
    remove_action( 'wp_ajax_nopriv_ajaxlogin', 'pv_member_ajax_login' );
    add_action( 'wp_ajax_nopriv_ajaxlogin', 'pv_member_ajax_login' );
}
add_action( 'init', 'pv_member_replace_parent_login', 9999 );

function pv_member_action() {
    if ( ! is_user_logged_in() ) { wp_send_json_error( array( 'message' => 'Oturum açmanız gerekiyor.' ), 401 ); }
    if ( ! check_ajax_referer( 'pv-member-action', 'security', false ) ) { wp_send_json_error( array( 'message' => 'Güvenlik doğrulaması geçersiz.' ), 403 ); }

    $user_id = get_current_user_id();
    $type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';

    if ( $type === 'edit_profile' ) {
        $email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
        if ( ! is_email( $email ) ) { wp_send_json_error( array( 'message' => 'Geçerli bir e-posta adresi girin.' ) ); }
        $existing = email_exists( $email );
        if ( $existing && (int) $existing !== $user_id ) { wp_send_json_error( array( 'message' => 'Bu e-posta adresi başka bir hesapta kullanılıyor.' ) ); }
        $updated = wp_update_user( array( 'ID' => $user_id, 'user_email' => $email ) );
        if ( is_wp_error( $updated ) ) { wp_send_json_error( array( 'message' => $updated->get_error_message() ) ); }
        update_user_meta( $user_id, 'first_name', isset( $_POST['user_firstname'] ) ? sanitize_text_field( wp_unslash( $_POST['user_firstname'] ) ) : '' );
        update_user_meta( $user_id, 'last_name', isset( $_POST['user_lastname'] ) ? sanitize_text_field( wp_unslash( $_POST['user_lastname'] ) ) : '' );
        update_user_meta( $user_id, 'biyografi', isset( $_POST['user_biyografi'] ) ? sanitize_textarea_field( wp_unslash( $_POST['user_biyografi'] ) ) : '' );
        wp_send_json_success( array( 'message' => 'Kaydedildi.' ) );
    }

    if ( $type === 'update_password' ) {
        $user = wp_get_current_user();
        $old = isset( $_POST['last_password'] ) ? (string) wp_unslash( $_POST['last_password'] ) : '';
        $new = isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '';
        $retry = isset( $_POST['new_password_retry'] ) ? (string) wp_unslash( $_POST['new_password_retry'] ) : '';
        if ( ! wp_check_password( $old, $user->user_pass, $user_id ) ) { wp_send_json_error( array( 'code' => 'hatali', 'message' => 'Eski şifreniz hatalı.' ) ); }
        if ( $new === '' || $new !== $retry ) { wp_send_json_error( array( 'code' => 'uyumsuz', 'message' => 'Yeni şifreler eşleşmiyor.' ) ); }
        wp_set_password( $new, $user_id );
        wp_set_auth_cookie( $user_id, true, is_ssl() );
        wp_send_json_success( array( 'message' => 'Şifreniz güncellendi.' ) );
    }

    if ( $type === 'update_social' ) {
        foreach ( array( 'twitter', 'instagram', 'facebook' ) as $key ) {
            $value = isset( $_POST[ $key ] ) ? esc_url_raw( trim( (string) wp_unslash( $_POST[ $key ] ) ) ) : '';
            update_user_meta( $user_id, $key, $value );
        }
        wp_send_json_success( array( 'message' => 'Kaydedildi.' ) );
    }

    if ( in_array( $type, array( 'insert_liste', 'delete_liste' ), true ) ) {
        $currency = isset( $_POST['doviz'] ) ? sanitize_key( wp_unslash( $_POST['doviz'] ) ) : '';
        $list = get_user_meta( $user_id, 'uye_liste', true );
        $list = is_array( $list ) ? array_values( array_filter( $list, 'is_string' ) ) : array();
        if ( $type === 'insert_liste' && $currency !== '' && ! in_array( $currency, $list, true ) ) { $list[] = $currency; }
        if ( $type === 'delete_liste' ) { $list = array_values( array_filter( $list, static function( $v ) use ( $currency ) { return $v !== $currency; } ) ); }
        update_user_meta( $user_id, 'uye_liste', $list );
        wp_send_json_success( array( 'message' => 'Ok' ) );
    }

    if ( in_array( $type, array( 'insert_alarm', 'delete_alarm' ), true ) ) {
        $currency = isset( $_POST['doviz'] ) ? sanitize_key( wp_unslash( $_POST['doviz'] ) ) : '';
        $amount = isset( $_POST['miktar'] ) ? sanitize_text_field( wp_unslash( $_POST['miktar'] ) ) : '';
        $meta = get_user_meta( $user_id, 'uye_alarm', true );
        $currencies = is_array( $meta ) && isset( $meta['doviz'] ) && is_array( $meta['doviz'] ) ? array_values( $meta['doviz'] ) : array();
        $amounts = is_array( $meta ) && isset( $meta['miktar'] ) && is_array( $meta['miktar'] ) ? array_values( $meta['miktar'] ) : array();
        $new_currencies = array(); $new_amounts = array();
        foreach ( $currencies as $i => $value ) {
            if ( $value === $currency ) { continue; }
            $new_currencies[] = $value;
            $new_amounts[] = isset( $amounts[ $i ] ) ? $amounts[ $i ] : '';
        }
        if ( $type === 'insert_alarm' && $currency !== '' ) { $new_currencies[] = $currency; $new_amounts[] = $amount; }
        update_user_meta( $user_id, 'uye_alarm', array( 'doviz' => $new_currencies, 'miktar' => $new_amounts ) );
        wp_send_json_success( array( 'message' => 'Ok' ) );
    }

    wp_send_json_error( array( 'message' => 'Geçersiz işlem.' ), 400 );
}
add_action( 'wp_ajax_pv_member_action', 'pv_member_action' );

function pv_member_upload_profile() {
    pv_member_require_login();
    check_admin_referer( 'pv-member-upload-profile' );
    if ( empty( $_FILES['userfile']['tmp_name'] ) ) { wp_safe_redirect( add_query_arg( 'error', 'file', pv_member_url( 'uye-profil-fotografi' ) ) ); exit; }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $file = wp_handle_upload( $_FILES['userfile'], array( 'test_form' => false, 'mimes' => array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) ) );
    if ( ! empty( $file['error'] ) || empty( $file['url'] ) ) { wp_safe_redirect( add_query_arg( 'error', 'upload', pv_member_url( 'uye-profil-fotografi' ) ) ); exit; }
    update_user_meta( get_current_user_id(), 'profil_pic_url', esc_url_raw( $file['url'] ) );
    update_user_meta( get_current_user_id(), 'profil_pic', basename( $file['file'] ) );
    wp_safe_redirect( add_query_arg( 'updated', '1', pv_member_url( 'uye-profil-fotografi' ) ) );
    exit;
}
add_action( 'admin_post_pv_member_upload_profile', 'pv_member_upload_profile' );
