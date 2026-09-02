<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'pv_v7_credit_urls' ) ) {
    function pv_v7_credit_urls() {
        return array(
            'ihtiyac' => home_url( '/ihtiyac-kredisi/' ),
            'konut'   => home_url( '/konut-kredisi/' ),
            'tasit'   => home_url( '/tasit-kredisi/' ),
            'kobi'    => home_url( '/kobi-kredisi/' ),
            'kredi'   => home_url( '/kredi/' ),
            'faiz'    => home_url( '/faiz-oranlari/' ),
            'mevduat' => home_url( '/mevduat-oranlari/' ),
            'hesapla' => home_url( '/kredi-hesapla/' ),
            'nabiz'   => home_url( '/piyasanin-nabzi/' ),
            'trafik'  => home_url( '/trafik-sigortasi/' ),
            'kasko'   => home_url( '/kasko-sigortasi/' ),
        );
    }
}

if ( ! function_exists( 'pv_v7_credit_route_params' ) ) {
    function pv_v7_credit_route_params( $type ) {
        global $wp;

        $defaults = function_exists( 'pv_v7_credit_defaults' )
            ? pv_v7_credit_defaults( $type )
            : array( 'amount' => 100000, 'term' => 12 );

        $amount = isset( $_GET['tutar'] )
            ? pv_v7_credit_clean_int( wp_unslash( $_GET['tutar'] ), $defaults['amount'] )
            : 0;
        $term = isset( $_GET['vade'] )
            ? pv_v7_credit_clean_int( wp_unslash( $_GET['vade'] ), $defaults['term'] )
            : 0;

        if ( ! $amount || ! $term ) {
            $request = '';
            if ( isset( $wp ) && ! empty( $wp->request ) ) {
                $request = (string) $wp->request;
            } elseif ( isset( $_SERVER['REQUEST_URI'] ) ) {
                $path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
                $request = is_string( $path ) ? trim( $path, '/' ) : '';
            }

            $last = basename( $request );
            if ( preg_match( '/(\d+)-ay-(\d+)-tl-kredi/i', $last, $match ) ) {
                $term   = (int) $match[1];
                $amount = (int) $match[2];
            }
        }

        if ( ! $amount ) { $amount = (int) $defaults['amount']; }
        if ( ! $term ) { $term = (int) $defaults['term']; }

        $_GET['type']  = $type;
        $_GET['vade']  = $term;
        $_GET['tutar'] = $amount;

        return array( 'type' => $type, 'amount' => $amount, 'term' => $term );
    }
}

if ( ! function_exists( 'pv_v7_credit_redirect_pretty_if_needed' ) ) {
    function pv_v7_credit_redirect_pretty_if_needed( $type ) {
        if ( empty( $_GET['type'] ) || empty( $_GET['vade'] ) || empty( $_GET['tutar'] ) ) {
            return;
        }

        $urls   = pv_v7_credit_urls();
        $amount = pv_v7_credit_clean_int( wp_unslash( $_GET['tutar'] ), 0 );
        $term   = pv_v7_credit_clean_int( wp_unslash( $_GET['vade'] ), 0 );

        if ( ! $amount || ! $term || empty( $urls[ $type ] ) ) {
            return;
        }

        $slug = sanitize_title( $term . '-ay-' . $amount . '-tl-kredi' );
        wp_safe_redirect( trailingslashit( $urls[ $type ] ) . $slug . '/' );
        exit;
    }
}

function pv_credit_hangikredi_text( $html ) {
    $html = html_entity_decode( (string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $html ) ) );
}

function pv_credit_hangikredi_match( $pattern, $html ) {
    if ( preg_match( $pattern, (string) $html, $match ) && isset( $match[1] ) ) {
        return pv_credit_hangikredi_text( $match[1] );
    }
    return '';
}

