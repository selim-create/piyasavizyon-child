<?php
if (!defined('ABSPATH')) { exit; }

if (!function_exists('pv_v7_credit_urls')) {
    function pv_v7_credit_urls() {
        global $bp_options;
        $map = array(
            'ihtiyac' => !empty($bp_options['page_ihtiyackredisi']) ? $bp_options['page_ihtiyackredisi'] : 'ihtiyac-kredisi',
            'konut'   => !empty($bp_options['page_konutkredisi']) ? $bp_options['page_konutkredisi'] : 'konut-kredisi',
            'tasit'   => !empty($bp_options['page_tasitkredisi']) ? $bp_options['page_tasitkredisi'] : 'tasit-kredisi',
            'kobi'    => !empty($bp_options['page_kobikredisi']) ? $bp_options['page_kobikredisi'] : 'kobi-kredisi',
        );
        $out = array();
        foreach ($map as $key => $slug) {
            $out[$key] = home_url('/' . trim($slug, '/') . '/');
        }
        $out['kredi'] = home_url('/kredi/');
        $out['faiz'] = home_url('/faiz-oranlari/');
        $out['mevduat'] = home_url('/mevduat-oranlari/');
        $out['hesapla'] = home_url('/kredi-hesapla/');
        $out['nabiz'] = home_url('/piyasanin-nabzi/');
        $out['trafik'] = home_url('/trafik-sigortasi/');
        $out['kasko'] = home_url('/kasko-sigortasi/');
        return $out;
    }
}

if (!function_exists('pv_v7_credit_type_label')) {
    function pv_v7_credit_type_label($type) {
        $labels = array(
            'ihtiyac' => 'İhtiyaç Kredisi',
            'konut' => 'Konut Kredisi',
            'tasit' => 'Taşıt Kredisi',
            'kobi' => 'KOBİ Kredisi',
        );
        return isset($labels[$type]) ? $labels[$type] : 'Kredi';
    }
}

if (!function_exists('pv_v7_credit_type_desc')) {
    function pv_v7_credit_type_desc($type) {
        $desc = array(
            'ihtiyac' => 'Nakit ihtiyaçlarınız için bankaların güncel faiz oranlarını, aylık taksitleri ve toplam geri ödeme tutarlarını karşılaştırın.',
            'konut' => 'Ev alımı için uzun vadeli konut kredisi seçeneklerini, ödeme planlarını ve bankaların güncel kampanyalarını tek ekranda inceleyin.',
            'tasit' => 'Araç finansmanı için taşıt kredisi oranlarını, aylık taksitleri ve toplam maliyeti karşılaştırın.',
            'kobi' => 'İşletmeniz için KOBİ kredisi alternatiflerini, faiz oranlarını ve toplam ödeme maliyetlerini görüntüleyin.',
        );
        return isset($desc[$type]) ? $desc[$type] : 'Güncel kredi oranlarını karşılaştırın.';
    }
}

if (!function_exists('pv_v7_credit_defaults')) {
    function pv_v7_credit_defaults($type) {
        $defaults = array(
            'ihtiyac' => array('amount' => 100000, 'term' => 12),
            'konut' => array('amount' => 1000000, 'term' => 120),
            'tasit' => array('amount' => 500000, 'term' => 36),
            'kobi' => array('amount' => 250000, 'term' => 24),
        );
        return isset($defaults[$type]) ? $defaults[$type] : array('amount' => 100000, 'term' => 12);
    }
}

if (!function_exists('pv_v7_credit_clean_int')) {
    function pv_v7_credit_clean_int($value, $default = 0) {
        if (is_array($value)) { return $default; }
        $value = preg_replace('/[^0-9]/', '', (string) $value);
        return $value !== '' ? max(0, (int) $value) : (int) $default;
    }
}

