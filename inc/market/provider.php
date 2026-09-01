<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/providers/coingecko.php';
require_once __DIR__ . '/providers/birtema.php';

/**
 * Fetch a raw market payload through the Piyasa Vizyon provider seam.
 *
 * Child-owned providers are preferred resource-by-resource. While BirFinans is
 * still active its get_data_service() function remains the final fallback for
 * resources that have not yet been migrated, and as a temporary failover for
 * crypto if CoinGecko cannot produce a valid payload. Migrated currency does
 * not fall back to the parent transport because the child BirTema transport
 * already targets the same entitled upstream service.
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
    }

    if ( $resource === 'currency' ) {
        if ( function_exists( 'pv_market_birtema_fetch' ) ) {
            $currency_data = pv_market_birtema_fetch( 'currency' );
            if ( ! is_wp_error( $currency_data ) ) {
                if ( ! function_exists( 'pv_market_payload_is_valid' ) || pv_market_payload_is_valid( 'currency', $currency_data ) ) {
                    return $currency_data;
                }
            }
        }

        return false;
    }

    if ( function_exists( 'get_data_service' ) ) {
        return get_data_service( $resource );
    }

    return false;
}

function pv_market_provider_is_legacy_fallback() {
    return function_exists( 'get_data_service' );
}
