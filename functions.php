<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pv_v7_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height'=>80,'width'=>280,'flex-height'=>true,'flex-width'=>true));
    register_nav_menus(array(
        'primary' => __('Ana Menü', 'piyasavizyon-v7'),
        'bfUstMenu' => __('Birfinans Ana Menü', 'piyasavizyon-v7'),
        'footer_markets' => __('Footer Piyasalar Menüsü', 'piyasavizyon-v7'),
    ));
}
add_action('after_setup_theme', 'pv_v7_setup');

function pv_v7_assets() {
    wp_enqueue_style('pv-v7-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap', array(), null);
    wp_enqueue_style('pv-v7-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2');
    wp_enqueue_style('pv-v7-main', get_stylesheet_directory_uri() . '/assets/css/pv-v7.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/pv-v7.css')); // v2.28 cache-bust
    wp_enqueue_style('pv-footer-v250', get_stylesheet_directory_uri() . '/assets/css/pv-footer-v250.css', array('pv-v7-main'), filemtime(get_stylesheet_directory() . '/assets/css/pv-footer-v250.css'));
    wp_enqueue_style('pv-corporate-v252', get_stylesheet_directory_uri() . '/assets/css/pv-corporate-v252.css', array('pv-footer-v250'), filemtime(get_stylesheet_directory() . '/assets/css/pv-corporate-v252.css'));
    wp_enqueue_style('pv-header-v260', get_stylesheet_directory_uri() . '/assets/css/pv-header-v260.css', array('pv-corporate-v252'), filemtime(get_stylesheet_directory() . '/assets/css/pv-header-v260.css'));
    wp_enqueue_script('pv-v7-main', get_stylesheet_directory_uri() . '/assets/js/pv-v7.js', array(), filemtime(get_stylesheet_directory() . '/assets/js/pv-v7.js'), true); // v2.28 cache-bust
}
add_action('wp_enqueue_scripts', 'pv_v7_assets', 50);


/**
 * Add stable body classes for finance/market plugin pages.
 * This lets the child theme restyle BirFinans output without touching plugin templates.
 */
function pv_v7_market_page_body_classes( $classes ) {
    $request_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
    $request_path = trim( (string) $request_path, '/' );

    $market_pages = array(
        'borsa' => 'pv-page-borsa',
        'tum-endeksler' => 'pv-page-indices',
        'endeks' => 'pv-page-index-detail',
        'tum-hisseler' => 'pv-page-stocks',
        'hisseler' => 'pv-page-stocks',
        'hisse' => 'pv-page-stock-detail',
        'tum-pariteler' => 'pv-page-parities',
        'pariteler' => 'pv-page-parities',
        'parite' => 'pv-page-parity-detail',
        'ekonomik-takvim' => 'pv-page-calendar',
        'kripto-para' => 'pv-page-crypto',
        'kripto-paralar' => 'pv-page-crypto',
        'kriptoparalar' => 'pv-page-crypto',
        'coin' => 'pv-page-crypto-detail',
    );

    if ( isset( $market_pages[ $request_path ] ) ) {
        $classes[] = 'pv-market-page';
        $classes[] = $market_pages[ $request_path ];
    }

    return array_values( array_unique( $classes ) );
}
add_filter( 'body_class', 'pv_v7_market_page_body_classes', 30 );

function pv_v7_widgets() {
    $areas = array(
        'pv_header_ad' => 'PV Header Üst Reklam (Desktop 970x90 / 970x250)',
        'pv_mobile_masthead' => 'PV Mobil Masthead Reklam (320x100 / 320x150 / 300x250)',
        'pv_right_ad' => 'PV Sağ Üst Reklam',
        'pv_content_ad' => 'PV İçerik Arası Reklam (Desktop 728x90)',
        'pv_mobile_content_ad' => 'PV Mobil İçerik Reklam (300x250 / 320x100)',
        'pv_sidebar_top' => 'PV Sidebar Üst Reklam',
        'pv_sidebar_mid' => 'PV Sidebar Orta Reklam',
        'pv_sidebar_sky' => 'PV Sidebar Skyscraper Reklam',
        'pv_mobile_sticky_ad' => 'PV Mobil Sticky Reklam (320x50)',
        'pv_footer_ad' => 'PV Footer Reklam',
        'pv_ipo_widget' => 'PV Halka Arz Takvimi / Plugin Alanı',
        'pv_stock_widget' => 'PV Hisse Verileri / Borsa Widget Alanı',
    );
    foreach ($areas as $id=>$name) {
        register_sidebar(array(
            'name'=>$name,'id'=>$id,
            'before_widget'=>'<div class="pv-widget %2$s">','after_widget'=>'</div>',
            'before_title'=>'<h3 class="pv-widget-title">','after_title'=>'</h3>'
        ));
    }
}
add_action('widgets_init','pv_v7_widgets');

function pv_v7_logo() {
    // Header ve footer için ana tercih: beyaz logo.
    foreach (array('piyasavizyon-logo-white.svg','piyasavizyon-logo-white.png','piyasavizyon-logo.svg','piyasavizyon-logo.png') as $file) {
        if (file_exists(get_stylesheet_directory().'/assets/img/'.$file)) {
            echo '<a class="logo logo-img logo-white" href="'.esc_url(home_url('/')).'"><img src="'.esc_url(get_stylesheet_directory_uri().'/assets/img/'.$file).'" alt="'.esc_attr(get_bloginfo('name')).'"></a>';
            return;
        }
    }
    if (has_custom_logo()) {
        echo '<a class="logo logo-img" href="'.esc_url(home_url('/')).'">';
        the_custom_logo();
        echo '</a>';
        return;
    }
    echo '<a class="logo" href="'.esc_url(home_url('/')).'">piyasa<span>vizyon</span></a>';
}

function pv_v7_footer_logo($type='pv') {
    $map = array(
        'pv' => array('piyasavizyon-logo-white.svg','piyasavizyon-logo-white.png','<div class="foot-logo">piyasa<span>vizyon</span></div>'),
        'hip' => array('hip-medya-logo-white.svg','hip-medya-logo-white.png','<div class="hip-medya-logo">hip<span>medya</span></div>'),
    );
    $m = $map[$type] ?? $map['pv'];
    foreach (array($m[0],$m[1]) as $file) {
        if (file_exists(get_stylesheet_directory().'/assets/img/'.$file)) {
            echo '<img class="pv-footer-logo-img pv-footer-logo-'.esc_attr($type).'" src="'.esc_url(get_stylesheet_directory_uri().'/assets/img/'.$file).'" alt="'.esc_attr($type==='hip'?'Hip Medya':'Piyasa Vizyon').'">';
            return;
        }
    }
    echo $m[2];
}

function pv_v7_ad($id, $label, $class='ad ad-728') {
    if (is_active_sidebar($id)) {
        echo '<div class="pv-ad-slot '.esc_attr($class).'">'; dynamic_sidebar($id); echo '</div>';
    } elseif ($id === 'pv_right_ad') {
        // Hero sağındaki CTA/reklam kutusu tasarımın bir parçası; boşsa da görünür kalsın.
        echo '<a class="'.esc_attr($class).' pv-ad-cta" href="'.esc_url(home_url('/reklam/')).'">'.esc_html($label).'</a>';
    }
}

function pv_v7_gam_content_ad() {
    static $desktop_i = 1;
    static $mobile_i = 0;

    // Tema bu slotları otomatik sırayla basar. Head tarafındaki GAM kodunda aynı ID'ler tanımlı olmalı.
    // Boş dönen reklamlar googletag.collapseEmptyDivs() ile ve CSS ile sayfada boşluk bırakmaz.
    if ($desktop_i <= 5) {
        echo '<div class="pv-gam-ad pv-gam-ad-728 only-desktop" data-ad-slot="inbanner-'.esc_attr($desktop_i).'"><div class="adbox" id="div-gpt-ad-1762761938425-'.esc_attr($desktop_i).'"></div></div>';
        $desktop_i++;
    }
    if ($mobile_i <= 5) {
        echo '<div class="pv-gam-ad pv-gam-ad-mobile only-mobile" data-ad-slot="mobile-display-'.esc_attr($mobile_i).'"><div class="adbox" id="div-gpt-ad-1762762739010-'.esc_attr($mobile_i).'"></div></div>';
        $mobile_i++;
    }
}


// GA / Google Ad Manager gibi global head ve body kodları için küçük tema ayarı.
function pv_v7_code_settings_menu() {
    add_theme_page('Piyasa Vizyon Kodları', 'PV Kodları', 'manage_options', 'pv-v7-codes', 'pv_v7_codes_page');
}
add_action('admin_menu', 'pv_v7_code_settings_menu');