function pv_credit_hangikredi_need_offers( $amount, $term ) {
    $amount = max( 1, (int) $amount );
    $term   = max( 1, (int) $term );
    $url    = 'https://www.hangikredi.com/kredi/ihtiyac-kredisi/sorgulama/' . $term . '-ay-' . $amount . '-tl-kredi';

    $cache_key = 'pv_credit_need_' . md5( $term . '|' . $amount );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) && ! empty( $cached['banka'] ) ) {
        return $cached;
    }

    $response = wp_safe_remote_get( $url, array(
        'timeout'     => 20,
        'redirection' => 4,
        'headers'     => array(
            'Accept'     => 'text/html,application/xhtml+xml',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/152 Safari/537.36',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        return array();
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    $html   = wp_remote_retrieve_body( $response );
    if ( $status < 200 || $status >= 300 || ! is_string( $html ) || $html === '' ) {
        return array();
    }

    preg_match_all( '@class=["\']card__container["\'](.*?)<div[^>]+class=["\']card__footer["\']@si', $html, $cards );
    if ( empty( $cards[1] ) ) {
        return array();
    }

    $out = array(
        'banka'          => array(),
        'kredi'          => array(),
        'faiz'           => array(),
        'tahsis_ucreti'  => array(),
        'aylik_taksit'   => array(),
        'toplam_odeme'   => array(),
        'id'             => array(),
        '_pv_source'     => 'hangikredi',
    );

    foreach ( $cards[1] as $card ) {
        $bank = pv_credit_hangikredi_match( '@data-testid=["\']bankName["\'][^>]*>(.*?)</[^>]+>@si', $card );
        $loan = pv_credit_hangikredi_match( '@data-testid=["\']name["\'][^>]*>(.*?)</[^>]+>@si', $card );
        $rate = pv_credit_hangikredi_match( '@data-testid=["\']rate["\'][^>]*>(.*?)</div>@si', $card );
        $fee  = pv_credit_hangikredi_match( '@data-testid=["\']expenseAmount["\'][^>]*>(.*?)</td>@si', $card );
        $monthly = pv_credit_hangikredi_match( '@data-testid=["\']monthlyInstallment["\'][^>]*>(.*?)</p>@si', $card );
        $total   = pv_credit_hangikredi_match( '@data-testid=["\']totalAmount["\'][^>]*>(.*?)</(?:div|span|p)>@si', $card );

        if ( $bank === '' || $rate === '' || $monthly === '' || $total === '' ) {
            continue;
        }

        $i = count( $out['banka'] );
        $out['banka'][ $i ]         = $bank;
        $out['kredi'][ $i ]         = $loan !== '' ? $loan : 'İhtiyaç Kredisi';
        $out['faiz'][ $i ]          = str_replace( '%', '', $rate );
        $out['tahsis_ucreti'][ $i ] = $fee !== '' ? $fee : '0';
        $out['aylik_taksit'][ $i ]  = $monthly;
        $out['toplam_odeme'][ $i ]  = $total;
        $out['id'][ $i ]            = sanitize_title( $bank );
    }

    if ( empty( $out['banka'] ) ) {
        return array();
    }

    set_transient( $cache_key, $out, 5 * MINUTE_IN_SECONDS );
    return $out;
}

if ( ! function_exists( 'pv_v7_credit_fetch_offers' ) ) {
    function pv_v7_credit_fetch_offers( $type, $amount, $term ) {
        if ( $type === 'ihtiyac' ) {
            $live = pv_credit_hangikredi_need_offers( $amount, $term );
            if ( ! empty( $live['banka'] ) && is_array( $live['banka'] ) ) {
                return $live;
            }
        }

        return function_exists( 'pv_v7_credit_fallback_offers' )
            ? pv_v7_credit_fallback_offers( $type, $amount, $term )
            : array();
    }
}

if ( ! function_exists( 'pv_v7_credit_bank_slug' ) ) {
    function pv_v7_credit_bank_slug( $bank ) {
        $slug = sanitize_title( (string) $bank );
        $slug = str_replace(
            array( 'turkiye-is-bankasi', 'yapi-kredi-bankasi', 'ing-bank', 'teb', 'finansbank' ),
            array( 'is-bankasi', 'yapi-kredi', 'ing', 'cepteteb', 'qnb-finansbank' ),
            $slug
        );
        return sanitize_title( $slug );
    }
}

if ( ! function_exists( 'pv_v7_credit_bank_logo' ) ) {
    function pv_v7_credit_bank_logo( $bank ) {
        return '';
    }
}
