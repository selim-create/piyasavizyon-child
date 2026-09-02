<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$query  = isset( $_GET['a'] ) ? sanitize_text_field( wp_unslash( $_GET['a'] ) ) : '';
$detail = $query !== '' ? pv_market_gold_detail( $query ) : array();

if ( empty( $detail['name'] ) ) {
    status_header( 404 );
}

$name       = ! empty( $detail['name'] ) ? (string) $detail['name'] : 'Altın Detayı';
$price      = isset( $detail['price'] ) && $detail['price'] !== '' ? (string) $detail['price'] : '-';
$buying     = isset( $detail['buying'] ) ? (string) $detail['buying'] : '';
$selling    = isset( $detail['selling'] ) ? (string) $detail['selling'] : '';
$change_pct = isset( $detail['change_pct'] ) ? (string) $detail['change_pct'] : '';
$update     = isset( $detail['update'] ) ? (string) $detail['update'] : '';
$stats      = isset( $detail['stats'] ) && is_array( $detail['stats'] ) ? $detail['stats'] : array();
$exchange   = isset( $detail['exchange_id'] ) ? (string) $detail['exchange_id'] : '';
$windows    = pv_market_gold_chart_windows();
$charts     = array();

if ( $exchange !== '' ) {
    foreach ( $windows as $window_key => $window ) {
        $charts[ $window_key ] = pv_market_bigpara_gold_chart( $exchange, $window['period'] );
    }
}

$numeric_change = (float) str_replace( array( '.', ',', '%' ), array( '', '.', '' ), $change_pct );
$direction      = $numeric_change > 0 ? 'increase' : ( $numeric_change < 0 ? 'decrease' : 'neutral' );
$color          = $numeric_change > 0 ? '#32ba5b' : ( $numeric_change < 0 ? '#ef291f' : '#667085' );

add_filter( 'pre_get_document_title', function() use ( $name ) {
    return $name . ' - ' . get_bloginfo( 'name' );
}, 20 );

