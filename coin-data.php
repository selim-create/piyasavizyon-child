<?php
/*
  Template Name: Coin Detay
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'pv_v7_crypto_known_slug' ) ) {
    function pv_v7_crypto_known_slug( $key, $name = '' ) {
        $raw = strtolower( trim( (string) $key ) );
        $map = array(
            'btc'=>'bitcoin','bitcoin'=>'bitcoin','eth'=>'ethereum','ethereum'=>'ethereum','xrp'=>'ripple','ripple'=>'ripple',
            'bch'=>'bitcoin-cash','bitcoin cash'=>'bitcoin-cash','bitcoin-cash'=>'bitcoin-cash','ltc'=>'litecoin','litecoin'=>'litecoin',
            'bnb'=>'binancecoin','binancecoin'=>'binancecoin','binance coin'=>'binancecoin','sol'=>'solana','solana'=>'solana',
            'doge'=>'dogecoin','dogecoin'=>'dogecoin','avax'=>'avalanche','avalanche'=>'avalanche','usdt'=>'tether','tether'=>'tether',
            'trx'=>'tron','tron'=>'tron','ada'=>'cardano','cardano'=>'cardano','xlm'=>'stellar','stellar'=>'stellar','xmr'=>'monero','monero'=>'monero',
            'fil'=>'filecoin','filecoin'=>'filecoin','eos'=>'eos','okb'=>'okb','pepe'=>'pepe'
        );
        if ( isset( $map[ $raw ] ) ) return $map[ $raw ];
        $name_key = strtolower( trim( (string) $name ) );
        if ( isset( $map[ $name_key ] ) ) return $map[ $name_key ];
        return sanitize_title( $name ?: $key );
    }
}

if ( ! function_exists( 'pv_v7_crypto_records' ) ) {
    function pv_v7_crypto_records() {
        if ( function_exists( 'pv_v7_ensure_market_data' ) ) pv_v7_ensure_market_data();
        global $coin_data;
        $records = array();
        if ( empty( $coin_data['name'] ) || ! is_array( $coin_data['name'] ) ) return $records;

        foreach ( $coin_data['name'] as $key => $name ) {
            if ( $name === '' || $name === null ) continue;
            $symbol = ! empty( $coin_data['symbol'][ $key ] ) ? (string) $coin_data['symbol'][ $key ] : ( is_string( $key ) ? (string) $key : '' );
            $records[] = array(
                'key'    => $key,
                'slug'   => pv_v7_crypto_known_slug( $symbol ?: $key, $name ),
                'symbol' => strtoupper( (string) $symbol ),
                'name'   => html_entity_decode( (string) $name, ENT_QUOTES, 'UTF-8' ),
                'price'  => isset( $coin_data['current_price'][ $key ] ) ? $coin_data['current_price'][ $key ] : '',
                'rate'   => isset( $coin_data['price_24h'][ $key ] ) ? $coin_data['price_24h'][ $key ] : '',
                'url'    => home_url( '/coin/?c=' . rawurlencode( pv_v7_crypto_known_slug( $symbol ?: $key, $name ) ) ),
            );
        }
        return $records;
    }
}

if ( ! function_exists( 'pv_v7_crypto_match' ) ) {
    function pv_v7_crypto_match( $needle, $records ) {
        $needle = strtolower( trim( (string) $needle ) );
        $needle_slug = sanitize_title( $needle );
        foreach ( $records as $record ) {
            $candidates = array(
                strtolower( (string) $record['key'] ), strtolower( (string) $record['symbol'] ), strtolower( (string) $record['slug'] ),
                sanitize_title( $record['name'] ), strtolower( (string) $record['name'] ),
            );
            foreach ( array_unique( $candidates ) as $candidate ) {
                if ( $candidate === $needle || $candidate === $needle_slug ) return $record;
            }
        }
        return null;
    }
}

$coin_param = isset( $_GET['c'] ) ? sanitize_text_field( wp_unslash( $_GET['c'] ) ) : '';
if ( $coin_param === '' ) {
    wp_safe_redirect( home_url( '/kripto-para/' ) );
    exit;
}

$crypto_records = pv_v7_crypto_records();
$current_coin   = pv_v7_crypto_match( $coin_param, $crypto_records );
$daily_points   = $current_coin && function_exists( 'pv_market_coingecko_chart' )
    ? pv_market_coingecko_chart( $current_coin['slug'], $current_coin['symbol'] )
    : array();

if ( $current_coin ) {
    $new_title = $current_coin['name'] . ' - ' . get_bloginfo( 'name' );
    $title_filter = function( $title ) use ( $new_title ) { return $new_title; };
    add_filter( 'pre_get_document_title', $title_filter, 10 );
    add_filter( 'wpseo_title', $title_filter, 15 );
}

get_header();
$rate_num = $current_coin && function_exists( 'pv_v7_parse_number' ) ? pv_v7_parse_number( $current_coin['rate'] ) : 0;
$crease_status = $rate_num >= 0 ? 'increase' : 'decrease';
?>
<script src="https://code.highcharts.com/7.1.1/highcharts.js"></script>
<div class="site-wrapper pv-market-native pv-market-crypto-detail-native pv-market-crypto-detail-child">
  <section class="content home">
    <div class="container-wrap">
      <div class="widebar floatLeft">
        <div class="singleWrapper">
          <div class="breadcrumb"><ul class="block">
            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
            <li><a href="<?php echo esc_url( home_url( '/kripto-para/' ) ); ?>">Kripto Paralar<i>/</i></a></li>
            <li class="post bg"><span><?php echo $current_coin ? esc_html( $current_coin['name'] ) : 'Coin'; ?></span></li>
          </ul></div>

          <?php if ( $current_coin ) : ?>
            <div class="pv-crypto-detail-hero">
              <div class="pv-crypto-title-wrap">
                <?php echo function_exists( 'pv_v7_coin_avatar' ) ? pv_v7_coin_avatar( $current_coin['symbol'], $current_coin['name'], 'lg' ) : '<span class="pv-coin-avatar pv-coin-avatar-lg">' . esc_html( mb_substr( $current_coin['symbol'] ?: $current_coin['name'], 0, 3, 'UTF-8' ) ) . '</span>'; ?>
                <div><h1 class="singlePageTitle"><?php echo esc_html( $current_coin['name'] ); ?></h1><span class="pv-crypto-symbol"><?php echo esc_html( $current_coin['symbol'] ); ?></span></div>
              </div>
              <div class="pv-crypto-price-card"><span>Son Fiyat</span><strong><?php echo esc_html( $current_coin['price'] ); ?></strong><em class="subtract <?php echo esc_attr( $crease_status ); ?>">% <?php echo esc_html( $current_coin['rate'] ); ?></em></div>
            </div>

            <div class="pv-market-detail-content pv-crypto-detail-content"><div class="mainContent onsAltin"><div class="main">
              <div class="widget pv-crypto-chart-widget"><div class="categoryTab"><div class="catTabContent">
                <div class="borsaTimerTabHead bg"><ul><li class="active"><span><?php echo esc_html( $current_coin['name'] ); ?> Günlük</span></li></ul></div>
                <div class="borsaTimerTabContent" style="display:block;">
                  <?php if ( $daily_points ) : ?>
                    <div class="currencyChart" id="pv_crypto_chart"></div>
                    <script>
                    document.addEventListener('DOMContentLoaded',function(){if(window.Highcharts&&document.getElementById('pv_crypto_chart')){Highcharts.chart('pv_crypto_chart',{chart:{zoomType:'x'},title:{text:<?php echo wp_json_encode( $current_coin['name'] . ' - USD' ); ?>},xAxis:{type:'datetime'},yAxis:{title:{text:''}},legend:{enabled:false},credits:{enabled:false},plotOptions:{area:{marker:{radius:2},lineWidth:1,states:{hover:{lineWidth:1}},threshold:null}},series:[{type:'area',name:<?php echo wp_json_encode( $current_coin['name'] ); ?>,data:<?php echo wp_json_encode( $daily_points ); ?>}]});}});
                    </script>
                  <?php else : ?>
                    <div class="pv-market-empty-state pv-crypto-chart-empty"><h2>Grafik verisi şu an alınamadı</h2><p>Anlık fiyat verisi gösterilmeye devam ediyor; CoinGecko günlük grafik verisi geçici olarak yanıt vermiyor olabilir.</p></div>
                  <?php endif; ?>
                  <p class="pv-market-note">* Grafik CoinGecko USD günlük piyasa verisini gösterir.</p>
                </div>
              </div></div></div>

              <div class="widget pv-crypto-stats-widget"><div class="pv-market-stats-grid">
                <div><span>Kripto Para</span><strong><?php echo esc_html( $current_coin['name'] ); ?></strong></div>
                <div><span>Sembol</span><strong><?php echo esc_html( $current_coin['symbol'] ); ?></strong></div>
                <div><span>Son Fiyat</span><strong><?php echo esc_html( $current_coin['price'] ); ?></strong></div>
                <div><span>24 Saatlik Değişim</span><strong><em class="subtract <?php echo esc_attr( $crease_status ); ?>">% <?php echo esc_html( $current_coin['rate'] ); ?></em></strong></div>
              </div></div>

              <?php if ( $crypto_records ) : ?>
                <div class="widget pv-crypto-related-widget"><h2 class="pv-market-section-title">Diğer Kripto Paralar</h2><div class="currencyShowcase mobileBottomNo"><table class="currencyTable gold kriptolar pv-crypto-related-table"><thead><tr><th>Kripto Para</th><th>Fiyat</th><th>Değişim</th></tr></thead><tbody>
                <?php $count = 0; foreach ( $crypto_records as $coin ) : if ( $coin['slug'] === $current_coin['slug'] ) continue; $related_rate = function_exists( 'pv_v7_parse_number' ) ? pv_v7_parse_number( $coin['rate'] ) : (float) $coin['rate']; $related_status = $related_rate >= 0 ? 'increase' : 'decrease'; ?>
                  <tr><td><?php echo function_exists( 'pv_v7_coin_avatar' ) ? pv_v7_coin_avatar( $coin['symbol'], $coin['name'], 'xs' ) : ''; ?> <a href="<?php echo esc_url( $coin['url'] ); ?>"><?php echo esc_html( $coin['name'] ); ?></a></td><td><i class="<?php echo esc_attr( $related_status ); ?>"></i> <?php echo esc_html( $coin['price'] ); ?></td><td><span class="subtract <?php echo esc_attr( $related_status ); ?>">% <?php echo esc_html( $coin['rate'] ); ?></span></td></tr>
                <?php $count++; if ( $count >= 8 ) break; endforeach; ?>
                </tbody></table></div></div>
              <?php endif; ?>
            </div></div></div>
          <?php else : ?>
            <div class="pv-market-detail-content pv-crypto-detail-content"><div class="pv-market-empty-state"><h1>Coin ile ilgili veri bulunamadı</h1><p>İstenen coin kodu veri cache’inde bulunamadı.</p><a class="pv-market-button" href="<?php echo esc_url( home_url( '/kripto-para/' ) ); ?>">Kripto Paralar</a></div></div>
          <?php endif; ?>
        </div>
      </div>
      <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) { pv_v7_market_sidebar( 'coin-detay' ); } ?>
    </div>
  </section>
  <div class="clear"></div>
</div>
<?php get_footer(); ?>
