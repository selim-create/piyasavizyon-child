<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_author_follow_table() {
    global $wpdb;
    return $wpdb->prefix . 'follower';
}

function pv_author_follow_table_exists() {
    static $exists = null;
    if ( null !== $exists ) { return $exists; }

    global $wpdb;
    $table = pv_author_follow_table();
    $found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
    $exists = ( $found === $table );
    return $exists;
}

function pv_author_follow_count( $user_id, $direction = 'followers' ) {
    if ( ! pv_author_follow_table_exists() ) { return 0; }

    global $wpdb;
    $table = pv_author_follow_table();
    $column = ( 'following' === $direction ) ? 'user_id' : 'follow_id';
    return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$column} = %d", (int) $user_id ) );
}

function pv_author_follow_ids( $user_id, $direction = 'followers', $limit = 24 ) {
    if ( ! pv_author_follow_table_exists() ) { return array(); }

    global $wpdb;
    $table = pv_author_follow_table();
    $limit = max( 1, min( 100, (int) $limit ) );

    if ( 'following' === $direction ) {
        $sql = $wpdb->prepare( "SELECT follow_id FROM {$table} WHERE user_id = %d ORDER BY follow_id DESC LIMIT %d", (int) $user_id, $limit );
    } else {
        $sql = $wpdb->prepare( "SELECT user_id FROM {$table} WHERE follow_id = %d ORDER BY user_id DESC LIMIT %d", (int) $user_id, $limit );
    }

    return array_values( array_filter( array_map( 'intval', (array) $wpdb->get_col( $sql ) ) ) );
}

function pv_author_is_following( $user_id, $follow_id ) {
    if ( ! $user_id || ! $follow_id || ! pv_author_follow_table_exists() ) { return false; }

    global $wpdb;
    $table = pv_author_follow_table();
    return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM {$table} WHERE user_id = %d AND follow_id = %d LIMIT 1", (int) $user_id, (int) $follow_id ) );
}

function pv_author_enqueue_assets() {
    if ( ! is_author() ) { return; }

    $css = get_stylesheet_directory() . '/assets/css/pv-author.css';
    $js  = get_stylesheet_directory() . '/assets/js/pv-author.js';

    wp_enqueue_style(
        'pv-author',
        get_stylesheet_directory_uri() . '/assets/css/pv-author.css',
        array( 'pv-v7-main' ),
        is_file( $css ) ? (string) filemtime( $css ) : '1'
    );

    wp_enqueue_script(
        'pv-author',
        get_stylesheet_directory_uri() . '/assets/js/pv-author.js',
        array(),
        is_file( $js ) ? (string) filemtime( $js ) : '1',
        true
    );

    wp_localize_script( 'pv-author', 'pvAuthorProfile', array(
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'pv-author-follow' ),
        'loginUrl' => function_exists( 'pv_member_login_url' ) ? pv_member_login_url() : wp_login_url(),
        'follow'   => 'Takip Et',
        'unfollow' => 'Takipten Çık',
        'error'    => 'İşlem tamamlanamadı. Lütfen tekrar deneyin.',
    ) );
}
add_action( 'wp_enqueue_scripts', 'pv_author_enqueue_assets', 1250 );

function pv_author_follow_action() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Oturum açmanız gerekiyor.' ), 401 );
    }
    if ( ! check_ajax_referer( 'pv-author-follow', 'security', false ) ) {
        wp_send_json_error( array( 'message' => 'Güvenlik doğrulaması geçersiz.' ), 403 );
    }
    if ( ! pv_author_follow_table_exists() ) {
        wp_send_json_error( array( 'message' => 'Takip sistemi şu anda kullanılamıyor.' ), 503 );
    }

    $current_id = get_current_user_id();
    $target_id  = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
    $mode       = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';

    if ( ! $target_id || ! get_userdata( $target_id ) ) {
        wp_send_json_error( array( 'message' => 'Kullanıcı bulunamadı.' ), 404 );
    }
    if ( $target_id === $current_id ) {
        wp_send_json_error( array( 'message' => 'Kendinizi takip edemezsiniz.' ), 400 );
    }
    if ( ! in_array( $mode, array( 'follow', 'unfollow' ), true ) ) {
        wp_send_json_error( array( 'message' => 'Geçersiz işlem.' ), 400 );
    }

    global $wpdb;
    $table = pv_author_follow_table();

    if ( 'follow' === $mode ) {
        if ( ! pv_author_is_following( $current_id, $target_id ) ) {
            $inserted = $wpdb->insert(
                $table,
                array( 'user_id' => $current_id, 'follow_id' => $target_id ),
                array( '%d', '%d' )
            );
            if ( false === $inserted ) {
                wp_send_json_error( array( 'message' => 'Takip işlemi kaydedilemedi.' ), 500 );
            }
        }
        $following = true;
    } else {
        $wpdb->delete(
            $table,
            array( 'user_id' => $current_id, 'follow_id' => $target_id ),
            array( '%d', '%d' )
        );
        $following = false;
    }

    wp_send_json_success( array(
        'following'      => $following,
        'follower_count' => pv_author_follow_count( $target_id, 'followers' ),
        'message'        => $following ? 'Takip ediliyor.' : 'Takipten çıkarıldı.',
    ) );
}
add_action( 'wp_ajax_pv_author_follow', 'pv_author_follow_action' );
