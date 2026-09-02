<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_currency_resolve_query( $query ) {
    global $currency_data;

    $query = sanitize_title( (string) $query );
    if ( $query === '' || ! is_array( $currency_data ) || empty( $currency_data['code'] ) || ! is_array( $currency_data['code'] ) ) {
        return array();
    }

    $match_key = null;
    foreach ( $currency_data['code'] as $key => $code ) {
        if ( sanitize_title( (string) $key ) === $query || sanitize_title( (string) $code ) === $query ) {
            $match_key = $key;
            break;
        }
    }

    if ( $match_key === null ) {
        return array();
    }

    $read = static function( $field, $fallback = '' ) use ( $currency_data, $match_key ) {
        if ( isset( $currency_data[ $field ] ) && is_array( $currency_data[ $field ] ) && array_key_exists( $match_key, $currency_data[ $field ] ) ) {
            return (string) $currency_data[ $field ][ $match_key ];
        }
        return (string) $fallback;
    };

    $code = strtolower( $read( 'code', (string) $match_key ) );

    return array(
        'query'      => $query,
        'key'        => $match_key,
        'code'       => $code,
        'name'       => $read( 'full_name', strtoupper( $code ) ),
        'buying'     => $read( 'selling' ), // Preserve legacy Piyasa Vizyon column semantics.
        'selling'    => $read( 'buying' ),
        'change_pct' => $read( 'change_rate', '0' ),
        'update'     => $read( 'time' ),
    );
}

