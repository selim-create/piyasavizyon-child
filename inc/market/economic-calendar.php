<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_economic_calendar_period( $period ) {
    $period = sanitize_key( (string) $period );
    $allowed = array( 'dun', 'bugun', 'yarin', '1-hafta', '1-ay' );
    return in_array( $period, $allowed, true ) ? $period : 'dun';
}

function pv_market_economic_calendar_endpoint( $period ) {
    $period = pv_market_economic_calendar_period( $period );
    $map = array(
        'dun'      => 'yesterday',
        'bugun'    => 'today',
        'yarin'    => 'tomorrow',
        '1-hafta'  => 'week',
        '1-ay'     => 'month',
    );
    return 'https://finans.mynet.com/api/ekonomiktakvim/events/' . $map[ $period ];
}

function pv_market_economic_calendar( $period = 'dun' ) {
    $period = pv_market_economic_calendar_period( $period );
    $cache_key = 'pv_economic_calendar_' . md5( $period );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) && isset( $cached['events'] ) ) {
        return $cached;
    }

    $response = wp_safe_remote_get(
        pv_market_economic_calendar_endpoint( $period ),
        array(
            'timeout'     => 15,
            'redirection' => 3,
            'headers'     => array(
                'Accept'     => 'application/json,text/plain,*/*',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/151.0 Safari/537.36',
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        return array( 'events' => array() );
    }
    $status = (int) wp_remote_retrieve_response_code( $response );
    if ( $status < 200 || $status >= 300 ) {
        return array( 'events' => array() );
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! is_array( $data ) || empty( $data['events'] ) || ! is_array( $data['events'] ) ) {
        return array( 'events' => array() );
    }

    set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );
    return $data;
}
