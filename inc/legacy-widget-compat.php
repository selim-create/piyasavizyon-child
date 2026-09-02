<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Minimal compatibility widgets for the two BirFinans widget id_bases that are
 * still assigned to active Piyasa Vizyon ad sidebars in production.
 *
 * Keeping the original id_base values means WordPress continues to read the
 * existing widget_html_sidebar and widget_anasayfa_reklam option rows without
 * a destructive migration during the standalone cutover.
 */

if ( ! class_exists( 'PV_Legacy_HTML_Sidebar_Widget' ) ) {
    class PV_Legacy_HTML_Sidebar_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct(
                'html_sidebar',
                'PV Legacy HTML',
                array( 'description' => 'Standalone compatibility renderer for existing HTML sidebar instances.' )
            );
        }

        public function widget( $args, $instance ) {
            $html = isset( $instance['html'] ) ? (string) $instance['html'] : '';
            if ( $html === '' ) { return; }

            echo isset( $args['before_widget'] ) ? $args['before_widget'] : '';
            echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted admin-managed ad markup.
            echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';
        }

        public function form( $instance ) {
            $html = isset( $instance['html'] ) ? (string) $instance['html'] : '';
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'html' ) ); ?>">HTML</label>
                <textarea class="widefat" rows="10" id="<?php echo esc_attr( $this->get_field_id( 'html' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'html' ) ); ?>"><?php echo esc_textarea( $html ); ?></textarea>
            </p>
            <?php
        }

        public function update( $new_instance, $old_instance ) {
            $instance = is_array( $old_instance ) ? $old_instance : array();
            $html     = isset( $new_instance['html'] ) ? (string) $new_instance['html'] : '';
            $instance['html'] = current_user_can( 'unfiltered_html' ) ? $html : wp_kses_post( $html );
            return $instance;
        }
    }
}

if ( ! class_exists( 'PV_Legacy_Home_Ad_Widget' ) ) {
    class PV_Legacy_Home_Ad_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct(
                'anasayfa_reklam',
                'PV Legacy Reklam',
                array( 'description' => 'Standalone compatibility renderer for existing homepage ad instances.' )
            );
        }

        public function widget( $args, $instance ) {
            $code = isset( $instance['code'] ) ? (string) $instance['code'] : '';
            if ( $code === '' ) { return; }

            echo isset( $args['before_widget'] ) ? $args['before_widget'] : '';
            echo $code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted admin-managed ad markup.
            echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';
        }

        public function form( $instance ) {
            $code = isset( $instance['code'] ) ? (string) $instance['code'] : '';
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'code' ) ); ?>">Reklam HTML</label>
                <textarea class="widefat" rows="10" id="<?php echo esc_attr( $this->get_field_id( 'code' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'code' ) ); ?>"><?php echo esc_textarea( $code ); ?></textarea>
            </p>
            <?php
        }

        public function update( $new_instance, $old_instance ) {
            $instance = is_array( $old_instance ) ? $old_instance : array();
            $code     = isset( $new_instance['code'] ) ? (string) $new_instance['code'] : '';
            $instance['code'] = current_user_can( 'unfiltered_html' ) ? $code : wp_kses_post( $code );
            return $instance;
        }
    }
}

function pv_register_legacy_widget_compat() {
    register_widget( 'PV_Legacy_HTML_Sidebar_Widget' );
    register_widget( 'PV_Legacy_Home_Ad_Widget' );
}
add_action( 'widgets_init', 'pv_register_legacy_widget_compat', 5 );
