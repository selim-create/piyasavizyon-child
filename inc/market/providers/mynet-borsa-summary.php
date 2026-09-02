<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_mynet_summary_number( $value ) {
    $value = pv_market_decode_text( $value );
    $value = str_replace( array( '%', '▲', '▼', ' ' ), '', $value );

    if ( strpos( $value, ',' ) !== false ) {
        $value = str_replace( '.', '', $value );
        $value = str_replace( ',', '.', $value );
    }

    $value = preg_replace( '/[^0-9.\-]/', '', $value );
    return is_numeric( $value ) ? (float) $value : 0.0;
}

function pv_market_mynet_summary_volume_number( $value ) {
    $text = mb_strtolower( pv_market_decode_text( $value ), 'UTF-8' );
    $number = pv_market_mynet_summary_number( $text );

    if ( strpos( $text, 'tr' ) !== false || strpos( $text, 'tn' ) !== false ) {
        return $number * 1000000000000;
    }
    if ( strpos( $text, 'mr' ) !== false || strpos( $text, 'bn' ) !== false ) {
        return $number * 1000000000;
    }
    if ( strpos( $text, 'mn' ) !== false || strpos( $text, 'milyon' ) !== false ) {
        return $number * 1000000;
    }
    if ( strpos( $text, 'bin' ) !== false || preg_match( '/(^|\s)k($|\s)/u', $text ) ) {
        return $number * 1000;
    }

    return $number;
}

function pv_market_mynet_summary_row( $row ) {
    return array(
        'hisse'   => isset( $row['name'] ) ? $row['name'] : '',
        'son'     => isset( $row['last'] ) ? $row['last'] : '',
        'hacim'   => isset( $row['volume'] ) ? $row['volume'] : '',
        'degisim' => isset( $row['change'] ) ? $row['change'] : '',
        'link'    => isset( $row['slug'] ) ? $row['slug'] : '',
    );
}

function pv_market_mynet_borsa_summary_fetch() {
    $stocks = function_exists( 'pv_market_mynet_stocks' ) ? pv_market_mynet_stocks() : array();
    $snapshot = function_exists( 'pv_market_mynet_bist100_snapshot' ) ? pv_market_mynet_bist100_snapshot() : array();

    if ( empty( $stocks ) || ! is_array( $stocks ) || empty( $snapshot['value'] ) ) {
        return false;
    }

    $gainers = $stocks;
    usort( $gainers, static function( $a, $b ) {
        $a_change = pv_market_mynet_summary_number( isset( $a['change'] ) ? $a['change'] : '' );
        $b_change = pv_market_mynet_summary_number( isset( $b['change'] ) ? $b['change'] : '' );
        return $b_change <=> $a_change;
    } );

    $losers = $stocks;
    usort( $losers, static function( $a, $b ) {
        $a_change = pv_market_mynet_summary_number( isset( $a['change'] ) ? $a['change'] : '' );
        $b_change = pv_market_mynet_summary_number( isset( $b['change'] ) ? $b['change'] : '' );
        return $a_change <=> $b_change;
    } );

    $volume = $stocks;
    usort( $volume, static function( $a, $b ) {
        $a_volume = pv_market_mynet_summary_volume_number( isset( $a['volume'] ) ? $a['volume'] : '' );
        $b_volume = pv_market_mynet_summary_volume_number( isset( $b['volume'] ) ? $b['volume'] : '' );
        return $b_volume <=> $a_volume;
    } );

    $normalize = static function( $rows ) {
        $result = array();
        foreach ( array_slice( $rows, 0, 20 ) as $row ) {
            $normalized = pv_market_mynet_summary_row( $row );
            if ( $normalized['hisse'] === '' || $normalized['link'] === '' ) {
                continue;
            }
            $result[] = $normalized;
        }
        return $result;
    };

    $payload = array(
        'bist_100' => array(
            'value'       => (string) $snapshot['value'],
            'change_rate' => isset( $snapshot['change'] ) ? (string) $snapshot['change'] : '0',
            'update'      => isset( $snapshot['update'] ) ? (string) $snapshot['update'] : '',
            '_pv_source'  => 'mynet',
        ),
        'borsa_artanlar'       => $normalize( $gainers ),
        'borsa_azalanlar'      => $normalize( $losers ),
        'borsa_islem_gorenler' => $normalize( $volume ),
    );

    if ( function_exists( 'pv_market_payload_is_valid' ) && ! pv_market_payload_is_valid( 'borsa', $payload ) ) {
        return false;
    }

    return $payload;
}
