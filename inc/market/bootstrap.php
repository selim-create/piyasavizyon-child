<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/provider.php';

function pv_market_cache_minutes() {
    global $bp_options;

    $minutes = 5;
    if ( is_array( $bp_options ) && ! empty( $bp_options['cache_time'] ) ) {
        $minutes = (int) $bp_options['cache_time'];
    }

    return max( 1, (int) apply_filters( 'pv_market_cache_minutes', $minutes ) );
}

function pv_market_cached_resource( $cache_file, $resource ) {
    static $cache = null;

    if ( ! $cache instanceof PV_Market_Cache ) {
        $cache = new PV_Market_Cache( pv_market_cache_minutes() );
    }

    $data = $cache->get( $cache_file );
    if ( $data !== false ) {
        return $data;
    }

    $data = pv_market_provider_fetch( $resource );
    if ( $data !== false && $data !== null && $data !== array() ) {
        $cache->set( $cache_file, $data );
    }

    return $data;
}

/**
 * Prime the legacy globals from the child-owned compatibility layer.
 *
 * The existing templates still consume these globals, so keeping their exact
 * shape allows us to move the data source without rewriting the UI in the same
 * deployment. Once the globals are populated, the old
 * pv_v7_ensure_market_data() function returns before directly loading parent
 * API files.
 */
function pv_market_prime_legacy_globals() {
    global $currency_data, $coin_data, $altin_data, $bist100_data, $parite_data;
    global $borsa_artanlar_data, $borsa_azalanlar_data, $borsa_islem_gorenler_data;

    if ( empty( $currency_data ) ) {
        $data = pv_market_cached_resource( 'doviz.json', 'currency' );
        if ( is_array( $data ) ) {
            $currency_data = $data;
        }
    }

    if ( empty( $altin_data ) ) {
        $data = pv_market_cached_resource( 'altin.json', 'altin' );
        if ( is_array( $data ) ) {
            $altin_data = $data;
        }
    }

    if ( empty( $parite_data ) ) {
        $data = pv_market_cached_resource( 'parite.json', 'parite' );
        if ( is_array( $data ) ) {
            $parite_data = $data;
        }
    }

    if ( empty( $coin_data ) ) {
        $data = pv_market_cached_resource( 'coin.json', 'coin' );
        if ( is_array( $data ) ) {
            $coin_data = $data;
        }
    }

    if ( empty( $bist100_data ) ) {
        $borsa_data = pv_market_cached_resource( 'borsa.json', 'borsa' );
        if ( is_array( $borsa_data ) ) {
            $bist100_data = isset( $borsa_data['bist_100'] ) ? $borsa_data['bist_100'] : array();
            $borsa_artanlar_data = isset( $borsa_data['borsa_artanlar'] ) ? $borsa_data['borsa_artanlar'] : array();
            $borsa_azalanlar_data = isset( $borsa_data['borsa_azalanlar'] ) ? $borsa_data['borsa_azalanlar'] : array();
            $borsa_islem_gorenler_data = isset( $borsa_data['borsa_islem_gorenler'] ) ? $borsa_data['borsa_islem_gorenler'] : array();
        }
    }
}

// after_setup_theme runs before init/template rendering and after the parent and
// child functions files have been loaded. Priority 1 keeps the compatibility
// data ready before the rest of the child theme begins using it.
add_action( 'after_setup_theme', 'pv_market_prime_legacy_globals', 1 );
