<?php
/*
  Template Name: Coin Detay
*/
if (!defined('ABSPATH')) { exit; }

global $bp_options;

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
            $market_cap = $coin_data['market_cap'][$key] ?? $coin_data['market_value'][$key] ?? '';
            $volume = $coin_data['total_volume'][$key] ?? $coin_data['volume'][$key] ?? '';
            $slug  = pv_v7_crypto_known_slug($symbol ?: $key, $name);
            $records[] = array(
                'key' => $key,
                'slug' => $slug,
                'symbol' => strtoupper((string) $symbol),
                'name' => html_entity_decode((string) $name, ENT_QUOTES, 'UTF-8'),
                'price' => $price,
                'rate' => $rate,
                'market_cap' => $market_cap,
                'volume' => $volume,
                'url' => home_url('/coin/?c=' . rawurlencode($slug)),
            );
        }
        return $records;
    }
}

if (!function_exists('pv_v7_crypto_match')) {
    function pv_v7_crypto_match($needle, $records) {
        $needle = strtolower(trim((string) $needle));
        $needle_slug = function_exists('sanitize_title') ? sanitize_title($needle) : preg_replace('/[^a-z0-9]+/i', '-', $needle);
        foreach ($records as $record) {
            $candidates = array(
                strtolower((string) $record['key']),
                strtolower((string) $record['symbol']),
                strtolower((string) $record['slug']),
                function_exists('sanitize_title') ? sanitize_title($record['name']) : strtolower(preg_replace('/[^a-z0-9]+/i', '-', $record['name'])),
                strtolower((string) $record['name']),
            );
            $candidates[] = pv_v7_crypto_known_slug($record['symbol'], $record['name']);
            foreach (array_unique($candidates) as $candidate) {
                if ($candidate === $needle || $candidate === $needle_slug) return $record;
            }
        }
        if (function_exists('pv_v7_find_coin_index')) {
            $idx = pv_v7_find_coin_index($needle);
            if ($idx !== null) {
                foreach ($records as $record) if ((string) $record['key'] === (string) $idx) return $record;
            }
        }
        return null;
    }
}

if (!function_exists('pv_v7_crypto_daily_points')) {
    function pv_v7_crypto_daily_points($slug, $symbol = '') {
        $points = array();
        if (!function_exists('get_url_curl')) return $points;
        $token = '';
        if (function_exists('get_url_doviz_auth')) {
            $coin_page = @get_url_doviz_auth('https://www.doviz.com/kripto-paralar/' . rawurlencode($slug));
            if (is_string($coin_page) && preg_match("@apiAccessToken\s*=\s*'([^']+)'@si", $coin_page, $m)) {
                $token = $m[1];
            }
        }
        $headers = array(
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'X-Requested-With: XMLHttpRequest',
        );
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;

        $candidates = array_values(array_unique(array_filter(array($slug, strtolower((string) $symbol), strtoupper((string) $symbol)))));
        foreach ($candidates as $candidate) {
            $json = @get_url_curl('https://www.doviz.com/api/v11/assets/' . rawurlencode($candidate) . '/daily', $headers);
            $decoded = json_decode((string) $json, true);
            if (!empty($decoded['data']) && is_array($decoded['data'])) {
                foreach ($decoded['data'] as $row) {
                    if (isset($row['update_date'], $row['close'])) {
                        $points[] = array(((int) $row['update_date']) * 1000, (float) $row['close']);
                    }
                }
                if (!empty($points)) break;
            }
        }
        return $points;
    }
}

$coin_param = isset($_GET['c']) ? sanitize_text_field(wp_unslash($_GET['c'])) : '';
if ($coin_param === '') {
    wp_safe_redirect(home_url('/'));
    exit;
}

$crypto_records = pv_v7_crypto_records();
$current_coin = pv_v7_crypto_match($coin_param, $crypto_records);

if ($current_coin) {
    $new_title = $current_coin['name'] . ' - ' . get_bloginfo('name');
    $pv_crypto_title_filter = function($title) use ($new_title) { return $new_title; };
    add_filter('pre_get_document_title', $pv_crypto_title_filter, 10);
    add_filter('wpseo_title', $pv_crypto_title_filter, 15);
}

