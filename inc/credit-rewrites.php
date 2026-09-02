<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_credit_register_pretty_rewrites() {
    $routes = array(
        'ihtiyac-kredisi' => 'ihtiyac-kredisi',
        'konut-kredisi'   => 'konut-kredisi',
        'tasit-kredisi'   => 'tasit-kredisi',
        'kobi-kredisi'    => 'kobi-kredisi',
    );

    foreach ( $routes as $prefix => $page_slug ) {
        add_rewrite_rule(
            '^' . preg_quote( $prefix, '~' ) . '/([0-9]+)-ay-([0-9]+)-tl-kredi/?$',
            'index.php?pagename=' . $page_slug,
            'top'
        );
    }
}
add_action( 'init', 'pv_credit_register_pretty_rewrites', 20 );

function pv_credit_maybe_flush_pretty_rewrites() {
    $version = '1';
    if ( get_option( 'pv_credit_rewrite_version' ) === $version ) {
        return;
    }

    pv_credit_register_pretty_rewrites();
    flush_rewrite_rules( false );
    update_option( 'pv_credit_rewrite_version', $version, false );
}
add_action( 'init', 'pv_credit_maybe_flush_pretty_rewrites', 99 );