function pv_v7_codes_page() {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['pv_v7_codes_nonce']) && wp_verify_nonce($_POST['pv_v7_codes_nonce'], 'pv_v7_codes_save')) {
        update_option('pv_v7_head_code', wp_unslash($_POST['pv_v7_head_code'] ?? ''));
        update_option('pv_v7_body_code', wp_unslash($_POST['pv_v7_body_code'] ?? ''));
        echo '<div class="updated"><p>Kodlar kaydedildi.</p></div>';
    }
    echo '<div class="wrap"><h1>Piyasa Vizyon Kodları</h1><p>Google Analytics, Google Tag Manager veya Google Ad Manager global head kodlarını buraya ekleyebilirsin.</p><form method="post">';
    wp_nonce_field('pv_v7_codes_save','pv_v7_codes_nonce');
    echo '<h2>Head kodları</h2><textarea name="pv_v7_head_code" rows="12" style="width:100%;font-family:monospace">'.esc_textarea(get_option('pv_v7_head_code','')).'</textarea>';
    echo '<h2>Body başlangıcı kodları</h2><textarea name="pv_v7_body_code" rows="8" style="width:100%;font-family:monospace">'.esc_textarea(get_option('pv_v7_body_code','')).'</textarea>';
    submit_button('Kaydet');
    echo '</form></div>';
}
function pv_v7_print_head_code(){ echo "
".get_option('pv_v7_head_code','')."
"; }
add_action('wp_head','pv_v7_print_head_code', 2);
function pv_v7_print_body_code(){ echo "
".get_option('pv_v7_body_code','')."
"; }
add_action('wp_body_open','pv_v7_print_body_code', 2);

function pv_v7_posts($args=array()) {
    $defaults=array('post_type'=>'post','posts_per_page'=>6,'ignore_sticky_posts'=>true);
    return new WP_Query(array_merge($defaults,$args));
}

/**
 * Homepage editorial flags.
 *
 * The post edit screen uses these meta keys:
 * - bf_anasayfa_slider = Manşete Eklensin Mi
 * - bf_anasayfa_kayan  = 4'lü kayan sliderda görünsün mü?
 *
 * Different switch/metabox implementations may save checked values as 1, on, yes, true, etc.
 * Keeping this helper tolerant prevents the homepage from falling back to latest posts because of
 * a value-format difference.
 */
function pv_v7_flagged_posts($meta_key, $args=array()) {
    $truthy_values = array('1', 1, 'on', 'yes', 'true', 'checked', 'evet', 'Evet', 'EVET');

    $defaults = array(
        'post_type'           => 'post',
        'posts_per_page'      => 4,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'meta_query'          => array(
            array(
                'key'     => $meta_key,
                'value'   => $truthy_values,
                'compare' => 'IN',
            ),
        ),
    );

    return new WP_Query(array_merge($defaults, $args));
}

function pv_v7_img($post_id, $size='medium_large') {
    if (has_post_thumbnail($post_id)) return get_the_post_thumbnail_url($post_id,$size);
    return '';
}

function pv_v7_num($value) {
    $value = strip_tags((string) $value);
    $value = trim(str_replace(array('TL','₺','$','%'), '', $value));
    return $value;
}

function pv_v7_find_coin_index($needle) {
    pv_v7_ensure_market_data();
    global $coin_data;
    if (empty($coin_data) || !is_array($coin_data)) return null;
    $needle = strtolower(remove_accents(str_replace(array('-', '_'), ' ', (string)$needle)));
    $aliases = array(
        'btc'=>array('btc','bitcoin'), 'bitcoin'=>array('btc','bitcoin'),
        'eth'=>array('eth','ethereum'), 'ethereum'=>array('eth','ethereum'),
        'bch'=>array('bch','bitcoin cash','bitcoin-cash'), 'bitcoin cash'=>array('bch','bitcoin cash','bitcoin-cash'),
        'ltc'=>array('ltc','litecoin'), 'xrp'=>array('xrp'), 'sol'=>array('sol','solana'),
        'bnb'=>array('bnb'), 'doge'=>array('doge','dogecoin'), 'avax'=>array('avax','avalanche')
    );
    $needles = $aliases[$needle] ?? array($needle);
    foreach (array('symbol','name','coin_name','coin_symbol') as $field) {
        if (!empty($coin_data[$field]) && is_array($coin_data[$field])) {
            foreach ($coin_data[$field] as $k=>$v) {
                $hay = strtolower(remove_accents(str_replace(array('-', '_'), ' ', (string)$v)));
                foreach ($needles as $n) {
                    $nn = strtolower(remove_accents(str_replace(array('-', '_'), ' ', $n)));
                    if ($hay === $nn || strpos($hay, $nn) !== false) return $k;
                }
            }
        }
    }
    return null;
}


function pv_v7_ensure_market_data() {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;
    global $currency_data, $coin_data, $altin_data, $bist100_data, $parite_data, $borsa_artanlar_data, $borsa_azalanlar_data, $borsa_islem_gorenler_data, $bp_options;

    if (!empty($currency_data) && !empty($altin_data) && !empty($bist100_data)) return;

    $api_dir = trailingslashit(get_template_directory()) . 'api/';
    if (is_file($api_dir . 'DataCache.php')) require_once $api_dir . 'DataCache.php';
    if (is_file($api_dir . 'api_helper.php')) require_once $api_dir . 'api_helper.php';
    if (!class_exists('DataCache') || !function_exists('get_data_service')) return;

    if (empty($bp_options) || !is_array($bp_options)) $bp_options = array();
    if (empty($bp_options['cache_time'])) $bp_options['cache_time'] = 5;
    $cache = new DataCache($bp_options['cache_time']);

    if (empty($currency_data)) {
        $currency_data = $cache->get('doviz.json');
        if (!$currency_data) { $tmp = get_data_service('currency'); if ($tmp) { $cache->write('doviz.json', $tmp); $currency_data = $tmp; } }
    }
    if (empty($altin_data)) {
        $altin_data = $cache->get('altin.json');
        if (!$altin_data) { $tmp = get_data_service('altin'); if ($tmp) { $cache->write('altin.json', $tmp); $altin_data = $tmp; } }
    }
    if (empty($parite_data)) {
        $parite_data = $cache->get('parite.json');
        if (!$parite_data) { $tmp = get_data_service('parite'); if ($tmp) { $cache->write('parite.json', $tmp); $parite_data = $tmp; } }
    }
    if (empty($coin_data)) {
        $coin_data = $cache->get('coin.json');
        if (!$coin_data) { $tmp = get_data_service('coin'); if ($tmp) { $cache->write('coin.json', $tmp); $coin_data = $tmp; } }
    }
    if (empty($bist100_data)) {
        $borsa_data = $cache->get('borsa.json');
        if (!$borsa_data) { $tmp = get_data_service('borsa'); if ($tmp) { $cache->write('borsa.json', $tmp); $borsa_data = $tmp; } }
        if (is_array($borsa_data)) {
            $bist100_data = $borsa_data['bist_100'] ?? array();
            $borsa_artanlar_data = $borsa_data['borsa_artanlar'] ?? array();
            $borsa_azalanlar_data = $borsa_data['borsa_azalanlar'] ?? array();
            $borsa_islem_gorenler_data = $borsa_data['borsa_islem_gorenler'] ?? array();
        }
    }
}


function pv_v7_parse_number($value) {
    $value = html_entity_decode(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
    $value = trim(str_replace(array('TL','₺','$','%',' '), '', $value));
    if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
    } else {
        $value = str_replace(',', '.', $value);
    }
    return (float) preg_replace('/[^0-9\.\-]/', '', $value);
}

function pv_v7_find_currency_index($needle) {
    pv_v7_ensure_market_data();
    global $currency_data;
    if (empty($currency_data) || !is_array($currency_data)) return null;
    $needle = strtolower((string)$needle);
    $aliases = array(
        'usd'=>array('usd','abd doları','dolar','amerikan doları'),
        'eur'=>array('eur','euro','avro'),
        'gbp'=>array('gbp','sterlin','ingiliz sterlini','pound'),
        'chf'=>array('chf','isviçre frangı'),
        'jpy'=>array('jpy','japon yeni'),
        'aud'=>array('aud','avustralya doları'),
        'cad'=>array('cad','kanada doları'),
        'cny'=>array('cny','çin yuanı','cin yuani','yuan','renminbi'),
        'rub'=>array('rub','rus rublesi'),
    );
    $needles = $aliases[$needle] ?? array($needle);
    $fields = array('code','full_name');
    foreach ($fields as $field) {
        if (empty($currency_data[$field]) || !is_array($currency_data[$field])) continue;
        foreach ($currency_data[$field] as $k=>$v) {
            $hay = strtolower(html_entity_decode((string)$v, ENT_QUOTES, 'UTF-8'));
            foreach ($needles as $n) {
                if ($hay === $n || strpos($hay, $n) !== false) return $k;
            }
        }
    }
    return null;
}

function pv_v7_find_altin_index($needle) {
    pv_v7_ensure_market_data();
    global $altin_data;
    if (empty($altin_data) || !is_array($altin_data)) return null;
    $needle = strtolower(remove_accents(str_replace(array('-', '_'), ' ', (string)$needle)));
    $needle = preg_replace('/\s+/', ' ', trim($needle));
    $aliases = array(
        'gram altin'=>array('gram altin','gramaltin','altin','gram'),
        'ceyrek altin'=>array('ceyrek altin','ceyrekaltin','ceyrek'),
        'yarim altin'=>array('yarim altin','yarimaltin','yarim'),
        'cumhuriyet altini'=>array('cumhuriyet altini','cumhuriyet','tam altin','tamaltin'),
        'ons altin'=>array('ons altin','onsaltin','ons altin usd','ons-altin-usd','ons'),
    );
    $needles = $aliases[$needle] ?? array($needle);
    foreach (array('altin_name','altin_key') as $field) {
        if (empty($altin_data[$field]) || !is_array($altin_data[$field])) continue;
        foreach ($altin_data[$field] as $k=>$v) {
            $hay = strtolower(remove_accents(html_entity_decode((string)$v, ENT_QUOTES, 'UTF-8')));
            $hay = preg_replace('/\s+/', ' ', str_replace(array('-', '_'), ' ', trim($hay)));
            foreach ($needles as $n) {
                $nn = strtolower(remove_accents(str_replace(array('-', '_'), ' ', $n)));
                if ($hay === $nn || strpos($hay, $nn) !== false || (is_string($k) && strpos(strtolower($k), str_replace(' ', '', $nn)) !== false)) return $k;
            }
        }
    }
    return null;
}

function pv_v7_find_parite_index($needle) {
    pv_v7_ensure_market_data();
    global $parite_data;
    if (empty($parite_data) || !is_array($parite_data)) return null;
    $needle = strtolower(str_replace(array('-', '_'), '/', (string)$needle));
    if (!empty($parite_data['parite_name']) && is_array($parite_data['parite_name'])) {
        foreach ($parite_data['parite_name'] as $k=>$v) {
            $hay = strtolower(str_replace(array('-', '_'), '/', html_entity_decode((string)$v, ENT_QUOTES, 'UTF-8')));
            if ($hay === $needle || strpos($hay, $needle) !== false) return $k;
        }
    }
    return null;
}

function pv_v7_arr_get($arr, $keys, $default='') {
    foreach ((array)$keys as $key) {
        if (is_array($arr) && array_key_exists($key, $arr) && $arr[$key] !== '' && $arr[$key] !== null) return $arr[$key];
    }
    return $default;
}

function pv_v7_market_item($type, $key, $fallback_name='', $fallback_value='0', $fallback_rate='0') {
    pv_v7_ensure_market_data();
    global $currency_data, $coin_data, $altin_data, $bist100_data, $parite_data;
    $item = array('name'=>$fallback_name, 'value'=>$fallback_value, 'buying'=>$fallback_value, 'selling'=>$fallback_value, 'rate'=>$fallback_rate, 'url'=>home_url('/'), 'type'=>$type, 'key'=>$key);
    if ($type === 'doviz') {
        $idx = pv_v7_find_currency_index($key);
        if ($idx !== null) {
            $item['name']  = !empty($currency_data['full_name'][$idx]) ? html_entity_decode($currency_data['full_name'][$idx], ENT_QUOTES, 'UTF-8') : strtoupper($key);
            $item['buying'] = pv_v7_arr_get($currency_data['buying'] ?? array(), array($idx, strtolower($key)), '0');
            $item['selling'] = pv_v7_arr_get($currency_data['selling'] ?? array(), array($idx, strtolower($key)), $item['buying']);
            $item['value'] = $item['selling'];
            $item['rate']  = pv_v7_arr_get($currency_data['change_rate'] ?? array(), array($idx, strtolower($key)), '0');
            $item['url']   = pv_v7_market_url('doviz', $key, $item['name']);
        }
    } elseif ($type === 'altin') {
        $idx = pv_v7_find_altin_index($key);
        if ($idx !== null) {
            $item['name']  = html_entity_decode(pv_v7_arr_get($altin_data['altin_name'] ?? array(), array($idx, $key), $fallback_name), ENT_QUOTES, 'UTF-8');
            $item['buying'] = pv_v7_arr_get($altin_data['altin_price_buying'] ?? array(), array($idx, $key), '0');
            $item['selling'] = pv_v7_arr_get($altin_data['altin_price_selling'] ?? array(), array($idx, $key), '0');
            $price = pv_v7_arr_get($altin_data['altin_price'] ?? array(), array($idx, $key), '0');
            if ($item['buying'] === '0') $item['buying'] = $price;
            if ($item['selling'] === '0') $item['selling'] = $price;
            $item['value'] = $item['selling'] !== '0' ? $item['selling'] : $item['buying'];
            $item['rate']  = pv_v7_arr_get($altin_data['altin_rate'] ?? array(), array($idx, $key), '0');
            $item['url']   = pv_v7_market_url('altin', $key, $item['name']);
        }
    } elseif ($type === 'coin') {
        $idx = is_numeric($key) ? $key : pv_v7_find_coin_index($key);
        if ($idx !== null) {
            $item['name']  = pv_v7_arr_get($coin_data['name'] ?? array(), array($idx, strtolower($key)), strtoupper($key));
            $item['value'] = pv_v7_arr_get($coin_data['current_price'] ?? array(), array($idx, strtolower($key)), '0');
            $item['buying'] = $item['selling'] = $item['value'];
            $item['rate']  = pv_v7_arr_get($coin_data['price_24h'] ?? array(), array($idx, strtolower($key)), '0');
            $item['url']   = pv_v7_market_url('coin', $key, $item['name']);
        }
    } elseif ($type === 'bist') {
        if (!empty($bist100_data)) {
            $item['name']  = 'BIST 100';
            $item['value'] = $bist100_data['value'] ?? $bist100_data['son'] ?? $bist100_data['price'] ?? '0';
            $item['buying'] = $item['selling'] = $item['value'];
            $item['rate']  = $bist100_data['change_rate'] ?? $bist100_data['degisim'] ?? $bist100_data['rate'] ?? '0';
            $item['url']   = home_url('/borsa/');
        }
    } elseif ($type === 'parite') {
        $idx = pv_v7_find_parite_index($key);
        if ($idx !== null) {
            $item['name']  = $parite_data['parite_name'][$idx] ?? strtoupper($key);
            $item['value'] = pv_v7_arr_get($parite_data['parite_price'] ?? array(), array($idx, $key), '0');
            $item['buying'] = $item['selling'] = $item['value'];
            $item['rate']  = pv_v7_arr_get($parite_data['parite_rate'] ?? array(), array($idx, $key), '0');
            $item['url']   = home_url('/parite/');
        }
        // Parent temada parite verisi bazen 0 dönebiliyor. EUR/USD gibi ana pariteler için
        // döviz satış değerlerinden güvenli fallback hesaplıyoruz.
        if (pv_v7_parse_number($item['value']) <= 0) {
            $norm = strtolower(str_replace(array('-', '_', ' '), '/', (string)$key));
            $parts = array_values(array_filter(explode('/', $norm)));
            if (count($parts) === 2) {
                $from = pv_v7_market_item('doviz', $parts[0], strtoupper($parts[0]), '0', '0');
                $to   = pv_v7_market_item('doviz', $parts[1], strtoupper($parts[1]), '0', '0');
                $from_v = pv_v7_parse_number($from['selling']);
                $to_v   = pv_v7_parse_number($to['selling']);
                if ($from_v > 0 && $to_v > 0) {
                    $item['name'] = strtoupper($parts[0].'/'.$parts[1]);
                    $item['value'] = number_format($from_v / $to_v, 4, ',', '.');
                    $item['buying'] = $item['selling'] = $item['value'];
                    $item['rate'] = number_format(pv_v7_parse_number($from['rate']) - pv_v7_parse_number($to['rate']), 2, ',', '.');
                    $item['url'] = home_url('/parite/');
                }
            }
        }
    }
    return $item;
}

function pv_v7_market_classes($rate) {
    $n = str_replace(',', '.', preg_replace('/[^0-9,\.\-]/', '', (string) $rate));
    return ((float)$n < 0) ? 'down' : 'up';
}

function pv_v7_market_slug($type, $key, $name='') {
    $key = strtolower(remove_accents((string)$key));
    $name_slug = sanitize_title($name ?: $key);
    $aliases = array(
        'usd'=>'usd','eur'=>'eur','gbp'=>'gbp','chf'=>'chf','cad'=>'cad','cny'=>'cny','jpy'=>'jpy','aud'=>'aud','rub'=>'rub',
        'gram altin'=>'gram-altin-fiyati','gram_altin'=>'gram-altin-fiyati','ceyrek altin'=>'ceyrek-altin-fiyati','yarim altin'=>'yarim-altin-fiyati','cumhuriyet altini'=>'cumhuriyet-altini','ons altin'=>'ons-altin',
        'btc'=>'bitcoin','eth'=>'ethereum','xrp'=>'xrp','bch'=>'bitcoin-cash','ltc'=>'litecoin','bnb'=>'bnb','sol'=>'solana','doge'=>'dogecoin','avax'=>'avalanche','ada'=>'cardano'
    );
    return $aliases[$key] ?? $aliases[str_replace('_',' ', $key)] ?? $name_slug;
}

function pv_v7_market_url($type, $key, $name='') {
    $slug = pv_v7_market_slug($type, $key, $name);
    if ($type === 'doviz') return home_url('/doviz/?c=' . rawurlencode($slug));
    if ($type === 'altin') return home_url('/altin/?a=' . rawurlencode($slug));
    if ($type === 'coin') return home_url('/coin/?c=' . rawurlencode($slug));
    if ($type === 'bist') return home_url('/borsa/');
    if ($type === 'hisse') return home_url('/hisse/?h=' . rawurlencode($slug));
    return home_url('/');
}

function pv_v7_ticker_items() {
    $usd = pv_v7_market_item('doviz', 'usd', '$ Dolar', '0', '0');
    $usd['name'] = '$ Dolar';
    $eur = pv_v7_market_item('doviz', 'eur', '€ Euro', '0', '0');
    $eur['name'] = '€ Euro';
    $gbp = pv_v7_market_item('doviz', 'gbp', '£ POUND', '0', '0');
    $gbp['name'] = '£ POUND';
    $xrp = pv_v7_market_item('coin', 'xrp', 'XRP', '0', '0');
    $xrp['name'] = 'XRP';
    $items = array(
        pv_v7_market_item('bist', '', 'BIST 100', '0', '0'),
        $usd,
        $eur,
        $gbp,
        pv_v7_market_item('altin', 'gram altin', 'GRAM ALTIN', '0', '0'),
        pv_v7_market_item('altin', 'ceyrek altin', 'ÇEYREK ALTIN', '0', '0'),
        pv_v7_market_item('coin', 'btc', 'BITCOIN', '0', '0'),
        pv_v7_market_item('coin', 'eth', 'ETHEREUM', '0', '0'),
        $xrp,
        pv_v7_market_item('parite', 'eur/usd', 'EUR/USD', '0', '0'),
    );
    return $items;
}


function pv_v7_converter_catalog() {
    pv_v7_ensure_market_data();
    global $currency_data, $altin_data, $coin_data;
    $catalog = array(
        'currency' => array('TRY'=>array('label'=>'TRY', 'rate'=>1)),
        'gold' => array(),
        'crypto' => array(),
    );
    $currency_labels = array('USD'=>'Dolar', 'EUR'=>'Euro', 'GBP'=>'Sterlin', 'CHF'=>'İsviçre Frangı', 'CAD'=>'Kanada Doları', 'CNY'=>'Çin Yuanı', 'JPY'=>'Japon Yeni', 'AUD'=>'Avustralya Doları', 'RUB'=>'Rus Rublesi');
    if (!empty($currency_data['selling']) && is_array($currency_data['selling'])) {
        foreach ($currency_data['selling'] as $idx=>$v) {
            $code = '';
            if (!empty($currency_data['code'][$idx])) $code = strtoupper(trim((string)$currency_data['code'][$idx]));
            elseif (is_string($idx) && strlen($idx) <= 4) $code = strtoupper($idx);
            elseif (!empty($currency_data['full_name'][$idx])) {
                $name_l = strtolower(html_entity_decode((string)$currency_data['full_name'][$idx], ENT_QUOTES, 'UTF-8'));
                foreach (array('USD'=>'dolar','EUR'=>'euro','GBP'=>'sterlin','CHF'=>'frank','CNY'=>'yuan','CNY'=>'çin','CAD'=>'kanada','RUB'=>'ruble','JPY'=>'japon','AUD'=>'avustralya') as $c=>$needle) if (strpos($name_l, $needle)!==false) { $code=$c; break; }
            }
            if (!$code) continue;
            $num = pv_v7_parse_number($v);
            $catalog['currency'][$code] = array('label'=>$currency_labels[$code] ?? $code, 'rate'=>$num);
        }
    }
    foreach ($currency_labels as $code=>$label) if (!isset($catalog['currency'][$code])) $catalog['currency'][$code] = array('label'=>$label, 'rate'=>0);

    $gold_keys = array(
        'GRAM_ALTIN'=>array('label'=>'Gram Altın','key'=>'gram altin'),
        'CEYREK_ALTIN'=>array('label'=>'Çeyrek Altın','key'=>'ceyrek altin'),
        'YARIM_ALTIN'=>array('label'=>'Yarım Altın','key'=>'yarim altin'),
        'CUMHURIYET_ALTINI'=>array('label'=>'Cumhuriyet Altını','key'=>'cumhuriyet altini'),
        'ONS_ALTIN'=>array('label'=>'Ons Altın','key'=>'ons altin'),
    );
    foreach ($gold_keys as $code=>$info) {
        $it = pv_v7_market_item('altin', $info['key'], $info['label'], '0', '0');
        $catalog['gold'][$code] = array('label'=>$info['label'], 'rate'=>pv_v7_parse_number($it['value']));
    }

    $crypto_labels = array('BTC'=>'Bitcoin','ETH'=>'Ethereum','XRP'=>'XRP','BCH'=>'Bitcoin Cash','LTC'=>'Litecoin','BNB'=>'BNB','SOL'=>'Solana','DOGE'=>'Dogecoin','AVAX'=>'Avalanche');
    $crypto_needles = array('BTC'=>array('btc','bitcoin'),'ETH'=>array('eth','ethereum'),'XRP'=>array('xrp'),'BCH'=>array('bch','bitcoin cash','bitcoin-cash'),'LTC'=>array('ltc','litecoin'),'BNB'=>array('bnb'),'SOL'=>array('sol','solana'),'DOGE'=>array('doge','dogecoin'),'AVAX'=>array('avax','avalanche'));
    foreach ($crypto_labels as $code=>$label) {
        $idx = null;
        foreach ($crypto_needles[$code] as $needle) { $idx = pv_v7_find_coin_index($needle); if ($idx !== null) break; }
        $rate = 0;
        if ($idx !== null && isset($coin_data['current_price'][$idx])) $rate = pv_v7_parse_number($coin_data['current_price'][$idx]);
        if ($rate > 0) $catalog['crypto'][$code] = array('label'=>$label, 'rate'=>$rate);
    }
    if (empty($catalog['crypto'])) $catalog['crypto'] = array('BTC'=>array('label'=>'Bitcoin','rate'=>0));
    return $catalog;
}

function pv_v7_converter_json() {
    $catalog = pv_v7_converter_catalog();
    $out = array('TRY'=>1);
    foreach ($catalog as $group) {
        foreach ($group as $code=>$item) $out[$code] = (float) $item['rate'];
    }
    return $out;
}

function pv_v7_converter_options($group='currency', $selected='') {
    $catalog = pv_v7_converter_catalog();
    $list = $catalog[$group] ?? $catalog['currency'];
    foreach ($list as $code=>$item) {
        printf('<option value="%s"%s>%s</option>', esc_attr($code), selected($selected, $code, false), esc_html($item['label']));
    }
}

function pv_v7_ipo_term($future=false) {
    if (!taxonomy_exists('hissetipi')) return null;
    $future_slugs = array('taslak-arzlar','taslak','gelecek-halka-arzlar','gelecek');
    $active_slugs = array('ilk-halka-arzlar','ilk-arz','halka-arz-takvimi','aktif');
    $slugs = $future ? $future_slugs : $active_slugs;
    foreach ($slugs as $slug) {
        $term = get_term_by('slug', $slug, 'hissetipi');
        if ($term && !is_wp_error($term)) return $term;
    }
    $term = get_term($future ? 64 : 63, 'hissetipi');
    return ($term && !is_wp_error($term)) ? $term : null;
}

function pv_v7_ipo_future_count() {
    if (!post_type_exists('halka-arz')) return 0;
    $tax_query = array();
    $future = pv_v7_ipo_term(true);
    if ($future) $tax_query[] = array('taxonomy'=>'hissetipi','field'=>'term_id','terms'=>array($future->term_id));
    $q = new WP_Query(array('post_type'=>'halka-arz','posts_per_page'=>1,'fields'=>'ids','tax_query'=>$tax_query));
    return (int) $q->found_posts;
}

function pv_v7_fallback_menu() {
    $items = array(
        'Gündem'=>array('/gundem/', array('Son Dakika'=>'/son-dakika/','Türkiye'=>'/gundem/turkiye/','Dünya'=>'/gundem/dunya/')),
        'Ekonomi'=>array('/ekonomi/', array('Piyasalar'=>'/piyasalar/','Şirket Haberleri'=>'/sirket-haberleri/','KOBİ'=>'/kobi/')),
        'Borsa'=>array('/borsa/', array('BIST 100'=>'/borsa/bist-100/','Hisseler'=>'/borsa/hisseler/','En Çok İşlem Görenler'=>'/borsa/en-cok-islem-gorenler/')),
        'Döviz'=>array('/doviz/', array('Dolar'=>'/doviz/dolar/','Euro'=>'/doviz/euro/','Pariteler'=>'/parite/')),
        'Altın'=>array('/altin/', array('Gram Altın'=>'/altin/gram-altin/','Çeyrek Altın'=>'/altin/ceyrek-altin/','Ons Altın'=>'/altin/ons-altin/')),
        'Finans'=>array('/finans/', array('Bankacılık'=>'/finans/bankacilik/','Kredi'=>'/kredi/','Mevduat'=>'/faiz-oranlari/')),
        'Halka Arz'=>array('/halka-arz/', array('Halka Arz Takvimi'=>'/halka-arz/','Gelecek Halka Arzlar'=>'/gelecek-halka-arzlar/')),
        'Kripto Para'=>array('/kripto-para/', array('Bitcoin'=>'/kripto-para/bitcoin/','Ethereum'=>'/kripto-para/ethereum/','Altcoin'=>'/kripto-para/altcoin/')),
    );
    echo '<ul class="pv-default-menu">';
    foreach ($items as $label=>$data) {
        echo '<li class="menu-item menu-item-has-children"><a href="'.esc_url(home_url($data[0])).'">'.esc_html($label).'</a><ul class="sub-menu">';
        foreach($data[1] as $sub=>$url) echo '<li><a href="'.esc_url(home_url($url)).'">'.esc_html($sub).'</a></li>';
        echo '</ul></li>';
    }
    echo '</ul>';
}

function pv_v7_ipo_meta($post_id){
    $meta = get_post_meta($post_id, 'hisse_ayarlar', true);
    return is_array($meta) ? $meta : array();
}
function pv_v7_ipo_meta_get($meta, $key, $default=''){
    return isset($meta[$key]) && $meta[$key] !== '' ? $meta[$key] : $default;
}
function pv_v7_ipo_date_obj($str){
    if(!$str) return null;
    $d = DateTime::createFromFormat('d/m/Y', $str);
    if(!$d) $d = DateTime::createFromFormat('d-m-Y', $str);
    if(!$d) $d = DateTime::createFromFormat('Y-m-d', $str);
    return $d ?: null;
}
function pv_v7_ipo_badges($meta, $mode='takvim'){
    $today = new DateTime('today');
    $start = pv_v7_ipo_date_obj(pv_v7_ipo_meta_get($meta,'halka-arz-tarihi-baslangic'));
    $end   = pv_v7_ipo_date_obj(pv_v7_ipo_meta_get($meta,'halka-arz-tarihi-bitis'));
    $bist_date_raw = pv_v7_ipo_meta_get($meta,'bist-islem-tarihi');
    $bist_date = pv_v7_ipo_date_obj($bist_date_raw);
    $out = '';
    if($mode === 'gelecek') {
        $out .= '<a class="gelecek-halka-arz-badge" href="'.esc_url(home_url('/gelecek-halka-arzlar/')).'"><i data-pv-title="Taslak Halka Arz" aria-label="Taslak Halka Arz" class="fa-solid fa-circle-dot snc-badge pv-ipo-icon"></i></a>';
        $out .= '<i data-pv-title="Hazırlanıyor" aria-label="Hazırlanıyor" class="fa-regular fa-bell snc-badge pv-ipo-icon"></i>';
        return $out;
    }
    if($start && $end && $start <= $today && $end >= $today) {
        $out .= '<span class="il-tt" data-pv-title="Talep Topluyor" aria-label="Talep Topluyor"><span class="circle pulse"></span><span class="sr-only">Talep Topluyor</span></span>';
    }
    if($end && $end < $today) {
        $out .= '<i data-pv-title="Talep Toplama Tamamlandı" aria-label="Talep Toplama Tamamlandı" class="fa-solid fa-clock-rotate-left snc-badge pv-ipo-icon"></i>';
    }
    if(!empty($meta['arz-yurt-ici-bireysel-kisi'])) {
        $out .= '<i data-pv-title="Halka Arz Sonuçları Açıklandı" aria-label="Halka Arz Sonuçları Açıklandı" class="fa-regular fa-chart-bar snc-badge pv-ipo-icon"></i>';
    }
    if(!empty($meta['on-onay'])) {
        $out .= '<i data-pv-title="Ön Onaylı" aria-label="Ön Onaylı" class="fa-solid fa-check snc-badge pv-ipo-icon"></i>';
    }
    // İşlem görmeye başladı rozeti sol durum alanında değil, BIST kodunun yanında gösterilir.
    // Bu yüzden burada bilinçli olarak basılmıyor.
    if(empty($bist_date_raw) || ($bist_date && $today < $bist_date)) {
        $out .= '<a href="'.esc_url(home_url('/durumlar/yeni/')).'" class="il-new" data-pv-title="Yeni" aria-label="Yeni"><span class="bell far fa-bell"></span></a>';
    }
    return $out;
}
function pv_v7_render_ipo_row($post_id, $mode='takvim'){
    $meta = pv_v7_ipo_meta($post_id);
    $thumb = get_the_post_thumbnail_url($post_id, 'thumbnail');
    $code = pv_v7_ipo_meta_get($meta,'bist-kodu','');
    $date = pv_v7_ipo_meta_get($meta,'arz-tarihi','');
    $value = pv_v7_ipo_meta_get($meta,'arz-deger','');
    $price = pv_v7_ipo_meta_get($meta,'arz-fiyat','');
    echo '<li><article class="index-list pv-ipo-row">';
    echo '<div class="il-badge">'.pv_v7_ipo_badges($meta, $mode).'</div>';
    if($thumb){ echo '<a class="pv-ipo-logo" href="'.esc_url(get_permalink($post_id)).'"><img src="'.esc_url($thumb).'" class="slogo" alt="'.esc_attr(get_the_title($post_id)).'"></a>'; }
    else { echo '<a class="pv-ipo-logo pv-ipo-logo-fallback" href="'.esc_url(get_permalink($post_id)).'">'.esc_html($code ?: mb_substr(get_the_title($post_id),0,2,'UTF-8')).'</a>'; }
    echo '<div class="il-content">';
    if($code){
        echo '<span class="il-bist-kod"><span data-pv-title="BIST Kodu: '.esc_attr($code).'" aria-label="BIST Kodu: '.esc_attr($code).'" class="bist-kodu">'.esc_html($code).'</span>';
        if(!empty($meta['on-onay'])) {
            echo ' <i data-pv-title="Ön Onaylı" aria-label="Ön Onaylı" class="fa-solid fa-check snc-badge pv-ipo-icon"></i>';
        }
        $bist_date_inline = pv_v7_ipo_date_obj(pv_v7_ipo_meta_get($meta,'bist-islem-tarihi'));
        if($bist_date_inline || pv_v7_ipo_meta_get($meta,'bist-islem-tarihi')) {
            echo ' <i data-pv-title="İşlem Görmeye Başladı" aria-label="İşlem Görmeye Başladı" class="fa-solid fa-arrow-trend-up snc-badge pv-ipo-icon"></i>';
        }
        echo '</span>';
    }
    echo '<h3 class="il-halka-arz-sirket"><a href="'.esc_url(get_permalink($post_id)).'">'.esc_html(get_the_title($post_id)).'</a></h3>';
    if($date || $value || $price){
        echo '<span class="il-halka-arz-tarihi">';
        if($date) echo '<time datetime="'.esc_attr($date).'">'.esc_html($date).'</time>';
        echo '<span class="fiyat-list">';
        if($value) echo ' - Halka Arz Değeri: <b>'.esc_html($value).'</b>';
        if($price) echo ' - Halka Arz Fiyatı: <b>'.esc_html($price).'</b>';
        echo '</span></span>';
    }
    echo '</div></article></li>';
}
function pv_v7_ipo_calendar($mode='takvim') {
    if (!post_type_exists('halka-arz')) { echo '<div class="agenda"><div class="agenda-empty">Halka arz eklentisi aktif olduğunda veriler burada listelenecek.</div></div>'; return; }
    $future = pv_v7_ipo_term(true);
    $tax_query = array();
    if(taxonomy_exists('hissetipi') && $future){
        if($mode === 'gelecek') $tax_query[] = array('taxonomy'=>'hissetipi','field'=>'term_id','terms'=>array($future->term_id));
        else $tax_query[] = array('taxonomy'=>'hissetipi','field'=>'term_id','terms'=>array($future->term_id),'operator'=>'NOT IN');
    }

    // Halka Arz Tarihi Başlangıç alanı hisse_ayarlar içinde tutulduğu için WP_Query ile direkt sıralanamıyor.
    // Bu yüzden ilgili kayıtları çekip PHP tarafında halka-arz-tarihi-baslangic'a göre sıralıyoruz.
    $q = new WP_Query(array(
        'post_type' => 'halka-arz',
        'posts_per_page' => 300,
        'fields' => 'ids',
        'ignore_sticky_posts' => true,
        'tax_query' => $tax_query,
    ));
    $ids = $q->posts;
    usort($ids, function($a, $b){
        $today = strtotime('today');
        $ma = pv_v7_ipo_meta($a);
        $mb = pv_v7_ipo_meta($b);
        $da = pv_v7_ipo_date_obj(pv_v7_ipo_meta_get($ma,'halka-arz-tarihi-baslangic')) ?: pv_v7_ipo_date_obj(pv_v7_ipo_meta_get($ma,'arz-tarihi'));
        $db = pv_v7_ipo_date_obj(pv_v7_ipo_meta_get($mb,'halka-arz-tarihi-baslangic')) ?: pv_v7_ipo_date_obj(pv_v7_ipo_meta_get($mb,'arz-tarihi'));
        $ta = $da ? $da->getTimestamp() : PHP_INT_MAX;
        $tb = $db ? $db->getTimestamp() : PHP_INT_MAX;
        $a_future = $ta >= $today;
        $b_future = $tb >= $today;
        if ($a_future && $b_future) return $ta <=> $tb; // En yakın gelecek tarih en üstte
        if (!$a_future && !$b_future) return $tb <=> $ta; // Geçmiş kayıtlar kendi içinde en yeni üstte
        return $a_future ? -1 : 1;
    });

    echo '<ul class="halka-arz-list pv-ipo-original-list">';
    if(!empty($ids)){
        foreach(array_slice($ids, 0, 6) as $post_id){ pv_v7_render_ipo_row($post_id, $mode === 'gelecek' ? 'gelecek' : 'takvim'); }
        wp_reset_postdata();
    } else {
        echo '<li class="pv-empty">Gösterilecek halka arz kaydı bulunamadı.</li>';
    }
    echo '</ul>';
    $count = $mode === 'gelecek' ? pv_v7_ipo_future_count() : (int)$q->found_posts;
    $url = $mode === 'gelecek' ? home_url('/gelecek-halka-arzlar/') : home_url('/halka-arz/');
    if($count > 6) echo '<a class="pv-ipo-all" href="'.esc_url($url).'">Tümünü Gör ('.esc_html($count).')</a>';
}

function pv_v7_stock_table($kind='volume') {
    pv_v7_ensure_market_data();
    global $borsa_islem_gorenler_data, $borsa_artanlar_data, $borsa_azalanlar_data, $bp_options;
    $map = array('volume'=>$borsa_islem_gorenler_data ?? array(), 'up'=>$borsa_artanlar_data ?? array(), 'down'=>$borsa_azalanlar_data ?? array());
    $data = $map[$kind] ?? array();
    $rows = array();
    if (!empty($data) && is_array($data)) {
        $count = 0;
        foreach ($data as $k=>$row) {
            if ($count++ >= 6) break;
            if (is_array($row)) {
                $name = $row['hisse'] ?? $row['name'] ?? $row['title'] ?? $row['sembol'] ?? $row['code'] ?? $row['symbol'] ?? $k;
                $price = $row['son'] ?? $row['price'] ?? $row['value'] ?? $row['last'] ?? '';
                $volume = $row['hacim'] ?? $row['volume'] ?? $row['islem'] ?? '';
                $rate = $row['degisim'] ?? $row['rate'] ?? $row['change'] ?? $row['change_rate'] ?? $row['yuzde'] ?? '';
                $link = $row['link'] ?? sanitize_title($name);
            } else { $name = is_numeric($k) ? $row : $k; $price = ''; $volume=''; $rate = ''; $link=sanitize_title($name); }
            $rows[] = array($name,$price,$volume,$rate,$link);
        }
    }
    echo '<div class="pv-stock-list pv-stock-'.esc_attr($kind).'">';
    if (!$rows) {
        echo '<div class="pv-empty">Gerçek borsa verisi henüz alınamadı.</div>';
    } else {
        foreach ($rows as $r) {
            $cls = pv_v7_market_classes($r[3]);
            $url = pv_v7_market_url('hisse', $r[4], $r[0]);
            echo '<a class="pv-stock-row" href="'.esc_url($url).'"><span class="stock-code">'.esc_html($r[0]).'</span><span class="stock-price">'.esc_html($r[1] ?: '—').'</span><span class="stock-volume">'.esc_html($r[2] ?: '—').'</span><span class="stock-change '.esc_attr($cls).'">'.esc_html($r[3] ?: '—').'</span></a>';
        }
    }
    echo '</div>';
}

function pv_v7_market_table_currency() {
    $rows = array(
        array('Amerikan Doları','usd'), array('Euro','eur'), array('İngiliz Sterlini','gbp'), array('İsviçre Frankı','chf'), array('Kanada Doları','cad'), array('Çin Yuanı','cny')
    );
    echo '<div class="pv-data-table-wrap"><table class="pv-data-table"><thead><tr><th>Döviz</th><th>Alış</th><th>Satış</th><th>Fark</th></tr></thead><tbody>';
    foreach ($rows as $r) { $it=pv_v7_market_item('doviz',$r[1],$r[0],'0','0'); $cls=pv_v7_market_classes($it['rate']); echo '<tr><td><a href="'.esc_url($it['url']).'">'.esc_html($r[0]).'</a></td><td>'.esc_html($it['buying']).'</td><td>'.esc_html($it['selling']).'</td><td class="'.esc_attr($cls).'">% '.esc_html(pv_v7_num($it['rate'])).'</td></tr>'; }
    echo '</tbody></table></div>';
}
function pv_v7_market_table_gold() {
    $rows = array(array('Ons','ons altin'),array('Gram','gram altin'),array('Çeyrek','ceyrek altin'),array('Yarım','yarim altin'),array('Tam','tam altin'),array('Cumhuriyet','cumhuriyet altini'));
    echo '<div class="pv-data-table-wrap"><table class="pv-data-table"><thead><tr><th>Altın</th><th>Alış</th><th>Satış</th></tr></thead><tbody>';
    foreach ($rows as $r) { $it=pv_v7_market_item('altin',$r[1],$r[0],'0','0'); echo '<tr><td><a href="'.esc_url($it['url']).'">'.esc_html($r[0]).'</a></td><td>'.esc_html($it['buying']).'</td><td>'.esc_html($it['selling']).'</td></tr>'; }
    echo '</tbody></table></div>';
}
function pv_v7_market_table_crypto() {
    $rows = array(array('Bitcoin','btc'),array('Ethereum','eth'),array('XRP','xrp'),array('Bitcoin Cash','bch'),array('Litecoin','ltc'),array('BNB','bnb'),array('Solana','sol'));
    echo '<div class="pv-data-table-wrap"><table class="pv-data-table"><thead><tr><th>Kripto Para</th><th>Türk Lirası</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $it=pv_v7_market_item('coin',$r[1],$r[0],'0','0');
        $value_num = pv_v7_parse_number($it['value']);
        if ($value_num <= 0) continue;
        echo '<tr><td><a href="'.esc_url($it['url']).'">'.esc_html($r[0]).'</a></td><td>'.esc_html($it['value']).'</td></tr>';
    }
    echo '</tbody></table></div>';
}
function pv_v7_market_summary_widget() {
    echo '<section class="panel pv-summary-widget"><div class="panel-h"><h2>Piyasa Özeti</h2><span class="badge">Canlı</span></div><div class="pv-summary-grid">';
    foreach(array_slice(pv_v7_ticker_items(),0,8) as $it){$cls=pv_v7_market_classes($it['rate']); echo '<a href="'.esc_url($it['url']).'"><small>'.esc_html($it['name']).'</small><b>'.esc_html($it['value']).'</b><em class="'.esc_attr($cls).'">'.($cls==='down'?'▼':'▲').' %'.esc_html(pv_v7_num($it['rate'])).'</em></a>';}
    echo '</div></section>';
}
function pv_v7_forex_widget() {
    echo '<section class="panel forex-widget"><div class="panel-h"><h2>Foreks Hesapla</h2><span class="badge">Parite</span></div><div class="forex-calc"><input type="number" value="1" class="pv-forex-amount"><select class="pv-forex-pair"><option value="EUR/USD">EUR/USD</option><option value="GBP/USD">GBP/USD</option><option value="USD/TRY">USD/TRY</option><option value="EUR/TRY">EUR/TRY</option></select><button type="button" class="primary pv-forex-btn">Hesapla</button><div class="pv-forex-result">Sonuç hesaplanacak</div></div></section>';
}

// v7.10 extra ad slots: left/right sticky page-skin towers.
add_action('widgets_init', function(){
    foreach (array('pv_pageskin_left'=>'PV Sol PageSkin 120x600 / 160x600', 'pv_pageskin_right'=>'PV Sağ PageSkin 120x600 / 160x600') as $id=>$name) {
        register_sidebar(array(
            'name'=>$name,'id'=>$id,
            'before_widget'=>'<div class="pv-widget %2$s">','after_widget'=>'</div>',
            'before_title'=>'<h3 class="pv-widget-title">','after_title'=>'</h3>'
        ));
    }
}, 99);

function pv_v7_pageskin_ads(){
    if (is_404() || is_search()) { return; }
    $path = isset($_SERVER['REQUEST_URI']) ? trim((string) wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH), '/') : '';
    $first = explode('/', $path)[0] ?? $path;
    $credit_paths = array(
        'kredi',
        'kredi-hesapla',
        'ihtiyac-kredisi',
        'konut-kredisi',
        'tasit-kredisi',
        'kobi-kredisi',
        'faiz-oranlari',
        'kredi-faiz-oranlari',
        'mevduat-oranlari',
        'piyasanin-nabzi',
        'trafik-sigortasi',
        'kasko-sigortasi',
        'hakkimizda',
        'kunye',
        'iletisim',
        'bize-reklam-ver',
        'reklam',
        'bulten',
        'aydinlatma-metni',
        'kvkk',
        'cerez-politikasi',
        'kullanim-kosullari',
        'sorumluluk-reddi',
        'yasal-metinler',
    );
    if (in_array($path, $credit_paths, true) || in_array($first, $credit_paths, true)) {
        return;
    }

    if (is_active_sidebar('pv_pageskin_left')) {
        echo '<aside class="pv-pageskin pv-pageskin-left" aria-label="Sol reklam">'; dynamic_sidebar('pv_pageskin_left'); echo '</aside>';
    }
    if (is_active_sidebar('pv_pageskin_right')) {
        echo '<aside class="pv-pageskin pv-pageskin-right" aria-label="Sağ reklam">'; dynamic_sidebar('pv_pageskin_right'); echo '</aside>';
    }
}
add_action('wp_body_open','pv_v7_pageskin_ads', 20);

