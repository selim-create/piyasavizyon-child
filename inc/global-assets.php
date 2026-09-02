<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * The child theme now owns the public frontend styling/auth script chain.
 * Remove the last globally rendered BirFinans assets after both themes have
 * finished enqueueing so parent files are no longer required at runtime.
 */
function pv_child_detach_parent_frontend_assets() {
    foreach ( array( 'mainstyle', 'my-theme-extra-style' ) as $handle ) {
        wp_dequeue_style( $handle );
        wp_deregister_style( $handle );
    }

    wp_dequeue_script( 'validate-script' );
    wp_deregister_script( 'validate-script' );
}
add_action( 'wp_enqueue_scripts', 'pv_child_detach_parent_frontend_assets', PHP_INT_MAX );

/**
 * Purge LiteSpeed once when this asset boundary changes so cached HTML does not
 * keep references to the removed parent-theme files.
 */
function pv_child_maybe_purge_global_asset_cache() {
    $version = '1';
    if ( get_option( 'pv_child_global_asset_version' ) === $version ) {
        return;
    }

    update_option( 'pv_child_global_asset_version', $version, false );

    if ( has_action( 'litespeed_purge_all' ) ) {
        do_action( 'litespeed_purge_all' );
    }
}
add_action( 'init', 'pv_child_maybe_purge_global_asset_cache', 2 );
