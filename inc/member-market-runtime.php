<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_member_market_enqueue() {
    if ( ! is_page_template( 'doviz-detay.php' ) ) { return; }
    $js = get_stylesheet_directory() . '/assets/js/pv-member-market.js';
    wp_enqueue_script( 'pv-member-market', get_stylesheet_directory_uri() . '/assets/js/pv-member-market.js', array( 'jquery' ), is_file( $js ) ? filemtime( $js ) : '1', true );
    wp_localize_script( 'pv-member-market', 'pvMemberMarket', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'pv-member-action' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'pv_member_market_enqueue', 1300 );
