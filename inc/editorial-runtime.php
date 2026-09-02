<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Preserve the two legacy homepage editorial flags after BirFinans is removed.
 * Existing meta keys and on/off values stay unchanged, so no content migration
 * is required and the current front-page queries continue to work.
 */
function pv_editorial_add_homepage_meta_box() {
    add_meta_box(
        'pv-homepage-flags',
        'Piyasa Vizyon Anasayfa',
        'pv_editorial_render_homepage_meta_box',
        'post',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes_post', 'pv_editorial_add_homepage_meta_box' );

function pv_editorial_render_homepage_meta_box( $post ) {
    wp_nonce_field( 'pv_editorial_homepage_flags', 'pv_editorial_homepage_nonce' );

    $headline = (string) get_post_meta( $post->ID, 'bf_anasayfa_slider', true );
    $slider   = (string) get_post_meta( $post->ID, 'bf_anasayfa_kayan', true );
    ?>
    <p>
        <label>
            <input type="checkbox" name="pv_bf_anasayfa_slider" value="on" <?php checked( in_array( $headline, array( '1', 'on', 'yes', 'true', 'checked', 'evet', 'Evet', 'EVET' ), true ) ); ?>>
            Manşete eklensin
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="pv_bf_anasayfa_kayan" value="on" <?php checked( in_array( $slider, array( '1', 'on', 'yes', 'true', 'checked', 'evet', 'Evet', 'EVET' ), true ) ); ?>>
            4'lü kayan alanda göster
        </label>
    </p>
    <?php
}

function pv_editorial_save_homepage_flags( $post_id ) {
    if ( ! isset( $_POST['pv_editorial_homepage_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pv_editorial_homepage_nonce'] ) ), 'pv_editorial_homepage_flags' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
    if ( wp_is_post_revision( $post_id ) ) { return; }
    if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

    update_post_meta( $post_id, 'bf_anasayfa_slider', isset( $_POST['pv_bf_anasayfa_slider'] ) ? 'on' : 'off' );
    update_post_meta( $post_id, 'bf_anasayfa_kayan', isset( $_POST['pv_bf_anasayfa_kayan'] ) ? 'on' : 'off' );
}
add_action( 'save_post_post', 'pv_editorial_save_homepage_flags' );