if (!function_exists('pv_v7_credit_money')) {
    function pv_v7_credit_money($value, $suffix = ' TL') {
        if ($value === '' || $value === null) { return '-'; }
        if (is_numeric($value)) { return number_format((float) $value, 2, ',', '.') . $suffix; }
        $value = trim(wp_strip_all_tags((string) $value));
        if ($value === '') { return '-'; }
        return preg_match('/TL|₺/iu', $value) ? $value : $value . $suffix;
    }
}

if (!function_exists('pv_v7_credit_route_params')) {
    function pv_v7_credit_route_params($type) {
        global $wp, $bp_options;
        $defaults = pv_v7_credit_defaults($type);
        $amount = isset($_GET['tutar']) ? pv_v7_credit_clean_int(wp_unslash($_GET['tutar']), $defaults['amount']) : 0;
        $term = isset($_GET['vade']) ? pv_v7_credit_clean_int(wp_unslash($_GET['vade']), $defaults['term']) : 0;

        if (!$amount || !$term) {
            $request = '';
            if (isset($wp) && !empty($wp->request)) {
                $request = (string) $wp->request;
            } else {
                $request = isset($_SERVER['REQUEST_URI']) ? trim((string) wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH), '/') : '';
            }
            $last = basename($request);
            if (preg_match('/(\d+)-ay-(\d+)-tl-kredi/i', $last, $m)) {
                $term = (int) $m[1];
                $amount = (int) $m[2];
            }
        }
        if (!$amount) { $amount = $defaults['amount']; }
        if (!$term) { $term = $defaults['term']; }

        $_GET['type'] = $type;
        $_GET['vade'] = $term;
        $_GET['tutar'] = $amount;

        return array('type' => $type, 'amount' => $amount, 'term' => $term);
    }
}

if (!function_exists('pv_v7_credit_redirect_pretty_if_needed')) {
    function pv_v7_credit_redirect_pretty_if_needed($type) {
        global $bp_options;
        if (empty($_GET['type']) || empty($_GET['vade']) || empty($_GET['tutar'])) { return; }
        $urls = pv_v7_credit_urls();
        $amount = pv_v7_credit_clean_int(wp_unslash($_GET['tutar']), 0);
        $term = pv_v7_credit_clean_int(wp_unslash($_GET['vade']), 0);
        if (!$amount || !$term || empty($urls[$type])) { return; }
        $pattern = !empty($bp_options['krediSorgulamaRewrite']) ? $bp_options['krediSorgulamaRewrite'] : '{ay}-ay-{tutar}-tl-kredi';
        $slug = str_replace(array('{ay}', '{vade}', '{tutar}', '{kredi}'), array($term, $term, $amount, sanitize_title(pv_v7_credit_type_label($type))), $pattern);
        if (function_exists('permalink_bf')) {
            $slug = permalink_bf($slug);
        } else {
            $slug = sanitize_title($slug);
        }
        wp_safe_redirect(trailingslashit($urls[$type]) . $slug . '/');
        exit;
    }
}

if (!function_exists('pv_v7_credit_normalize_number')) {
    function pv_v7_credit_normalize_number($value) {
        if (is_numeric($value)) { return (float) $value; }
        $value = trim(wp_strip_all_tags((string) $value));
        if ($value === '') { return 0; }
        $value = str_replace(array('TL','₺','%',' '), '', $value);
        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        return is_numeric($value) ? (float) $value : 0;
    }
}

if (!function_exists('pv_v7_credit_monthly_payment')) {
    function pv_v7_credit_monthly_payment($amount, $term, $rate) {
        $amount = max(0, (float) $amount);
        $term = max(1, (int) $term);
        $monthly = max(0, (float) $rate) / 100;
        if ($amount <= 0) { return 0; }
        if ($monthly <= 0) { return $amount / $term; }
        return $amount * ($monthly * pow(1 + $monthly, $term)) / (pow(1 + $monthly, $term) - 1);
    }
}

