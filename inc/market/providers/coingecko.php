<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Fetch top crypto assets from CoinGecko and normalize them into the legacy
 * Piyasa Vizyon coin payload contract.
 */
function pv_market_coingecko_fetch() {
    $url = add_query_arg(
        array(
            'vs_currency'              => 'try',
            'order'                    => 'market_cap_desc',
            'per_page'                 => 100,
            'page'                     => 1,
            'sparkline'                => 'false',
            'price_change_percentage'  => '24h',
        ),
        'https://api.coingecko.com/api/v3/coins/markets'
    );

    $response = wp_remote_get(
        $url,
        array(
            'timeout'     => 20,
            'redirection' => 3,
            'headers'     => array(
                'Accept'     => 'application/json',
                'User-Agent' => 'PiyasaVizyon/1.0; ' . home_url('/'),
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    if ( $status < 200 || $status >= 300 ) {
        return new WP_Error( 'pv_coingecko_http', 'CoinGecko HTTP status: ' . $status );
    }

    $rows = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! is_array( $rows ) || $rows === array() ) {
        return new WP_Error( 'pv_coingecko_payload', 'CoinGecko payload is empty or invalid.' );
    }

    $payload = array(
        'symbol'        => array(),
        'name'          => array(),
        'current_price' => array(),
        'price_24h'     => array(),
        'last_updated'  => array(),
        'change_rate'   => array(),
        'suply'         => array(),
        'image'         => array(),
    );

    foreach ( array_slice( $rows, 0, 100 ) as $index => $row ) {
        if ( ! is_array( $row ) || empty( $row['name'] ) || empty( $row['symbol'] ) ) {
            continue;
        }

        $change_24h = isset( $row['price_change_percentage_24h'] ) && is_numeric( $row['price_change_percentage_24h'] )
            ? (float) $row['price_change_percentage_24h']
            : 0.0;

        $ath_change = isset( $row['ath_change_percentage'] ) && is_numeric( $row['ath_change_percentage'] )
            ? (float) $row['ath_change_percentage']
            : 0.0;

        $supply = isset( $row['circulating_supply'] ) && is_numeric( $row['circulating_supply'] )
            ? (float) $row['circulating_supply']
            : 0.0;

        $last_updated = '';
        if ( ! empty( $row['last_updated'] ) ) {
            $timestamp = strtotime( (string) $row['last_updated'] );
            if ( $timestamp !== false ) {
                $last_updated = gmdate( 'H:i', $timestamp );
            }
        }

        $payload['symbol'][ $index ]        = strtoupper( sanitize_text_field( (string) $row['symbol'] ) );
        $payload['name'][ $index ]          = sanitize_text_field( (string) $row['name'] );
        $payload['current_price'][ $index ] = isset( $row['current_price'] ) && is_numeric( $row['current_price'] ) ? (float) $row['current_price'] : 0.0;
        $payload['price_24h'][ $index ]     = $change_24h;
        $payload['last_updated'][ $index ]  = $last_updated;
        $payload['change_rate'][ $index ]   = $ath_change;
        $payload['suply'][ $index ]         = $supply;
        $payload['image'][ $index ]         = ! empty( $row['image'] ) ? esc_url_raw( (string) $row['image'] ) : '';
    }

    if ( function_exists( 'pv_market_payload_is_valid' ) && ! pv_market_payload_is_valid( 'coin', $payload ) ) {
        return new WP_Error( 'pv_coingecko_contract', 'Normalized CoinGecko payload failed the coin contract.' );
    }

    return $payload;
}

function pv_market_coingecko_id( $slug, $symbol = '' ) {
    $slug = sanitize_title( (string) $slug );
    $symbol = strtolower( sanitize_key( (string) $symbol ) );
    $map = array(
        'btc' => 'bitcoin', 'bitcoin' => 'bitcoin',
        'eth' => 'ethereum', 'ethereum' => 'ethereum',
        'xrp' => 'ripple', 'ripple' => 'ripple',
        'bch' => 'bitcoin-cash', 'bitcoin-cash' => 'bitcoin-cash',
        'ltc' => 'litecoin', 'litecoin' => 'litecoin',
        'bnb' => 'binancecoin', 'binancecoin' => 'binancecoin',
        'sol' => 'solana', 'solana' => 'solana',
        'doge' => 'dogecoin', 'dogecoin' => 'dogecoin',
        'avax' => 'avalanche-2', 'avalanche' => 'avalanche-2', 'avalanche-2' => 'avalanche-2',
        'ada' => 'cardano', 'cardano' => 'cardano',
        'trx' => 'tron', 'tron' => 'tron',
        'xlm' => 'stellar', 'stellar' => 'stellar',
        'xmr' => 'monero', 'monero' => 'monero',
        'fil' => 'filecoin', 'filecoin' => 'filecoin',
        'usdt' => 'tether', 'tether' => 'tether',
    );
    if ( isset( $map[ $symbol ] ) ) {
        return $map[ $symbol ];
    }
    return isset( $map[ $slug ] ) ? $map[ $slug ] : $slug;
}

function pv_market_coingecko_chart( $slug, $symbol = '' ) {
    $id = pv_market_coingecko_id( $slug, $symbol );
    if ( $id === '' ) {
        return array();
    }

    $cache_key = 'pv_coingecko_chart_' . md5( $id );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) && $cached !== array() ) {
        return $cached;
    }

    $url = add_query_arg(
        array( 'vs_currency' => 'usd', 'days' => 1 ),
        'https://api.coingecko.com/api/v3/coins/' . rawurlencode( $id ) . '/market_chart'
    );
    $response = wp_safe_remote_get(
        $url,
        array(
            'timeout'     => 20,
            'redirection' => 3,
            'headers'     => array(
                'Accept'     => 'application/json',
                'User-Agent' => 'PiyasaVizyon/1.0; ' . home_url('/'),
            ),
        )
    );
    if ( is_wp_error( $response ) ) {
        return array();
    }
    $status = (int) wp_remote_retrieve_response_code( $response );
    if ( $status < 200 || $status >= 300 ) {
        return array();
    }

    $payload = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $payload['prices'] ) || ! is_array( $payload['prices'] ) ) {
        return array();
    }

    $points = array();
    foreach ( $payload['prices'] as $row ) {
        if ( is_array( $row ) && isset( $row[0], $row[1] ) && is_numeric( $row[0] ) && is_numeric( $row[1] ) ) {
            $points[] = array( (int) $row[0], (float) $row[1] );
        }
    }
    if ( $points ) {
        set_transient( $cache_key, $points, 5 * MINUTE_IN_SECONDS );
    }
    return $points;
}

