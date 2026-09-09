<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/providers/coingecko.php';
require_once __DIR__ . '/providers/birtema.php';

/**
 * Short failure backoff prevents a temporary upstream outage from turning every
 * cache miss into another slow remote request. This is intentionally brief so
 * recovery remains fast while PHP workers are protected from retry storms.
 */
function pv_market_provider_backoff_seconds( $resource ) {
    return max( 15, (int) apply_filters( 'pv_market_provider_backoff_seconds', 60, $resource ) );
}

function pv_market_provider_backoff_key( $resource ) {
    return 'pv_market_provider_fail_' . sanitize_key( (string) $resource );
}

function pv_market_provider_is_backing_off( $resource ) {
    return (bool) get_transient( pv_market_provider_backoff_key( $resource ) );
}

function pv_market_provider_mark_failure( $resource ) {
    set_transient(
        pv_market_provider_backoff_key( $resource ),
        1,
        pv_market_provider_backoff_seconds( $resource )
    );
}

function pv_market_provider_clear_failure( $resource ) {
    delete_transient( pv_market_provider_backoff_key( $resource ) );
}

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

    if ( pv_market_provider_is_backing_off( $resource ) ) {
        return false;
    }

    if ( $resource === 'coin' && function_exists( 'pv_market_coingecko_fetch' ) ) {
        $coin_data = pv_market_coingecko_fetch();
        if ( ! is_wp_error( $coin_data ) ) {
            if ( ! function_exists( 'pv_market_payload_is_valid' ) || pv_market_payload_is_valid( 'coin', $coin_data ) ) {
                pv_market_provider_clear_failure( $resource );
                return $coin_data;
            }
        }
        pv_market_provider_mark_failure( $resource );
        return false;
    }

    if ( in_array( $resource, array( 'currency', 'altin', 'parite' ), true ) ) {
        if ( function_exists( 'pv_market_birtema_fetch' ) ) {
            $market_data = pv_market_birtema_fetch( $resource );
            if ( ! is_wp_error( $market_data ) ) {
                if ( ! function_exists( 'pv_market_payload_is_valid' ) || pv_market_payload_is_valid( $resource, $market_data ) ) {
                    pv_market_provider_clear_failure( $resource );
                    return $market_data;
                }
            }
        }
        pv_market_provider_mark_failure( $resource );
        return false;
    }

    if ( $resource === 'borsa' && function_exists( 'pv_market_mynet_borsa_summary_fetch' ) ) {
        $borsa_data = pv_market_mynet_borsa_summary_fetch();
        if ( ! function_exists( 'pv_market_payload_is_valid' ) || pv_market_payload_is_valid( 'borsa', $borsa_data ) ) {
            pv_market_provider_clear_failure( $resource );
            return $borsa_data;
        }
        pv_market_provider_mark_failure( $resource );
        return false;
    }

    return false;
}

function pv_market_provider_is_legacy_fallback() {
    return false;
}
