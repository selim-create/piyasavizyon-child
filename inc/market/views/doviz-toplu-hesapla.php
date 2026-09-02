<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$rows = pv_market_currency_converter_rows();

add_filter( 'pre_get_document_title', static function() {
    return 'Döviz Çevirici - ' . get_bloginfo( 'name' );
}, 20 );

get_header();
?>
<style>
html body .pv-market-currency-bulk-child .pv-bulk-card{width:100%;margin:0 0 18px;padding:22px;background:#fff;border:1px solid #dce8f6;border-radius:22px;box-shadow:0 14px 36px rgba(8,35,78,.07)}
html body .pv-market-currency-bulk-child .pv-bulk-table{width:100%;border-collapse:separate;border-spacing:0 8px}
html body .pv-market-currency-bulk-child .pv-bulk-table th{padding:0 12px 6px;color:#687991;font-size:11px;font-weight:900;text-align:left;text-transform:uppercase}
html body .pv-market-currency-bulk-child .pv-bulk-table td{padding:12px;background:#f9fbfe;border-top:1px solid #dce8f6;border-bottom:1px solid #dce8f6}
html body .pv-market-currency-bulk-child .pv-bulk-table td:first-child{border-left:1px solid #dce8f6;border-radius:14px 0 0 14px}
html body .pv-market-currency-bulk-child .pv-bulk-table td:last-child{border-right:1px solid #dce8f6;border-radius:0 14px 14px 0}
html body .pv-market-currency-bulk-child .pv-bulk-code{display:inline-flex;align-items:center;justify-content:center;min-width:44px;height:28px;margin-right:10px;padding:0 10px;border-radius:999px;background:#eef5ff;color:#2057b8;font-size:11px;font-weight:900}
html body .pv-market-currency-bulk-child .pv-bulk-name{color:#10203b;font-weight:800}
html body .pv-market-currency-bulk-child .pv-bulk-input{width:100%;max-width:180px;height:40px;padding:0 11px;border:1px solid #cddaea;border-radius:10px;background:#fff;color:#10203b;font-weight:700;text-align:right}
html body .pv-market-currency-bulk-child .pv-bulk-result{color:#10203b;font-weight:900;white-space:nowrap}
@media(max-width:760px){html body .pv-market-currency-bulk-child .pv-bulk-table th:nth-child(3),html body .pv-market-currency-bulk-child .pv-bulk-table td:nth-child(3){display:none}html body .pv-market-currency-bulk-child .pv-bulk-table td{padding:10px 8px}html body .pv-market-currency-bulk-child .pv-bulk-name{display:none}}
</style>
<div class="site-wrapper pv-market-native pv-market-currency-bulk-child" data-pv-view="currency-bulk-converter">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft"><div class="singleWrapper">
                <div class="breadcrumb"><ul class="block">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
                    <li class="post bg"><span>Döviz Çevirici</span></li>
                </ul></div>
                <h1 class="postTitle">Döviz Çevirici</h1>
                <div class="pv-market-detail-content"><div class="mainContent"><div class="main">
                    <section class="pv-bulk-card">
                        <table class="pv-bulk-table">
                            <thead><tr><th>Döviz</th><th>Miktar</th><th>Kur</th><th>TL Karşılığı</th></tr></thead>
                            <tbody>
                            <?php foreach ( $rows as $row ) : ?>
                                <?php $rate = pv_market_currency_converter_number( $row['buying'] ); ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url( pv_market_currency_converter_pretty_url( 1, $row['code'] ) ); ?>">
                                            <span class="pv-bulk-code"><?php echo esc_html( strtoupper( $row['code'] ) ); ?></span>
                                            <span class="pv-bulk-name"><?php echo esc_html( $row['name'] ); ?></span>
                                        </a>
                                    </td>
                                    <td><input class="pv-bulk-input" type="number" min="0" step="0.01" value="0" data-rate="<?php echo esc_attr( $rate ); ?>" aria-label="<?php echo esc_attr( $row['name'] . ' miktarı' ); ?>"></td>
                                    <td><?php echo esc_html( number_format( $rate, 4, ',', '.' ) . ' TL' ); ?></td>
                                    <td class="pv-bulk-result">0,00 TL</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                </div></div></div>
            </div></div>
            <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) { pv_v7_market_sidebar( 'doviz-detay' ); } ?>
        </div>
    </section>
    <div class="clear"></div>
</div>
<script>
(function(){
    var inputs = document.querySelectorAll('.pv-market-currency-bulk-child .pv-bulk-input');
    function formatTry(value){
        try { return new Intl.NumberFormat('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:4}).format(value) + ' TL'; }
        catch(e) { return value.toFixed(2) + ' TL'; }
    }
    inputs.forEach(function(input){
        input.addEventListener('input', function(){
            var amount = parseFloat(input.value || '0') || 0;
            var rate = parseFloat(input.getAttribute('data-rate') || '0') || 0;
            var result = input.closest('tr').querySelector('.pv-bulk-result');
            if (result) result.textContent = formatTry(amount * rate);
        });
    });
})();
</script>
<?php get_footer();
