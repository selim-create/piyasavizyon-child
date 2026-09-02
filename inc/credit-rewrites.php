<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_credit_pretty_rule_map() {
    return array(
        'ihtiyac-kredisi/([0-9]+)-ay-([0-9]+)-tl-kredi/?$' => 'index.php?pagename=ihtiyac-kredisi',
        'konut-kredisi/([0-9]+)-ay-([0-9]+)-tl-kredi/?$'   => 'index.php?pagename=konut-kredisi',
        'tasit-kredisi/([0-9]+)-ay-([0-9]+)-tl-kredi/?$'   => 'index.php?pagename=tasit-kredisi',
        'kobi-kredisi/([0-9]+)-ay-([0-9]+)-tl-kredi/?$'    => 'index.php?pagename=kobi-kredisi',
    );
}

function pv_credit_register_pretty_rewrites() {
    foreach ( pv_credit_pretty_rule_map() as $regex => $target ) {
        add_rewrite_rule( $regex, $target, 'top' );
    }
}
add_action( 'init', 'pv_credit_register_pretty_rewrites', 20 );

/**
 * Keep the child-owned numeric credit rules in the persisted rewrite array even
 * when the legacy parent theme injects broader credit patterns later.
 */
function pv_credit_prepend_persisted_pretty_rewrites( $rules ) {
    if ( ! is_array( $rules ) ) {
        $rules = array();
    }

    return pv_credit_pretty_rule_map() + $rules;
}
add_filter( 'rewrite_rules_array', 'pv_credit_prepend_persisted_pretty_rewrites', PHP_INT_MAX );

function pv_credit_maybe_flush_pretty_rewrites() {
    $version = '2';
    if ( get_option( 'pv_credit_rewrite_version' ) === $version ) {
        return;
    }

    pv_credit_register_pretty_rewrites();
    flush_rewrite_rules( false );
    update_option( 'pv_credit_rewrite_version', $version, false );
}
add_action( 'init', 'pv_credit_maybe_flush_pretty_rewrites', 99 );
