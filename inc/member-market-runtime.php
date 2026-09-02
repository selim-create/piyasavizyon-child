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

function pv_member_market_filter_output( $html ) {
    if ( ! is_string( $html ) || strpos( $html, 'user_api.php' ) === false ) { return $html; }
    $pattern = <<<'REGEX'
~<script>\s*\(function\(\$\)\{\s*if \(!\$\) return;\s*var endpoint = .*?user_api\.php.*?window\.girisYap = function\(\)\{ alert\('Bu özelliği kullanmak için lütfen giriş yapınız\.'\); \};\s*\}\)\(window\.jQuery\);\s*</script>~s
REGEX;
    $replacement = "<script>window.girisYap=function(){alert('Bu özelliği kullanmak için lütfen giriş yapınız.');};</script>";
    return preg_replace( $pattern, $replacement, $html, 1 );
}

function pv_member_market_begin_output_filter() {
    if ( is_page_template( 'doviz-detay.php' ) ) { ob_start( 'pv_member_market_filter_output' ); }
}
add_action( 'template_redirect', 'pv_member_market_begin_output_filter', 1 );
