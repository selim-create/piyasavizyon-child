<?php
/*
  Template Name: Döviz Tablo
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $currency_data;

get_header();
?>
<style>
.pv-market-currency-native .currencyTable tr th{font-weight:500;}
.pv-market-currency-native .currencyTable tr td b{color:#3b72de!important;}
.pv-market-currency-native .pv-currency-code{display:inline-flex;align-items:center;justify-content:center;min-width:42px;height:26px;margin-right:8px;padding:0 9px;border-radius:999px;background:#eef4ff;color:#2057b8;font-size:11px;font-weight:800;vertical-align:middle;}
.pv-market-currency-native .pv-currency-name-link{display:inline-flex;align-items:center;text-decoration:none;}
</style>
<div class="site-wrapper pv-market-native pv-market-currency-native">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft">
                <div class="singleWrapper">
                    <div class="breadcrumb">
                        <ul class="block">
                            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                            <li class="post bg"><span><?php the_title(); ?></span></li>
                        </ul>
                    </div>

                    <h1 class="postTitle"><?php the_title(); ?></h1>

                    <div class="pv-market-list-content pv-currency-list-content">
                        <div class="mainContent">
                            <div class="main">
                                <div class="widget">
                                    <div class="currencyShowcase fullShowcase mobileBottomNo">
                                        <?php if ( wp_is_mobile() ) : ?>
                                            <table class="currencyTable currencyFullTable">
                                                <tr>
                                                    <th style="width:70%!important;display:inline-block;">Döviz</th>
                                                    <th class="sagagit2" style="width:20%;display:inline-block;padding-left:0;">Alış</th>
                                                </tr>
                                                <?php if ( is_array( $currency_data ) && ! empty( $currency_data['code'] ) ) : ?>
                                                    <?php foreach ( array_unique( $currency_data['code'] ) as $key => $val ) : ?>
                                                        <?php
                                                        if ( ! isset( $currency_data['full_name'][ $key ], $currency_data['selling'][ $key ] ) ) {
                                                            continue;
                                                        }
                                                        $change        = isset( $currency_data['change_rate'][ $key ] ) ? (string) $currency_data['change_rate'][ $key ] : '0';
                                                        $crease_status = (float) str_replace( ',', '.', $change ) > 0 ? 'increase' : 'decrease';
                                                        $code          = strtoupper( (string) $currency_data['code'][ $key ] );
                                                        $detail_url    = pv_market_currency_detail_url( $key );
                                                        ?>
                                                        <tr class="alt dKurlariS">
                                                            <td style="width:70%!important;display:inline-block;">
                                                                <a class="pv-currency-name-link" href="<?php echo esc_url( $detail_url ); ?>">
                                                                    <span class="pv-currency-code"><?php echo esc_html( $code ); ?></span>
                                                                    <b><?php echo esc_html( $code ); ?></b>
                                                                </a>
                                                            </td>
                                                            <td style="width:20%;display:inline-block;padding-left:0;">
                                                                <i class="<?php echo esc_attr( $crease_status ); ?>" style="<?php if ( ! is_user_logged_in() ) : ?>position:relative;top:26px;<?php endif; ?>"></i>
                                                                <span><?php echo esc_html( $currency_data['selling'][ $key ] ); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </table>
                                        <?php else : ?>
                                            <table class="currencyTable currencyFullTable">
                                                <tr>
                                                    <th>Döviz</th>
                                                    <th>Alış</th>
                                                    <th>Satış</th>
                                                    <th>Fark</th>
                                                    <th>Saat</th>
                                                </tr>
                                                <?php if ( is_array( $currency_data ) && ! empty( $currency_data['code'] ) ) : ?>
                                                    <?php foreach ( array_unique( $currency_data['code'] ) as $key => $val ) : ?>
                                                        <?php
                                                        if ( ! isset( $currency_data['full_name'][ $key ], $currency_data['selling'][ $key ], $currency_data['buying'][ $key ] ) ) {
                                                            continue;
                                                        }
                                                        $change        = isset( $currency_data['change_rate'][ $key ] ) ? (string) $currency_data['change_rate'][ $key ] : '0';
                                                        $is_up         = (float) str_replace( ',', '.', $change ) > 0;
                                                        $crease_status = $is_up ? 'increase' : 'decrease';
                                                        $color         = $is_up ? '#40bc9a' : '#fc4b67';
                                                        $code          = strtoupper( (string) $currency_data['code'][ $key ] );
                                                        $name          = (string) $currency_data['full_name'][ $key ];
                                                        $time          = isset( $currency_data['time'][ $key ] ) ? (string) $currency_data['time'][ $key ] : '';
                                                        $detail_url    = pv_market_currency_detail_url( $key );
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <a class="pv-currency-name-link" href="<?php echo esc_url( $detail_url ); ?>">
                                                                    <span class="pv-currency-code"><?php echo esc_html( $code ); ?></span>
                                                                    <b><?php echo esc_html( $name . ' - ' . $code ); ?></b>
                                                                </a>
                                                            </td>
                                                            <td style="font-weight:500;color:<?php echo esc_attr( $color ); ?>;"><i class="<?php echo esc_attr( $crease_status ); ?>"></i> <?php echo esc_html( $currency_data['selling'][ $key ] ); ?></td>
                                                            <td style="font-weight:normal;"><?php echo esc_html( $currency_data['buying'][ $key ] ); ?></td>
                                                            <td style="font-weight:normal;"><span class="<?php echo esc_attr( $crease_status ); ?> subtract">% <?php echo esc_html( $change ); ?></span></td>
                                                            <td style="padding:0 15px;font-weight:normal;"><?php echo esc_html( $time ); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </table>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) {
                pv_v7_market_sidebar( 'doviz-kurlari' );
            } ?>
        </div>
    </section>
    <div class="clear"></div>
</div>
<?php get_footer(); ?>
