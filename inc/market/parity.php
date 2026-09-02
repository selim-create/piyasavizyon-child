<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Resolve the legacy public ?p= contract to a Mynet parity slug.
 *
 * Historical links may pass a payload array key (often numeric), a compact
 * six-letter code such as EURUSD, or an already-normalized slug such as eur-usd.
 */
function pv_market_parity_resolve_query( $query ) {
    global $parite_data;

    $query = trim( (string) $query );
    if ( $query === '' ) {
        return array();
    }

    $code      = '';
    $full_name = '';

    if ( is_array( $parite_data ) && isset( $parite_data['code'] ) && is_array( $parite_data['code'] ) ) {
        if ( array_key_exists( $query, $parite_data['code'] ) ) {
            $code = (string) $parite_data['code'][ $query ];
            if ( isset( $parite_data['full_name'][ $query ] ) ) {
                $full_name = (string) $parite_data['full_name'][ $query ];
            }
        } else {
            foreach ( $parite_data['code'] as $key => $candidate ) {
                $candidate_code = strtoupper( preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $candidate ) ) );
                $query_code     = strtoupper( preg_replace( '/[^A-Z0-9]/', '', strtoupper( $query ) ) );
                if ( $candidate_code !== '' && $candidate_code === $query_code ) {
                    $code = (string) $candidate;
                    if ( isset( $parite_data['full_name'][ $key ] ) ) {
                        $full_name = (string) $parite_data['full_name'][ $key ];
                    }
                    break;
                }
            }
        }
    }

    if ( $code === '' ) {
        $code = $query;
    }

    $compact = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $code ) );
    if ( strlen( $compact ) === 6 ) {
        $slug = strtolower( substr( $compact, 0, 3 ) . '-' . substr( $compact, 3, 3 ) );
    } else {
        $slug = sanitize_title( $code );
    }

    if ( $slug === '' ) {
        return array();
    }

    return array(
        'query'     => $query,
        'code'      => $compact !== '' ? $compact : strtoupper( str_replace( '-', '', $slug ) ),
        'slug'      => $slug,
        'full_name' => $full_name,
    );
}