get_header();
$rate_num = $current_coin && function_exists('pv_v7_parse_number') ? pv_v7_parse_number($current_coin['rate']) : 0;
$crease_status = $rate_num >= 0 ? 'increase' : 'decrease';
$daily_points = $current_coin ? pv_v7_crypto_daily_points($current_coin['slug'], $current_coin['symbol']) : array();
?>
<script src="<?php echo esc_url(get_template_directory_uri() . '/js/highcharts.js'); ?>"></script>
<div class="site-wrapper pv-market-native pv-market-crypto-detail-native">
    <section class="content home">
        <div class="container-wrap">
            <div class="widebar floatLeft">
                <div class="singleWrapper">
                    <div class="breadcrumb">
                        <ul class="block">
                            <li><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa<i>/</i></a></li>
                            <li><a href="<?php echo esc_url(home_url('/' . (($bp_options['page_kriptoparalar'] ?? 'kripto-para')) . '/')); ?>">Kripto Paralar<i>/</i></a></li>
                            <li class="post bg"><span><?php echo $current_coin ? esc_html($current_coin['name']) : 'Coin'; ?></span></li>
                        </ul>
                    </div>

                    <?php if ($current_coin) : ?>
                        <header class="pv-crypto-detail-hero">
                            <div class="pv-crypto-title-wrap">
                                <?php echo function_exists('pv_v7_coin_avatar') ? pv_v7_coin_avatar($current_coin['symbol'], $current_coin['name'], 'lg') : '<span class="pv-coin-avatar pv-coin-avatar-lg">' . esc_html(mb_substr($current_coin['symbol'] ?: $current_coin['name'], 0, 3, 'UTF-8')) . '</span>'; ?>
                                <div>
                                    <h1 class="singlePageTitle"><?php echo esc_html($current_coin['name']); ?></h1>
                                    <span class="pv-crypto-symbol"><?php echo esc_html($current_coin['symbol']); ?></span>
                                </div>
                            </div>
                            <div class="pv-crypto-price-card">
                                <span>Son Fiyat</span>
                                <strong><?php echo esc_html($current_coin['price']); ?></strong>
                                <em class="subtract <?php echo esc_attr($crease_status); ?>">% <?php echo esc_html($current_coin['rate']); ?></em>
                            </div>
                        </header>

                        <div class="pv-market-detail-content pv-crypto-detail-content">
                            <div class="mainContent onsAltin">
                                <div class="main">
                                    <div class="widget pv-crypto-chart-widget">
                                        <div class="categoryTab">
                                            <div class="catTabContent">
                                                <div class="borsaTimerTabHead bg"><ul><li class="active"><span><?php echo esc_html($current_coin['name']); ?> Günlük</span></li></ul></div>
                                                <div class="borsaTimerTabContent" style="display:block;">
                                                    <?php if (!empty($daily_points)) : ?>
                                                        <div class="currencyChart" id="pv_crypto_chart"></div>
                                                        <script>
                                                        document.addEventListener('DOMContentLoaded', function(){
                                                            if (window.Highcharts && document.getElementById('pv_crypto_chart')) {
                                                                Highcharts.chart('pv_crypto_chart', {
                                                                    chart: { zoomType: 'x' },
                                                                    title: { text: <?php echo wp_json_encode($current_coin['name'] . ' - USD'); ?> },
                                                                    xAxis: { type: 'datetime' },
                                                                    yAxis: { title: { text: '' } },
                                                                    legend: { enabled: false },
                                                                    credits: { enabled: false },
                                                                    plotOptions: { area: { marker: { radius: 2 }, lineWidth: 1, states: { hover: { lineWidth: 1 } }, threshold: null } },
                                                                    series: [{ type: 'area', name: <?php echo wp_json_encode($current_coin['name']); ?>, data: <?php echo wp_json_encode($daily_points); ?> }]
                                                                });
                                                            }
                                                        });
                                                        </script>
                                                    <?php else : ?>
                                                        <div class="pv-market-empty-state pv-crypto-chart-empty">
                                                            <h2>Grafik verisi şu an alınamadı</h2>
                                                            <p>Sayfa boş kalmasın diye anlık fiyat verisi Piyasa Vizyon veri cache’inden gösteriliyor. Grafik datası için Doviz.com günlük endpoint’i geçici olarak yanıt vermiyor olabilir.</p>
                                                        </div>
                                                    <?php endif; ?>
                                                    <p class="pv-market-note">* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmayabilir.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="widget pv-crypto-stats-widget">
                                        <div class="pv-market-stats-grid">
                                            <div><span>Kripto Para</span><strong><?php echo esc_html($current_coin['name']); ?></strong></div>
                                            <div><span>Sembol</span><strong><?php echo esc_html($current_coin['symbol']); ?></strong></div>
                                            <div><span>Son Fiyat</span><strong><?php echo esc_html($current_coin['price']); ?></strong></div>
                                            <div><span>24 Saatlik Değişim</span><strong><em class="subtract <?php echo esc_attr($crease_status); ?>">% <?php echo esc_html($current_coin['rate']); ?></em></strong></div>
                                            <?php if (!empty($current_coin['market_cap'])) : ?><div><span>Piyasa Değeri</span><strong><?php echo esc_html($current_coin['market_cap']); ?></strong></div><?php endif; ?>
                                            <?php if (!empty($current_coin['volume'])) : ?><div><span>Hacim</span><strong><?php echo esc_html($current_coin['volume']); ?></strong></div><?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($crypto_records)) : ?>
                                    <div class="widget pv-crypto-related-widget">
                                        <h2 class="pv-market-section-title">Diğer Kripto Paralar</h2>
                                        <div class="currencyShowcase mobileBottomNo">
                                            <table class="currencyTable gold kriptolar pv-crypto-related-table">
                                                <thead><tr><th>Kripto Para</th><th>Fiyat</th><th>Değişim</th></tr></thead>
                                                <tbody>
                                                <?php $count = 0; foreach ($crypto_records as $coin) : if ($coin['slug'] === $current_coin['slug']) continue;
                                                    $related_rate = function_exists('pv_v7_parse_number') ? pv_v7_parse_number($coin['rate']) : (float) str_replace(',', '.', (string) $coin['rate']);
                                                    $related_status = $related_rate >= 0 ? 'increase' : 'decrease';
                                                                                                    ?>
                                                    <tr>
                                                        <td><?php echo function_exists('pv_v7_coin_avatar') ? pv_v7_coin_avatar($coin['symbol'], $coin['name'], 'xs') : '<span class="pv-coin-avatar pv-coin-avatar-xs">' . esc_html(mb_substr($coin['symbol'] ?: $coin['name'], 0, 3, 'UTF-8')) . '</span>'; ?> <a href="<?php echo esc_url($coin['url']); ?>"><?php echo esc_html($coin['name']); ?></a></td>
                                                        <td><i class="<?php echo esc_attr($related_status); ?>"></i> <?php echo esc_html($coin['price']); ?></td>
                                                        <td><span class="subtract <?php echo esc_attr($related_status); ?>">% <?php echo esc_html($coin['rate']); ?></span></td>
                                                    </tr>
                                                <?php $count++; if ($count >= 8) break; endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php
                                    if (!empty($bp_options['veri_sayfalari_text']) && is_array($bp_options['veri_sayfalari_text'])) {
                                        foreach ($bp_options['veri_sayfalari_text'] as $value) {
                                            if (!empty($value['type']) && $value['type'] === 'kripto' && !empty($value['kisa_kod']) && strtolower($value['kisa_kod']) === strtolower($current_coin['slug'])) {
                                                echo '<div class="widget"><div class="sayfaAltMakale"><h2>' . esc_html($value['baslik'] ?? '') . '</h2><p>' . esc_html(strip_tags($value['content'] ?? '')) . '</p></div></div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="pv-market-detail-content pv-crypto-detail-content">
                            <div class="pv-market-empty-state">
                                <h1>Coin ile ilgili veri bulunamadı</h1>
                                <p>İstenen coin kodu veri cache’inde bulunamadı. Kripto çevirici doğru çalışıyorsa coin slug’ı farklı olabilir. Kripto paralar listesine dönüp ilgili coin bağlantısından tekrar deneyin.</p>
                                <a class="pv-market-button" href="<?php echo esc_url(home_url('/' . (($bp_options['page_kriptoparalar'] ?? 'kripto-para')) . '/')); ?>">Kripto Paralar</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!wp_is_mobile() && function_exists('pv_v7_market_sidebar')) { pv_v7_market_sidebar('coin-detay'); } ?>
        </div>
    </section>
    <div class="clear"></div>
</div>
<?php get_footer(); ?>
