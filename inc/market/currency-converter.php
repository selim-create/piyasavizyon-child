<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_currency_converter_slug_map() {
    return array(
        'cad' => 'kanada-dolari',
        'aud' => 'avustralya-dolari',
        'usd' => 'dolar',
        'eur' => 'euro',
        'gbp' => 'sterlin',
        'chf' => 'isvicre-frangi',
        'cny' => 'cin-yuani',
        'rub' => 'rus-rublesi',
        'nok' => 'norvec-kronu',
        'jpy' => 'japon-yeni',
        'dkk' => 'danimarka-kronu',
        'pln' => 'polonya-zlotisi',
    );
}

function pv_market_currency_converter_code_from_slug( $slug ) {
    $slug = sanitize_title( (string) $slug );
    foreach ( pv_market_currency_converter_slug_map() as $code => $mapped_slug ) {
        if ( $slug === $mapped_slug || $slug === $code ) {
            return $code;
        }
    }
    return '';
}

function pv_market_currency_converter_page_url() {
    static $url = null;
    if ( $url !== null ) {
        return $url;
    }

    $pages = get_posts( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'doviz-hesapla.php',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );

    $url = $pages ? get_permalink( $pages[0] ) : home_url( '/doviz-hesapla/' );
    return $url;
}

function pv_market_currency_bulk_converter_url() {
    static $url = null;
    if ( $url !== null ) {
        return $url;
    }

    $pages = get_posts( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'doviz-toplu-hesapla.php',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );

    $url = $pages ? get_permalink( $pages[0] ) : home_url( '/doviz-cevirici/' );
    return $url;
}

function pv_market_currency_converter_pretty_url( $amount, $code ) {
    $amount = str_replace( ',', '.', (string) $amount );
    $amount = preg_replace( '/[^0-9.]/', '', $amount );
    if ( $amount === '' || (float) $amount <= 0 ) {
        $amount = '1';
    }

    $code = strtolower( sanitize_key( (string) $code ) );
    $map  = pv_market_currency_converter_slug_map();
    $slug = isset( $map[ $code ] ) ? $map[ $code ] : $code;

    return home_url( '/' . rawurlencode( $amount ) . '-' . $slug . '-ne-kadar/' );
}

function pv_market_currency_converter_number( $value ) {
    $value = pv_market_decode_text( $value );
    $value = str_replace( array( 'TL', '₺', '$', '%', ' ' ), '', $value );
    if ( strpos( $value, ',' ) !== false && strpos( $value, '.' ) !== false ) {
        $value = str_replace( '.', '', $value );
        $value = str_replace( ',', '.', $value );
    } else {
        $value = str_replace( ',', '.', $value );
    }
    $value = preg_replace( '/[^0-9.\-]/', '', $value );
    return is_numeric( $value ) ? (float) $value : 0.0;
}

function pv_market_currency_converter_request() {
    $code   = isset( $_GET['doviz'] ) ? strtolower( sanitize_key( wp_unslash( $_GET['doviz'] ) ) ) : '';
    $amount = isset( $_GET['miktar'] ) ? wp_unslash( $_GET['miktar'] ) : '';

    if ( $code === '' || $amount === '' ) {
        $path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
        if ( is_string( $path ) && preg_match( '~/(\d+(?:[\.,]\d+)?)-([a-z0-9-]+)-ne-kadar/?$~i', $path, $match ) ) {
            $amount = $match[1];
            $code   = pv_market_currency_converter_code_from_slug( $match[2] );
        }
    }

    $amount_number = pv_market_currency_converter_number( $amount );
    if ( $amount_number <= 0 ) {
        $amount_number = 1.0;
    }

    $detail = $code !== '' ? pv_market_currency_resolve_query( $code ) : array();
    if ( empty( $detail['code'] ) ) {
        $detail = pv_market_currency_resolve_query( 'usd' );
    }

    $rate = ! empty( $detail['buying'] ) ? pv_market_currency_converter_number( $detail['buying'] ) : 0.0;

    return array(
        'amount' => $amount_number,
        'detail' => $detail,
        'rate'   => $rate,
        'result' => $rate > 0 ? $amount_number * $rate : 0.0,
    );
}

function pv_market_currency_converter_rows() {
    global $currency_data;
    $rows = array();

    if ( empty( $currency_data['code'] ) || ! is_array( $currency_data['code'] ) ) {
        return $rows;
    }

    foreach ( $currency_data['code'] as $key => $code ) {
        $detail = pv_market_currency_resolve_query( (string) $key );
        if ( empty( $detail['code'] ) ) {
            continue;
        }
        $rows[] = $detail;
    }

    return $rows;
}

function pv_market_currency_converter_rewrite() {
    add_rewrite_rule(
        '^([0-9]+(?:[\.,][0-9]+)?)-([a-z0-9-]+)-ne-kadar/?$',
        'index.php?pagename=doviz-hesapla-ajax',
        'top'
    );
}
add_action( 'init', 'pv_market_currency_converter_rewrite', 20 );

function pv_market_currency_converter_flush_rewrite() {
    pv_market_currency_converter_rewrite();
    flush_rewrite_rules( false );
}
add_action( 'after_switch_theme', 'pv_market_currency_converter_flush_rewrite' );

function pv_market_route_currency_converter_template( $template ) {
    $base = basename( (string) $template );

    if ( $base === 'doviz-hesapla.php' ) {
        return __DIR__ . '/views/doviz-hesapla.php';
    }
    if ( $base === 'doviz-hesapla-ajax.php' ) {
        return __DIR__ . '/views/doviz-hesapla-ajax.php';
    }
    if ( $base === 'doviz-toplu-hesapla.php' ) {
        return __DIR__ . '/views/doviz-toplu-hesapla.php';
    }

    return $template;
}
add_filter( 'template_include', 'pv_market_route_currency_converter_template', 104 );
