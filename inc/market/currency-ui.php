<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_market_currency_detail_layout_hotfix() {
    if ( ! wp_style_is( 'pv-v7-main', 'enqueued' ) ) {
        return;
    }

    $css = <<<'CSS'
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget{
  display:block !important;
  width:100% !important;
  max-width:100% !important;
}
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:first-child{
  display:flex !important;
  flex-wrap:wrap !important;
  gap:14px !important;
  align-items:flex-start !important;
}
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:first-child > .kurTrade{
  flex:0 1 190px !important;
  width:auto !important;
  min-width:170px !important;
  max-width:220px !important;
}
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:first-child > .lastUpdate2{
  flex:1 1 220px !important;
  min-width:180px !important;
  margin:0 !important;
}
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:first-child > .clear{
  display:none !important;
}
html body .pv-market-currency-detail-child .pv-currency-period-head,
html body .pv-market-currency-detail-child .pv-currency-chart-panel,
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:first-child > p{
  flex:0 0 100% !important;
  width:100% !important;
  max-width:100% !important;
  grid-column:1 / -1 !important;
}
html body .pv-market-currency-detail-child .pv-currency-period-head{
  margin-top:4px !important;
}
html body .pv-market-currency-detail-child .pv-currency-chart-panel{
  margin:0 !important;
}
html body .pv-market-currency-detail-child .pv-currency-chart-panel .currencyChart{
  width:100% !important;
  min-width:0 !important;
}
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:nth-of-type(2) .financeBar,
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:nth-of-type(2) .financeBlockBig,
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:nth-of-type(2) .currencyShowcase{
  display:block !important;
  width:100% !important;
  max-width:100% !important;
}
html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:nth-of-type(2) table.currencyTable{
  width:100% !important;
  min-width:0 !important;
  margin:0 !important;
}
@media(max-width:760px){
  html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:first-child > .kurTrade,
  html body .pv-market-currency-detail-child .pv-currency-detail-content .main > .widget:first-child > .lastUpdate2{
    flex:0 0 100% !important;
    max-width:100% !important;
    min-width:0 !important;
  }
}
CSS;

    wp_add_inline_style( 'pv-v7-main', $css );
}
add_action( 'wp_enqueue_scripts', 'pv_market_currency_detail_layout_hotfix', 99 );
