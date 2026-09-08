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

if ( ! function_exists( 'pv_v7_newsletter_page_polish' ) ) {
    function pv_v7_newsletter_page_polish() {
        if ( ! is_page_template( 'page-bulten.php' ) || ! wp_style_is( 'pv-corporate-v252', 'enqueued' ) ) {
            return;
        }

        $css = '
        .pv-corp-newsletter-page .pv-corp-newsletter-hero{padding:34px 38px!important;border-radius:26px!important;margin-bottom:20px!important}
        .pv-corp-newsletter-page .pv-corp-newsletter-hero:before{width:340px!important;height:340px!important;opacity:.72!important}
        .pv-corp-newsletter-page .pv-corp-newsletter-hero:after{width:250px!important;height:250px!important;right:-85px!important;bottom:-130px!important}
        .pv-corp-newsletter-page .pv-corp-newsletter-hero h1{max-width:760px!important;font-size:clamp(34px,4.4vw,56px)!important;line-height:1!important;letter-spacing:-2.2px!important;margin-bottom:10px!important}
        .pv-corp-newsletter-page .pv-corp-newsletter-hero>p{max-width:720px!important;font-size:15px!important;line-height:1.6!important}
        .pv-corp-newsletter-page .pv-corp-hiposta-newsletter{margin-top:18px!important;max-width:690px!important}
        .pv-corp-newsletter-page .pv-corp-hiposta-newsletter .pv-hiposta--page{max-width:690px!important}
        .pv-corp-newsletter-page .pv-corp-feature{padding:18px!important}
        .pv-corp-newsletter-page .pv-corp-icon{width:40px!important;height:40px!important;border-radius:12px!important;background:#eef4ff!important;color:#0758c9!important;margin-bottom:13px!important}
        .pv-corp-newsletter-page .pv-corp-icon svg{width:21px!important;height:21px!important;stroke:currentColor!important;stroke-width:1.8!important;stroke-linecap:round!important;stroke-linejoin:round!important}
        @media(max-width:700px){
          .pv-corp-newsletter-page .pv-corp-newsletter-hero{padding:26px 20px!important;border-radius:22px!important}
          .pv-corp-newsletter-page .pv-corp-newsletter-hero h1{font-size:36px!important;letter-spacing:-1.6px!important}
          .pv-corp-newsletter-page .pv-corp-hiposta-newsletter{margin-top:15px!important}
        }';

        wp_add_inline_style( 'pv-corporate-v252', $css );
    }
}
add_action( 'wp_enqueue_scripts', 'pv_v7_newsletter_page_polish', 40 );
