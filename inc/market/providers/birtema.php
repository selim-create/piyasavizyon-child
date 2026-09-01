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
 * Preserve the historical Piyasa Vizyon gold identifier for ONS.
 *
 * BirTema currently returns the canonical string key `altin-ons` and detail slug
 * `altin-ons-fiyati`. Existing Piyasa Vizyon URLs and templates historically use
 * `ons-altin-usd` / `ons-altin-usd-fiyati`. Numeric aliases are left untouched;
 * only the string-key view is renamed so the legacy public contract remains
 * stable while the upstream service can evolve independently.
 */
function pv_market_birtema_normalize_gold( $payload ) {
    if ( ! is_array( $payload ) || $payload === array() ) {
        return $payload;
    }

    $dual_fields = array(
        'altin_price',
        'altin_price_buying',
        'altin_price_selling',
        'altin_update',
        'altin_time',
        'altin_rate',
        'altin_name',
        'altin_full_name',
    );

    foreach ( $dual_fields as $field ) {
        if ( ! isset( $payload[ $field ] ) || ! is_array( $payload[ $field ] ) || ! array_key_exists( 'altin-ons', $payload[ $field ] ) ) {
            continue;
        }

        $normalized = array();
        foreach ( $payload[ $field ] as $key => $value ) {
            $normalized[ $key === 'altin-ons' ? 'ons-altin-usd' : $key ] = $value;
        }
        $payload[ $field ] = $normalized;
    }

    if ( isset( $payload['altin_key'] ) && is_array( $payload['altin_key'] ) && array_key_exists( 'altin-ons', $payload['altin_key'] ) ) {
        $normalized = array();
        foreach ( $payload['altin_key'] as $key => $value ) {
            if ( $key === 'altin-ons' ) {
                $normalized['ons-altin-usd'] = 'ons-altin-usd-fiyati';
                continue;
            }
            $normalized[ $key ] = $value;
        }
        $payload['altin_key'] = $normalized;
    }

    return $payload;
}

/**
 * Fetch an entitled market payload directly from BirTema's data service.
 *
 * This replaces the legacy BirFinans get_data_service() transport one resource
 * at a time while preserving the upstream payload contract expected by Piyasa
 * Vizyon. Resource-specific compatibility normalization is applied after fetch.
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

    if ( $resource === 'altin' ) {
        $payload = pv_market_birtema_normalize_gold( $payload );
    }

    return $payload;
}
