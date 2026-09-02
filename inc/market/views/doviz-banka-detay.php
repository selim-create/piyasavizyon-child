<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $currency_data;

$query     = isset( $_GET['c'] ) ? sanitize_text_field( wp_unslash( $_GET['c'] ) ) : '';
$bank_name = isset( $_GET['banka'] ) ? sanitize_text_field( wp_unslash( $_GET['banka'] ) ) : '';
$detail    = $query !== '' ? pv_market_currency_detail( $query ) : array();

if ( empty( $detail['name'] ) ) {
    status_header( 404 );
}

$key   = isset( $detail['key'] ) ? $detail['key'] : $query;
$code  = ! empty( $detail['code'] ) ? strtoupper( (string) $detail['code'] ) : strtoupper( $query );
$name  = ! empty( $detail['name'] ) ? (string) $detail['name'] : 'Döviz Detayı';
$banks = isset( $detail['banks'] ) && is_array( $detail['banks'] ) ? array_values( $detail['banks'] ) : array();
$bank  = pv_market_currency_find_bank( $banks, $bank_name );
$chart = isset( $detail['chart'] ) && is_array( $detail['chart'] ) ? array_values( $detail['chart'] ) : array();

if ( empty( $bank ) ) {
    wp_safe_redirect( pv_market_currency_detail_url( $key ) );
    exit;
}

$selected_bank_name = (string) $bank['name'];
$buying             = isset( $bank['buying'] ) ? (string) $bank['buying'] : '';
$selling            = isset( $bank['selling'] ) ? (string) $bank['selling'] : '';
$update             = isset( $detail['update'] ) ? (string) $detail['update'] : '';

add_filter( 'pre_get_document_title', function() use ( $name, $selected_bank_name ) {
    return $name . ' - ' . $selected_bank_name . ' - ' . get_bloginfo( 'name' );
}, 20 );

