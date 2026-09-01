<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_live_borsa_allowed_endex( $endex ) {
    $allowed = array( 'bist-TUM', 'bist-100', 'bist-50', 'bist-30' );
    return in_array( $endex, $allowed, true ) ? $endex : 'bist-100';
}

function pv_live_borsa_source_url( $endex ) {
    $endex = pv_live_borsa_allowed_endex( (string) $endex );

    if ( $endex === 'bist-100' ) {
        return 'https://uzmanpara.milliyet.com.tr/canli-borsa/';
    }

    return 'https://uzmanpara.milliyet.com.tr/canli-borsa/' . rawurlencode( $endex ) . '-hisseleri/';
}

function pv_live_borsa_fetch_html( $endex ) {
    $response = wp_safe_remote_get(
        pv_live_borsa_source_url( $endex ),
        array(
            'timeout'     => 15,
            'redirection' => 5,
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
    return is_string( $body ) ? $body : '';
}

function pv_live_borsa_rows_from_html( $html ) {
    $rows = array();
    if ( ! is_string( $html ) || trim( $html ) === '' || ! class_exists( 'DOMDocument' ) ) {
        return $rows;
    }

    $previous = libxml_use_internal_errors( true );
    $dom = new DOMDocument();
    $loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous );

    if ( ! $loaded ) {
        return $rows;
    }

    $xpath = new DOMXPath( $dom );
    $tr_nodes = $xpath->query( '//tr[starts-with(@id,"h_tr_id_")]' );
    if ( ! $tr_nodes ) {
        return $rows;
    }

    foreach ( $tr_nodes as $tr ) {
        $link = $xpath->query( './/td[contains(@class,"currency")]//a[contains(@href,"/borsa/hisse-senetleri/")]', $tr )->item( 0 );
        if ( ! $link instanceof DOMElement ) {
            continue;
        }

        $symbol_node = $xpath->query( './/b[starts-with(@id,"h_b_ad_id_")]', $tr )->item( 0 );
        $price_node  = $xpath->query( './/td[starts-with(@id,"h_td_fiyat_id_")]', $tr )->item( 0 );
        $dir_node    = $xpath->query( './/td[starts-with(@id,"h_td_yon_id_")]', $tr )->item( 0 );
        $pct_node    = $xpath->query( './/td[starts-with(@id,"h_td_yuzde_id_")]', $tr )->item( 0 );
        $time_node   = $xpath->query( './/td[starts-with(@id,"h_td_zaman_id_")]', $tr )->item( 0 );

        $symbol = $symbol_node ? trim( wp_strip_all_tags( $symbol_node->textContent ) ) : '';
        if ( $symbol === '' ) {
            continue;
        }

        $href = (string) $link->getAttribute( 'href' );
        $slug = trim( str_replace( '/borsa/hisse-senetleri/', '', $href ), '/' );
        $key  = $pct_node instanceof DOMElement ? preg_replace( '/^h_td_yuzde_id_/', '', (string) $pct_node->getAttribute( 'id' ) ) : sanitize_title( $symbol );

        $direction = 'decrease';
        if ( $dir_node instanceof DOMElement ) {
            $class = ' ' . trim( (string) $dir_node->getAttribute( 'class' ) ) . ' ';
            if ( strpos( $class, ' currency-up ' ) !== false ) {
                $direction = 'increase';
            }
        }

        $rows[] = array(
            'symbol'    => $symbol,
            'slug'      => $slug,
            'key'       => $key,
            'price'     => $price_node ? trim( wp_strip_all_tags( $price_node->textContent ) ) : '-',
            'percent'   => $pct_node ? trim( wp_strip_all_tags( $pct_node->textContent ) ) : '-',
            'time'      => $time_node ? trim( wp_strip_all_tags( $time_node->textContent ) ) : '-',
            'direction' => $direction,
        );
    }

    return $rows;
}

function pv_live_borsa_rows( $endex ) {
    $endex = pv_live_borsa_allowed_endex( (string) $endex );
    $cache_key = 'pv_live_borsa_' . md5( $endex );
    $cached = get_transient( $cache_key );

    if ( is_array( $cached ) && $cached !== array() ) {
        return $cached;
    }

    $rows = pv_live_borsa_rows_from_html( pv_live_borsa_fetch_html( $endex ) );
    if ( $rows !== array() ) {
        set_transient( $cache_key, $rows, 5 );
    }

    return $rows;
}

function pv_live_borsa_ajax() {
    $endex = isset( $_GET['endex'] ) ? sanitize_text_field( wp_unslash( $_GET['endex'] ) ) : 'bist-100';
    $endex = pv_live_borsa_allowed_endex( $endex );
    $rows  = pv_live_borsa_rows( $endex );

    if ( $rows === array() ) {
        wp_send_json_error( array( 'message' => 'Canlı borsa verisi alınamadı.' ), 503 );
    }

    $payload = array();
    foreach ( $rows as $row ) {
        if ( empty( $row['key'] ) ) {
            continue;
        }
        $payload[ $row['key'] ] = array(
            'fiyat' => $row['price'],
            'yuzde' => $row['percent'],
            'zaman' => $row['time'],
        );
    }

    wp_send_json_success( $payload );
}
add_action( 'wp_ajax_pv_live_borsa', 'pv_live_borsa_ajax' );
add_action( 'wp_ajax_nopriv_pv_live_borsa', 'pv_live_borsa_ajax' );
