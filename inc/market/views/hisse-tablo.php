<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$rows = function_exists( 'pv_market_mynet_stocks' ) ? pv_market_mynet_stocks() : array();
?>
<div class="site-wrapper pv-market-native pv-market-stocks-native">
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
                    <div class="pv-market-list-content pv-stocks-content">
                        <div class="mainContent">
                            <div class="main">
                                <div class="widget">
                                    <div class="currencyShowcase fullShowcase mobileBottomNo">
                                        <table class="currencyTable currencyFullTable">
                                            <tr>
                                                <th>Hisse</th>
                                                <th>Son</th>
                                                <th>Değişim</th>
                                                <?php if ( ! wp_is_mobile() ) : ?><th>Hacim (TL)</th><th>Güncelleme</th><?php endif; ?>
                                            </tr>
                                            <?php foreach ( $rows as $row ) :
                                                $detail_url = home_url( '/hisse/?h=' . rawurlencode( $row['slug'] ) );
                                                ?>
                                                <tr>
                                                    <td><a href="<?php echo esc_url( $detail_url ); ?>"><b><?php echo esc_html( mb_substr( $row['name'], 0, 50, 'UTF-8' ) ); ?></b></a></td>
                                                    <td><i class="<?php echo esc_attr( $row['direction'] ); ?>"></i><?php echo esc_html( $row['last'] !== '' ? $row['last'] : '-' ); ?></td>
                                                    <td><span class="subtract <?php echo esc_attr( $row['direction'] ); ?>"><?php echo esc_html( $row['change'] !== '' ? $row['change'] : '-' ); ?></span></td>
                                                    <?php if ( ! wp_is_mobile() ) : ?>
                                                        <td><?php echo esc_html( $row['volume'] !== '' ? $row['volume'] : '-' ); ?></td>
                                                        <td><?php echo esc_html( $row['time'] !== '' ? $row['time'] : '-' ); ?></td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if ( $rows === array() ) : ?>
                                                <tr><td colspan="5">Hisse verileri şu anda alınamadı.</td></tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) { pv_v7_market_sidebar( 'tum-hisseler' ); } ?>
        </div>
        <?php dynamic_sidebar( 'Sayfa Alt (Tüm Hisseler)' ); ?>
    </section>
    <div class="clear"></div>
</div>
<?php get_footer(); ?>
