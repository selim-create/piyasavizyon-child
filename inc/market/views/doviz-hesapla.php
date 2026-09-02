<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$request = pv_market_currency_converter_request();
$detail  = isset( $request['detail'] ) && is_array( $request['detail'] ) ? $request['detail'] : array();
$amount  = isset( $request['amount'] ) ? (float) $request['amount'] : 1.0;

if ( isset( $_GET['doviz'], $_GET['miktar'] ) && ! empty( $detail['code'] ) ) {
    wp_safe_redirect( pv_market_currency_converter_pretty_url( $amount, $detail['code'] ) );
    exit;
}

add_filter( 'pre_get_document_title', static function() {
    return 'Döviz Hesapla - ' . get_bloginfo( 'name' );
}, 20 );

get_header();
$rows = pv_market_currency_converter_rows();
?>
<style>
html body .pv-market-currency-converter-child .pv-converter-card{width:100%;margin:0 0 18px;padding:22px;background:#fff;border:1px solid #dce8f6;border-radius:22px;box-shadow:0 14px 36px rgba(8,35,78,.07)}
html body .pv-market-currency-converter-child .pv-converter-form{display:grid;grid-template-columns:minmax(120px,1fr) minmax(180px,1fr) auto;gap:12px;align-items:end}
html body .pv-market-currency-converter-child .pv-converter-field label{display:block;margin-bottom:7px;color:#58708d;font-size:11px;font-weight:900;text-transform:uppercase}
html body .pv-market-currency-converter-child .pv-converter-field input,html body .pv-market-currency-converter-child .pv-converter-field select{width:100%;height:46px;padding:0 13px;border:1px solid #cddaea;border-radius:12px;background:#fff;color:#10203b;font-weight:700}
html body .pv-market-currency-converter-child .pv-converter-submit{height:46px;padding:0 18px;border:0;border-radius:12px;background:#0758c9;color:#fff;font-size:12px;font-weight:900;cursor:pointer}
html body .pv-market-currency-converter-child .pv-converter-links{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
html body .pv-market-currency-converter-child .pv-converter-links a{display:inline-flex;align-items:center;min-height:34px;padding:0 11px;border-radius:999px;background:#eef5ff;color:#2057b8!important;font-size:11px;font-weight:800;text-decoration:none!important}
@media(max-width:760px){html body .pv-market-currency-converter-child .pv-converter-form{grid-template-columns:1fr}html body .pv-market-currency-converter-child .pv-converter-submit{width:100%}}
</style>
<div class="site-wrapper pv-market-native pv-market-currency-converter-child" data-pv-view="currency-converter-form">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft"><div class="singleWrapper">
                <div class="breadcrumb"><ul class="block">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                    <li class="post bg"><span>Döviz Hesapla</span></li>
                </ul></div>
                <h1 class="postTitle">Döviz Hesapla</h1>
                <div class="pv-market-detail-content"><div class="mainContent"><div class="main">
                    <section class="pv-converter-card">
                        <form class="pv-converter-form" action="<?php echo esc_url( pv_market_currency_converter_page_url() ); ?>" method="get">
                            <div class="pv-converter-field">
                                <label for="pv-converter-amount">Miktar</label>
                                <input id="pv-converter-amount" type="number" min="0.01" step="0.01" name="miktar" value="1" required>
                            </div>
                            <div class="pv-converter-field">
                                <label for="pv-converter-currency">Döviz</label>
                                <select id="pv-converter-currency" name="doviz">
                                    <?php foreach ( $rows as $row ) : ?>
                                        <option value="<?php echo esc_attr( $row['code'] ); ?>"><?php echo esc_html( strtoupper( $row['code'] ) . ' - ' . $row['name'] ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="pv-converter-submit" type="submit">HESAPLA</button>
                        </form>
                        <div class="pv-converter-links">
                            <a href="<?php echo esc_url( pv_market_currency_converter_pretty_url( 1, 'usd' ) ); ?>">1 Dolar Ne Kadar?</a>
                            <a href="<?php echo esc_url( pv_market_currency_converter_pretty_url( 1, 'eur' ) ); ?>">1 Euro Ne Kadar?</a>
                            <a href="<?php echo esc_url( pv_market_currency_converter_pretty_url( 1, 'gbp' ) ); ?>">1 Sterlin Ne Kadar?</a>
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
