<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_gold_resolve_query( $query ) {
    global $altin_data;

    $query = sanitize_title( (string) $query );
    if ( $query === '' ) {
        return array();
    }

    $match_key   = null;
    $public_slug = $query;

    if ( is_array( $altin_data ) && isset( $altin_data['altin_key'] ) && is_array( $altin_data['altin_key'] ) ) {
        foreach ( $altin_data['altin_key'] as $key => $candidate ) {
            $candidate_slug = sanitize_title( (string) $candidate );
            $key_slug       = sanitize_title( (string) $key );

            if ( $candidate_slug === $query || $key_slug === $query ) {
                $match_key   = $key;
                $public_slug = $candidate_slug !== '' ? $candidate_slug : $query;
                break;
            }
        }
    }

    if ( $match_key === null ) {
        return array();
    }

    $read = static function( $field, $fallback = '' ) use ( $altin_data, $match_key ) {
        if ( isset( $altin_data[ $field ] ) && is_array( $altin_data[ $field ] ) && array_key_exists( $match_key, $altin_data[ $field ] ) ) {
            return (string) $altin_data[ $field ][ $match_key ];
        }
        return (string) $fallback;
    };

    $name      = $read( 'altin_name' );
    $full_name = $read( 'altin_full_name', $name );
    $price     = $read( 'altin_price' );
    $buying    = $read( 'altin_price_buying' );
    $selling   = $read( 'altin_price_selling' );
    $change    = $read( 'altin_rate', '0' );
    $update    = $read( 'altin_update' );
    $time      = $read( 'altin_time' );

    if ( $price === '' ) {
        $price = $selling !== '' ? $selling : $buying;
    }

    $source_slug = $public_slug === 'ons-altin-usd-fiyati' ? 'altin-ons-fiyati' : $public_slug;

    return array(
        'query'       => $query,
        'key'         => $match_key,
        'slug'        => $public_slug,
        'source_slug' => $source_slug,
        'name'        => $name !== '' ? $name : $full_name,
        'full_name'   => $full_name !== '' ? $full_name : $name,
        'price'       => $price,
        'buying'      => $buying,
        'selling'     => $selling,
        'change_pct'  => $change,
        'update'      => $update !== '' ? $update : $time,
    );
}

