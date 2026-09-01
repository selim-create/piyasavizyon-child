<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Stable market payload contracts used by the child theme.
 *
 * These contracts describe the legacy shapes currently consumed by Piyasa
 * Vizyon templates. New providers must normalize their responses into these
 * shapes before data is accepted into the child-owned cache.
 */
function pv_market_contract_required_fields( $resource ) {
    $contracts = array(
        'currency' => array( 'change_rate', 'selling', 'buying', 'code', 'time', 'full_name' ),
        'altin'    => array( 'altin_price', 'altin_price_buying', 'altin_price_selling', 'altin_update', 'altin_time', 'altin_rate', 'altin_name', 'altin_key', 'altin_full_name' ),
        'parite'   => array( 'code', 'full_name', 'buying', 'selling', 'change_rate', 'time' ),
        'coin'     => array( 'symbol', 'name', 'current_price', 'price_24h', 'last_updated', 'change_rate', 'suply', 'image' ),
        'borsa'    => array( 'bist_100', 'borsa_artanlar', 'borsa_azalanlar', 'borsa_islem_gorenler' ),
    );

    $resource = ltrim( (string) $resource, '/' );
    return isset( $contracts[ $resource ] ) ? $contracts[ $resource ] : array();
}

function pv_market_payload_is_valid( $resource, $data ) {
    if ( ! is_array( $data ) || $data === array() ) {
        return false;
    }

    $required = pv_market_contract_required_fields( $resource );
    if ( $required === array() ) {
        return false;
    }

    foreach ( $required as $field ) {
        if ( ! array_key_exists( $field, $data ) || ! is_array( $data[ $field ] ) ) {
            return false;
        }
    }

    // A structurally correct payload is still unusable if every required field
    // is empty. This is especially important for borsa: the unavailable legacy
    // provider currently returns the full shape with empty inner arrays, which
    // must not be cached or treated as real market data.
    foreach ( $required as $field ) {
        if ( ! empty( $data[ $field ] ) ) {
            return true;
        }
    }

    return false;
}
