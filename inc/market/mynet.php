<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Fetch an HTML page from the public Mynet Finans market section.
 */
function pv_market_fetch_mynet( $path ) {
    $path = '/' . ltrim( (string) $path, '/' );
    $url  = 'https://finans.mynet.com' . $path;

    $response = wp_safe_remote_get(
        $url,
        array(
            'timeout'     => 25,
            'redirection' => 5,
            'headers'     => array(
                'Accept'     => 'text/html,application/xhtml+xml',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0 Safari/537.36',
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        return '';
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    if ( $status < 200 || $status >= 300 ) {
        return '';
    }

    $body = wp_remote_retrieve_body( $response );
    return is_string( $body ) ? $body : '';
}

function pv_market_normalize_label( $text ) {
    $text = trim( wp_strip_all_tags( (string) $text ) );
    $text = remove_accents( mb_strtolower( $text, 'UTF-8' ) );
    $text = preg_replace( '/\s+/', ' ', $text );
    return trim( (string) $text );
}

function pv_market_find_header_index( $headers, $needles ) {
    foreach ( $headers as $index => $header ) {
        $normalized = pv_market_normalize_label( $header );
        foreach ( $needles as $needle ) {
            if ( strpos( $normalized, $needle ) !== false ) {
                return $index;
            }
        }
    }
    return null;
}

/**
 * Parse the current Mynet table without depending on exact class ordering.
 */
function pv_market_parse_mynet_table( $html, $link_fragment, $type ) {
    $rows = array();
    if ( ! is_string( $html ) || trim( $html ) === '' || ! class_exists( 'DOMDocument' ) ) {
        return $rows;
    }

    $previous = libxml_use_internal_errors( true );
    $dom      = new DOMDocument();
    $loaded   = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous );

    if ( ! $loaded ) {
        return $rows;
    }

    $xpath  = new DOMXPath( $dom );
    $tables = $xpath->query( '//table[contains(concat(" ", normalize-space(@class), " "), " table-data ")]' );

    if ( ! $tables ) {
        return $rows;
    }

    foreach ( $tables as $table ) {
        $headers = array();
        foreach ( $xpath->query( './/tr[1]/*[self::th or self::td]', $table ) as $cell ) {
            $headers[] = trim( preg_replace( '/\s+/', ' ', $cell->textContent ) );
        }

        $name_index   = pv_market_find_header_index( $headers, array( 'endeks', 'hisse', 'hisseler' ) );
        $last_index   = pv_market_find_header_index( $headers, array( 'son', 'fiyat' ) );
        $change_index = pv_market_find_header_index( $headers, array( '% fark', 'degisim yuzde', 'degisim', 'fark%' ) );
        $volume_index = pv_market_find_header_index( $headers, array( 'hacim' ) );
        $time_index   = pv_market_find_header_index( $headers, array( 'saat', 'guncelleme' ) );

        foreach ( $xpath->query( './/tr[position() > 1]', $table ) as $tr ) {
            $link = $xpath->query( './/a[contains(@href, "' . $link_fragment . '")]', $tr )->item( 0 );
            if ( ! $link instanceof DOMElement ) {
                continue;
            }

            $cells = array();
            foreach ( $xpath->query( './td', $tr ) as $cell ) {
                $cells[] = trim( preg_replace( '/\s+/', ' ', $cell->textContent ) );
            }
            if ( $cells === array() ) {
                continue;
            }

            $href = (string) $link->getAttribute( 'href' );
            if ( strpos( $href, 'https://finans.mynet.com' ) === 0 ) {
                $href = substr( $href, strlen( 'https://finans.mynet.com' ) );
            }

            $slug = trim( str_replace( $link_fragment, '', $href ), '/' );
            if ( $slug === '' ) {
                continue;
            }

            $name = trim( preg_replace( '/\s+/', ' ', $link->textContent ) );
            if ( $name === '' && $name_index !== null && isset( $cells[ $name_index ] ) ) {
                $name = $cells[ $name_index ];
            }

            $last = ( $last_index !== null && isset( $cells[ $last_index ] ) ) ? $cells[ $last_index ] : '';
            $change = ( $change_index !== null && isset( $cells[ $change_index ] ) ) ? $cells[ $change_index ] : '';
            $volume = ( $volume_index !== null && isset( $cells[ $volume_index ] ) ) ? $cells[ $volume_index ] : '';
            $time = ( $time_index !== null && isset( $cells[ $time_index ] ) ) ? $cells[ $time_index ] : '';

            $direction = 'decrease';
            $numeric_change = (float) str_replace( array( '.', ',', '%' ), array( '', '.', '' ), $change );
            if ( $numeric_change > 0 ) {
                $direction = 'increase';
            }

            $rows[] = array(
                'type'      => $type,
                'slug'      => $slug,
                'name'      => $name,
                'last'      => $last,
                'change'    => $change,
                'volume'    => $volume,
                'time'      => $time,
                'direction' => $direction,
            );
        }

        if ( $rows !== array() ) {
            break;
        }
    }

    return $rows;
}

function pv_market_mynet_indices() {
    return pv_market_parse_mynet_table(
        pv_market_fetch_mynet( '/borsa/endeks/' ),
        '/borsa/endeks/',
        'index'
    );
}

function pv_market_mynet_stocks() {
    return pv_market_parse_mynet_table(
        pv_market_fetch_mynet( '/borsa/hisseler/' ),
        '/borsa/hisseler/',
        'stock'
    );
}

function pv_market_mynet_bist100_snapshot() {
    $html = pv_market_fetch_mynet( '/borsa/' );
    if ( $html === '' ) {
        return array();
    }

    $snapshot = array(
        'value'  => '',
        'change' => '',
        'update' => '',
    );

    if ( preg_match( '@<span[^>]*class="[^"]*dynamic-price-XU100[^"]*"[^>]*>(.*?)</span>@si', $html, $match ) ) {
        $snapshot['value'] = trim( wp_strip_all_tags( $match[1] ) );
    }

    if ( preg_match( '@<span[^>]*class="[^"]*dynamic-direction-XU100[^"]*"[^>]*>(.*?)</span>@si', $html, $match ) ) {
        $snapshot['change'] = trim( wp_strip_all_tags( $match[1] ) );
    }

    if ( preg_match( '@<span[^>]*class="[^"]*dynamic-last-updated-date-XU100[^"]*"[^>]*>(.*?)</span>@si', $html, $match ) ) {
        $snapshot['update'] = trim( wp_strip_all_tags( $match[1] ) );
    }

    return array_filter( $snapshot, static function( $value ) {
        return $value !== '';
    } );
}

function pv_market_bist100_ajax() {
    $snapshot = pv_market_mynet_bist100_snapshot();
    if ( empty( $snapshot['value'] ) ) {
        wp_send_json_error( array( 'message' => 'BIST 100 verisi alınamadı.' ), 503 );
    }

    wp_send_json_success( $snapshot );
}
add_action( 'wp_ajax_pv_bist100_snapshot', 'pv_market_bist100_ajax' );
add_action( 'wp_ajax_nopriv_pv_bist100_snapshot', 'pv_market_bist100_ajax' );

/**
 * Keep the legacy page assignment but render child-owned repaired views.
 */
function pv_market_repair_market_templates( $template ) {
    $base = basename( (string) $template );

    if ( $base === 'endeksler-tablo.php' ) {
        return __DIR__ . '/views/endeksler-tablo.php';
    }

    if ( $base === 'hisse-tablo.php' ) {
        return __DIR__ . '/views/hisse-tablo.php';
    }

    return $template;
}
add_filter( 'template_include', 'pv_market_repair_market_templates', 99 );

/**
 * Repair only the stale BIST 100 summary parser on the legacy borsa page.
 * The chart and BIST 50/30 tabs remain untouched in this PR.
 */
function pv_market_bist100_footer_patch() {
    if ( ! is_page_template( 'borsa-page.php' ) ) {
        return;
    }
    ?>
    <script>
    (function(){
        var url = <?php echo wp_json_encode( admin_url( 'admin-ajax.php?action=pv_bist100_snapshot' ) ); ?>;
        fetch(url, {credentials: 'same-origin'})
            .then(function(response){ return response.json(); })
            .then(function(payload){
                if (!payload || !payload.success || !payload.data) return;
                var data = payload.data;
                var box = document.querySelector('.catTabContent .borsaValue');
                if (box && data.value) {
                    var label = box.querySelector('span');
                    var value = box.querySelector('.pv-bist100-repaired-value');
                    if (!value) {
                        value = document.createElement('b');
                        value.className = 'pv-bist100-repaired-value';
                        if (label && label.nextSibling) {
                            box.insertBefore(value, label.nextSibling);
                        } else if (label) {
                            label.insertAdjacentElement('afterend', value);
                        } else {
                            box.insertBefore(value, box.firstChild);
                        }
                    }
                    value.textContent = data.value;
                }

                var rate = document.querySelector('.catTabContent .borsaRate');
                if (rate && data.change) {
                    var numeric = parseFloat(String(data.change).replace('%','').replace('.','').replace(',','.'));
                    rate.textContent = '(' + data.change + ')';
                    rate.style.color = numeric > 0 ? '#32ba5b' : '#ef291f';
                }

                var updated = document.querySelector('.catTabContent .lastUpdate');
                if (updated && data.update) {
                    updated.textContent = 'Son Güncelleme: ' + data.update;
                }
            })
            .catch(function(){});
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'pv_market_bist100_footer_patch', 50 );