get_header();
?>
<script src="https://code.highcharts.com/7.1.1/highcharts.js"></script>
<div class="site-wrapper pv-market-native pv-market-gold-detail-native">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft">
                <div class="singleWrapper">
                    <div class="breadcrumb">
                        <ul class="block">
                            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/altin/' ) ); ?>">Altın Fiyatları<i>/</i></a></li>
                            <li class="post bg"><span><?php echo esc_html( $name ); ?></span></li>
                        </ul>
                    </div>

                    <h1 class="singlePageTitle"><?php echo esc_html( $name ); ?></h1>

                    <div class="pv-market-detail-content pv-gold-detail-content">
                        <div class="mainContent onsAltin">
                            <div class="main">
                                <?php if ( empty( $detail['name'] ) || $price === '-' ) : ?>
                                    <div class="widget"><div class="pv-market-empty">Altın verisi şu anda alınamıyor.</div></div>
                                <?php else : ?>
                                    <div class="widget" style="margin-bottom:15px;">
                                        <div class="categoryTab pv-gold-detail-tabs">
                                            <div class="catTabContent">
                                                <div class="borsaValue">
                                                    <span>Son</span><?php echo esc_html( $price ); ?>
                                                    <div class="borsaRate" style="color:<?php echo esc_attr( $color ); ?> !important;">
                                                        <?php if ( $direction !== 'neutral' ) : ?><i class="<?php echo esc_attr( $direction ); ?>"></i><?php endif; ?>
                                                        (<?php echo esc_html( $change_pct !== '' ? $change_pct : '0,00' ); ?>%)
                                                    </div>
                                                </div>
                                                <?php if ( $update !== '' ) : ?>
                                                    <div class="lastUpdate">Son Güncelleme: <?php echo esc_html( $update ); ?></div>
                                                <?php endif; ?>
                                                <div class="clear"></div>

                                                <div class="borsaTimerTabHead bg pv-gold-period-head">
                                                    <ul>
                                                        <?php $first = true; foreach ( $windows as $window_key => $window ) : ?>
                                                            <li class="<?php echo $first ? 'active' : ''; ?>" data-pv-gold-window="<?php echo esc_attr( $window_key ); ?>"><span><?php echo esc_html( $window['label'] ); ?></span></li>
                                                        <?php $first = false; endforeach; ?>
                                                    </ul>
                                                </div>

                                                <?php $first = true; foreach ( $windows as $window_key => $window ) : ?>
                                                    <div class="borsaTimerTabContent pv-gold-chart-panel" data-pv-gold-window-panel="<?php echo esc_attr( $window_key ); ?>" style="<?php echo $first ? 'display:block;' : 'display:none;'; ?>">
                                                        <?php if ( ! empty( $charts[ $window_key ] ) ) : ?>
                                                            <div class="currencyChart" id="pv_gold_<?php echo esc_attr( $window_key ); ?>"></div>
                                                        <?php else : ?>
                                                            <div class="pv-market-empty">Bu dönem için grafik verisi şu anda alınamıyor.</div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php $first = false; endforeach; ?>

                                                <div class="clear"></div>
                                                <p style="margin-bottom:15px;margin-top:15px;">* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ( $buying !== '' || $selling !== '' || $stats ) : ?>
                                        <div class="widget">
                                            <div class="currencyShowcase fullShowcase mobileBottomNo">
                                                <table class="currencyTable currencyFullTable">
                                                    <tr><th>Gösterge</th><th>Değer</th></tr>
                                                    <?php if ( $buying !== '' ) : ?><tr><td><b>Alış</b></td><td><?php echo esc_html( $buying ); ?></td></tr><?php endif; ?>
                                                    <?php if ( $selling !== '' ) : ?><tr><td><b>Satış</b></td><td><?php echo esc_html( $selling ); ?></td></tr><?php endif; ?>
                                                    <?php foreach ( array_slice( $stats, 0, 12, true ) as $label => $value ) : ?>
                                                        <tr><td><b><?php echo esc_html( $label ); ?></b></td><td><?php echo esc_html( $value ); ?></td></tr>
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
                pv_v7_market_sidebar( 'altin-detay' );
            } ?>
        </div>
        <?php dynamic_sidebar( 'Sayfa Alt (Altın Detay)' ); ?>
    </section>
    <div class="clear"></div>
</div>

<?php if ( $exchange !== '' ) : ?>
<script>
(function(){
    if (typeof Highcharts === 'undefined') return;
    var charts = <?php echo wp_json_encode( $charts ); ?>;
    var windows = <?php echo wp_json_encode( $windows ); ?>;
    var name = <?php echo wp_json_encode( $name ); ?>;

    Object.keys(windows).forEach(function(key){
        if (!Array.isArray(charts[key]) || !charts[key].length) return;
        Highcharts.chart('pv_gold_' + key, {
            chart: { zoomType: 'x' },
            title: { text: name + ' ' + windows[key].title },
            xAxis: { type: 'datetime' },
            yAxis: { title: { text: '' } },
            legend: { enabled: false },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: { x1:0, y1:0, x2:0, y2:1 },
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: { radius: 2 },
                    lineWidth: 1,
                    states: { hover: { lineWidth: 1 } },
                    threshold: null
                }
            },
            series: [{ type:'area', name:name, data:charts[key] }]
        });
    });

    var root = document.querySelector('.pv-gold-detail-tabs');
    if (!root) return;
    root.querySelectorAll('[data-pv-gold-window]').forEach(function(tab){
        tab.addEventListener('click', function(){
            var key = tab.getAttribute('data-pv-gold-window');
            root.querySelectorAll('[data-pv-gold-window]').forEach(function(item){ item.classList.remove('active'); });
            root.querySelectorAll('[data-pv-gold-window-panel]').forEach(function(item){ item.style.display = 'none'; });
            tab.classList.add('active');
            var target = root.querySelector('[data-pv-gold-window-panel="' + key + '"]');
            if (target) {
                target.style.display = 'block';
                if (window.Highcharts) {
                    setTimeout(function(){
                        window.Highcharts.charts.forEach(function(chart){ if (chart) chart.reflow(); });
                    }, 0);
                }
            }
        });
    });
})();
</script>
<?php endif; ?>
<?php get_footer(); ?>