if (!function_exists('pv_v7_credit_fallback_offers')) {
    function pv_v7_credit_fallback_offers($type, $amount, $term) {
        $amount = max(1, (float) $amount);
        $term = max(1, (int) $term);
        $rates = array(
            'ihtiyac' => array(
                'Akbank' => 3.69, 'Garanti BBVA' => 3.79, 'QNB Finansbank' => 3.84, 'Yapı Kredi' => 3.89,
                'ING' => 3.94, 'TEB' => 3.99, 'DenizBank' => 4.05, 'Türkiye İş Bankası' => 4.09,
                'VakıfBank' => 4.19, 'Ziraat Bankası' => 4.24, 'Halkbank' => 4.29, 'Fibabanka' => 4.34,
            ),
            'konut' => array(
                'Ziraat Bankası' => 2.79, 'VakıfBank' => 2.84, 'Halkbank' => 2.89, 'Türkiye İş Bankası' => 2.94,
                'Garanti BBVA' => 3.05, 'Yapı Kredi' => 3.09, 'Akbank' => 3.14, 'QNB Finansbank' => 3.19,
                'Kuveyt Türk' => 3.24, 'Albaraka Türk' => 3.29, 'Türkiye Finans' => 3.34, 'DenizBank' => 3.39,
            ),
            'tasit' => array(
                'Garanti BBVA' => 3.39, 'Yapı Kredi' => 3.44, 'Akbank' => 3.49, 'Türkiye İş Bankası' => 3.54,
                'QNB Finansbank' => 3.59, 'TEB' => 3.64, 'DenizBank' => 3.69, 'VakıfBank' => 3.74,
                'Ziraat Bankası' => 3.79, 'Halkbank' => 3.84, 'Albaraka Türk' => 3.89, 'Kuveyt Türk' => 3.94,
            ),
            'kobi' => array(
                'Akbank' => 3.89, 'Garanti BBVA' => 3.94, 'Türkiye İş Bankası' => 3.99, 'Yapı Kredi' => 4.04,
                'QNB Finansbank' => 4.09, 'DenizBank' => 4.14, 'VakıfBank' => 4.19, 'Ziraat Bankası' => 4.24,
                'Halkbank' => 4.29, 'TEB' => 4.34, 'Kuveyt Türk' => 4.39, 'Türkiye Finans' => 4.44,
            ),
        );
        $selected = isset($rates[$type]) ? $rates[$type] : $rates['ihtiyac'];
        $out = array('banka'=>array(), 'kredi'=>array(), 'faiz'=>array(), 'tahsis_ucreti'=>array(), 'aylik_taksit'=>array(), 'toplam_odeme'=>array(), 'id'=>array(), '_fallback'=>true);
        $i = 0;
        foreach ($selected as $bank => $rate) {
            $payment = pv_v7_credit_monthly_payment($amount, $term, $rate);
            $fee = min(max($amount * 0.005, 250), 25000);
            $total = ($payment * $term) + $fee;
            $out['banka'][$i] = $bank;
            $out['kredi'][$i] = pv_v7_credit_type_label($type) . ' Paketi';
            $out['faiz'][$i] = number_format($rate, 2, ',', '.');
            $out['tahsis_ucreti'][$i] = number_format($fee, 2, ',', '.');
            $out['aylik_taksit'][$i] = number_format($payment, 2, ',', '.');
            $out['toplam_odeme'][$i] = number_format($total, 2, ',', '.');
            $out['id'][$i] = sanitize_title($bank);
            $i++;
        }
        return $out;
    }
}

if (!function_exists('pv_v7_credit_fetch_offers')) {
    function pv_v7_credit_fetch_offers($type, $amount, $term) {
        $data = array();
        $file = get_template_directory() . '/api/kredi.php';
        if (file_exists($file)) { require_once $file; }
        if (function_exists('kredi_data')) {
            ob_start();
            try {
                $candidate = kredi_data($type, $amount, $term);
                if (is_array($candidate)) { $data = $candidate; }
            } catch (Throwable $e) {
                $data = array();
            }
            ob_end_clean();
        }
        if (empty($data['banka']) || !is_array($data['banka'])) {
            return pv_v7_credit_fallback_offers($type, $amount, $term);
        }
        return $data;
    }
}

