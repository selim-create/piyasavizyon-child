<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Resolve the existing BirTema market-data license without committing secrets
 * to the repository.
 *
 * Preferred long-term sources are a wp-config constant or environment variable.
 * During migration we can still read the plain legacy BirFinans lisans.php file
 * so the encoded parent runtime is not required just to obtain the entitlement.
 */
function pv_market_birtema_license() {
    if ( defined( 'PV_BIRTEMA_LICENSE' ) && is_string( PV_BIRTEMA_LICENSE ) ) {
        $license = trim( PV_BIRTEMA_LICENSE );
        if ( $license !== '' ) {
            return $license;
        }
    }

    $environment = getenv( 'PV_BIRTEMA_LICENSE' );
    if ( is_string( $environment ) && trim( $environment ) !== '' ) {
        return trim( $environment );
    }

    $legacy_file = WP_CONTENT_DIR . '/themes/birfinans/lisans.php';
    if ( ! is_readable( $legacy_file ) ) {
        return '';
    }

    $lisans = null;
    include $legacy_file;

    return is_string( $lisans ) ? trim( $lisans ) : '';
}

/**
 * Fetch an entitled market payload directly from BirTema's data service.
 *
 * This replaces the legacy BirFinans get_data_service() transport one resource
 * at a time while preserving the upstream payload exactly as returned.
 */
function pv_market_birtema_fetch( $resource ) {
    $resource = sanitize_key( (string) $resource );
    $allowed = array( 'currency', 'altin', 'parite' );

    if ( ! in_array( $resource, $allowed, true ) ) {
        return new WP_Error( 'pv_birtema_resource', 'Unsupported BirTema market resource.' );
    }

    $license = pv_market_birtema_license();
    if ( $license === '' ) {
        return new WP_Error( 'pv_birtema_license', 'BirTema market-data license is unavailable.' );
    }

    $domain = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
    $domain = is_string( $domain ) ? strtolower( trim( $domain ) ) : '';
    $domain = preg_replace( '/^www\./', '', $domain );

    if ( $domain === '' ) {
        return new WP_Error( 'pv_birtema_domain', 'Site domain could not be resolved.' );
    }

    $response = wp_remote_post(
        'https://data.birtema.com/data/' . rawurlencode( $resource ),
        array(
            'timeout'     => 10,
            'redirection' => 2,
            'headers'     => array(
                'Accept'     => 'application/json',
                'User-Agent' => 'PiyasaVizyon/1.0; ' . home_url( '/' ),
            ),
            'body'        => array(
                'lisans' => $license,
                'domain' => $domain,
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    if ( $status < 200 || $status >= 300 ) {
        return new WP_Error( 'pv_birtema_http', 'BirTema HTTP status: ' . $status );
    }

    $payload = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! is_array( $payload ) || $payload === array() ) {
        return new WP_Error( 'pv_birtema_payload', 'BirTema payload is empty or invalid.' );
    }

    return $payload;
}