/* === v2.26: shared inner/sidebar widgets and related posts === */
function pv_v7_converter_links_widget() {
    $links = array(
        array('Amerikan Doları - USD', 'usd', '🇺🇸'),
        array('Euro - EUR', 'eur', '🇪🇺'),
        array('İngiliz Sterlini - GBP', 'gbp', '🇬🇧'),
        array('İsviçre Frankı - CHF', 'chf', '🇨🇭'),
        array('Kanada Doları - CAD', 'cad', '🇨🇦'),
        array('Çin Yuanı - CNY', 'cny', '🇨🇳'),
        array('Japon Yeni - JPY', 'jpy', '🇯🇵'),
        array('Gram Altın', 'gram-altin-fiyati', '🟡'),
        array('Çeyrek Altın', 'ceyrek-altin-fiyati', '🟠'),
        array('Bitcoin', 'bitcoin', '₿'),
        array('Ethereum', 'ethereum', 'Ξ'),
        array('XRP', 'xrp', 'X'),
    );
    echo '<section class="panel pv-converter-links"><div class="panel-h"><h2>Tıkla ve Hesapla</h2><span class="badge">Canlı</span></div><div class="pv-converter-link-list">';
    foreach ($links as $l) {
        if (in_array($l[1], array('gram-altin-fiyati','ceyrek-altin-fiyati'), true)) {
            $url = home_url('/altin/?a='.$l[1]);
        } elseif (in_array($l[1], array('bitcoin','ethereum','xrp'), true)) {
            $url = home_url('/coin/?c='.$l[1]);
        } else {
            $url = home_url('/doviz/?c='.$l[1]);
        }
        echo '<a href="'.esc_url($url).'"><span>'.esc_html($l[2]).'</span><b>'.esc_html($l[0]).'</b></a>';
    }
    echo '</div></section>';
}

