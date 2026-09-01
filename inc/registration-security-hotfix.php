<?php
/**
 * Production hotfixes for the legacy BirFinans registration template.
 *
 * The legacy login.php template calls wp_head() but never calls wp_footer().
 * Therefore the hardened auth script and Turnstile API must be printed in the
 * document head. The legacy registration flow also historically worked
 * independently from WordPress' users_can_register option, so keep that
 * compatibility only for the dedicated AJAX registration action.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Preserve the existing public registration behaviour for the custom AJAX form
 * without enabling WordPress' generic registration endpoint site-wide.
 */
function pv_registration_hotfix_allow_custom_ajax( $value ) {
    if ( wp_doing_ajax() ) {
        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
        if ( $action === 'ajaxregister' ) {
            return '1';
        }
    }

    return $value;
}
add_filter( 'option_users_can_register', 'pv_registration_hotfix_allow_custom_ajax', 9999 );

/**
 * login.php does not execute wp_footer(), so footer scripts never run there.
 * Re-register both Turnstile and the hardened auth script for wp_head().
 */
function pv_registration_hotfix_force_head_auth_script() {
    if ( is_user_logged_in() ) {
        return;
    }

    if ( ! wp_script_is( 'ajax-auth-script', 'registered' ) && ! wp_script_is( 'ajax-auth-script', 'enqueued' ) ) {
        return;
    }

    $dependencies      = array( 'jquery' );
    $turnstile_enabled = function_exists( 'pv_registration_turnstile_enabled' ) && pv_registration_turnstile_enabled();

    if ( $turnstile_enabled ) {
        // The main security module originally registers Turnstile for the footer.
        // The legacy login template has no wp_footer(), so move it to the head.
        wp_dequeue_script( 'pv-turnstile' );
        wp_deregister_script( 'pv-turnstile' );
        wp_register_script(
            'pv-turnstile',
            'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
            array(),
            null,
            false
        );
        wp_enqueue_script( 'pv-turnstile' );
        $dependencies[] = 'pv-turnstile';
    }

    wp_dequeue_script( 'ajax-auth-script' );
    wp_deregister_script( 'ajax-auth-script' );

    $script_path = get_stylesheet_directory() . '/assets/js/pv-auth-security.js';
    $version     = is_file( $script_path ) ? (string) filemtime( $script_path ) : '1.0.2';

    // false => print in wp_head(), because the legacy template has no wp_footer().
    wp_register_script(
        'ajax-auth-script',
        get_stylesheet_directory_uri() . '/assets/js/pv-auth-security.js',
        $dependencies,
        $version,
        false
    );
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
            'turnstileEnabled' => $turnstile_enabled,
            'turnstileSiteKey' => function_exists( 'pv_registration_security_option' ) ? (string) pv_registration_security_option( 'site_key', '' ) : '',
            'turnstileMessage' => __( 'Lütfen güvenlik doğrulamasını tamamlayın.', 'piyasavizyon-v7' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'pv_registration_hotfix_force_head_auth_script', 1001 );
