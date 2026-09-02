<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$query  = isset( $_GET['p'] ) ? sanitize_text_field( wp_unslash( $_GET['p'] ) ) : '';
$detail = $query !== '' ? pv_market_mynet_parity_detail( $query ) : array();

if ( empty( $detail['name'] ) ) {
    status_header( 404 );
}

$name       = isset( $detail['name'] ) && $detail['name'] !== '' ? $detail['name'] : 'Parite Detayı';
$price      = isset( $detail['price'] ) && $detail['price'] !== '' ? $detail['price'] : '-';
$change_pct = isset( $detail['change_pct'] ) ? (string) $detail['change_pct'] : '';
$update     = isset( $detail['update'] ) ? (string) $detail['update'] : '';
$stats      = isset( $detail['stats'] ) && is_array( $detail['stats'] ) ? $detail['stats'] : array();
$chart      = isset( $detail['chart'] ) && is_array( $detail['chart'] ) ? array_values( $detail['chart'] ) : array();
$windows    = pv_market_parity_chart_windows();

$numeric_change = (float) str_replace( array( '.', ',', '%' ), array( '', '.', '' ), $change_pct );
$direction      = $numeric_change > 0 ? 'increase' : ( $numeric_change < 0 ? 'decrease' : 'neutral' );
$color          = $numeric_change > 0 ? '#32ba5b' : ( $numeric_change < 0 ? '#ef291f' : '#667085' );

add_filter( 'pre_get_document_title', function() use ( $name ) {
    return $name . ' - ' . get_bloginfo( 'name' );
}, 20 );

get_header();
?>
<script src="https://code.highcharts.com/7.1.1/highcharts.js"></script>
<div class="site-wrapper pv-market-native pv-market-parity-detail-native">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft">
                <div class="singleWrapper">
                    <div class="breadcrumb">
                        <ul class="block">
                            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/tum-pariteler/' ) ); ?>">Pariteler<i>/</i></a></li>
                            <li class="post bg"><span><?php echo esc_html( $name ); ?></span></li>
                        </ul>
                    </div>

                    <h1 class="singlePageTitle"><?php echo esc_html( $name ); ?></h1>

                    <div class="pv-market-detail-content pv-parity-detail-content">
                        <div class="mainContent onsAltin">
                            <div class="main">
                                <?php if ( empty( $detail['name'] ) || $price === '-' ) : ?>
                                    <div class="widget"><div class="pv-market-empty">Parite verisi şu anda alınamıyor.</div></div>
                                <?php else : ?>
                                    <div class="widget" style="margin-bottom:15px;">
                                        <div class="categoryTab pv-parity-detail-tabs">
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

                                                <div class="borsaTimerTabHead bg pv-parity-period-head">
                                                    <ul>
                                                        <?php $first = true; foreach ( $windows as $window_key => $window ) : ?>
                                                            <li class="<?php echo $first ? 'active' : ''; ?>" data-pv-window="<?php echo esc_attr( $window_key ); ?>"><span><?php echo esc_html( $window['label'] ); ?></span></li>
                                                        <?php $first = false; endforeach; ?>
                                                    </ul>
                                                </div>

                                                <?php $first = true; foreach ( $windows as $window_key => $window ) : ?>
                                                    <div class="borsaTimerTabContent pv-parity-chart-panel" data-pv-window-panel="<?php echo esc_attr( $window_key ); ?>" style="<?php echo $first ? 'display:block;' : 'display:none;'; ?>">
                                                        <div class="currencyChart" id="pv_parity_<?php echo esc_attr( $window_key ); ?>"></div>
                                                    </div>
                                                <?php $first = false; endforeach; ?>

                                                <div class="clear"></div>
                                                <p style="margin-bottom:15px;margin-top:15px;">* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ( $stats ) : ?>
                                        <div class="widget">
                                            <div class="currencyShowcase fullShowcase mobileBottomNo">
                                                <table class="currencyTable currencyFullTable">
                                                    <tr><th>Gösterge</th><th>Değer</th></tr>
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
                pv_v7_market_sidebar( 'parite-detay' );
            } ?>
        </div>
    </section>
    <div class="clear"></div>
</div>

<?php if ( $chart ) : ?>
<script>
(function(){
    if (typeof Highcharts === 'undefined') return;
    var allData = <?php echo wp_json_encode( $chart ); ?>;
    var windows = <?php echo wp_json_encode( $windows ); ?>;
    var name = <?php echo wp_json_encode( $name ); ?>;

    Object.keys(windows).forEach(function(key){
        var config = windows[key];
        var cutoff = config.seconds ? (Date.now() - (Number(config.seconds) * 1000)) : 0;
        var data = allData.filter(function(point){
            return Array.isArray(point) && point.length >= 2 && (!cutoff || Number(point[0]) >= cutoff);
        });
        Highcharts.chart('pv_parity_' + key, {
            chart: { zoomType: 'x' },
            title: { text: name + ' ' + config.title },
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
            series: [{ type:'area', name:name, data:data }]
        });
    });

    var root = document.querySelector('.pv-parity-detail-tabs');
    if (!root) return;
    root.querySelectorAll('[data-pv-window]').forEach(function(tab){
        tab.addEventListener('click', function(){
            var key = tab.getAttribute('data-pv-window');
            root.querySelectorAll('[data-pv-window]').forEach(function(item){ item.classList.remove('active'); });
            root.querySelectorAll('[data-pv-window-panel]').forEach(function(item){ item.style.display = 'none'; });
            tab.classList.add('active');
            var target = root.querySelector('[data-pv-window-panel="' + key + '"]');
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