function pv_v7_sidebar_gam_ad($area, $label, $class, $fallback_id) {
    if (is_active_sidebar($area)) {
        pv_v7_ad($area, $label, $class);
        return;
    }
    if ($fallback_id) {
        echo '<div class="pv-ad-slot '.esc_attr($class).' pv-ad-fallback"><div class="adbox" id="'.esc_attr($fallback_id).'"></div></div>';
    }
}

function pv_v7_inner_sidebar($context='archive') {
    echo '<aside class="sidebar"><div class="sidebar-sticky-inner">';
    // PV widget alanları boş olsa bile GAM head kodunda tanımlı standart slotları basıyoruz.
    // Böylece single/archive sayfalarda ana sayfadaki reklam yerleşimi kaybolmaz.
    pv_v7_sidebar_gam_ad('pv_sidebar_top','300×250 Reklam','ad ad-300x250 pv-ad-desktop','div-gpt-300x250-mr1');
    pv_v7_converter_links_widget();
    pv_v7_sidebar_gam_ad('pv_sidebar_mid','320×100 Reklam','ad ad-320 pv-ad-desktop','div-gpt-300x250-mr2');
    pv_v7_market_summary_widget();
    pv_v7_forex_widget();
    pv_v7_sidebar_gam_ad('pv_sidebar_sky','300×600 Reklam','ad ad-300x600 pv-ad-desktop','div-gpt-300x600');
    echo '</div></aside>';
}



