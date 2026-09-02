<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/providers/coingecko.php';
require_once __DIR__ . '/providers/birtema.php';

/**
 * Fetch a raw market payload through the Piyasa Vizyon provider seam.
 *
 * All supported runtime resources now resolve through child-owned providers.
 */
function pv_market_provider_fetch( $resource ) {
    $resource = ltrim( (string) $resource, '/' );
    if ( $resource === '' ) {
        return false;
    }

    $filtered = apply_filters( 'pv_market_provider_response', null, $resource );
    if ( $filtered !== null ) {
        return $filtered;
    }

    if ( $resource === 'coin' && function_exists( 'pv_market_coingecko_fetch' ) ) {
        $coin_data = pv_market_coingecko_fetch();
        if ( ! is_wp_error( $coin_data ) ) {
            if ( ! function_exists( 'pv_market_payload_is_valid' ) || pv_market_payload_is_valid( 'coin', $coin_data ) ) {
                return $coin_data;
            }
        }
        return false;
    }

    if ( in_array( $resource, array( 'currency', 'altin', 'parite' ), true ) ) {
        if ( function_exists( 'pv_market_birtema_fetch' ) ) {
            $market_data = pv_market_birtema_fetch( $resource );
            if ( ! is_wp_error( $market_data ) ) {
                if ( ! function_exists( 'pv_market_payload_is_valid' ) || pv_market_payload_is_valid( $resource, $market_data ) ) {
                    return $market_data;
                }
            }
        }
        return false;
    }

    if ( $resource === 'borsa' && function_exists( 'pv_market_mynet_borsa_summary_fetch' ) ) {
        return pv_market_mynet_borsa_summary_fetch();
    }

    return false;
}

function pv_market_provider_is_legacy_fallback() {
    return false;
}
