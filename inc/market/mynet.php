<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Fetch an HTML page from the public Mynet Finans market section.
 *
 * Keeping this transport child-owned removes the need to proxy public Mynet
 * pages through BirFinans/BirTema helpers while preserving the existing
 * template parsers during the migration.
 */
function pv_market_fetch_mynet( $path ) {
    $path = '/' . ltrim( (string) $path, '/' );
    $url  = 'https://finans.mynet.com' . $path;

    $response = wp_safe_remote_get(
        $url,
        array(
            'timeout'     => 25,
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