function pv_market_parse_mynet_parity_detail_html( $html, $slug, $fallback_name = '' ) {
    if ( ! is_string( $html ) || trim( $html ) === '' ) {
        return array();
    }

    $slug   = sanitize_title( (string) $slug );
    $detail = array(
        'slug'       => $slug,
        'name'       => (string) $fallback_name,
        'price'      => '',
        'change_pct' => '',
        'update'     => '',
        'stats'      => array(),
        'chart'      => array(),
    );

    if ( preg_match( '@<h1[^>]*>(.*?)</h1>@si', $html, $match ) ) {
        $name = pv_market_decode_text( $match[1] );
        if ( $name !== '' ) {
            $detail['name'] = $name;
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

            $unit = $xpath->query( '//div[contains(concat(" ", normalize-space(@class), " "), " unit-price ")]' )->item( 0 );
            if ( $unit instanceof DOMElement ) {
                $value = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " data-value ")]', $unit )->item( 0 );
                $label = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " label ")]', $unit )->item( 0 );
                if ( $value ) {
                    $detail['price'] = pv_market_decode_text( $value->textContent );
                }
                if ( $label ) {
                    $update = pv_market_decode_text( $label->textContent );
                    $detail['update'] = trim( preg_replace( '/^Son:\s*/u', '', $update ) );
                }
            }

            $daily = $xpath->query( '//div[contains(concat(" ", normalize-space(@class), " "), " daily-change ")]' )->item( 0 );
            if ( $daily instanceof DOMElement ) {
                $value = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " data-value ")]', $daily )->item( 0 );
                if ( $value ) {
                    $text = pv_market_decode_text( $value->textContent );
                    if ( preg_match( '/(-?[0-9][0-9.,]*)\s*%/u', $text, $parts ) ) {
                        $detail['change_pct'] = $parts[1];
                    }
                }
            }

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

    foreach ( $detail['stats'] as $label => $value ) {
        $normalized = pv_market_normalize_label( $label );

        if ( $detail['price'] === '' && in_array( $normalized, array( 'son deger', 'son', 'son fiyat' ), true ) ) {
            $detail['price'] = $value;
        }

        if (
            $detail['change_pct'] === '' &&
            ( $normalized === 'gunluk degisim %' || $normalized === 'gunluk degisim (%)' )
        ) {
            $detail['change_pct'] = ltrim( (string) $value, '%' );
        }
    }

    if ( preg_match( '@initChartData\(\{(.*?)\}\)@si', $html, $match ) ) {
        $chart = json_decode( '{' . $match[1] . '}', true );
        if ( isset( $chart['data'] ) && is_array( $chart['data'] ) ) {
            $detail['chart'] = $chart['data'];
        }
    }

    $dynamic_code = strtoupper( str_replace( '-', '', $slug ) );
    if ( $detail['price'] === '' && $dynamic_code !== '' ) {
        if ( preg_match( '@<span[^>]*class="[^"]*dynamic-price-' . preg_quote( $dynamic_code, '@' ) . '[^"]*"[^>]*>(.*?)</span>@si', $html, $match ) ) {
            $detail['price'] = pv_market_decode_text( $match[1] );
        }
    }

    if ( $detail['change_pct'] === '' && $dynamic_code !== '' ) {
        if ( preg_match( '@<span[^>]*class="[^"]*dynamic-direction-' . preg_quote( $dynamic_code, '@' ) . '[^"]*"[^>]*>(.*?)</span>@si', $html, $match ) ) {
            $detail['change_pct'] = ltrim( pv_market_decode_text( $match[1] ), '%' );
        }
    }

    return $detail;
}

function pv_market_mynet_parity_detail( $query ) {
    $resolved = pv_market_parity_resolve_query( $query );
    if ( empty( $resolved['slug'] ) ) {
        return array();
    }

    $slug      = $resolved['slug'];
    $cache_key = 'pv_parity_detail_' . md5( $slug );
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) && ! empty( $cached['price'] ) ) {
        return $cached;
    }

    $html = pv_market_fetch_mynet( '/parite/' . rawurlencode( $slug ) . '/' );
    if ( $html === '' ) {
        return array();
    }

    $fallback = $resolved['full_name'] !== '' ? $resolved['full_name'] : $resolved['code'];
    $detail   = pv_market_parse_mynet_parity_detail_html( $html, $slug, $fallback );
    $detail['code'] = $resolved['code'];

    if ( ! empty( $detail['price'] ) ) {
        set_transient( $cache_key, $detail, MINUTE_IN_SECONDS );
    }

    return $detail;
}

function pv_market_parity_chart_windows() {
    return array(
        'daily'   => array( 'label' => 'BUGÜN',    'title' => 'Günlük',   'seconds' => DAY_IN_SECONDS * 3 ),
        'weekly'  => array( 'label' => 'BU HAFTA', 'title' => 'Haftalık', 'seconds' => WEEK_IN_SECONDS ),
        'monthly' => array( 'label' => 'BU AY',    'title' => 'Aylık',    'seconds' => DAY_IN_SECONDS * 30 ),
        'yearly'  => array( 'label' => 'BU YIL',   'title' => 'Yıllık',   'seconds' => YEAR_IN_SECONDS ),
        'all'     => array( 'label' => '5 YILLIK', 'title' => '5 Yıllık', 'seconds' => 0 ),
    );
}

function pv_market_route_parity_detail_template( $template ) {
    if ( basename( (string) $template ) === 'parite-detay.php' ) {
        return __DIR__ . '/views/parite-detay.php';
    }
    return $template;
}
add_filter( 'template_include', 'pv_market_route_parity_detail_template', 101 );