if (!function_exists('pv_v7_credit_bank_slug')) {
    function pv_v7_credit_bank_slug($bank) {
        $slug = function_exists('permalink') ? permalink($bank) : sanitize_title($bank);
        if (function_exists('replaceBanka')) { $slug = replaceBanka($slug); }
        $slug = str_replace(array('turkiye-is-bankasi','yapi-kredi-bankasi','ing-bank','teb','finansbank'), array('is-bankasi','yapi-kredi','ing','cepteteb','qnb-finansbank'), $slug);
        return sanitize_title($slug);
    }
}

if (!function_exists('pv_v7_credit_bank_logo')) {
    function pv_v7_credit_bank_logo($bank) {
        $slug = pv_v7_credit_bank_slug($bank);
        $dir = get_template_directory() . '/img/banka/';
        $uri = get_template_directory_uri() . '/img/banka/';
        $candidates = array($slug . '.svg', $slug . '.png', str_replace('-bankasi', '', $slug) . '.svg', str_replace('-bankasi', '', $slug) . '.png');
        foreach ($candidates as $file) {
            if (file_exists($dir . $file)) { return $uri . $file; }
        }
        return '';
    }
}

if (!function_exists('pv_v7_credit_render_form')) {
    function pv_v7_credit_render_form($active = 'ihtiyac', $class = '') {
        $urls = pv_v7_credit_urls();
        $types = array('ihtiyac', 'konut', 'tasit', 'kobi');
        echo '<div class="pv-credit-calculator ' . esc_attr($class) . '" data-pv-credit-calculator>';
        echo '<div class="pv-credit-tabs" role="tablist">';
        foreach ($types as $type) {
            echo '<button type="button" class="pv-credit-tab ' . ($active === $type ? 'active' : '') . '" data-pv-credit-tab="' . esc_attr($type) . '">' . esc_html(pv_v7_credit_type_label($type)) . '</button>';
        }
        echo '</div>';
        foreach ($types as $type) {
            $defaults = pv_v7_credit_defaults($type);
            $terms = $type === 'konut' ? array(12,24,36,48,60,72,84,96,108,120) : array(3,6,9,12,18,24,30,36,42,48,60);
            echo '<form class="pv-credit-form-panel ' . ($active === $type ? 'active' : '') . '" data-pv-credit-panel="' . esc_attr($type) . '" action="' . esc_url($urls[$type]) . '" method="get">';
            echo '<input type="hidden" name="type" value="' . esc_attr($type) . '">';
            echo '<label><span>Kredi Tutarı</span><input type="text" inputmode="numeric" name="tutar" class="pv-credit-number" value="' . esc_attr(number_format($defaults['amount'], 0, ',', '.')) . '" placeholder="Kredi Tutarı"></label>';
            echo '<label><span>Vade</span><select name="vade">';
            foreach ($terms as $term) {
                echo '<option value="' . esc_attr($term) . '" ' . selected($term, $defaults['term'], false) . '>' . esc_html($term . ' Ay') . '</option>';
            }
            echo '</select></label>';
            echo '<button type="submit">En Uygun Krediyi Bul</button>';
            echo '</form>';
        }
        echo '</div>';
    }
}

