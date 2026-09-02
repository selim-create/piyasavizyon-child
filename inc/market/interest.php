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
    $tables = $xpath->query( '//table[contains(concat(" ", normalize-space(@class), " "), " finans-data-table ")]' );
    if ( ! $tables || $tables->length < 3 ) {
        return array();
    }

    $index = array( 'try' => 0, 'usd' => 1, 'eur' => 2 );
    $table = $tables->item( $index[ $type ] );
    if ( ! $table ) {
        return array();
    }

    $rows = array();
    foreach ( $xpath->query( './/tr[td]', $table ) as $tr ) {
        $cells = $xpath->query( './td', $tr );
        if ( ! $cells || $cells->length < 5 ) {
            continue;
        }

        $name = pv_market_decode_text( $cells->item( 0 )->textContent );
        if ( $name === '' ) {
            continue;
        }

        $rows[] = array(
            'bank' => $name,
            'm1'   => pv_market_decode_text( $cells->item( 1 )->textContent ),
            'm3'   => pv_market_decode_text( $cells->item( 2 )->textContent ),
            'm6'   => pv_market_decode_text( $cells->item( 3 )->textContent ),
            'm12'  => pv_market_decode_text( $cells->item( 4 )->textContent ),
        );
    }

    return $rows;
}
