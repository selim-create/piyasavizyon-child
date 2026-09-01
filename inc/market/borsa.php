<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_borsa_index_map() {
    return array(
        'xu100' => array(
            'label' => 'BIST 100',
            'path'  => '/borsa/endeks/xu100-bist-100/',
        ),
        'xu050' => array(
            'label' => 'BIST 50',
            'path'  => '/borsa/endeks/xu050-bist-50/',
        ),
        'xu030' => array(
            'label' => 'BIST 30',
            'path'  => '/borsa/endeks/xu030-bist-30/',
        ),
    );
}

function pv_market_borsa_index_detail( $code ) {
    $map = pv_market_borsa_index_map();
    $code = strtolower( sanitize_key( (string) $code ) );

    if ( ! isset( $map[ $code ] ) ) {
        return array();
    }

    $cache_key = 'pv_borsa_index_' . $code;
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) && ! empty( $cached['price'] ) ) {
        return $cached;
    }

    $html = pv_market_fetch_mynet( $map[ $code ]['path'] );
    if ( $html === '' ) {
        return array();
    }

    $detail = array(
        'code'       => $code,
        'name'       => $map[ $code ]['label'],
        'price'      => '',
        'change'     => '',
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
        $dom = new DOMDocument();
        $loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
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
                    if ( preg_match( '/(-?[0-9.,]+)\s*\/\s*(-?[0-9.,]+)\s*%?/u', $text, $parts ) ) {
                        $detail['change'] = $parts[1];
                        $detail['change_pct'] = $parts[2];
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

    if ( preg_match( '@initChartData\(\{(.*?)\}\)@si', $html, $match ) ) {
        $chart = json_decode( '{' . $match[1] . '}', true );
        if ( isset( $chart['data'] ) && is_array( $chart['data'] ) ) {
            $detail['chart'] = $chart['data'];
        }
    }

    if ( $detail['price'] === '' ) {
        $dynamic_class = 'dynamic-price-' . strtoupper( $code );
        if ( preg_match( '@<span[^>]*class="[^"]*' . preg_quote( $dynamic_class, '@' ) . '[^"]*"[^>]*>(.*?)</span>@si', $html, $match ) ) {
            $detail['price'] = pv_market_decode_text( $match[1] );
        }
    }

    if ( $detail['change_pct'] === '' ) {
        $dynamic_class = 'dynamic-direction-' . strtoupper( $code );
        if ( preg_match( '@<span[^>]*class="[^"]*' . preg_quote( $dynamic_class, '@' ) . '[^"]*"[^>]*>(.*?)</span>@si', $html, $match ) ) {
            $detail['change_pct'] = ltrim( pv_market_decode_text( $match[1] ), '%' );
        }
    }

    if ( ! empty( $detail['price'] ) ) {
        set_transient( $cache_key, $detail, MINUTE_IN_SECONDS );
    }

    return $detail;
}

function pv_market_borsa_chart_windows() {
    return array(
        'daily'   => array( 'label' => 'BUGÜN',      'title' => 'Günlük',    'seconds' => DAY_IN_SECONDS * 3 ),
        'weekly'  => array( 'label' => 'BU HAFTA',   'title' => 'Haftalık',  'seconds' => WEEK_IN_SECONDS ),
        'monthly' => array( 'label' => 'BU AY',      'title' => 'Aylık',     'seconds' => DAY_IN_SECONDS * 30 ),
        'yearly'  => array( 'label' => 'BU YIL',     'title' => 'Yıllık',    'seconds' => YEAR_IN_SECONDS ),
        'all'     => array( 'label' => '12 YILLIK',  'title' => '12 Yıllık', 'seconds' => 0 ),
    );
}

function pv_market_route_borsa_template( $template ) {
    if ( basename( (string) $template ) === 'borsa-page.php' ) {
        return __DIR__ . '/views/borsa.php';
    }
    return $template;
}
add_filter( 'template_include', 'pv_market_route_borsa_template', 100 );