if (!function_exists('pv_v7_credit_render_tools_grid')) {
    function pv_v7_credit_render_tools_grid() {
        $u = pv_v7_credit_urls();
        $items = array(
            array('Kredi Faiz Oranları', 'Bankaların güncel kredi kampanyalarını ve maliyetlerini karşılaştırın.', $u['faiz'], 'fa-solid fa-percent'),
            array('Mevduat Oranları', 'TL, USD ve EUR mevduat oranlarını dönemlere göre inceleyin.', $u['mevduat'], 'fa-solid fa-piggy-bank'),
            array('Kredi Hesapla', 'Tutar ve vadeye göre ihtiyaç, konut, taşıt ve KOBİ kredisi hesaplayın.', $u['hesapla'], 'fa-solid fa-calculator'),
            array('Piyasanın Nabzı', 'Kredi, faiz ve ekonomi gündemindeki son gelişmeleri takip edin.', $u['nabiz'], 'fa-solid fa-chart-line'),
            array('Trafik Sigortası', 'Zorunlu trafik sigortası primlerini etkileyen başlıkları ve poliçe kapsamlarını inceleyin.', $u['trafik'], 'fa-solid fa-car-side'),
            array('Kasko Sigortası', 'Araç kasko kapsamları, teminat başlıkları ve maliyet kalemlerini inceleyin.', $u['kasko'], 'fa-solid fa-shield-halved'),
        );
        echo '<section class="pv-credit-section"><div class="pv-credit-section-head"><span>Finans araçları</span><h2>Popüler hesaplamalar ve karşılaştırmalar</h2></div><div class="pv-credit-tools-grid">';
        foreach ($items as $item) {
            echo '<a class="pv-credit-tool-card" href="' . esc_url($item[2]) . '"><i class="' . esc_attr($item[3]) . '"></i><strong>' . esc_html($item[0]) . '</strong><p>' . esc_html($item[1]) . '</p><em>İncele</em></a>';
        }
        echo '</div></section>';
    }
}

if (!function_exists('pv_v7_credit_render_type_cards')) {
    function pv_v7_credit_render_type_cards() {
        $u = pv_v7_credit_urls();
        $types = array('ihtiyac', 'konut', 'tasit', 'kobi');
        echo '<section class="pv-credit-section"><div class="pv-credit-section-head"><span>Kredi türleri</span><h2>İhtiyacınıza göre kredi seçin</h2></div><div class="pv-credit-type-grid">';
        foreach ($types as $type) {
            $d = pv_v7_credit_defaults($type);
            $query = add_query_arg(array('type'=>$type, 'tutar'=>$d['amount'], 'vade'=>$d['term']), $u[$type]);
            echo '<a class="pv-credit-type-card pv-credit-type-' . esc_attr($type) . '" href="' . esc_url($query) . '">';
            echo '<span>' . esc_html($d['term']) . ' ay / ' . esc_html(number_format($d['amount'], 0, ',', '.')) . ' TL</span>';
            echo '<h3>' . esc_html(pv_v7_credit_type_label($type)) . '</h3>';
            echo '<p>' . esc_html(pv_v7_credit_type_desc($type)) . '</p>';
            echo '<strong>Hesapla →</strong>';
            echo '</a>';
        }
        echo '</div></section>';
    }
}

if (!function_exists('pv_v7_credit_render_offer')) {
    function pv_v7_credit_render_offer($data, $key, $index, $amount, $term, $type) {
        $bank = isset($data['banka'][$key]) ? wp_strip_all_tags((string) $data['banka'][$key]) : 'Banka';
        $loan = isset($data['kredi'][$key]) ? wp_strip_all_tags((string) $data['kredi'][$key]) : pv_v7_credit_type_label($type);
        $interest = isset($data['faiz'][$key]) ? trim(wp_strip_all_tags((string) $data['faiz'][$key])) : '-';
        $installment = isset($data['aylik_taksit'][$key]) ? $data['aylik_taksit'][$key] : '-';
        $total = isset($data['toplam_odeme'][$key]) ? $data['toplam_odeme'][$key] : '-';
        $fee = isset($data['tahsis_ucreti'][$key]) ? $data['tahsis_ucreti'][$key] : '-';
        $logo = pv_v7_credit_bank_logo($bank);
        echo '<article class="pv-credit-offer ' . ($index === 0 ? 'is-featured' : '') . '">';
        if ($index === 0) { echo '<div class="pv-credit-offer-badge">Ayın avantajlı ürünü</div>'; }
        echo '<div class="pv-credit-bank-cell">';
        if ($logo) {
            echo '<img src="' . esc_url($logo) . '" alt="' . esc_attr($bank) . '" loading="lazy">';
        } else {
            echo '<span class="pv-credit-bank-fallback">' . esc_html(mb_substr($bank, 0, 2, 'UTF-8')) . '</span>';
        }
        echo '<div><strong>' . esc_html($bank) . '</strong><small>' . esc_html($loan) . '</small></div></div>';
        echo '<div class="pv-credit-metric"><span>Faiz Oranı</span><strong>%' . esc_html($interest) . '</strong></div>';
        echo '<div class="pv-credit-metric"><span>Aylık Taksit</span><strong>' . esc_html(pv_v7_credit_money($installment)) . '</strong></div>';
        echo '<div class="pv-credit-metric"><span>Toplam Ödeme</span><strong>' . esc_html(pv_v7_credit_money($total)) . '</strong><small>Tahsis: ' . esc_html(pv_v7_credit_money($fee)) . '</small></div>';
        $urls = pv_v7_credit_urls();
        echo '<div class="pv-credit-actions"><a href="' . esc_url(add_query_arg(array('type'=>$type,'tutar'=>$amount,'vade'=>$term), $urls[$type])) . '">Hesabı Güncelle</a></div>';
        echo '</article>';
    }
}

