<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Presentation helpers for header/footer market tickers.
 *
 * Market caches keep their provider-native values for calculations. Formatting
 * is applied only at render time so crypto prices and rates use Turkish number
 * separators without changing the underlying market payloads.
 */
function pv_v7_ticker_display_number( $value, $decimals = 2 ) {
    if ( is_int( $value ) || is_float( $value ) ) {
        $number = (float) $value;
    } else {
        $raw = trim( html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, 'UTF-8' ) );
        $raw = str_replace( array( 'TL', '₺', '$', '%', ' ' ), '', $raw );

        if ( strpos( $raw, ',' ) !== false && strpos( $raw, '.' ) !== false ) {
            $last_comma = strrpos( $raw, ',' );
            $last_dot   = strrpos( $raw, '.' );

            if ( $last_comma > $last_dot ) {
                $raw = str_replace( '.', '', $raw );
                $raw = str_replace( ',', '.', $raw );
            } else {
                $raw = str_replace( ',', '', $raw );
            }
        } elseif ( strpos( $raw, ',' ) !== false ) {
            $raw = str_replace( ',', '.', $raw );
        }

        $raw    = preg_replace( '/[^0-9.\-]/', '', $raw );
        $number = is_numeric( $raw ) ? (float) $raw : 0.0;
    }

    return number_format( $number, max( 0, (int) $decimals ), ',', '.' );
}

function pv_v7_ticker_display_value( $item ) {
    $value = isset( $item['value'] ) ? $item['value'] : '0';
    $type  = isset( $item['type'] ) ? (string) $item['type'] : '';

    if ( $type !== 'coin' ) {
        return (string) $value;
    }

    $numeric = pv_v7_ticker_display_number( $value, 8 );
    $parsed  = (float) str_replace( array( '.', ',' ), array( '', '.' ), $numeric );
    $abs     = abs( $parsed );

    if ( $abs >= 1000 ) {
        $decimals = 0;
    } elseif ( $abs >= 1 ) {
        $decimals = 2;
    } else {
        $decimals = 4;
    }

    return pv_v7_ticker_display_number( $value, $decimals );
}

function pv_v7_ticker_display_rate( $item ) {
    $rate = isset( $item['rate'] ) ? $item['rate'] : '0';
    $type = isset( $item['type'] ) ? (string) $item['type'] : '';

    if ( $type === 'coin' ) {
        return pv_v7_ticker_display_number( $rate, 2 );
    }

    return function_exists( 'pv_v7_num' ) ? pv_v7_num( $rate ) : (string) $rate;
}

/**
 * Preserve older public/legal URLs while footer and legal navigation use the
 * canonical production page slugs.
 */
function pv_v7_legal_legacy_redirects() {
    if ( is_admin() ) {
        return;
    }

    $request_path = isset( $_SERVER['REQUEST_URI'] )
        ? trim( (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ), '/' )
        : '';

    $redirects = array(
        'kullanim-sartlari'       => '/kullanim-kosullari/',
        'kvkk-aydinlatma-metni'   => '/kvkk/',
        'sorumluluk-reddi'        => '/sorumluluk-reddi-beyani/',
    );

    if ( isset( $redirects[ $request_path ] ) ) {
        wp_safe_redirect( home_url( $redirects[ $request_path ] ), 301 );
        exit;
    }
}
add_action( 'template_redirect', 'pv_v7_legal_legacy_redirects', 1 );