/* === v2.39: clean sidebar for native BirFinans market templates === */
if (!function_exists('pv_v7_market_sidebar')) {
    function pv_v7_market_sidebar($context='market') {
        echo '<aside class="sidebar pv-market-sidebar-clean"><div class="sidebar-sticky-inner pv-market-sidebar-inner">';
        pv_v7_sidebar_gam_ad('pv_sidebar_top','300×250 Reklam','ad ad-300x250 pv-ad-desktop','div-gpt-300x250-mr1');
        pv_v7_market_summary_widget();
        pv_v7_converter_links_widget();
        pv_v7_forex_widget();
        pv_v7_sidebar_gam_ad('pv_sidebar_mid','300×250 Reklam','ad ad-300x250 pv-ad-desktop','div-gpt-300x250-mr2');
        pv_v7_sidebar_gam_ad('pv_sidebar_sky','300×250 Reklam','ad ad-300x250 pv-ad-desktop','div-gpt-300x250-mr3');
        echo '</div></aside>';
    }
}

function pv_v7_related_posts_block($post_id=null, $limit=8) {
    $post_id = $post_id ?: get_the_ID();
    $cats = wp_get_post_categories($post_id);
    $args = array('posts_per_page'=>$limit, 'post__not_in'=>array($post_id), 'ignore_sticky_posts'=>true);
    if (!empty($cats)) $args['category__in'] = $cats;
    $q = new WP_Query($args);
    if (!$q->have_posts()) return;
    echo '<section class="pv-related-block"><div class="section-title"><h2>Benzer Haberler</h2><a class="badge" href="'.esc_url(home_url('/haberler/')).'">Tüm Haberler</a></div><div class="cards-grid pv-related-grid">';
    while($q->have_posts()): $q->the_post(); $img = pv_v7_img(get_the_ID());
        echo '<a class="mini-card" href="'.esc_url(get_permalink()).'"><div class="mini-img" '.($img ? 'style="background-image:url('.esc_url($img).');background-size:cover;background-position:center"' : '').'></div><h4>'.esc_html(get_the_title()).'</h4></a>';
    endwhile; wp_reset_postdata();
    echo '</div></section>';
}