if (!function_exists('pv_v7_credit_render_offers')) {
    function pv_v7_credit_render_offers($data, $amount, $term, $type) {
        echo '<section class="pv-credit-results-card">';
        echo '<div class="pv-credit-results-head"><div><span>Banka karşılaştırması</span><h2>Banka teklifleri</h2></div><p>Faiz oranları bankaya, kredi notuna ve kampanya koşullarına göre değişebilir.</p></div>';
        if (!empty($data['_fallback'])) {
            echo '<div class="pv-credit-info-note"><strong>Temsili hesaplama</strong><span>Oranlar bilgilendirme amaçlıdır; başvuru öncesi bankanın güncel koşulları esas alınır.</span></div>';
        }
        if (empty($data['banka']) || !is_array($data['banka'])) {
            echo '<div class="pv-credit-empty"><strong>Sonuç listesi şu anda yüklenemedi.</strong><p>Farklı tutar ya da vade ile tekrar deneyebilirsiniz.</p></div>';
        } else {
            $list = isset($data['faiz']) && is_array($data['faiz']) ? $data['faiz'] : array_keys($data['banka']);
            asort($list);
            $i = 0;
            echo '<div class="pv-credit-offers-list">';
            foreach ($list as $key => $rate) {
                pv_v7_credit_render_offer($data, $key, $i, $amount, $term, $type);
                $i++;
            }
            echo '</div>';
        }
        echo '</section>';
    }
}

if (!function_exists('pv_v7_credit_render_popular')) {
    function pv_v7_credit_render_popular() {
        $u = pv_v7_credit_urls();
        $items = array(
            array('50.000 TL 12 Ay İhtiyaç Kredisi', 'ihtiyac', 50000, 12),
            array('100.000 TL 24 Ay İhtiyaç Kredisi', 'ihtiyac', 100000, 24),
            array('250.000 TL 36 Ay Taşıt Kredisi', 'tasit', 250000, 36),
            array('1.000.000 TL 120 Ay Konut Kredisi', 'konut', 1000000, 120),
            array('250.000 TL 24 Ay KOBİ Kredisi', 'kobi', 250000, 24),
            array('500.000 TL 36 Ay KOBİ Kredisi', 'kobi', 500000, 36),
        );
        echo '<section class="pv-credit-section"><div class="pv-credit-section-head"><span>Hızlı erişim</span><h2>Popüler hesaplamalar</h2></div><div class="pv-credit-popular-grid">';
        foreach ($items as $item) {
            echo '<a href="' . esc_url(add_query_arg(array('type'=>$item[1], 'tutar'=>$item[2], 'vade'=>$item[3]), $u[$item[1]])) . '"><strong>' . esc_html($item[0]) . '</strong><span>Hesapla →</span></a>';
        }
        echo '</div></section>';
    }
}

