<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Resolve a WordPress user ID from the values accepted by get_avatar().
 */
function pv_member_avatar_user_id( $id_or_email ) {
    if ( is_numeric( $id_or_email ) ) {
        return (int) $id_or_email;
    }

    if ( $id_or_email instanceof WP_User ) {
        return (int) $id_or_email->ID;
    }

    if ( $id_or_email instanceof WP_Post ) {
        return (int) $id_or_email->post_author;
    }

    if ( $id_or_email instanceof WP_Comment ) {
        if ( ! empty( $id_or_email->user_id ) ) {
            return (int) $id_or_email->user_id;
        }
        $id_or_email = (string) $id_or_email->comment_author_email;
    }

    if ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        return $user instanceof WP_User ? (int) $user->ID : 0;
    }

    return 0;
}

/**
 * Make the child-owned uploaded profile photo the canonical frontend avatar.
 * WordPress/Gravatar remains the fallback whenever no custom photo is stored.
 */
function pv_member_filter_avatar_data( $args, $id_or_email ) {
    $user_id = pv_member_avatar_user_id( $id_or_email );
    if ( $user_id <= 0 ) {
        return $args;
    }

    $url = esc_url_raw( (string) get_user_meta( $user_id, 'profil_pic_url', true ) );
    if ( $url === '' ) {
        return $args;
    }

    $args['url'] = $url;
    $args['found_avatar'] = true;
    return $args;
}
add_filter( 'get_avatar_data', 'pv_member_filter_avatar_data', 20, 2 );
