<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $currency_data;

$query  = isset( $_GET['c'] ) ? sanitize_text_field( wp_unslash( $_GET['c'] ) ) : '';
$detail = $query !== '' ? pv_market_currency_detail( $query ) : array();

if ( empty( $detail['name'] ) ) {
    status_header( 404 );
}

$key        = isset( $detail['key'] ) ? $detail['key'] : $query;
$code       = ! empty( $detail['code'] ) ? strtoupper( (string) $detail['code'] ) : strtoupper( $query );
$name       = ! empty( $detail['name'] ) ? (string) $detail['name'] : 'Döviz Detayı';
$buying     = isset( $detail['buying'] ) ? (string) $detail['buying'] : '';
$selling    = isset( $detail['selling'] ) ? (string) $detail['selling'] : '';
$change_pct = isset( $detail['change_pct'] ) ? (string) $detail['change_pct'] : '';
$update     = isset( $detail['update'] ) ? (string) $detail['update'] : '';
$chart      = isset( $detail['chart'] ) && is_array( $detail['chart'] ) ? array_values( $detail['chart'] ) : array();
$banks      = isset( $detail['banks'] ) && is_array( $detail['banks'] ) ? $detail['banks'] : array();
$windows    = pv_market_currency_chart_windows();

$numeric_change = (float) str_replace( array( '.', ',', '%' ), array( '', '.', '' ), $change_pct );
$direction      = $numeric_change > 0 ? 'increase' : ( $numeric_change < 0 ? 'decrease' : 'neutral' );
$color          = $numeric_change > 0 ? '#32ba5b' : ( $numeric_change < 0 ? '#ef291f' : '#667085' );

$liste_data   = array();
$status_liste = false;
$alarm_liste  = false;
if ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $liste_data   = get_user_meta( $current_user->ID, 'uye_liste', true );
    $liste_data   = is_array( $liste_data ) ? $liste_data : array();
    $status_liste = array_search( $key, $liste_data, true ) !== false;

    $alarm_meta = get_user_meta( $current_user->ID, 'uye_alarm', true );
    $alarm_data = is_array( $alarm_meta ) && isset( $alarm_meta['doviz'] ) && is_array( $alarm_meta['doviz'] ) ? $alarm_meta['doviz'] : array();
    $alarm_liste = array_search( $key, $alarm_data, true ) !== false;
}

add_filter( 'pre_get_document_title', function() use ( $name ) {
    return $name . ' - ' . get_bloginfo( 'name' );
}, 20 );