if (!function_exists('pv_v7_credit_render_ad')) {
    function pv_v7_credit_render_ad($class = 'pv-credit-ad-wide') {
        echo '<div class="pv-credit-ad ' . esc_attr($class) . '">';
        if (function_exists('pv_v7_gam_content_ad')) { pv_v7_gam_content_ad(); }
        else { echo '<span>Reklam Alanı</span>'; }
        echo '</div>';
    }
}

if (!function_exists('pv_v7_credit_post_image')) {
    function pv_v7_credit_post_image($post_id, $size = 'medium_large') {
        $img = '';

        // 1) WordPress öne çıkan görsel.
        if (has_post_thumbnail($post_id)) {
            $img = get_the_post_thumbnail_url($post_id, $size);
        }

        // 2) Temanın ortak helper'ı.
        if (!$img && function_exists('pv_v7_img')) {
            $img = pv_v7_img($post_id, $size);
        }

        // 3) Sık kullanılan özel alan / SEO görsel alanları.
        if (!$img) {
            $meta_keys = array('_thumbnail_ext_url', 'thumbnail_url', 'image', 'resim', 'haber_gorseli', '_yoast_wpseo_opengraph-image');
            foreach ($meta_keys as $key) {
                $candidate = get_post_meta($post_id, $key, true);
                if (is_array($candidate)) {
                    $candidate = $candidate['url'] ?? $candidate['sizes'][$size] ?? reset($candidate);
                }
                if (is_numeric($candidate)) {
                    $candidate = wp_get_attachment_image_url((int) $candidate, $size);
                }
                if (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_URL)) {
                    $img = $candidate;
                    break;
                }
            }
        }

        // 4) İçerikteki ilk görsel. Lazyload kullanan temalarda src yerine data-src gelebilir.
        if (!$img) {
            $content = get_post_field('post_content', $post_id);
            if ($content && preg_match('/<img[^>]+(?:src|data-src|data-lazy-src|data-original)=["\']([^"\']+)["\']/i', $content, $m)) {
                $img = html_entity_decode($m[1]);
            }
        }

        return esc_url_raw($img);
    }
}


if (!function_exists('pv_v7_credit_render_news')) {
    function pv_v7_credit_render_news($limit = 6) {
        $limit = max(3, (int) $limit);
        $posts = array();
        $queries = array(
            array('s'=>'kredi OR faiz OR ekonomi', 'posts_per_page'=>$limit),
            array('category_name'=>'ekonomi,finans,kredi', 'posts_per_page'=>$limit),
            array('posts_per_page'=>$limit),
        );
        foreach ($queries as $args) {
            if (count($posts) >= $limit) { break; }
            $args = array_merge(array('post_type'=>'post', 'ignore_sticky_posts'=>true, 'post_status'=>'publish'), $args);
            $q = new WP_Query($args);
            if ($q->have_posts()) {
                while ($q->have_posts()) { $q->the_post();
                    $id = get_the_ID();
                    if (!isset($posts[$id])) { $posts[$id] = $id; }
                    if (count($posts) >= $limit) { break; }
                }
                wp_reset_postdata();
            }
        }
        echo '<section class="pv-credit-section"><div class="pv-credit-section-head"><span>Piyasanın Nabzı</span><h2>Finans gündeminden öne çıkanlar</h2></div>';
        if (empty($posts)) {
            echo '<div class="pv-credit-empty"><p>Şu anda gösterilecek haber bulunamadı.</p></div></section>';
            return;
        }
        echo '<div class="pv-credit-news-grid">';
        foreach (array_slice($posts, 0, $limit) as $post_id) {
            $img = pv_v7_credit_post_image($post_id, 'medium_large');
            echo '<a class="pv-credit-news-card" href="' . esc_url(get_permalink($post_id)) . '">';
            echo '<span class="pv-credit-news-img">';
            if ($img) {
                echo '<img src="' . esc_url($img) . '" alt="' . esc_attr(get_the_title($post_id)) . '" loading="lazy" decoding="async">';
            } else {
                echo '<em>' . esc_html(mb_substr(get_the_title($post_id), 0, 1, 'UTF-8')) . '</em>';
            }
            echo '</span>';
            echo '<strong>' . esc_html(get_the_title($post_id)) . '</strong>';
            echo '</a>';
        }
        echo '</div></section>';
    }
}

