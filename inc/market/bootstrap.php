<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/contracts.php';
require_once __DIR__ . '/provider.php';

function pv_market_cache_minutes() {
    global $bp_options;

    $minutes = 5;
    if ( is_array( $bp_options ) && ! empty( $bp_options['cache_time'] ) ) {
        $minutes = (int) $bp_options['cache_time'];
    }

    return max( 1, (int) apply_filters( 'pv_market_cache_minutes', $minutes ) );
}

function pv_market_resource_for_cache_file( $cache_file ) {
    $map = array(
        'doviz.json'  => 'currency',
        'altin.json'  => 'altin',
        'parite.json' => 'parite',
        'coin.json'   => 'coin',
        'borsa.json'  => 'borsa',
    );

    return isset( $map[ $cache_file ] ) ? $map[ $cache_file ] : '';
}

function pv_market_cached_resource( $cache_file, $resource ) {
    static $cache = null;

    if ( ! $cache instanceof PV_Market_Cache ) {
        $cache = new PV_Market_Cache( pv_market_cache_minutes() );
    }

    // Migrated resources must not silently repopulate from the BirFinans cache
    // namespace. Crypto is the first migrated resource, so a missing/expired
    // child coin cache must proceed to the CoinGecko provider instead.
    $data = ( $resource === 'coin' && method_exists( $cache, 'get_current' ) )
        ? $cache->get_current( $cache_file )
        : $cache->get( $cache_file );

    if ( $data !== false && pv_market_payload_is_valid( $resource, $data ) ) {
        return $data;
    }

    $data = pv_market_provider_fetch( $resource );
    if ( pv_market_payload_is_valid( $resource, $data ) ) {
        $cache->set( $cache_file, $data );
        return $data;
    }

    return false;
}

function pv_market_seed_existing_payload( $cache_file, $data ) {
    $resource = pv_market_resource_for_cache_file( $cache_file );
    if ( $resource === '' || ! pv_market_payload_is_valid( $resource, $data ) ) {
        return false;
    }

    $cache = new PV_Market_Cache( pv_market_cache_minutes() );
    return $cache->set( $cache_file, $data );
}

/**
 * Prime the legacy globals from the child-owned compatibility layer.
 *
 * BirFinans can populate several market globals before the child theme reaches
 * after_setup_theme. During migration we still mirror untouched legacy payloads
 * for currency, gold and parity. Crypto is now child-provider-owned: it always
 * reads the child cache/provider path so the parent can no longer refresh and
 * pin the legacy coin payload indefinitely.
 */
function pv_market_prime_legacy_globals() {
    global $currency_data, $coin_data, $altin_data, $bist100_data, $parite_data, $borsa_data;
    global $borsa_artanlar_data, $borsa_azalanlar_data, $borsa_islem_gorenler_data;

    if ( ! empty( $currency_data ) && is_array( $currency_data ) ) {
        pv_market_seed_existing_payload( 'doviz.json', $currency_data );
    } else {
        $data = pv_market_cached_resource( 'doviz.json', 'currency' );
        if ( is_array( $data ) ) {
            $currency_data = $data;
        }
    }

    if ( ! empty( $altin_data ) && is_array( $altin_data ) ) {
        pv_market_seed_existing_payload( 'altin.json', $altin_data );
    } else {
        $data = pv_market_cached_resource( 'altin.json', 'altin' );
        if ( is_array( $data ) ) {
            $altin_data = $data;
        }
    }

    if ( ! empty( $parite_data ) && is_array( $parite_data ) ) {
        pv_market_seed_existing_payload( 'parite.json', $parite_data );
    } else {
        $data = pv_market_cached_resource( 'parite.json', 'parite' );
        if ( is_array( $data ) ) {
            $parite_data = $data;
        }
    }

    // Crypto has completed the first provider migration. Do not mirror an
    // already-populated parent $coin_data back into the child cache; otherwise
    // its timestamp would be reset on every request and CoinGecko would never
    // become authoritative. A fresh child cache is used until TTL expiry, then
    // pv_market_provider_fetch('coin') requests CoinGecko first and falls back
    // to BirFinans only if the child provider cannot produce a valid payload.
    $data = pv_market_cached_resource( 'coin.json', 'coin' );
    if ( is_array( $data ) ) {
        $coin_data = $data;
    }

    if ( ! empty( $borsa_data ) && is_array( $borsa_data ) ) {
        pv_market_seed_existing_payload( 'borsa.json', $borsa_data );

        $bist100_data = isset( $borsa_data['bist_100'] ) ? $borsa_data['bist_100'] : $bist100_data;
        $borsa_artanlar_data = isset( $borsa_data['borsa_artanlar'] ) ? $borsa_data['borsa_artanlar'] : $borsa_artanlar_data;
        $borsa_azalanlar_data = isset( $borsa_data['borsa_azalanlar'] ) ? $borsa_data['borsa_azalanlar'] : $borsa_azalanlar_data;
        $borsa_islem_gorenler_data = isset( $borsa_data['borsa_islem_gorenler'] ) ? $borsa_data['borsa_islem_gorenler'] : $borsa_islem_gorenler_data;
    } elseif ( empty( $bist100_data ) ) {
        $data = pv_market_cached_resource( 'borsa.json', 'borsa' );
        if ( is_array( $data ) && $data !== array() ) {
            $borsa_data = $data;
            $bist100_data = isset( $data['bist_100'] ) ? $data['bist_100'] : array();
            $borsa_artanlar_data = isset( $data['borsa_artanlar'] ) ? $data['borsa_artanlar'] : array();
            $borsa_azalanlar_data = isset( $data['borsa_azalanlar'] ) ? $data['borsa_azalanlar'] : array();
            $borsa_islem_gorenler_data = isset( $data['borsa_islem_gorenler'] ) ? $data['borsa_islem_gorenler'] : array();
        }
    }

    /**
     * Temporary runtime guard for the historical pv_v7_ensure_market_data().
     *
     * That legacy helper decides whether to load BirFinans DataCache.php and
     * api_helper.php by checking whether currency, gold and BIST globals are
     * non-empty. The current BirFinans borsa provider returns an empty payload,
     * which would otherwise force the old parent API bootstrap even after the
     * child-owned cache has successfully supplied every usable core dataset.
     *
     * Once currency, gold, parity and crypto are all ready, expose an explicit
     * non-cacheable "unavailable" BIST marker. Existing rendering already falls
     * back to 0/0 for missing BIST values, so this does not invent market data;
     * it only prevents the obsolete parent cache/bootstrap path from running.
     * The marker is never written to the child market cache.
     */
    $core_market_ready =
        ! empty( $currency_data ) && is_array( $currency_data ) &&
        ! empty( $altin_data ) && is_array( $altin_data ) &&
        ! empty( $parite_data ) && is_array( $parite_data ) &&
        ! empty( $coin_data ) && is_array( $coin_data );

    if ( $core_market_ready && empty( $bist100_data ) ) {
        $bist100_data = array(
            '_pv_unavailable' => true,
            'value'           => '0',
            'change_rate'     => '0',
        );
    }
}

// after_setup_theme runs after parent and child functions files are loaded. This
// is the earliest safe point to mirror any parent-populated globals into the
// child-owned cache without changing the visible market payloads.
add_action( 'after_setup_theme', 'pv_market_prime_legacy_globals', 1 );
