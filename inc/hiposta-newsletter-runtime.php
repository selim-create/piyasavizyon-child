<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Thin theme bridge for the standalone PiyasaVizyon Hiposta plugin.
 * The plugin owns the subscription contract and UI; the theme only decides
 * where the newsletter surfaces are mounted.
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

if ( ! function_exists( 'pv_v7_hiposta_footer_bridge' ) ) {
    function pv_v7_hiposta_footer_bridge() {
        if ( ! function_exists( 'pv_hiposta_render_form' ) ) {
            return;
        }

        $form = pv_hiposta_render_form( 'piyasavizyon_footer', 'footer' );
        if ( ! is_string( $form ) || trim( $form ) === '' ) {
            return;
        }
        ?>
        <template id="pvHipostaFooterTemplate"><?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted renderer output. ?></template>
        <script>
        (function () {
            var target = document.querySelector('.pv-footer-newsletter-card');
            var template = document.getElementById('pvHipostaFooterTemplate');
            if (!target || !template || !template.content) return;
            target.replaceChildren(template.content.cloneNode(true));
            target.classList.add('pv-footer-newsletter-card--hiposta');
            template.remove();
        })();
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'pv_v7_hiposta_footer_bridge', 5 );