/* === v2.44: crypto visual fixes + native IPO templates === */
if (!function_exists('pv_v7_coin_avatar')) {
    function pv_v7_coin_avatar($symbol = '', $name = '', $size = 'md') {
        $symbol = strtoupper(trim((string) $symbol));
        $name = trim((string) $name);
        $label = $symbol ?: mb_substr($name ?: '₿', 0, 3, 'UTF-8');
        $label = mb_substr($label, 0, 4, 'UTF-8');
        return '<span class="pv-coin-avatar pv-coin-avatar-' . esc_attr($size) . '" aria-hidden="true">' . esc_html($label) . '</span>';
    }
}

add_filter('body_class', function($classes){
    $path = isset($_SERVER['REQUEST_URI']) ? trim((string) wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH), '/') : '';
    if (is_singular('halka-arz')) {
        $classes[] = 'pv-ipo-theme';
        $classes[] = 'pv-page-ipo-single';
    }
    if (is_post_type_archive('halka-arz') || is_tax(array('durumlar','hissetipi','yillar','pazar','endeks'))) {
        $classes[] = 'pv-ipo-theme';
        $classes[] = 'pv-page-ipo-archive';
    }
    if (in_array($path, array('halka-arz','halka-arz-takvimi','halka-arz-takivimi','gelecek-halka-arzlar'), true)) {
        $classes[] = 'pv-ipo-theme';
        $classes[] = 'pv-page-ipo-calendar';
    }
    return array_values(array_unique($classes));
}, 80);