function pv_market_coingecko_shadow_fetch() {
    return pv_market_coingecko_fetch();
}

function pv_market_coingecko_shadow_compare( $legacy_payload, $shadow_payload ) {
    $result = array(
        'legacy_count'  => 0,
        'shadow_count'  => 0,
        'symbol_overlap'=> 0,
        'legacy_only'   => array(),
        'shadow_only'   => array(),
    );

    $legacy_symbols = array();
    $shadow_symbols = array();

    if ( is_array( $legacy_payload ) && ! empty( $legacy_payload['symbol'] ) && is_array( $legacy_payload['symbol'] ) ) {
        $legacy_symbols = array_values( array_unique( array_filter( array_map( 'strtoupper', array_map( 'strval', $legacy_payload['symbol'] ) ) ) ) );
    }

    if ( is_array( $shadow_payload ) && ! empty( $shadow_payload['symbol'] ) && is_array( $shadow_payload['symbol'] ) ) {
        $shadow_symbols = array_values( array_unique( array_filter( array_map( 'strtoupper', array_map( 'strval', $shadow_payload['symbol'] ) ) ) ) );
    }

    $result['legacy_count']   = count( $legacy_symbols );
    $result['shadow_count']   = count( $shadow_symbols );
    $result['symbol_overlap'] = count( array_intersect( $legacy_symbols, $shadow_symbols ) );
    $result['legacy_only']    = array_values( array_diff( $legacy_symbols, $shadow_symbols ) );
    $result['shadow_only']    = array_values( array_diff( $shadow_symbols, $legacy_symbols ) );

    return $result;
}
