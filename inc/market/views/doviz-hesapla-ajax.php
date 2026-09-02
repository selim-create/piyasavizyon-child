<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$request = pv_market_currency_converter_request();
$detail  = isset( $request['detail'] ) && is_array( $request['detail'] ) ? $request['detail'] : array();
$amount  = isset( $request['amount'] ) ? (float) $request['amount'] : 1.0;
$result  = isset( $request['result'] ) ? (float) $request['result'] : 0.0;
$rate    = isset( $request['rate'] ) ? (float) $request['rate'] : 0.0;
$code    = ! empty( $detail['code'] ) ? strtoupper( (string) $detail['code'] ) : 'USD';
$name    = ! empty( $detail['name'] ) ? (string) $detail['name'] : 'Amerikan Doları';
$change  = isset( $detail['change_pct'] ) ? (string) $detail['change_pct'] : '0';

add_filter( 'pre_get_document_title', static function() use ( $amount, $name ) {
    $amount_label = rtrim( rtrim( number_format( $amount, 2, ',', '.' ), '0' ), ',' );
    return $amount_label . ' ' . $name . ' Ne Kadar, Kaç TL? - ' . get_bloginfo( 'name' );
}, 20 );

get_header();
$rows = pv_market_currency_converter_rows();
$amount_label = rtrim( rtrim( number_format( $amount, 2, ',', '.' ), '0' ), ',' );
?>
<style>
html body .pv-market-currency-converter-result-child .pv-converter-result-card{width:100%;margin:0 0 18px;padding:22px;background:#fff;border:1px solid #dce8f6;border-radius:22px;box-shadow:0 14px 36px rgba(8,35,78,.07)}
html body .pv-market-currency-converter-result-child .pv-converter-result-main{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr);gap:14px}
html body .pv-market-currency-converter-result-child .pv-converter-result-box{padding:20px;border-radius:18px;background:#f7faff;border:1px solid #dce8f6}
html body .pv-market-currency-converter-result-child .pv-converter-result-box small{display:block;margin-bottom:8px;color:#58708d;font-size:11px;font-weight:900;text-transform:uppercase}
html body .pv-market-currency-converter-result-child .pv-converter-result-box strong{display:block;color:#10203b;font-size:32px;line-height:1.1;font-weight:900}
html body .pv-market-currency-converter-result-child .pv-converter-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px}
html body .pv-market-currency-converter-result-child .pv-converter-meta div{padding:14px;border-radius:14px;background:#fff;border:1px solid #dce8f6}
html body .pv-market-currency-converter-result-child .pv-converter-meta small{display:block;margin-bottom:5px;color:#687991;font-size:10px;font-weight:900;text-transform:uppercase}
html body .pv-market-currency-converter-result-child .pv-converter-meta b{color:#10203b;font-size:15px}
html body .pv-market-currency-converter-result-child .pv-converter-form{display:grid;grid-template-columns:minmax(100px,.8fr) minmax(180px,1fr) auto;gap:10px;margin-top:16px;align-items:end}
html body .pv-market-currency-converter-result-child .pv-converter-form label{display:block;margin-bottom:6px;color:#687991;font-size:10px;font-weight:900;text-transform:uppercase}
html body .pv-market-currency-converter-result-child .pv-converter-form input,html body .pv-market-currency-converter-result-child .pv-converter-form select{width:100%;height:44px;padding:0 12px;border:1px solid #cddaea;border-radius:12px;background:#fff;color:#10203b;font-weight:700}
html body .pv-market-currency-converter-result-child .pv-converter-submit{height:44px;padding:0 16px;border:0;border-radius:12px;background:#0758c9;color:#fff;font-size:12px;font-weight:900;cursor:pointer}
html body .pv-market-currency-converter-result-child .pv-converter-shortcuts{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}
html body .pv-market-currency-converter-result-child .pv-converter-shortcuts a{display:inline-flex;align-items:center;min-height:34px;padding:0 11px;border-radius:999px;background:#eef5ff;color:#2057b8!important;font-size:11px;font-weight:800;text-decoration:none!important}
@media(max-width:760px){html body .pv-market-currency-converter-result-child .pv-converter-result-main{grid-template-columns:1fr}html body .pv-market-currency-converter-result-child .pv-converter-form{grid-template-columns:1fr}html body .pv-market-currency-converter-result-child .pv-converter-submit{width:100%}}
</style>
<div class="site-wrapper pv-market-native pv-market-currency-converter-result-child" data-pv-view="currency-converter-result">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft"><div class="singleWrapper">
                <div class="breadcrumb"><ul class="block">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                    <li><a href="<?php echo esc_url( pv_market_currency_converter_page_url() ); ?>">Döviz Hesapla<i>/</i></a></li>
                    <li class="post bg"><span><?php echo esc_html( $amount_label . ' ' . $name ); ?></span></li>
                </ul></div>
                <h1 class="postTitle"><?php echo esc_html( $amount_label . ' ' . $name . ' Ne Kadar, Kaç TL?' ); ?></h1>
                <div class="pv-market-detail-content"><div class="mainContent"><div class="main">
                    <section class="pv-converter-result-card">
                        <div class="pv-converter-result-main">
                            <div class="pv-converter-result-box">
                                <small><?php echo esc_html( $amount_label . ' ' . $code ); ?></small>
                                <strong><?php echo esc_html( number_format( $result, 4, ',', '.' ) . ' TL' ); ?></strong>
                            </div>
                            <div class="pv-converter-meta">
                                <div><small>1 <?php echo esc_html( $code ); ?></small><b><?php echo esc_html( number_format( $rate, 4, ',', '.' ) . ' TL' ); ?></b></div>
                                <div><small>Günlük Değişim</small><b><?php echo esc_html( $change . ' %' ); ?></b></div>
                            </div>
                        </div>

                        <form class="pv-converter-form" action="<?php echo esc_url( pv_market_currency_converter_page_url() ); ?>" method="get">
                            <div><label for="pv-converter-result-amount">Miktar</label><input id="pv-converter-result-amount" type="number" min="0.01" step="0.01" name="miktar" value="<?php echo esc_attr( $amount ); ?>" required></div>
                            <div><label for="pv-converter-result-currency">Döviz</label><select id="pv-converter-result-currency" name="doviz">
                                <?php foreach ( $rows as $row ) : ?>
                                    <option value="<?php echo esc_attr( $row['code'] ); ?>" <?php selected( strtolower( $row['code'] ), strtolower( $detail['code'] ) ); ?>><?php echo esc_html( strtoupper( $row['code'] ) . ' - ' . $row['name'] ); ?></option>
                                <?php endforeach; ?>
                            </select></div>
                            <button class="pv-converter-submit" type="submit">HESAPLA</button>
                        </form>

                        <div class="pv-converter-shortcuts">
                            <?php foreach ( array( 1, 5, 10, 20, 50, 100, 1000 ) as $shortcut ) : ?>
                                <a href="<?php echo esc_url( pv_market_currency_converter_pretty_url( $shortcut, $detail['code'] ) ); ?>"><?php echo esc_html( $shortcut . ' ' . $code . ' Kaç TL?' ); ?></a>
                            <?php endforeach; ?>
                            <a href="<?php echo esc_url( pv_market_currency_bulk_converter_url() ); ?>">Toplu Döviz Çevirici</a>
                        </div>
                    </section>
                </div></div></div>
            </div></div>
            <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) { pv_v7_market_sidebar( 'doviz-detay' ); } ?>
        </div>
    </section>
    <div class="clear"></div>
</div>
<?php get_footer();
