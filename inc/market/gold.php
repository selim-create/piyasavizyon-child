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

    return array(
        'query'       => $query,
        'key'         => $match_key,
        'slug'        => $public_slug,
        'name'        => $name !== '' ? $name : $full_name,
        'full_name'   => $full_name !== '' ? $full_name : $name,
        'price'       => $price,
        'buying'      => $buying,
        'selling'     => $selling,
        'change_pct'  => $change,
        'update'      => $update !== '' ? $update : $time,
    );
}

function pv_market_gold_mynet_overrides() {
    return array(
        'gram-altin-fiyati'    => 'xgld-spot-altin-tl-gr',
        'ons-altin-usd-fiyati' => 'xau-usd-ons-altin',
    );
}

function pv_market_gold_catalog() {
    $cache_key = 'pv_gold_mynet_catalog_v1';
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) && $cached !== array() ) {
        return $cached;
    }

    $html = pv_market_fetch_mynet( '/altin/' );
    if ( $html === '' ) {
        return array();
    }

    $catalog = array();
    if ( preg_match_all( '@<a[^>]+href=["\']/altin/([^/"\']+)/["\'][^>]*>(.*?)</a>@si', $html, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
            $slug = sanitize_title( $match[1] );
            $name = pv_market_decode_text( $match[2] );
            if ( $slug === '' || $name === '' ) {
                continue;
            }
            $catalog[ $slug ] = $name;
        }
    }

    if ( $catalog !== array() ) {
        set_transient( $cache_key, $catalog, HOUR_IN_SECONDS );
    }

    return $catalog;
}

function pv_market_gold_tokens( $value ) {
    $value = pv_market_normalize_label( (string) $value );
    $parts = preg_split( '/\s+/', $value );
    $stop  = array( 'altin', 'fiyati', 'fiyat', 'alis', 'satis', 've', 'tl', 'usd' );
    $out   = array();

    foreach ( (array) $parts as $part ) {
        $part = trim( (string) $part );
        if ( strlen( $part ) < 2 || in_array( $part, $stop, true ) ) {
            continue;
        }
        $out[ $part ] = true;
    }

    return array_keys( $out );
}

function pv_market_gold_resolve_mynet_slug( $detail ) {
    $public_slug = isset( $detail['slug'] ) ? sanitize_title( (string) $detail['slug'] ) : '';
    $overrides   = pv_market_gold_mynet_overrides();
    if ( isset( $overrides[ $public_slug ] ) ) {
        return $overrides[ $public_slug ];
    }

    $catalog = pv_market_gold_catalog();
    if ( $catalog === array() ) {
        return '';
    }

    $needles = array_filter( array(
        $public_slug,
        isset( $detail['name'] ) ? $detail['name'] : '',
        isset( $detail['full_name'] ) ? $detail['full_name'] : '',
    ) );
    $needle_tokens = array();
    foreach ( $needles as $needle ) {
        $needle_tokens = array_merge( $needle_tokens, pv_market_gold_tokens( $needle ) );
    }
    $needle_tokens = array_values( array_unique( $needle_tokens ) );

    $best_slug  = '';
    $best_score = 0;

    foreach ( $catalog as $slug => $name ) {
        $score = 0;
        if ( $public_slug !== '' && strpos( $slug, preg_replace( '/-fiyati$/', '', $public_slug ) ) !== false ) {
            $score += 5;
        }

        $candidate_tokens = array_merge( pv_market_gold_tokens( $slug ), pv_market_gold_tokens( $name ) );
        $candidate_tokens = array_values( array_unique( $candidate_tokens ) );
        foreach ( $needle_tokens as $token ) {
            if ( in_array( $token, $candidate_tokens, true ) ) {
                $score++;
            }
        }

        if ( $score > $best_score ) {
            $best_score = $score;
            $best_slug  = $slug;
        }
    }

    return $best_score >= 2 ? $best_slug : '';
}

function pv_market_parse_mynet_gold_detail_html( $html, $fallback_name = '' ) {
    if ( ! is_string( $html ) || trim( $html ) === '' ) {
        return array();
    }

    $detail = array(
        'mynet_name' => (string) $fallback_name,
        'stats'      => array(),
        'chart'      => array(),
    );

    if ( preg_match( '@<h1[^>]*>(.*?)</h1>@si', $html, $match ) ) {
        $name = pv_market_decode_text( $match[1] );
        if ( $name !== '' ) {
            $detail['mynet_name'] = $name;
        }
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
                    $detail['stats'][ $label ] = $value;
                }
            }
        }
    }

    if ( preg_match( '@initChartData\(\{(.*?)\}\)@si', $html, $match ) ) {
        $chart = json_decode( '{' . $match[1] . '}', true );
        if ( isset( $chart['data'] ) && is_array( $chart['data'] ) ) {
            $detail['chart'] = $chart['data'];
        }
    }

    return $detail;
}

function pv_market_gold_detail( $query ) {
    $detail = pv_market_gold_resolve_query( $query );
    if ( empty( $detail['slug'] ) ) {
        return array();
    }

    $mynet_slug = pv_market_gold_resolve_mynet_slug( $detail );
    $detail['mynet_slug'] = $mynet_slug;
    $detail['stats']      = array();
    $detail['chart']      = array();

    if ( $mynet_slug === '' ) {
        return $detail;
    }

    $html   = pv_market_fetch_mynet( '/altin/' . rawurlencode( $mynet_slug ) . '/' );
    $parsed = pv_market_parse_mynet_gold_detail_html( $html, $detail['name'] );

    $detail['stats'] = isset( $parsed['stats'] ) && is_array( $parsed['stats'] ) ? $parsed['stats'] : array();
    $detail['chart'] = isset( $parsed['chart'] ) && is_array( $parsed['chart'] ) ? $parsed['chart'] : array();

    return $detail;
}

function pv_market_gold_chart_windows() {
    return array(
        'daily'   => array( 'label' => 'GÜNLÜK',   'title' => 'Günlük',   'seconds' => DAY_IN_SECONDS ),
        'weekly'  => array( 'label' => 'HAFTA',    'title' => 'Haftalık', 'seconds' => WEEK_IN_SECONDS ),
        'monthly' => array( 'label' => 'AY',       'title' => 'Aylık',    'seconds' => DAY_IN_SECONDS * 30 ),
        'yearly'  => array( 'label' => 'BU YIL',   'title' => 'Yıllık',   'seconds' => YEAR_IN_SECONDS ),
        'all'     => array( 'label' => '3 YILLIK', 'title' => '3 Yıllık', 'seconds' => YEAR_IN_SECONDS * 3 ),
    );
}

function pv_market_route_gold_detail_template( $template ) {
    if ( basename( (string) $template ) === 'altin-detay.php' ) {
        return __DIR__ . '/views/altin-detay.php';
    }
    return $template;
}
add_filter( 'template_include', 'pv_market_route_gold_detail_template', 102 );
