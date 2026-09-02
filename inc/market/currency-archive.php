<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_currency_archive_normalize_date( $date ) {
    $date = trim( (string) $date );
    if ( $date === '' ) {
        return wp_date( 'Y-m-d', current_time( 'timestamp' ) );
    }

    if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $match ) ) {
        return wp_date( 'Y-m-d', current_time( 'timestamp' ) );
    }

    $year  = (int) $match[1];
    $month = (int) $match[2];
    $day   = (int) $match[3];

    if ( ! checkdate( $month, $day, $year ) ) {
        return wp_date( 'Y-m-d', current_time( 'timestamp' ) );
    }

    return sprintf( '%04d-%02d-%02d', $year, $month, $day );
}

function pv_market_currency_archive_page_date( $html, $fallback ) {
    if ( is_string( $html ) && preg_match( '/\b(\d{2})\.(\d{2})\.(\d{4})\b/u', $html, $match ) ) {
        $day   = (int) $match[1];
        $month = (int) $match[2];
        $year  = (int) $match[3];
        if ( checkdate( $month, $day, $year ) ) {
            return sprintf( '%04d-%02d-%02d', $year, $month, $day );
        }
    }

    return pv_market_currency_archive_normalize_date( $fallback );
}

function pv_market_currency_archive_resolve_code( $name, $href = '' ) {
    global $currency_data;

    $href = (string) $href;
    if ( $href !== '' ) {
        $path = wp_parse_url( $href, PHP_URL_PATH );
        if ( is_string( $path ) && preg_match( '#/doviz/([^/]+)/?$#', $path, $match ) ) {
            $slug      = sanitize_title( $match[1] );
            $candidate = explode( '-', $slug );
            $candidate = isset( $candidate[0] ) ? $candidate[0] : '';
            $resolved  = pv_market_currency_resolve_query( $candidate );
            if ( ! empty( $resolved['code'] ) ) {
                return strtolower( (string) $resolved['code'] );
            }
        }
    }

    $normalized_name = pv_market_normalize_label( $name );
    if ( $normalized_name === '' || ! is_array( $currency_data ) || empty( $currency_data['full_name'] ) || ! is_array( $currency_data['full_name'] ) ) {
        return '';
    }

    foreach ( $currency_data['full_name'] as $key => $full_name ) {
        if ( pv_market_normalize_label( $full_name ) !== $normalized_name ) {
            continue;
        }

        $resolved = pv_market_currency_resolve_query( $key );
        if ( ! empty( $resolved['code'] ) ) {
            return strtolower( (string) $resolved['code'] );
        }
    }

    return '';
}

function pv_market_parse_currency_archive_html( $html ) {
    $rows = array();
    if ( ! is_string( $html ) || trim( $html ) === '' || ! class_exists( 'DOMDocument' ) ) {
        return $rows;
    }

    $previous = libxml_use_internal_errors( true );
    $dom      = new DOMDocument();
    $loaded   = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous );

    if ( ! $loaded ) {
        return $rows;
    }

    $xpath  = new DOMXPath( $dom );
    $tables = $xpath->query( '//table' );
    if ( ! $tables ) {
        return $rows;
    }

    foreach ( $tables as $table ) {
        $headers = array();
        foreach ( $xpath->query( './/tr[1]/*[self::th or self::td]', $table ) as $cell ) {
            $headers[] = pv_market_decode_text( $cell->textContent );
        }

        $name_index  = pv_market_find_header_index( $headers, array( 'isim', 'doviz' ) );
        $open_index  = pv_market_find_header_index( $headers, array( 'acilis' ) );
        $low_index   = pv_market_find_header_index( $headers, array( 'en dusuk' ) );
        $high_index  = pv_market_find_header_index( $headers, array( 'en yuksek' ) );
        $close_index = pv_market_find_header_index( $headers, array( 'kapanis' ) );

        if ( $name_index === null || $open_index === null || $low_index === null || $high_index === null || $close_index === null ) {
            continue;
        }

        foreach ( $xpath->query( './/tr[position() > 1]', $table ) as $tr ) {
            $cells = array();
            foreach ( $xpath->query( './td', $tr ) as $cell ) {
                $cells[] = pv_market_decode_text( $cell->textContent );
            }

            $required_max = max( $name_index, $open_index, $low_index, $high_index, $close_index );
            if ( count( $cells ) <= $required_max ) {
                continue;
            }

            $link = $xpath->query( './/a[1]', $tr )->item( 0 );
            $href = $link instanceof DOMElement ? (string) $link->getAttribute( 'href' ) : '';
            $name = $link instanceof DOMElement ? pv_market_decode_text( $link->textContent ) : $cells[ $name_index ];
            if ( $name === '' ) {
                $name = $cells[ $name_index ];
            }

            $open  = $cells[ $open_index ];
            $low   = $cells[ $low_index ];
            $high  = $cells[ $high_index ];
            $close = $cells[ $close_index ];

            if ( $name === '' || $open === '' || $low === '' || $high === '' || $close === '' ) {
                continue;
            }

            $rows[] = array(
                'code'  => pv_market_currency_archive_resolve_code( $name, $href ),
                'name'  => $name,
                'open'  => $open,
                'low'   => $low,
                'high'  => $high,
                'close' => $close,
            );
        }

        if ( $rows !== array() ) {
            break;
        }
    }

    return $rows;
}

function pv_market_currency_archive( $requested_date = '' ) {
    $requested_date = trim( (string) $requested_date );
    $date           = pv_market_currency_archive_normalize_date( $requested_date );

    if ( $requested_date === '' ) {
        $path = '/doviz/arsiv/';
    } else {
        $timestamp = strtotime( $date . ' 12:00:00' );
        $path      = '/doviz/arsiv/' . wp_date( 'd.m.Y', $timestamp ) . '/';
    }

    $html = pv_market_fetch_mynet( $path );
    $rows = pv_market_parse_currency_archive_html( $html );

    return array(
        'date'        => pv_market_currency_archive_page_date( $html, $date ),
        'rows'        => $rows,
        'source_path' => $path,
    );
}
