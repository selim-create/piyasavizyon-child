<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Thin compatibility layer for the standalone PiyasaVizyon Hiposta plugin.
 * Newsletter surfaces now render the plugin output directly in templates.
 */

if ( ! function_exists( 'pv_v7_hiposta_enqueue_assets' ) ) {
    function pv_v7_hiposta_enqueue_assets() {
        if ( ! function_exists( 'pv_hiposta_render_form' ) ) {
            return;
        }

        if ( wp_style_is( 'pv-hiposta-newsletter', 'registered' ) ) {
            wp_enqueue_style( 'pv-hiposta-newsletter' );
        }
        if ( wp_script_is( 'pv-hiposta-newsletter', 'registered' ) ) {
            wp_enqueue_script( 'pv-hiposta-newsletter' );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'pv_v7_hiposta_enqueue_assets', 20 );
