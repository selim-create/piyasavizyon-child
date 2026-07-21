<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'pv_v252_inner_masthead' ) ) {
    function pv_v252_inner_masthead() {
        echo '<div class="wrap pv-inner-masthead-wrap">';
        if ( function_exists( 'pv_v7_ad' ) ) {
            pv_v7_ad( 'pv_header_ad', '970×250 / 970×90 Üst Banner Reklam Alanı', 'ad ad-970 pv-header-masthead pv-ad-desktop' );
            pv_v7_ad( 'pv_mobile_masthead', '320×100 / 320×150 Mobil Masthead Reklam', 'ad ad-mobile-masthead pv-ad-mobile' );
        }
        echo '</div>';
    }
}

if ( ! function_exists( 'pv_v252_adbar' ) ) {
    function pv_v252_adbar( $label = '728×90 Reklam Alanı' ) {
        echo '<div class="pv-corp-adbar" aria-label="Reklam alanı">';
        if ( function_exists( 'pv_v7_ad' ) && is_active_sidebar( 'pv_content_ad' ) ) {
            pv_v7_ad( 'pv_content_ad', $label, 'pv-corp-adbar-inner' );
        } else {
            echo '<span>' . esc_html( $label ) . '</span>';
        }
        echo '</div>';
    }
}

if ( ! function_exists( 'pv_v252_page_content' ) ) {
    function pv_v252_page_content() {
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                $content = trim( get_the_content() );
                if ( $content ) {
                    echo '<section class="pv-corp-inline-content">';
                    the_content();
                    echo '</section>';
                }
            }
            wp_reset_postdata();
        }
    }
}

if ( ! function_exists( 'pv_v252_side_nav' ) ) {
    function pv_v252_side_nav( $current = '' ) {
        $items = array(
            'hakkimizda' => array( 'Hakkımızda', home_url( '/hakkimizda/' ) ),
            'kunye' => array( 'Künye', home_url( '/kunye/' ) ),
            'iletisim' => array( 'İletişim', home_url( '/iletisim/' ) ),
            'bize-reklam-ver' => array( 'Bize Reklam Ver', home_url( '/bize-reklam-ver/' ) ),
            'bulten' => array( 'Bülten', home_url( '/bulten/' ) ),
        );
        echo '<aside class="pv-corp-side" aria-label="Kurumsal menü">';
        echo '<div class="pv-corp-side-card"><h3>Kurumsal</h3>';
        foreach ( $items as $key => $item ) {
            $mark = $key === $current ? ' <span>●</span>' : '<span>›</span>';
            echo '<a href="' . esc_url( $item[1] ) . '"><b>' . esc_html( $item[0] ) . '</b>' . $mark . '</a>';
        }
        echo '</div>';
        echo '<div class="pv-corp-side-card"><h3>Hızlı Erişim</h3>';
        $quick = array(
            array( 'Borsa', '/borsa/' ),
            array( 'Döviz Kurları', '/doviz/' ),
            array( 'Altın Piyasaları', '/altin/' ),
            array( 'Kripto Paralar', '/kripto-para/' ),
            array( 'Halka Arz', '/halka-arz/' ),
            array( 'Kredi Hesapla', '/kredi-hesapla/' ),
        );
        foreach ( $quick as $q ) {
            echo '<a href="' . esc_url( home_url( $q[1] ) ) . '"><b>' . esc_html( $q[0] ) . '</b><span>›</span></a>';
        }
        echo '</div>';
        echo '</aside>';
    }
}

if ( ! function_exists( 'pv_v252_market_side_card' ) ) {
    function pv_v252_market_side_card() {
        if ( ! function_exists( 'pv_v7_ticker_items' ) ) { return; }
        echo '<div class="pv-corp-side-card"><h3>Piyasa Özeti</h3><div class="pv-corp-mini-market">';
        foreach ( array_slice( pv_v7_ticker_items(), 0, 5 ) as $item ) {
            $rate = isset( $item['rate'] ) ? (string) $item['rate'] : '';
            $cls  = ( strpos( $rate, '-' ) !== false ) ? 'down' : 'up';
            echo '<a href="' . esc_url( $item['url'] ?? '#' ) . '"><span><small>' . esc_html( $item['name'] ?? '' ) . '</small><b>' . esc_html( $item['value'] ?? '—' ) . '</b></span><em class="' . esc_attr( $cls ) . '">' . esc_html( $rate ?: '—' ) . '</em></a>';
        }
        echo '</div></div>';
    }
}

if ( ! function_exists( 'pv_v252_latest_posts' ) ) {
    function pv_v252_latest_posts( $title = 'Son Haberler', $count = 3, $args = array() ) {
        $q = new WP_Query( array_merge( array(
            'post_type' => 'post',
            'posts_per_page' => $count,
            'ignore_sticky_posts' => true,
        ), $args ) );
        if ( ! $q->have_posts() ) { return; }
        echo '<section class="pv-corp-card"><div class="pv-corp-section-head"><div><h2>' . esc_html( $title ) . '</h2><p>Piyasa gündeminden öne çıkan başlıklar.</p></div><a class="pv-corp-badge" href="' . esc_url( home_url( '/haberler/' ) ) . '">Tüm Haberler</a></div><div class="pv-corp-news-grid">';
        while ( $q->have_posts() ) {
            $q->the_post();
            $img = function_exists( 'pv_v7_img' ) ? pv_v7_img( get_the_ID(), 'medium_large' ) : get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
            $cat = get_the_category();
            echo '<a class="pv-corp-post" href="' . esc_url( get_permalink() ) . '">';
            echo '<div class="pv-corp-post-img" ' . ( $img ? 'style="background-image:url(' . esc_url( $img ) . ')"' : '' ) . '></div>';
            echo '<div class="pv-corp-post-body"><small>' . esc_html( $cat ? $cat[0]->name : 'Haber' ) . '</small><h3>' . esc_html( get_the_title() ) . '</h3><p>' . esc_html( wp_trim_words( get_the_excerpt(), 18 ) ) . '</p></div>';
            echo '</a>';
        }
        wp_reset_postdata();
        echo '</div></section>';
    }
}