function pv_market_fetch_bigpara_gold_html( $source_slug ) {
    $source_slug = sanitize_title( (string) $source_slug );
    if ( $source_slug === '' ) {
        return '';
    }

    $cache_key = 'pv_gold_html_' . md5( $source_slug );
    $cached    = get_transient( $cache_key );
    if ( is_string( $cached ) && $cached !== '' ) {
        return $cached;
    }

    $response = wp_safe_remote_get(
        'https://bigpara.hurriyet.com.tr/altin/' . rawurlencode( $source_slug ) . '/',
        array(
            'timeout'     => 20,
            'redirection' => 4,
            'headers'     => array(
                'Accept'     => 'text/html,application/xhtml+xml',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0 Safari/537.36',
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

    set_transient( $cache_key, $body, MINUTE_IN_SECONDS );
    return $body;
}

function pv_market_parse_bigpara_gold_html( $html ) {
    $parsed = array( 'exchange_id' => '', 'stats' => array() );
    if ( ! is_string( $html ) || trim( $html ) === '' ) {
        return $parsed;
    }

    if ( preg_match( '@/api/v1/chart/exchangegold/([^/"\']+)/@i', $html, $match ) || preg_match( '@/v1/chart/exchangegold/([^/"\']+)/@i', $html, $match ) ) {
        $parsed['exchange_id'] = sanitize_text_field( $match[1] );
    }

    if ( class_exists( 'DOMDocument' ) ) {
        $previous = libxml_use_internal_errors( true );
        $dom      = new DOMDocument();
        $loaded   = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
        libxml_clear_errors();
        libxml_use_internal_errors( $previous );

        if ( $loaded ) {
            $xpath = new DOMXPath( $dom );
            foreach ( $xpath->query( '//li[contains(@class,"justify-content-between")]' ) as $li ) {
                $spans = $xpath->query( './span', $li );
                if ( ! $spans || $spans->length < 2 ) {
                    continue;
                }
                $label = pv_market_decode_text( $spans->item( 0 )->textContent );
                $value = pv_market_decode_text( $spans->item( 1 )->textContent );
                if ( $label !== '' && $value !== '' ) {
                    $parsed['stats'][ $label ] = $value;
                }
            }
        }
    }

    return $parsed;
}

function pv_market_bigpara_gold_chart( $exchange_id, $period ) {
    $exchange_id = preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $exchange_id );
    $period      = (int) $period;
    $allowed     = array( 1, 3, 4, 8, 9 );

    if ( $exchange_id === '' || ! in_array( $period, $allowed, true ) ) {
        return array();
    }

    $cache_key = 'pv_gold_chart_' . md5( $exchange_id . ':' . $period );
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) && $cached !== array() ) {
        return $cached;
    }

    $response = wp_safe_remote_get(
        'https://bigpara.hurriyet.com.tr/api/v1/chart/exchangegold/' . rawurlencode( $exchange_id ) . '/' . $period,
        array(
            'timeout'     => 20,
            'redirection' => 3,
            'headers'     => array(
                'Accept'     => 'application/json,text/plain,*/*',
                'User-Agent' => 'PiyasaVizyon/1.0; ' . home_url( '/' ),
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
    if ( ! is_array( $payload ) ) {
        return array();
    }

    $points = array();
    foreach ( $payload as $row ) {
        if ( ! is_array( $row ) || empty( $row['tarih'] ) || ! isset( $row['kapanis'] ) ) {
            continue;
        }
        $timestamp = strtotime( (string) $row['tarih'] );
        if ( ! $timestamp ) {
            continue;
        }
        $value = $row['kapanis'];
        if ( ! is_numeric( $value ) ) {
            $value = str_replace( ',', '.', (string) $value );
        }
        if ( ! is_numeric( $value ) ) {
            continue;
        }
        $points[] = array( (int) $timestamp * 1000, (float) $value );
    }

    if ( $points !== array() ) {
        set_transient( $cache_key, $points, 5 * MINUTE_IN_SECONDS );
    }

    return $points;
}

function pv_market_gold_detail( $query ) {
    $detail = pv_market_gold_resolve_query( $query );
    if ( empty( $detail['slug'] ) ) {
        return array();
    }

    $parsed = pv_market_parse_bigpara_gold_html( pv_market_fetch_bigpara_gold_html( $detail['source_slug'] ) );
    $detail['exchange_id'] = isset( $parsed['exchange_id'] ) ? $parsed['exchange_id'] : '';
    $detail['stats']       = isset( $parsed['stats'] ) && is_array( $parsed['stats'] ) ? $parsed['stats'] : array();

    return $detail;
}

function pv_market_gold_chart_windows() {
    return array(
        'daily'   => array( 'label' => 'GÜNLÜK',   'title' => 'Günlük',   'period' => 1 ),
        'weekly'  => array( 'label' => 'HAFTA',    'title' => 'Haftalık', 'period' => 3 ),
        'monthly' => array( 'label' => 'AY',       'title' => 'Aylık',    'period' => 4 ),
        'yearly'  => array( 'label' => 'BU YIL',   'title' => 'Yıllık',   'period' => 8 ),
        'all'     => array( 'label' => '3 YILLIK', 'title' => '3 Yıllık', 'period' => 9 ),
    );
}

function pv_market_route_gold_detail_template( $template ) {
    if ( basename( (string) $template ) === 'altin-detay.php' ) {
        return __DIR__ . '/views/altin-detay.php';
    }
    return $template;
}
add_filter( 'template_include', 'pv_market_route_gold_detail_template', 102 );