function pv_market_currency_fetch_source( $url, $cache_key, $ttl = MINUTE_IN_SECONDS ) {
    $cached = get_transient( $cache_key );
    if ( is_string( $cached ) && $cached !== '' ) {
        return $cached;
    }

    $response = wp_safe_remote_get(
        $url,
        array(
            'timeout'     => 20,
            'redirection' => 4,
            'headers'     => array(
                'Accept'     => 'text/html,application/xhtml+xml,application/json',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/151.0 Safari/537.36',
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        return '';
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    if ( $status < 200 || $status >= 300 ) {
        return '';
    }

    $body = wp_remote_retrieve_body( $response );
    if ( ! is_string( $body ) || trim( $body ) === '' ) {
        return '';
    }

    set_transient( $cache_key, $body, $ttl );
    return $body;
}

function pv_market_currency_mynet_chart( $code ) {
    $code = sanitize_title( (string) $code );
    if ( $code === '' ) {
        return array();
    }

    $html = pv_market_fetch_mynet( '/doviz/' . rawurlencode( $code ) . '/' );
    if ( $html === '' || ! preg_match( '@initChartData\\(\\{(.*?)\\}\\)@si', $html, $match ) ) {
        return array();
    }

    $chart = json_decode( '{' . $match[1] . '}', true );
    return isset( $chart['data'] ) && is_array( $chart['data'] ) ? array_values( $chart['data'] ) : array();
}

function pv_market_currency_bank_source_slug( $detail ) {
    $code = isset( $detail['code'] ) ? strtolower( sanitize_key( (string) $detail['code'] ) ) : '';

    $map = array(
        'usd' => 'amerikan-dolari',
        'eur' => 'euro',
        'gbp' => 'sterlin',
    );

    if ( isset( $map[ $code ] ) ) {
        return $map[ $code ];
    }

    return ! empty( $detail['name'] ) ? sanitize_title( (string) $detail['name'] ) : '';
}

function pv_market_currency_bank_rows( $detail ) {
    if ( empty( $detail['name'] ) || ! class_exists( 'DOMDocument' ) ) {
        return array();
    }

    $source_slug = pv_market_currency_bank_source_slug( $detail );
    if ( $source_slug === '' ) {
        return array();
    }

    $url  = 'https://kur.doviz.com/serbest-piyasa/' . rawurlencode( $source_slug );
    $html = pv_market_currency_fetch_source( $url, 'pv_currency_banks_' . md5( $source_slug ), 5 * MINUTE_IN_SECONDS );
    if ( $html === '' ) {
        return array();
    }

    $previous = libxml_use_internal_errors( true );
    $dom      = new DOMDocument();
    $loaded   = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous );
    if ( ! $loaded ) {
        return array();
    }

    $xpath = new DOMXPath( $dom );
    $rows  = array();

    foreach ( $xpath->query( '//table//tr' ) as $tr ) {
        $cells = $xpath->query( './td', $tr );
        if ( ! $cells || $cells->length < 3 ) {
            continue;
        }

        $name = pv_market_decode_text( $cells->item( 0 )->textContent );
        $buy  = pv_market_decode_text( $cells->item( 1 )->textContent );
        $sell = pv_market_decode_text( $cells->item( 2 )->textContent );
        if ( $name === '' || $buy === '' || $sell === '' ) {
            continue;
        }

        $rows[] = array( 'name' => $name, 'buying' => $buy, 'selling' => $sell );
    }

    return array_slice( $rows, 0, 30 );
}

function pv_market_currency_normalize_bank_name( $name ) {
    $name = pv_market_decode_text( $name );
    $name = mb_strtolower( $name, 'UTF-8' );
    $name = str_replace( "\xCC\x87", '', $name ); // Strip combining dot left by Turkish capital İ.
    $name = remove_accents( $name );
    $name = preg_replace( '/\s+/u', ' ', $name );
    return trim( (string) $name );
}

function pv_market_currency_find_bank( $banks, $requested_name ) {
    $needle = pv_market_currency_normalize_bank_name( $requested_name );
    if ( $needle === '' || ! is_array( $banks ) ) {
        return array();
    }

    foreach ( $banks as $bank ) {
        if ( empty( $bank['name'] ) ) {
            continue;
        }
        if ( pv_market_currency_normalize_bank_name( $bank['name'] ) === $needle ) {
            return $bank;
        }
    }

    return array();
}

function pv_market_currency_detail( $query ) {
    $detail = pv_market_currency_resolve_query( $query );
    if ( empty( $detail['code'] ) ) {
        return array();
    }

    $detail['chart'] = pv_market_currency_mynet_chart( $detail['code'] );
    $detail['banks'] = pv_market_currency_bank_rows( $detail );
    return $detail;
}

function pv_market_currency_chart_windows() {
    return array(
        'daily'   => array( 'label' => 'BUGÜN',     'title' => 'Günlük',   'seconds' => DAY_IN_SECONDS ),
        'weekly'  => array( 'label' => 'BU HAFTA',  'title' => 'Haftalık', 'seconds' => WEEK_IN_SECONDS ),
        'monthly' => array( 'label' => 'BU AY',     'title' => 'Aylık',    'seconds' => 30 * DAY_IN_SECONDS ),
        'yearly'  => array( 'label' => 'BU YIL',    'title' => 'Yıllık',   'seconds' => YEAR_IN_SECONDS ),
        'all'     => array( 'label' => '5 YILLIK',  'title' => '5 Yıllık', 'seconds' => 5 * YEAR_IN_SECONDS ),
    );
}

function pv_market_currency_list_url() {
    static $url = null;
    if ( $url !== null ) {
        return $url;
    }

    $pages = get_posts( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'doviz-tablo.php',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );

    $url = $pages ? get_permalink( $pages[0] ) : home_url( '/doviz-kurlari/' );
    return $url;
}

function pv_market_currency_detail_url( $key ) {
    return add_query_arg( 'c', sanitize_title( (string) $key ), home_url( '/doviz/' ) );
}

function pv_market_route_currency_detail_template( $template ) {
    if ( basename( (string) $template ) !== 'doviz-detay.php' ) {
        return $template;
    }

    if ( ! empty( $_GET['banka'] ) ) {
        return __DIR__ . '/views/doviz-banka-detay.php';
    }

    return __DIR__ . '/views/doviz-detay.php';
}
add_filter( 'template_include', 'pv_market_route_currency_detail_template', 103 );
