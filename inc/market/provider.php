<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Fetch a raw market payload through the Piyasa Vizyon provider seam.
 *
 * A custom provider can short-circuit this function with the
 * `pv_market_provider_response` filter. While BirFinans is still active we
 * intentionally keep its get_data_service() function as the final fallback so
 * production payload shapes do not change during Phase 2A.
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

    if ( function_exists( 'get_data_service' ) ) {
        return get_data_service( $resource );
    }

    return false;
}

function pv_market_provider_is_legacy_fallback() {
    return function_exists( 'get_data_service' );
}