get_header();
?>
<script src="https://code.highcharts.com/7.1.1/highcharts.js"></script>
<style>
html body .pv-market-currency-bank-detail-child .pv-currency-bank-native-card{width:100%;margin:0 0 18px;padding:20px;background:#fff;border:1px solid #dce8f6;border-radius:22px;box-shadow:0 14px 36px rgba(8,35,78,.07)}
html body .pv-market-currency-bank-detail-child .pv-currency-bank-price-grid{display:grid;grid-template-columns:minmax(0,180px) minmax(0,180px) minmax(180px,1fr);gap:14px}
html body .pv-market-currency-bank-detail-child .pv-currency-bank-price-box{min-height:92px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:14px 16px;background:#fbfdff;border:1px solid #dce8f6;border-radius:18px}
html body .pv-market-currency-bank-detail-child .pv-currency-bank-price-box small{margin-bottom:7px;color:#58708d;font-size:11px;font-weight:900;text-transform:uppercase}
html body .pv-market-currency-bank-detail-child .pv-currency-bank-price-box strong{color:#10203b;font-size:23px;line-height:1;font-weight:900}
html body .pv-market-currency-bank-detail-child .pv-currency-bank-updated{display:flex;align-items:center;justify-content:center;min-height:92px;padding:14px 18px;border-radius:18px;background:#eef5ff;color:#58708d;font-size:12px;font-weight:900;text-align:center}
html body .pv-market-currency-bank-detail-child .pv-currency-bank-chart{width:100%;min-height:360px;margin-top:16px;border:1px solid #dce8f6;border-radius:18px;overflow:hidden;background:#fff}
html body .pv-market-currency-bank-detail-child .pv-currency-bank-note{margin:12px 2px 0;color:#687991;font-size:12px}
html body .pv-market-currency-bank-detail-child .pv-currency-bank-back{display:inline-flex;align-items:center;min-height:38px;margin-top:16px;padding:0 14px;border-radius:12px;background:#0758c9;color:#fff!important;font-size:11px;font-weight:900;text-decoration:none!important}
@media(max-width:760px){html body .pv-market-currency-bank-detail-child .pv-currency-bank-price-grid{grid-template-columns:1fr 1fr}html body .pv-market-currency-bank-detail-child .pv-currency-bank-updated{grid-column:1/-1;min-height:58px}html body .pv-market-currency-bank-detail-child .pv-currency-bank-chart{min-height:300px}}
</style>

<div class="site-wrapper pv-market-native pv-market-currency-detail-native pv-market-currency-bank-detail-child">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft">
                <div class="singleWrapper">
                    <section class="pv-currency-detail-hero">
                        <div class="breadcrumb">
                            <ul class="block">
                                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                                <li><a href="<?php echo esc_url( pv_market_currency_list_url() ); ?>">Döviz Kurları<i>/</i></a></li>
                                <li><a href="<?php echo esc_url( pv_market_currency_detail_url( $key ) ); ?>"><?php echo esc_html( $name ); ?><i>/</i></a></li>
                                <li class="post bg"><span><?php echo esc_html( $selected_bank_name ); ?></span></li>
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
                            <span><?php echo esc_html( $selected_bank_name ); ?> <i class="dropdown-arrow"></i></span>
                            <div class="mCustomScrollbar changeCurrency">
                                <a href="<?php echo esc_url( pv_market_currency_detail_url( $key ) ); ?>">Serbest Piyasa</a>
                                <?php foreach ( $banks as $row ) : ?>
                                    <?php if ( pv_market_currency_normalize_bank_name( $row['name'] ) === pv_market_currency_normalize_bank_name( $selected_bank_name ) ) { continue; } ?>
                                    <?php $bank_url = add_query_arg( array( 'c' => sanitize_title( (string) $key ), 'banka' => (string) $row['name'] ), home_url( '/doviz/' ) ); ?>
                                    <a href="<?php echo esc_url( $bank_url ); ?>"><?php echo esc_html( $row['name'] ); ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </section>

                    <div class="pv-market-detail-content pv-currency-detail-content">
                        <div class="mainContent"><div class="main">
                            <section class="pv-currency-bank-native-card">
                                <div class="pv-currency-bank-price-grid">
                                    <div class="pv-currency-bank-price-box"><small>Alış</small><strong><?php echo esc_html( $buying !== '' ? $buying : '-' ); ?></strong></div>
                                    <div class="pv-currency-bank-price-box"><small>Satış</small><strong><?php echo esc_html( $selling !== '' ? $selling : '-' ); ?></strong></div>
                                    <div class="pv-currency-bank-updated"><?php echo esc_html( $selected_bank_name . ( $update !== '' ? ' · Son Güncelleme: ' . $update : '' ) ); ?></div>
                                </div>

                                <?php if ( $chart ) : ?>
                                    <div class="pv-currency-bank-chart" id="pv_currency_bank_daily"></div>
                                <?php else : ?>
                                    <div class="pv-market-empty">Grafik verisi şu anda alınamıyor.</div>
                                <?php endif; ?>
                                <p class="pv-currency-bank-note">* Grafik, seçilen bankanın geçmiş fiyat serisi değil; ilgili dövizin genel piyasa günlük hareketini referans olarak gösterir.</p>
                                <a class="pv-currency-bank-back" href="<?php echo esc_url( pv_market_currency_detail_url( $key ) ); ?>">Serbest Piyasa görünümüne dön</a>
                            </section>
                        </div></div>
                    </div>
                </div>
            </div>

            <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) { pv_v7_market_sidebar( 'doviz-detay' ); } ?>
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
    var cutoff = Date.now() - (24 * 60 * 60 * 1000);
    var data = allData.filter(function(point){ return Array.isArray(point) && point.length >= 2 && Number(point[0]) >= cutoff; });
    Highcharts.chart('pv_currency_bank_daily', {
        chart: { zoomType: 'x', height: 360 },
        title: { text: <?php echo wp_json_encode( $code . ' - TRY Günlük Piyasa Referans Grafiği' ); ?>, style: { fontSize: '16px', fontWeight: '700' } },
        xAxis: { type: 'datetime' },
        yAxis: { title: { text: '' } },
        legend: { enabled: false },
        credits: { enabled: false },
        plotOptions: { area: { marker: { radius: 2 }, lineWidth: 1, states: { hover: { lineWidth: 1 } }, threshold: null } },
        series: [{ type: 'area', name: <?php echo wp_json_encode( $code . ' - TRY' ); ?>, data: data }]
    });
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>