get_header();
?>
<script src="https://code.highcharts.com/7.1.1/highcharts.js"></script>
<div class="site-wrapper pv-market-native pv-market-currency-detail-native pv-market-currency-detail-child">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft">
                <div class="singleWrapper">
                    <section class="pv-currency-detail-hero">
                        <div class="breadcrumb">
                            <ul class="block">
                                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                                <li><a href="<?php echo esc_url( pv_market_currency_list_url() ); ?>">Döviz Kurları<i>/</i></a></li>
                                <li class="post bg"><span><?php echo esc_html( $name ); ?></span></li>
                            </ul>
                        </div>

                        <h1 class="singlePageTitle floatLeft">
                            <span class="dropCustomCurrency"><?php echo esc_html( $code . ' - ' . $name ); ?> <i class="dropdown-arrow"></i></span>
                            <div class="mCustomScrollbar changeCurrency">
                                <?php if ( is_array( $currency_data ) && ! empty( $currency_data['code'] ) ) : ?>
                                    <?php foreach ( array_unique( $currency_data['code'] ) as $key2 => $val ) : ?>
                                        <?php if ( ! isset( $currency_data['full_name'][ $key2 ] ) ) { continue; } ?>
                                        <a href="<?php echo esc_url( pv_market_currency_detail_url( $key2 ) ); ?>"><?php echo esc_html( $currency_data['full_name'][ $key2 ] ); ?></a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </h1>
                        <div class="clearfix"></div>
                    </section>

                    <div class="pv-market-detail-content pv-currency-detail-content">
                        <div class="mainContent">
                            <div class="main">
                                <?php if ( empty( $detail['name'] ) ) : ?>
                                    <div class="widget"><div class="pv-market-empty">Döviz verisi şu anda alınamıyor.</div></div>
                                <?php else : ?>
                                    <div class="widget">
                                        <div class="borsaValue kurTrade"><span>Alış</span><?php echo esc_html( $buying !== '' ? $buying : '-' ); ?></div>
                                        <div class="borsaValue kurTrade">
                                            <span>Satış</span><?php echo esc_html( $selling !== '' ? $selling : '-' ); ?>
                                            <div class="borsaRate" style="color:<?php echo esc_attr( $color ); ?> !important;">
                                                <?php if ( $direction !== 'neutral' ) : ?><i class="<?php echo esc_attr( $direction ); ?>"></i><?php endif; ?>
                                                (<?php echo esc_html( $change_pct !== '' ? $change_pct : '0,00' ); ?> %)
                                            </div>
                                        </div>
                                        <?php if ( $update !== '' ) : ?><div class="lastUpdate2">Son Güncelleme: <?php echo esc_html( $update ); ?></div><?php endif; ?>
                                        <div class="clear"></div>

                                        <div class="borsaTimerTabHead bg pv-currency-period-head">
                                            <ul>
                                                <?php $first = true; foreach ( $windows as $window_key => $window ) : ?>
                                                    <li class="<?php echo $first ? 'active' : ''; ?>" data-pv-currency-window="<?php echo esc_attr( $window_key ); ?>"><span><?php echo esc_html( $window['label'] ); ?></span></li>
                                                <?php $first = false; endforeach; ?>
                                            </ul>
                                            <div class="userNotification">
                                                <?php if ( is_user_logged_in() ) : ?>
                                                    <?php if ( $status_liste ) : ?>
                                                        <a href="javascript:;" onclick="listedenCikar('<?php echo esc_js( $key ); ?>')" class="addList">ÇIKAR<i class="remove"></i></a>
                                                    <?php else : ?>
                                                        <a href="javascript:;" onclick="listemeEkle('<?php echo esc_js( $key ); ?>')" class="addList">LİSTEME EKLE<i class="add"></i></a>
                                                    <?php endif; ?>
                                                    <?php if ( $alarm_liste ) : ?>
                                                        <a href="javascript:;" onclick="alarmCikar('<?php echo esc_js( $key ); ?>')" class="alarmKur">ÇIKAR <i class="remove"></i></a>
                                                    <?php else : ?>
                                                        <a href="javascript:;" onclick="alarmKur('<?php echo esc_js( $key ); ?>')" class="alarmKur">ALARM KUR <i class="ring"></i></a>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <a href="javascript:;" onclick="girisYap();" class="addList">LİSTEME EKLE <i class="add"></i></a>
                                                    <a href="javascript:;" onclick="girisYap();" class="alarmKur">ALARM KUR <i class="ring"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php $first = true; foreach ( $windows as $window_key => $window ) : ?>
                                            <div class="borsaTimerTabContent pv-currency-chart-panel" data-pv-currency-window-panel="<?php echo esc_attr( $window_key ); ?>" style="<?php echo $first ? 'display:block;' : 'display:none;'; ?>">
                                                <?php if ( $chart ) : ?>
                                                    <div class="currencyChart" id="pv_currency_<?php echo esc_attr( $window_key ); ?>"></div>
                                                <?php else : ?>
                                                    <div class="pv-market-empty">Grafik verisi şu anda alınamıyor.</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php $first = false; endforeach; ?>
                                        <p>* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                    </div>

                                    <?php if ( $banks ) : ?>
                                        <div class="widget">
                                            <div class="financeBar">
                                                <div class="financeBlockBig lastFinanceBlock">
                                                    <div class="financeBlockHead kur"><?php echo esc_html( mb_strtoupper( $name, 'UTF-8' ) ); ?> BANKA KURLARI</div>
                                                    <div class="currencyShowcase fullShowcase mobileBottomNo">
                                                        <table class="currencyTable currencyFullTable">
                                                            <tr><th>Banka</th><th>Alış</th><th>Satış</th></tr>
                                                            <?php foreach ( $banks as $bank ) : ?>
                                                                <tr>
                                                                    <td><?php echo esc_html( $bank['name'] ); ?></td>
                                                                    <td><?php echo esc_html( $bank['buying'] ); ?></td>
                                                                    <td><?php echo esc_html( $bank['selling'] ); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </table>
                                                    </div>
                                                </div>
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
                pv_v7_market_sidebar( 'doviz-detay' );
            } ?>
        </div>
        <?php dynamic_sidebar( 'Sayfa Alt (Döviz Detay)' ); ?>
    </section>
    <div class="clear"></div>
