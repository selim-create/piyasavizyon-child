<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Keep legacy security settings compatible while making the Theme Control
 * Center the primary admin surface.
 */
function pv_control_center_hide_legacy_security_menu() {
    remove_submenu_page( 'options-general.php', 'pv-registration-security' );
}
add_action( 'admin_menu', 'pv_control_center_hide_legacy_security_menu', 100 );

function pv_control_center_security_save() {
    if ( empty( $_POST['pv_return_to_control_center'] ) ) {
        return;
    }

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

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'    => 'pv-control-center',
                'tab'     => 'security',
                'updated' => '1',
            ),
            admin_url( 'admin.php' )
        )
    );
    exit;
}
add_action( 'admin_post_pv_registration_security_save', 'pv_control_center_security_save', 1 );
