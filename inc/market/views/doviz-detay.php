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
$banks      = isset( $detail['banks'] ) && is_array( $detail['banks'] ) ? array_values( $detail['banks'] ) : array();
$windows    = pv_market_currency_chart_windows();

$numeric_change = (float) str_replace( array( '.', ',', '%' ), array( '', '.', '' ), $change_pct );
$direction      = $numeric_change > 0 ? 'increase' : ( $numeric_change < 0 ? 'decrease' : 'neutral' );
$change_class   = $numeric_change > 0 ? 'is-up' : ( $numeric_change < 0 ? 'is-down' : 'is-flat' );

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

<style>
html body .pv-market-currency-detail-child .pv-currency-native-card,
html body .pv-market-currency-detail-child .pv-currency-bank-card{
    width:100%;
    margin:0 0 18px;
    padding:20px;
    background:#fff;
    border:1px solid #dce8f6;
    border-radius:22px;
    box-shadow:0 14px 36px rgba(8,35,78,.07);
}
html body .pv-market-currency-detail-child .pv-currency-price-grid{
    display:grid;
    grid-template-columns:minmax(0,180px) minmax(0,180px) minmax(180px,1fr);
    gap:14px;
    align-items:stretch;
}
html body .pv-market-currency-detail-child .pv-currency-price-box{
    min-height:92px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:14px 16px;
    background:#fbfdff;
    border:1px solid #dce8f6;
    border-radius:18px;
}
html body .pv-market-currency-detail-child .pv-currency-price-box small{
    display:block;
    margin-bottom:7px;
    color:#58708d;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.04em;
}
html body .pv-market-currency-detail-child .pv-currency-price-box strong{
    color:#10203b;
    font-size:23px;
    line-height:1;
    font-weight:900;
}
html body .pv-market-currency-detail-child .pv-currency-change{
    margin-top:7px;
    font-size:12px;
    font-weight:900;
}
html body .pv-market-currency-detail-child .pv-currency-change.is-up{color:#16a56d}
html body .pv-market-currency-detail-child .pv-currency-change.is-down{color:#ef291f}
html body .pv-market-currency-detail-child .pv-currency-change.is-flat{color:#667085}
html body .pv-market-currency-detail-child .pv-currency-updated{
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:92px;
    padding:14px 18px;
    border-radius:18px;
    background:#eef5ff;
    color:#58708d;
    font-size:12px;
    font-weight:900;
    text-align:center;
}
html body .pv-market-currency-detail-child .pv-currency-toolbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    margin-top:16px;
    padding:10px;
    background:#f5f9ff;
    border:1px solid #dce8f6;
    border-radius:18px;
}
html body .pv-market-currency-detail-child .pv-currency-periods,
html body .pv-market-currency-detail-child .pv-currency-actions{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    align-items:center;
}
html body .pv-market-currency-detail-child .pv-currency-period{
    appearance:none;
    border:1px solid #d7e4f5;
    background:#fff;
    color:#58708d;
    border-radius:12px;
    min-height:38px;
    padding:0 13px;
    font:inherit;
    font-size:11px;
    font-weight:900;
    cursor:pointer;
}
html body .pv-market-currency-detail-child .pv-currency-period.active{
    background:#0758c9;
    border-color:#0758c9;
    color:#fff;
}
html body .pv-market-currency-detail-child .pv-currency-action{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:38px;
    padding:0 14px;
    border-radius:12px;
    color:#fff !important;
    font-size:11px;
    font-weight:900;
    text-decoration:none !important;
}
html body .pv-market-currency-detail-child .pv-currency-action.is-list{background:#0758c9}
html body .pv-market-currency-detail-child .pv-currency-action.is-alarm{background:#ef4444}
html body .pv-market-currency-detail-child .pv-currency-chart-panel{
    margin-top:16px;
    padding:0;
}
html body .pv-market-currency-detail-child .pv-currency-chart{
    width:100%;
    min-height:360px;
    border:1px solid #dce8f6;
    border-radius:18px;
    overflow:hidden;
    background:#fff;
}
html body .pv-market-currency-detail-child .pv-currency-market-note{
    margin:12px 2px 0;
    color:#687991;
    font-size:12px;
}
html body .pv-market-currency-detail-child .pv-currency-bank-title{
    margin:0 0 14px;
    color:#10203b;
    font-size:15px;
    font-weight:900;
}
html body .pv-market-currency-detail-child .pv-currency-bank-wrap{
    width:100%;
    overflow:auto;
}
html body .pv-market-currency-detail-child table.pv-currency-bank-table{
    width:100%;
    min-width:520px;
    border-collapse:collapse;
    border-spacing:0;
    font-size:12px;
}
html body .pv-market-currency-detail-child .pv-currency-bank-table th{
    padding:10px 12px;
    border-bottom:1px solid #dce8f6;
    color:#7b8ca5;
    text-align:left;
    font-size:10px;
    font-weight:900;
    text-transform:uppercase;
}
html body .pv-market-currency-detail-child .pv-currency-bank-table td{
    padding:11px 12px;
    border-bottom:1px solid #edf2f8;
    background:#fff;
    color:#243550;
}
html body .pv-market-currency-detail-child .pv-currency-bank-table td:first-child{font-weight:800}
@media(max-width:760px){
    html body .pv-market-currency-detail-child .pv-currency-price-grid{grid-template-columns:1fr 1fr}
    html body .pv-market-currency-detail-child .pv-currency-updated{grid-column:1 / -1;min-height:58px}
    html body .pv-market-currency-detail-child .pv-currency-toolbar{align-items:stretch;flex-direction:column}
    html body .pv-market-currency-detail-child .pv-currency-periods,
    html body .pv-market-currency-detail-child .pv-currency-actions{width:100%}
    html body .pv-market-currency-detail-child .pv-currency-period{flex:1 1 calc(33.333% - 8px)}
    html body .pv-market-currency-detail-child .pv-currency-action{flex:1 1 calc(50% - 8px)}
    html body .pv-market-currency-detail-child .pv-currency-chart{min-height:300px}
}
</style>

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

                        <div class="changeCurrencySource">
                            <span>Serbest Piyasa <i class="dropdown-arrow"></i></span>
                            <?php if ( $banks ) : ?>
                                <div class="mCustomScrollbar changeCurrency">
                                    <?php foreach ( $banks as $bank ) : ?>
                                        <?php
                                        $bank_url = add_query_arg(
                                            array(
                                                'c'     => sanitize_title( (string) $key ),
                                                'banka' => (string) $bank['name'],
                                            ),
                                            home_url( '/doviz/' )
                                        );
                                        ?>
                                        <a href="<?php echo esc_url( $bank_url ); ?>"><?php echo esc_html( $bank['name'] ); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                    </section>

                    <div class="pv-market-detail-content pv-currency-detail-content">
                        <div class="mainContent">
                            <div class="main">
                                <?php if ( empty( $detail['name'] ) ) : ?>
                                    <div class="pv-currency-native-card"><div class="pv-market-empty">Döviz verisi şu anda alınamıyor.</div></div>
                                <?php else : ?>
                                    <section class="pv-currency-native-card">
                                        <div class="pv-currency-price-grid">
                                            <div class="pv-currency-price-box">
                                                <small>Alış</small>
                                                <strong><?php echo esc_html( $buying !== '' ? $buying : '-' ); ?></strong>
                                            </div>
                                            <div class="pv-currency-price-box">
                                                <small>Satış</small>
                                                <strong><?php echo esc_html( $selling !== '' ? $selling : '-' ); ?></strong>
                                                <div class="pv-currency-change <?php echo esc_attr( $change_class ); ?>">
                                                    <?php echo $direction === 'increase' ? '▲' : ( $direction === 'decrease' ? '▼' : '•' ); ?>
                                                    <?php echo esc_html( $change_pct !== '' ? $change_pct : '0,00' ); ?>%
                                                </div>
                                            </div>
                                            <div class="pv-currency-updated">
                                                <?php echo $update !== '' ? esc_html( 'Son Güncelleme: ' . $update ) : 'Güncelleme bilgisi yok'; ?>
                                            </div>
                                        </div>

                                        <div class="pv-currency-toolbar">
                                            <div class="pv-currency-periods">
                                                <?php $first = true; foreach ( $windows as $window_key => $window ) : ?>
                                                    <button type="button" class="pv-currency-period <?php echo $first ? 'active' : ''; ?>" data-pv-currency-window="<?php echo esc_attr( $window_key ); ?>"><?php echo esc_html( $window['label'] ); ?></button>
                                                <?php $first = false; endforeach; ?>
                                            </div>
                                            <div class="pv-currency-actions">
                                                <?php if ( is_user_logged_in() ) : ?>
                                                    <?php if ( $status_liste ) : ?>
                                                        <a href="javascript:;" onclick="listedenCikar('<?php echo esc_js( $key ); ?>')" class="pv-currency-action is-list">Listeden Çıkar</a>
                                                    <?php else : ?>
                                                        <a href="javascript:;" onclick="listemeEkle('<?php echo esc_js( $key ); ?>')" class="pv-currency-action is-list">Listeme Ekle</a>
                                                    <?php endif; ?>
                                                    <?php if ( $alarm_liste ) : ?>
                                                        <a href="javascript:;" onclick="alarmCikar('<?php echo esc_js( $key ); ?>')" class="pv-currency-action is-alarm">Alarmı Kaldır</a>
                                                    <?php else : ?>
                                                        <a href="javascript:;" onclick="alarmKur('<?php echo esc_js( $key ); ?>')" class="pv-currency-action is-alarm">Alarm Kur</a>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <a href="javascript:;" onclick="girisYap();" class="pv-currency-action is-list">Listeme Ekle</a>
                                                    <a href="javascript:;" onclick="girisYap();" class="pv-currency-action is-alarm">Alarm Kur</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php $first = true; foreach ( $windows as $window_key => $window ) : ?>
                                            <div class="pv-currency-chart-panel" data-pv-currency-window-panel="<?php echo esc_attr( $window_key ); ?>" style="<?php echo $first ? 'display:block;' : 'display:none;'; ?>">
                                                <?php if ( $chart ) : ?>
                                                    <div class="pv-currency-chart" id="pv_currency_<?php echo esc_attr( $window_key ); ?>"></div>
                                                <?php else : ?>
                                                    <div class="pv-market-empty">Grafik verisi şu anda alınamıyor.</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php $first = false; endforeach; ?>
                                        <p class="pv-currency-market-note">* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                    </section>

                                    <?php if ( $banks ) : ?>
                                        <section class="pv-currency-bank-card">
                                            <h2 class="pv-currency-bank-title"><?php echo esc_html( mb_strtoupper( $name, 'UTF-8' ) ); ?> BANKA KURLARI</h2>
                                            <div class="pv-currency-bank-wrap">
                                                <table class="pv-currency-bank-table">
                                                    <thead><tr><th>Banka</th><th>Alış</th><th>Satış</th></tr></thead>
                                                    <tbody>
                                                        <?php foreach ( $banks as $bank ) : ?>
                                                            <tr>
                                                                <td><?php echo esc_html( $bank['name'] ); ?></td>
                                                                <td><?php echo esc_html( $bank['buying'] ); ?></td>
                                                                <td><?php echo esc_html( $bank['selling'] ); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </section>
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
            chart: { zoomType: 'x', height: 360 },
            title: { text: name + ' ' + config.title + ' Grafik Tablosu', style: { fontSize: '16px', fontWeight: '700' } },
            xAxis: { type: 'datetime' },
            yAxis: { title: { text: '' } },
            legend: { enabled: false },
            credits: { enabled: false },
            plotOptions: { area: { marker: { radius: 2 }, lineWidth: 1, states: { hover: { lineWidth: 1 } }, threshold: null } },
            series: [{ type:'area', name:name, data:data }]
        });
    });

    var root = document.querySelector('.pv-currency-native-card');
    if (!root) return;
    root.querySelectorAll('[data-pv-currency-window]').forEach(function(tab){
        tab.addEventListener('click', function(){
            var key = tab.getAttribute('data-pv-currency-window');
            root.querySelectorAll('[data-pv-currency-window]').forEach(function(item){ item.classList.remove('active'); });
            root.querySelectorAll('[data-pv-currency-window-panel]').forEach(function(item){ item.style.display = 'none'; });
            tab.classList.add('active');
            var target = root.querySelector('[data-pv-currency-window-panel="' + key + '"]');
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