add_filter('single_template', function($template){
    if (is_singular('halka-arz')) {
        $child = get_stylesheet_directory() . '/single-halka-arz.php';
        if (file_exists($child)) return $child;
    }
    return $template;
}, 99);

add_filter('archive_template', function($template){
    if (is_post_type_archive('halka-arz')) {
        $child = get_stylesheet_directory() . '/archive-halka-arz.php';
        if (file_exists($child)) return $child;
    }
    return $template;
}, 99);

add_filter('template_include', function($template){
    if (is_singular('halka-arz')) {
        $child = get_stylesheet_directory() . '/single-halka-arz.php';
        if (file_exists($child)) return $child;
    }
    if (is_post_type_archive('halka-arz') || is_tax(array('durumlar','hissetipi','yillar','pazar','endeks'))) {
        $child = get_stylesheet_directory() . '/archive-halka-arz.php';
        if (file_exists($child)) return $child;
    }
    if (is_page_template('hativ2-halka-arz-takvimi') || is_page(array('halka-arz-takvimi','halka-arz-takivimi','halka-arz','gelecek-halka-arzlar'))) {
        $child = get_stylesheet_directory() . '/page-halka-arz-takvimi.php';
        if (file_exists($child)) return $child;
    }
    return $template;
}, 99);

if (!function_exists('pv_v7_ipo_safe_meta')) {
    function pv_v7_ipo_safe_meta($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        $meta = get_post_meta($post_id, 'hisse_ayarlar', true);
        return is_array($meta) ? $meta : array();
    }
}

if (!function_exists('pv_v7_ipo_get')) {
    function pv_v7_ipo_get($meta, $key, $default = '') {
        return isset($meta[$key]) && $meta[$key] !== '' && $meta[$key] !== null ? $meta[$key] : $default;
    }
}

if (!function_exists('pv_v7_ipo_date_value')) {
    function pv_v7_ipo_date_value($meta) {
        $date = pv_v7_ipo_get($meta, 'halka-arz-tarihi-baslangic');
        if (!$date) $date = pv_v7_ipo_get($meta, 'arz-tarihi');
        $obj = function_exists('pv_v7_ipo_date_obj') ? pv_v7_ipo_date_obj($date) : null;
        return $obj ? $obj->getTimestamp() : PHP_INT_MAX;
    }
}

if (!function_exists('pv_v7_ipo_term_tax_query')) {
    function pv_v7_ipo_term_tax_query($mode = 'takvim') {
        $tax_query = array();
        if (!taxonomy_exists('hissetipi') || !function_exists('pv_v7_ipo_term')) return $tax_query;
        $future = pv_v7_ipo_term(true);
        if (!$future) return $tax_query;
        if ($mode === 'gelecek') {
            $tax_query[] = array('taxonomy' => 'hissetipi', 'field' => 'term_id', 'terms' => array($future->term_id));
        } elseif ($mode === 'takvim') {
            $tax_query[] = array('taxonomy' => 'hissetipi', 'field' => 'term_id', 'terms' => array($future->term_id), 'operator' => 'NOT IN');
        }
        return $tax_query;
    }
}

if (!function_exists('pv_v7_ipo_collect_ids')) {
    function pv_v7_ipo_collect_ids($mode = 'takvim', $limit = 300, $extra_args = array()) {
        if (!post_type_exists('halka-arz')) return array();
        $args = array(
            'post_type' => 'halka-arz',
            'post_status' => 'publish',
            'posts_per_page' => 300,
            'fields' => 'ids',
            'ignore_sticky_posts' => true,
            'tax_query' => pv_v7_ipo_term_tax_query($mode),
        );
        if (!empty($extra_args)) {
            if (isset($extra_args['tax_query'])) {
                $base_tax = !empty($args['tax_query']) ? $args['tax_query'] : array();
                $extra_tax = is_array($extra_args['tax_query']) ? $extra_args['tax_query'] : array();
                unset($extra_args['tax_query']);
                $args = array_merge($args, $extra_args);
                $args['tax_query'] = array_merge($base_tax, $extra_tax);
                if (count($args['tax_query']) > 1) $args['tax_query']['relation'] = 'AND';
            } else {
                $args = array_merge($args, $extra_args);
            }
        }
        if (empty($args['tax_query'])) unset($args['tax_query']);
        $q = new WP_Query($args);
        $ids = array_map('intval', $q->posts);
        usort($ids, function($a, $b){
            $today = strtotime('today');
            $ta = pv_v7_ipo_date_value(pv_v7_ipo_safe_meta($a));
            $tb = pv_v7_ipo_date_value(pv_v7_ipo_safe_meta($b));
            $af = $ta >= $today;
            $bf = $tb >= $today;
            if ($af && $bf) return $ta <=> $tb;
            if (!$af && !$bf) return $tb <=> $ta;
            return $af ? -1 : 1;
        });
        if ($limit > 0) $ids = array_slice($ids, 0, (int) $limit);
        return $ids;
    }
}

if (!function_exists('pv_v7_ipo_render_list_items')) {
    function pv_v7_ipo_render_list_items($ids, $mode = 'takvim') {
        echo '<ul class="halka-arz-list pv-ipo-original-list pv-ipo-calendar-list">';
        if (empty($ids)) {
            echo '<li class="pv-ipo-empty">Gösterilecek halka arz kaydı bulunamadı.</li>';
        } else {
            foreach ($ids as $post_id) {
                if (function_exists('pv_v7_render_ipo_row')) {
                    pv_v7_render_ipo_row($post_id, $mode);
                }
            }
        }
        echo '</ul>';
    }
}

if (!function_exists('pv_v7_ipo_render_calendar_tabs')) {
    function pv_v7_ipo_render_calendar_tabs($active = 'takvim', $limit = 120, $archive_args = array()) {
        $takvim_ids = pv_v7_ipo_collect_ids('takvim', $limit, $archive_args);
        $gelecek_ids = pv_v7_ipo_collect_ids('gelecek', $limit, $archive_args);
        $all_count = count($takvim_ids) + count($gelecek_ids);
        echo '<section class="pv-ipo-calendar-card">';
        echo '<div class="pv-ipo-card-head"><div><span class="pv-eyebrow">Canlı Takvim</span><h2>Halka Arz Takvimi</h2></div><a class="pv-ipo-head-link" href="' . esc_url(home_url('/halka-arz/')) . '">Tümünü Gör</a></div>';
        echo '<div class="pv-ipo-tabs" role="tablist" aria-label="Halka arz sekmeleri">';
        echo '<button type="button" class="pv-ipo-tab ' . ($active === 'takvim' ? 'active' : '') . '" data-pv-ipo-tab="takvim">Halka Arz Takvimi <span>' . esc_html(count($takvim_ids)) . '</span></button>';
        echo '<button type="button" class="pv-ipo-tab ' . ($active === 'gelecek' ? 'active' : '') . '" data-pv-ipo-tab="gelecek">Gelecek Halka Arzlar <span>' . esc_html(count($gelecek_ids)) . '</span></button>';
        echo '<button type="button" class="pv-ipo-tab" data-pv-ipo-tab="tum">Tümü <span>' . esc_html($all_count) . '</span></button>';
        echo '</div>';
        echo '<div class="pv-ipo-panel ' . ($active === 'takvim' ? 'active' : '') . '" data-pv-ipo-panel="takvim">'; pv_v7_ipo_render_list_items($takvim_ids, 'takvim'); echo '</div>';
        echo '<div class="pv-ipo-panel ' . ($active === 'gelecek' ? 'active' : '') . '" data-pv-ipo-panel="gelecek">'; pv_v7_ipo_render_list_items($gelecek_ids, 'gelecek'); echo '</div>';
        echo '<div class="pv-ipo-panel" data-pv-ipo-panel="tum">'; pv_v7_ipo_render_list_items(array_merge($takvim_ids, $gelecek_ids), 'takvim'); echo '</div>';
        echo '</section>';
    }
}