</div>

<?php if ( $chart ) : ?>
<script>
(function(){
    if (typeof Highcharts === 'undefined') return;
    var allData = <?php echo wp_json_encode( $chart ); ?>;
    var windows = <?php echo wp_json_encode( $windows ); ?>;
    var name = <?php echo wp_json_encode( $code . ' - TRY' ); ?>;

    Object.keys(windows).forEach(function(key){
        var config = windows[key];
        var cutoff = config.seconds ? (Date.now() - (Number(config.seconds) * 1000)) : 0;
        var data = allData.filter(function(point){
            return Array.isArray(point) && point.length >= 2 && (!cutoff || Number(point[0]) >= cutoff);
        });
        Highcharts.chart('pv_currency_' + key, {
            chart: { zoomType: 'x' },
            title: { text: name + ' ' + config.title + ' Grafik Tablosu' },
            xAxis: { type: 'datetime' },
            yAxis: { title: { text: '' } },
            legend: { enabled: false },
            plotOptions: { area: { marker: { radius: 2 }, lineWidth: 1, states: { hover: { lineWidth: 1 } }, threshold: null } },
            series: [{ type:'area', name:name, data:data }]
        });
    });

    var root = document.querySelector('.pv-currency-period-head');
    if (!root) return;
    var container = root.closest('.widget');
    root.querySelectorAll('[data-pv-currency-window]').forEach(function(tab){
        tab.addEventListener('click', function(){
            var key = tab.getAttribute('data-pv-currency-window');
            root.querySelectorAll('[data-pv-currency-window]').forEach(function(item){ item.classList.remove('active'); });
            container.querySelectorAll('[data-pv-currency-window-panel]').forEach(function(item){ item.style.display = 'none'; });
            tab.classList.add('active');
            var target = container.querySelector('[data-pv-currency-window-panel="' + key + '"]');
            if (target) {
                target.style.display = 'block';
                setTimeout(function(){ window.Highcharts.charts.forEach(function(chart){ if (chart) chart.reflow(); }); }, 0);
            }
        });
    });
})();
</script>
<?php endif; ?>

<script>
(function($){
    if (!$) return;
    var endpoint = <?php echo wp_json_encode( trailingslashit( get_template_directory_uri() ) . 'user_api.php' ); ?>;
    window.listemeEkle = function(doviz){ $.post(endpoint + '?type=insert_liste&_=' + Date.now(), {doviz:doviz}, function(result){ if(result==='Ok'){ location.reload(); } else { alert('Bir hata oluştu.'); } }); };
    window.listedenCikar = function(doviz){ $.post(endpoint + '?type=delete_liste&_=' + Date.now(), {doviz:doviz}, function(result){ if(result==='Ok'){ location.reload(); } else { alert('Bir hata oluştu.'); } }); };
    window.alarmCikar = function(doviz){ $.post(endpoint + '?type=delete_alarm&_=' + Date.now(), {doviz:doviz}, function(result){ if(result==='Ok'){ location.reload(); } else { alert('Bir hata oluştu.'); } }); };
    window.alarmKur = function(doviz){ var miktar=prompt('Haberdar olmak istediğiniz miktarı girin'); if(miktar!==null){ $.post(endpoint + '?type=insert_alarm&_=' + Date.now(), {doviz:doviz,miktar:miktar}, function(result){ if(result==='Ok'){ location.reload(); } else { alert('Bir hata oluştu.'); } }); } };
    window.girisYap = function(){ alert('Bu özelliği kullanmak için lütfen giriş yapınız.'); };
})(window.jQuery);
</script>
<?php get_footer(); ?>
