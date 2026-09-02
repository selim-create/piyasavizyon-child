<?php
/*
  Template Name: Döviz Arşiv
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

$requested_date = isset( $_GET['tarih'] ) ? sanitize_text_field( wp_unslash( $_GET['tarih'] ) ) : '';
$archive        = pv_market_currency_archive( $requested_date );
$archive_date   = isset( $archive['date'] ) ? (string) $archive['date'] : pv_market_currency_archive_normalize_date( '' );
$rows           = isset( $archive['rows'] ) && is_array( $archive['rows'] ) ? $archive['rows'] : array();

get_header();
?>
<style>
.pv-market-currency-archive-native .pv-currency-archive-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px;}
.pv-market-currency-archive-native .pv-currency-archive-head .postTitle{width:auto;float:none;margin:0;}
.pv-market-currency-archive-native .pv-currency-date{padding:10px 12px;border:1px solid #dcdcdc;border-radius:8px;background:#fff;min-width:180px;}
.pv-market-currency-archive-native .currencyTable tr th{font-weight:600;}
.pv-market-currency-archive-native .currencyTable tr td b{color:#3b72de!important;}
.pv-market-currency-archive-native .pv-currency-code{display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:24px;border-radius:999px;background:#eef4ff;color:#2057b8;font-size:11px;font-weight:700;margin-right:8px;vertical-align:middle;}
.pv-market-currency-archive-native .pv-market-empty{padding:22px;text-align:center;color:#667085;}
@media(max-width:760px){
  .pv-market-currency-archive-native .pv-currency-archive-head{align-items:flex-start;flex-direction:column;}
  .pv-market-currency-archive-native .pv-currency-date{width:100%;min-width:0;}
  .pv-market-currency-archive-native .pv-currency-archive-table{overflow-x:auto;-webkit-overflow-scrolling:touch;}
  .pv-market-currency-archive-native .pv-currency-archive-table table{min-width:720px;}
}
</style>
<div class="site-wrapper pv-market-native pv-market-currency-archive-native">
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

                    <div class="pv-currency-archive-head">
                        <h1 class="postTitle"><?php the_title(); ?></h1>
                        <input type="date" value="<?php echo esc_attr( $archive_date ); ?>" id="DateSelection" class="pv-currency-date" max="<?php echo esc_attr( wp_date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>" />
                    </div>

                    <div class="pv-market-list-content pv-currency-archive-content">
                        <div class="mainContent">
                            <div class="main">
                                <div class="widget">
                                    <?php if ( $rows ) : ?>
                                        <div class="currencyShowcase fullShowcase mobileBottomNo pv-currency-archive-table">
                                            <table class="currencyTable currencyFullTable">
                                                <tr>
                                                    <th>Döviz</th>
                                                    <th>Açılış</th>
                                                    <th>En Düşük</th>
                                                    <th>En Yüksek</th>
                                                    <th>Kapanış</th>
                                                </tr>
                                                <?php foreach ( $rows as $row ) : ?>
                                                    <?php
                                                    $code = ! empty( $row['code'] ) ? strtolower( sanitize_key( (string) $row['code'] ) ) : '';
                                                    $url  = $code !== '' ? pv_market_currency_detail_url( $code ) : '';
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ( $url !== '' ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php endif; ?>
                                                                <?php if ( $code !== '' ) : ?><span class="pv-currency-code"><?php echo esc_html( strtoupper( $code ) ); ?></span><?php endif; ?>
                                                                <b><?php echo esc_html( $row['name'] ); ?></b>
                                                            <?php if ( $url !== '' ) : ?></a><?php endif; ?>
                                                        </td>
                                                        <td><?php echo esc_html( $row['open'] ); ?></td>
                                                        <td><?php echo esc_html( $row['low'] ); ?></td>
                                                        <td><?php echo esc_html( $row['high'] ); ?></td>
                                                        <td><?php echo esc_html( $row['close'] ); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </table>
                                        </div>
                                    <?php else : ?>
                                        <div class="pv-market-empty">Seçilen tarih için döviz arşiv verisi bulunamadı.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) {
                pv_v7_market_sidebar( 'doviz-arsivi' );
            } ?>
        </div>
    </section>
    <div class="clear"></div>
</div>
<script>
(function(){
    var input = document.getElementById('DateSelection');
    if (!input) return;
    input.addEventListener('change', function(){
        var value = input.value || '';
        var target = <?php echo wp_json_encode( home_url( '/doviz-arsiv/' ) ); ?>;
        window.location.href = target + '?tarih=' + encodeURIComponent(value);
    });
})();
</script>
<?php get_footer(); ?>