if (!function_exists('pv_v7_ipo_terms_text')) {
    function pv_v7_ipo_terms_text($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);
        if (!$terms || is_wp_error($terms)) return '';
        return implode(', ', wp_list_pluck($terms, 'name'));
    }
}

if (!function_exists('pv_v7_ipo_date_range_label')) {
    function pv_v7_ipo_date_range_label($meta) {
        $start = pv_v7_ipo_get($meta, 'halka-arz-tarihi-baslangic');
        $end = pv_v7_ipo_get($meta, 'halka-arz-tarihi-bitis');
        $fallback = pv_v7_ipo_get($meta, 'arz-tarihi');
        if (!$start || !$end) return $fallback;
        $start_obj = function_exists('pv_v7_ipo_date_obj') ? pv_v7_ipo_date_obj($start) : null;
        $end_obj = function_exists('pv_v7_ipo_date_obj') ? pv_v7_ipo_date_obj($end) : null;
        if (!$start_obj || !$end_obj) return $fallback ?: trim($start . ' - ' . $end, ' -');
        $start_obj->modify('+1 day');
        $end_obj->modify('-1 day');
        $months = array('01'=>'Ocak','02'=>'Şubat','03'=>'Mart','04'=>'Nisan','05'=>'Mayıs','06'=>'Haziran','07'=>'Temmuz','08'=>'Ağustos','09'=>'Eylül','10'=>'Ekim','11'=>'Kasım','12'=>'Aralık');
        return $start_obj->format('d') . ' ' . ($months[$start_obj->format('m')] ?? $start_obj->format('m')) . ' - ' . $end_obj->format('d') . ' ' . ($months[$end_obj->format('m')] ?? $end_obj->format('m')) . ' ' . $end_obj->format('Y');
    }
}

if (!function_exists('pv_v7_ipo_detail_rows')) {
    function pv_v7_ipo_detail_rows($post_id, $meta) {
        $rows = array(
            'Halka Arz Tarihi' => pv_v7_ipo_date_range_label($meta),
            'Halka Arz Fiyatı/Aralığı' => pv_v7_ipo_get($meta, 'arz-fiyat-aralik') ?: pv_v7_ipo_get($meta, 'arz-fiyat'),
            'Dağıtım Yöntemi' => pv_v7_ipo_get($meta, 'arz-dagitim') !== '0' ? pv_v7_ipo_get($meta, 'arz-dagitim') : '',
            'Halka Arz Büyüklüğü' => pv_v7_ipo_get($meta, 'arz-deger'),
            'Pay' => pv_v7_ipo_get($meta, 'arz-pay'),
            'Aracı Kurum' => pv_v7_ipo_get($meta, 'arz-araci'),
            'Fiili Dolaşımdaki Pay' => pv_v7_ipo_get($meta, 'arz-dolasimdaki-pay'),
            'Fiili Dolaşımdaki Pay Oranı' => pv_v7_ipo_get($meta, 'arz-dolasimdaki-pay-yuzde'),
            'BIST İlk İşlem Tarihi' => pv_v7_ipo_get($meta, 'bist-islem-tarihi'),
            'BIST Kodu' => pv_v7_ipo_get($meta, 'bist-kodu'),
            'Pazar' => pv_v7_ipo_terms_text($post_id, 'pazar'),
            'Endeks' => pv_v7_ipo_terms_text($post_id, 'endeks'),
        );
        if (!empty($meta['halka-arz-bilgiler']) && is_array($meta['halka-arz-bilgiler'])) {
            foreach ($meta['halka-arz-bilgiler'] as $item) {
                if (!empty($item['bilgi-baslik']) && isset($item['bilgi-aciklama'])) {
                    $rows[$item['bilgi-baslik']] = $item['bilgi-aciklama'];
                }
            }
        }
        return array_filter($rows, function($v){ return $v !== '' && $v !== null; });
    }
}

if (!function_exists('pv_v7_ipo_status_label')) {
    function pv_v7_ipo_status_label($post_id, $meta) {
        $terms = pv_v7_ipo_terms_text($post_id, 'durumlar');
        if ($terms) return $terms;
        $today = new DateTime('today');
        $start = function_exists('pv_v7_ipo_date_obj') ? pv_v7_ipo_date_obj(pv_v7_ipo_get($meta, 'halka-arz-tarihi-baslangic')) : null;
        $end = function_exists('pv_v7_ipo_date_obj') ? pv_v7_ipo_date_obj(pv_v7_ipo_get($meta, 'halka-arz-tarihi-bitis')) : null;
        if ($start && $end && $start <= $today && $end >= $today) return 'Talep Topluyor';
        if ($end && $end < $today) return 'Tamamlandı';
        return 'Takvimde';
    }
}

if (!function_exists('pv_v7_ipo_render_ad')) {
    function pv_v7_ipo_render_ad($class = 'pv-ipo-ad-wide') {
        echo '<div class="pv-ipo-ad ' . esc_attr($class) . '">';
        if (function_exists('pv_v7_gam_content_ad')) {
            pv_v7_gam_content_ad();
        } else {
            echo '<span>Reklam Alanı</span>';
        }
        echo '</div>';
    }
}

if (!function_exists('pv_v7_ipo_related_news')) {
    function pv_v7_ipo_related_news($post_id, $limit = 4) {
        $title = get_the_title($post_id);
        $code = pv_v7_ipo_get(pv_v7_ipo_safe_meta($post_id), 'bist-kodu');
        $q = new WP_Query(array(
            'post_type' => 'post',
            'posts_per_page' => $limit,
            'ignore_sticky_posts' => true,
            's' => $code ?: $title,
        ));
        if (!$q->have_posts()) {
            echo '<div class="pv-ipo-empty">Bu halka arzla ilişkili haber bulunamadı.</div>';
            return;
        }
        echo '<div class="pv-ipo-news-grid">';
        while ($q->have_posts()) { $q->the_post();
            $img = function_exists('pv_v7_img') ? pv_v7_img(get_the_ID(), 'medium') : get_the_post_thumbnail_url(get_the_ID(), 'medium');
            echo '<a class="pv-ipo-news-card" href="' . esc_url(get_permalink()) . '">';
            echo '<span class="pv-ipo-news-img"' . ($img ? ' style="background-image:url(' . esc_url($img) . ')"' : '') . '></span>';
            echo '<strong>' . esc_html(get_the_title()) . '</strong>';
            echo '</a>';
        }
        wp_reset_postdata();
        echo '</div>';
    }
}

/* === v2.46: Credit/loan pages body classes === */
add_filter('body_class', function($classes){
    $path = isset($_SERVER['REQUEST_URI']) ? trim((string) wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH), '/') : '';
    $credit_pages = array(
        'kredi' => 'pv-page-credit-home',
        'kredi-hesapla' => 'pv-page-credit-calc',
        'ihtiyac-kredisi' => 'pv-page-credit-need',
        'konut-kredisi' => 'pv-page-credit-home-loan',
        'tasit-kredisi' => 'pv-page-credit-auto-loan',
        'kobi-kredisi' => 'pv-page-credit-sme-loan',
        'faiz-oranlari' => 'pv-page-credit-rates',
        'kredi-faiz-oranlari' => 'pv-page-credit-rates',
        'mevduat-oranlari' => 'pv-page-credit-rates',
        'piyasanin-nabzi' => 'pv-page-credit-news',
        'trafik-sigortasi' => 'pv-page-credit-insurance',
        'kasko-sigortasi' => 'pv-page-credit-insurance',
    );
    $first = explode('/', $path)[0] ?? $path;
    if (isset($credit_pages[$path])) {
        $classes[] = 'pv-credit-theme';
        $classes[] = $credit_pages[$path];
    } elseif (isset($credit_pages[$first])) {
        $classes[] = 'pv-credit-theme';
        $classes[] = $credit_pages[$first];
    }
    return array_values(array_unique($classes));
}, 90);


/* === v2.52: corporate/static page body classes === */
function pv_v252_corporate_body_classes( $classes ) {
    $path = isset($_SERVER['REQUEST_URI']) ? trim((string) wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH), '/') : '';
    $corp_map = array(
        'hakkimizda' => 'pv-page-about',
        'kunye' => 'pv-page-masthead',
        'iletisim' => 'pv-page-contact',
        'bize-reklam-ver' => 'pv-page-advertise',
        'bize-reklam-verin' => 'pv-page-advertise',
        'reklam' => 'pv-page-advertise',
        'bulten' => 'pv-page-newsletter',
        'son-dakika' => 'pv-page-breaking-news',
        'canli-borsa' => 'pv-page-live-borsa',
        'aydinlatma-metni' => 'pv-page-legal',
        'kvkk' => 'pv-page-legal',
        'cerez-politikasi' => 'pv-page-legal',
        'kullanim-kosullari' => 'pv-page-legal',
        'sorumluluk-reddi' => 'pv-page-legal',
        'yasal-metinler' => 'pv-page-legal',
    );
    if (isset($corp_map[$path])) {
        $classes[] = 'pv-corporate-theme';
        $classes[] = $corp_map[$path];
    }
    if (is_404()) {
        $classes[] = 'pv-corporate-theme';
        $classes[] = 'pv-page-404';
    }
    if (is_search()) {
        $classes[] = 'pv-corporate-theme';
        $classes[] = 'pv-page-search-results';
    }
    return array_values(array_unique($classes));
}
add_filter('body_class', 'pv_v252_corporate_body_classes', 60);
