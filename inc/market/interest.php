<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_interest_type( $type ) {
    $type = sanitize_key( (string) $type );
    return in_array( $type, array( 'try', 'usd', 'eur' ), true ) ? $type : 'try';
}

function pv_market_interest_fetch_html() {
    $cache_key = 'pv_market_interest_html';
    $cached = get_transient( $cache_key );
    if ( is_string( $cached ) && $cached !== '' ) {
        return $cached;
    }

    $html = pv_market_fetch_mynet( '/faiz/' );
    if ( $html !== '' ) {
        set_transient( $cache_key, $html, 10 * MINUTE_IN_SECONDS );
    }
    return $html;
}

function pv_market_interest_rows( $type = 'try' ) {
    $type = pv_market_interest_type( $type );
    $html = pv_market_interest_fetch_html();
    if ( $html === '' || ! class_exists( 'DOMDocument' ) ) {
        return array();
    }

    $previous = libxml_use_internal_errors( true );
    $dom = new DOMDocument();
    $loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous );
    if ( ! $loaded ) {
        return array();
    }

    $xpath = new DOMXPath( $dom );
    $bodies = $xpath->query( '//tbody[contains(concat(" ", normalize-space(@class), " "), " tbody-type-default ")]' );
    if ( ! $bodies || $bodies->length < 1 ) {
        return array();
    }

    $index = array( 'try' => 0, 'usd' => 1, 'eur' => 2 );
    $body = $bodies->item( $index[ $type ] );
    if ( ! $body ) {
        return array();
    }

    $rows = array();
    foreach ( $xpath->query( './tr', $body ) as $tr ) {
        $name_node = $xpath->query( './/span[contains(concat(" ", normalize-space(@class), " "), " mr-2 ")]', $tr )->item( 0 );
        $cells = $xpath->query( './td[contains(concat(" ", normalize-space(@class), " "), " text-center ")]', $tr );
        if ( ! $name_node || ! $cells || $cells->length < 1 ) {
            continue;
        }

        $values = array();
        foreach ( $cells as $cell ) {
            $values[] = pv_market_decode_text( $cell->textContent );
        }

        $name = pv_market_decode_text( $name_node->textContent );
        if ( $name === '' ) {
            continue;
        }

        $rows[] = array(
            'bank' => $name,
            'm1'   => isset( $values[0] ) ? $values[0] : '',
            'm3'   => isset( $values[1] ) ? $values[1] : '',
            'm6'   => isset( $values[2] ) ? $values[2] : '',
            'm12'  => isset( $values[3] ) ? $values[3] : '',
        );
    }

    return $rows;
}
