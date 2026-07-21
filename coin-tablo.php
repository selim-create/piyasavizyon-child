<?php
/*
  Template Name: Coin Tablo
*/
if (!defined('ABSPATH')) { exit; }

if (!function_exists('pv_v7_crypto_known_slug')) {
    function pv_v7_crypto_known_slug($key, $name = '') {
        $raw = strtolower(trim((string) $key));
        $map = array(
            'btc' => 'bitcoin', 'bitcoin' => 'bitcoin',
            'eth' => 'ethereum', 'ethereum' => 'ethereum',
            'xrp' => 'ripple', 'ripple' => 'ripple',
            'bch' => 'bitcoin-cash', 'bitcoin cash' => 'bitcoin-cash', 'bitcoin-cash' => 'bitcoin-cash',
            'ltc' => 'litecoin', 'litecoin' => 'litecoin',
            'bnb' => 'binancecoin', 'binancecoin' => 'binancecoin', 'binance coin' => 'binancecoin',
            'sol' => 'solana', 'solana' => 'solana',
            'doge' => 'dogecoin', 'dogecoin' => 'dogecoin',
            'avax' => 'avalanche', 'avalanche' => 'avalanche',
            'usdt' => 'tether', 'tether' => 'tether',
            'trx' => 'tron', 'tron' => 'tron',
            'ada' => 'cardano', 'cardano' => 'cardano',
            'xlm' => 'stellar', 'stellar' => 'stellar',
            'xmr' => 'monero', 'monero' => 'monero',
            'fil' => 'fil', 'filecoin' => 'fil',
            'eos' => 'eos', 'okb' => 'okb', 'pepe' => 'pepe'
        );
        if (isset($map[$raw])) return $map[$raw];
        $name_key = strtolower(trim((string) $name));
        if (isset($map[$name_key])) return $map[$name_key];
        return function_exists('sanitize_title') ? sanitize_title($name ?: $key) : strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)($name ?: $key)));
    }
}

if (!function_exists('pv_v7_crypto_records')) {
    function pv_v7_crypto_records() {
        if (function_exists('pv_v7_ensure_market_data')) pv_v7_ensure_market_data();
        global $coin_data;
        $records = array();
        if (empty($coin_data) || !is_array($coin_data) || empty($coin_data['name']) || !is_array($coin_data['name'])) {
            return $records;
        }
        foreach ($coin_data['name'] as $key => $name) {
            if ($name === '' || $name === null) continue;
            $symbol = '';
            if (!empty($coin_data['symbol'][$key])) $symbol = (string) $coin_data['symbol'][$key];
            elseif (is_string($key)) $symbol = (string) $key;
            else $symbol = strtoupper(substr((string) $name, 0, 4));

            $price = $coin_data['current_price'][$key] ?? $coin_data['price'][$key] ?? $coin_data['value'][$key] ?? '';
            $rate  = $coin_data['price_24h'][$key] ?? $coin_data['change_rate'][$key] ?? $coin_data['rate'][$key] ?? '';
            $slug  = pv_v7_crypto_known_slug($symbol ?: $key, $name);
            $records[] = array(
                'key' => $key,
                'slug' => $slug,
                'symbol' => strtoupper((string) $symbol),
                'name' => html_entity_decode((string) $name, ENT_QUOTES, 'UTF-8'),
                'price' => $price,
                'rate' => $rate,
                'url' => home_url('/coin/?c=' . rawurlencode($slug)),
            );
        }
        return $records;
    }
}

get_header();
$crypto_records = pv_v7_crypto_records();
?>
<div class="site-wrapper pv-market-native pv-market-crypto-native">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft">
                <div class="singleWrapper">
                    <div class="breadcrumb">
                        <ul class="block">
                            <li><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa<i>/</i></a></li>
                            <li class="post bg"><span><?php the_title(); ?></span></li>
                        </ul>
                    </div>
                    <h1 class="postTitle"><?php the_title(); ?></h1>

                    <div class="pv-market-list-content pv-crypto-list-content">
                        <div class="mainContent">
                            <div class="main">
                                <div class="widget pv-crypto-table-widget">
                                    <div class="currencyShowcase fullShowcase mobileBottomNo">
                                        <?php if (!empty($crypto_records)) : ?>
                                            <table class="currencyTable currencyFullTable pv-crypto-table">
                                                <thead>
                                                    <tr>
                                                        <th>Kripto Para</th>
                                                        <th>Fiyat</th>
                                                        <th>Değişim (24 s.)</th>
                                                        <th>Sembol</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($crypto_records as $coin) :
                                                    $rate_num = function_exists('pv_v7_parse_number') ? pv_v7_parse_number($coin['rate']) : (float) str_replace(',', '.', (string) $coin['rate']);
                                                    $crease_status = $rate_num >= 0 ? 'increase' : 'decrease';
                                                                                                    ?>
                                                    <tr>
                                                        <td class="pv-crypto-name-cell">
                                                            <?php echo function_exists('pv_v7_coin_avatar') ? pv_v7_coin_avatar($coin['symbol'], $coin['name'], 'sm') : '<span class="pv-coin-avatar pv-coin-avatar-sm">' . esc_html(mb_substr($coin['symbol'] ?: $coin['name'], 0, 3, 'UTF-8')) . '</span>'; ?>
                                                            <a href="<?php echo esc_url($coin['url']); ?>"><b><?php echo esc_html($coin['name']); ?></b></a>
                                                        </td>
                                                        <td><i class="<?php echo esc_attr($crease_status); ?>"></i> <?php echo esc_html($coin['price']); ?></td>
                                                        <td><span class="subtract <?php echo esc_attr($crease_status); ?>">% <?php echo esc_html($coin['rate']); ?></span></td>
                                                        <td><?php echo esc_html($coin['symbol']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else : ?>
                                            <div class="pv-market-empty-state">
                                                <h2>Kripto para verisi alınamadı</h2>
                                                <p>Veri servisi geçici olarak yanıt vermiyor olabilir. Kripto çevirici çalışıyorsa cache temizlendikten sonra bu tablo da aynı veri kaynağından beslenecektir.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!wp_is_mobile() && function_exists('pv_v7_market_sidebar')) { pv_v7_market_sidebar('kriptoparalar'); } ?>
        </div>
    </section>
    <div class="clear"></div>
</div>
<?php get_footer(); ?>