if (!function_exists('pv_v7_credit_render_estimator')) {
    function pv_v7_credit_render_estimator() {
        echo '<section class="pv-credit-estimator" data-pv-credit-estimator>';
        echo '<div><span>Hızlı simülasyon</span><h2>Kredi hesaplama aracı</h2><p>Tutar, vade ve aylık faiz oranına göre yaklaşık aylık taksit hesaplayın.</p></div>';
        echo '<div class="pv-credit-estimator-form"><label><span>Tutar</span><input type="text" class="pv-est-amount pv-credit-number" value="100.000"></label><label><span>Vade</span><input type="number" class="pv-est-term" value="12" min="1"></label><label><span>Aylık faiz %</span><input type="number" class="pv-est-rate" value="3.25" step="0.01" min="0"></label><button type="button" class="pv-est-button">Hesapla</button><strong class="pv-est-result">-</strong></div>';
        echo '</section>';
    }
}

if (!function_exists('pv_v7_credit_render_advanced_calculator')) {
    function pv_v7_credit_render_advanced_calculator() {
        $u = pv_v7_credit_urls();
        $types = array('ihtiyac', 'konut', 'tasit', 'kobi');
        echo '<section class="pv-credit-advanced" data-pv-credit-advanced>';
        echo '<div class="pv-credit-advanced-copy"><span>Kredi Hesapla</span><h2>Gelişmiş kredi simülasyonu</h2><p>Tutar, vade, faiz, masraf ve vergi alanlarını değiştirerek aylık taksit, toplam geri ödeme ve toplam faiz maliyetini anında hesaplayın.</p></div>';
        echo '<div class="pv-credit-advanced-box">';
        echo '<div class="pv-credit-tabs pv-credit-advanced-tabs" role="tablist">';
        foreach ($types as $type) {
            echo '<button type="button" class="pv-credit-tab ' . ($type === 'ihtiyac' ? 'active' : '') . '" data-pv-adv-type="' . esc_attr($type) . '" data-action="' . esc_url($u[$type]) . '">' . esc_html(pv_v7_credit_type_label($type)) . '</button>';
        }
        echo '</div>';
        echo '<form class="pv-credit-advanced-form" method="get" action="' . esc_url($u['ihtiyac']) . '">';
        echo '<input type="hidden" name="type" value="ihtiyac" class="pv-adv-type-input">';
        echo '<label><span>Kredi Tutarı</span><input type="text" name="tutar" class="pv-credit-number pv-adv-amount" value="100.000"></label>';
        echo '<label><span>Vade</span><input type="number" name="vade" class="pv-adv-term" value="12" min="1" max="240"></label>';
        echo '<label><span>Aylık Faiz %</span><input type="number" class="pv-adv-rate" value="3.25" step="0.01" min="0"></label>';
        echo '<label><span>Tahsis / Masraf</span><input type="text" class="pv-credit-number pv-adv-fee" value="500"></label>';
        echo '<label><span>Vergiler</span><select class="pv-adv-tax"><option value="0">Yok</option><option value="0.15">BSMV/KKDF dahil simülasyon</option></select></label>';
        echo '<button type="submit">Sonuçları Listele</button>';
        echo '</form>';
        echo '<div class="pv-credit-advanced-results"><div><span>Aylık Taksit</span><strong class="pv-adv-monthly">-</strong></div><div><span>Toplam Geri Ödeme</span><strong class="pv-adv-total">-</strong></div><div><span>Toplam Faiz/Maliyet</span><strong class="pv-adv-cost">-</strong></div></div>';
        echo '</div></section>';
    }
}
