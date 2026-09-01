<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$slug = isset( $_GET['h'] ) ? sanitize_title( wp_unslash( $_GET['h'] ) ) : '';
$detail = $slug !== '' ? pv_market_mynet_stock_detail( $slug ) : array();

if ( empty( $detail['name'] ) ) {
    status_header( 404 );
}

$name       = isset( $detail['name'] ) ? $detail['name'] : 'Hisse Detayı';
$price      = isset( $detail['price'] ) ? $detail['price'] : '-';
$change     = isset( $detail['change'] ) ? $detail['change'] : '-';
$change_pct = isset( $detail['change_pct'] ) ? $detail['change_pct'] : '-';
$update     = isset( $detail['update'] ) ? $detail['update'] : '';
$stats      = isset( $detail['stats'] ) && is_array( $detail['stats'] ) ? $detail['stats'] : array();
$chart      = isset( $detail['chart'] ) && is_array( $detail['chart'] ) ? $detail['chart'] : array();

$numeric_change = (float) str_replace( array( '.', ',', '%' ), array( '', '.', '' ), (string) $change_pct );
$direction      = $numeric_change > 0 ? 'increase' : 'decrease';
$color          = $numeric_change > 0 ? '#32ba5b' : '#ef291f';

add_filter( 'pre_get_document_title', function() use ( $name ) {
    return $name . ' - ' . get_bloginfo( 'name' );
}, 20 );

get_header();
?>
<script src="https://code.highcharts.com/7.1.1/highcharts.js"></script>
<div class="site-wrapper pv-market-native pv-market-stock-detail-native">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft">
                <div class="singleWrapper">
                    <div class="breadcrumb">
                        <ul class="block">
                            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/tum-hisseler/' ) ); ?>">Hisseler<i>/</i></a></li>
                            <li class="post bg"><span><?php echo esc_html( $name ); ?></span></li>
                        </ul>
                    </div>

                    <h1 class="singlePageTitle"><?php echo esc_html( $name ); ?></h1>

                    <div class="pv-market-detail-content pv-stock-detail-content">
                        <div class="mainContent">
                            <div class="main">
                                <?php if ( empty( $detail['name'] ) ) : ?>
                                    <div class="widget"><div class="pv-market-empty">Hisse verisi şu anda alınamıyor.</div></div>
                                <?php else : ?>
                                    <div class="widget" style="margin-bottom:15px;">
                                        <div class="categoryTab">
                                            <div class="catTabContent">
                                                <div class="borsaValue">
                                                    <span>Son</span><?php echo esc_html( $price ); ?>₺
                                                    <div class="borsaRate" style="color:<?php echo esc_attr( $color ); ?> !important;">
                                                        <i class="<?php echo esc_attr( $direction ); ?>"></i>(<?php echo esc_html( $change_pct ); ?>%)
                                                    </div>
                                                </div>
                                                <?php if ( $update !== '' ) : ?>
                                                    <div class="lastUpdate">Son Güncelleme: <?php echo esc_html( $update ); ?></div>
                                                <?php endif; ?>
                                                <div class="clear"></div>

                                                <div class="borsaTimerTabHead bg">
                                                    <ul><li><span>BUGÜN</span></li></ul>
                                                </div>
                                                <div class="borsaTimerTabContent" style="display:block;">
                                                    <div class="currencyChart" id="pv_stock_chart"></div>
                                                    <div class="clear"></div>
                                                    <p>* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ( $stats ) : ?>
                                        <div class="widget">
                                            <div class="currencyShowcase fullShowcase mobileBottomNo">
                                                <table class="currencyTable currencyFullTable">
                                                    <tr><th>Gösterge</th><th>Değer</th></tr>
                                                    <?php
                                                    $preferred = array(
                                                        'Alış', 'Satış', 'Günlük Değişim', 'Günlük Değişim (%)',
                                                        'Günlük Hacim (Lot)', 'Günlük Hacim (TL)', 'Günlük Ortalama',
                                                        'En Düşük', 'En Yüksek'
                                                    );
                                                    foreach ( $preferred as $label ) :
                                                        if ( ! isset( $stats[ $label ] ) ) { continue; }
                                                        ?>
                                                        <tr>
                                                            <td><b><?php echo esc_html( $label ); ?></b></td>
                                                            <td><?php echo esc_html( $stats[ $label ] ); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) {
                pv_v7_market_sidebar( 'hisse-detay' );
            } ?>
        </div>
    </section>
    <div class="clear"></div>
</div>

<?php if ( $chart ) : ?>
<script>
(function(){
    if (typeof Highcharts === 'undefined') return;
    var data = <?php echo wp_json_encode( array_values( $chart ) ); ?>;
    Highcharts.chart('pv_stock_chart', {
        chart: { zoomType: 'x' },
        title: { text: <?php echo wp_json_encode( $name . ' Günlük' ); ?> },
        xAxis: { type: 'datetime' },
        yAxis: { title: { text: '' } },
        legend: { enabled: false },
        series: [{
            type: 'area',
            name: <?php echo wp_json_encode( $name ); ?>,
            data: data
        }]
    });
})();
</script>
<?php endif; ?>
<?php get_footer(); ?>
