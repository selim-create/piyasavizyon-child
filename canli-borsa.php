<?php
/*
  Template Name: Canlı Borsa
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

$endex = isset($_GET['Endex']) ? sanitize_text_field(wp_unslash($_GET['Endex'])) : 'bist-100';
$allowed_endex = array('bist-TUM','bist-100','bist-50','bist-30');
if ( ! in_array($endex, $allowed_endex, true) ) { $endex = 'bist-100'; }

$url = $endex && $endex !== 'bist-100'
  ? 'https://uzmanpara.milliyet.com.tr/canli-borsa/' . rawurlencode($endex) . '-hisseleri/'
  : 'https://uzmanpara.milliyet.com.tr/canli-borsa/';

$uzmanpara = function_exists('get_url_curl') ? get_url_curl($url) : '';
preg_match_all('@<table cellspacing="0" cellpadding="0" border="0" class="table3">(.*?)</table>@si', (string) $uzmanpara, $table_data);

function pv_v255_live_borsa_rows_from_table( $html ) {
  $rows = array();
  if ( empty($html) ) { return $rows; }
  preg_match_all('@<tr class="zebra" id="h_tr_id_(.*?)" >(.*?)</tr>@si', $html, $matches);
  foreach ( (array) ($matches[2] ?? array()) as $val ) {
    preg_match('@<td class="currency"><a href="/borsa/hisse-senetleri/(.*?)/"\s+target\s*=\s*"_blank"\s*><b id="h_b_ad_id_(.*?)"\s*>(.*?)</b></a></td>@si', $val, $name);
    preg_match('@<td class="center" id="h_td_fiyat_id_(.*?)">(.*?)</td>@si', $val, $fiyat);
    preg_match('@<td class="currency-(.*?)" id="h_td_yon_id_(.*?)"\s*>@si', $val, $yon);
    preg_match('@<td class="center" id="h_td_yuzde_id_(.*?)">(.*?)</td>@si', $val, $yuzde);
    preg_match('@<td class="center" id="h_td_zaman_id_(.*?)">(.*?)</td>@si', $val, $zaman);
    $symbol = trim(wp_strip_all_tags($name[3] ?? ''));
    if ( $symbol === '' ) { continue; }
    $slug = trim($name[1] ?? '');
    $key = trim($yuzde[1] ?? sanitize_title($symbol));
    $direction = (isset($yon[1]) && $yon[1] === 'up') ? 'increase' : 'decrease';
    $rows[] = array(
      'symbol' => $symbol,
      'slug' => $slug,
      'key' => $key,
      'price' => trim(wp_strip_all_tags($fiyat[2] ?? '-')),
      'percent' => trim(wp_strip_all_tags($yuzde[2] ?? '-')),
      'time' => trim(wp_strip_all_tags($zaman[2] ?? '-')),
      'direction' => $direction,
    );
  }
  return $rows;
}

$groups = array();
foreach ( (array) ($table_data[1] ?? array()) as $i => $table_html ) {
  $rows = pv_v255_live_borsa_rows_from_table($table_html);
  if ( $rows ) { $groups[] = $rows; }
}
$all_rows = array_merge(...($groups ?: array(array())));
$total_count = count($all_rows);
$up_count = count(array_filter($all_rows, static fn($r) => $r['direction'] === 'increase'));
$down_count = max(0, $total_count - $up_count);
$updated = $all_rows[0]['time'] ?? current_time('H:i');
$tabs = array(
  'bist-TUM' => 'BIST Tümü',
  'bist-100' => 'BIST 100',
  'bist-50' => 'BIST 50',
  'bist-30' => 'BIST 30',
);
?>
<main class="pv-live-borsa-page">
  <div class="pv-live-wrap">
    <section class="pv-live-hero">
      <div>
        <span class="pv-live-kicker">Canlı Borsa</span>
        <h1>Borsa İstanbul hisselerini canlı takip edin.</h1>
        <p>Hisse fiyatları, anlık değişimler, yüzdelik hareketler ve son güncelleme saatleri tek ekranda modern tablo görünümüyle listelenir.</p>
      </div>
      <div class="pv-live-hero-card">
        <small>Son Güncelleme</small>
        <b><?php echo esc_html($updated); ?></b>
        <span>Veriler kaynak servisten alınır ve belirli aralıklarla yenilenir.</span>
      </div>
    </section>

    <section class="pv-live-stats" aria-label="Canlı borsa özeti">
      <article><small>Listelenen Hisse</small><b><?php echo esc_html(number_format_i18n($total_count)); ?></b><span>Aktif tablo kaydı</span></article>
      <article><small>Yükselen</small><b class="pv-up"><?php echo esc_html(number_format_i18n($up_count)); ?></b><span>Pozitif değişim</span></article>
      <article><small>Düşen</small><b class="pv-down"><?php echo esc_html(number_format_i18n($down_count)); ?></b><span>Negatif değişim</span></article>
      <article><small>Aktif Endeks</small><b><?php echo esc_html($tabs[$endex] ?? 'BIST 100'); ?></b><span>Seçili görünüm</span></article>
    </section>

    <section class="pv-live-board">
      <div class="pv-live-tabs" role="navigation" aria-label="Endeks filtreleri">
        <?php foreach ($tabs as $key => $label): ?>
          <a class="<?php echo $endex === $key ? 'active' : ''; ?>" href="<?php echo esc_url(home_url('/canli-borsa/?Endex=' . $key)); ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
      </div>

      <?php if ( $all_rows ) : ?>
        <div class="pv-live-table-shell">
          <table class="pv-live-table">
            <thead><tr><th>Hisse</th><th>Fiyat</th><th>Değişim</th><th>Zaman</th></tr></thead>
            <tbody>
              <?php foreach ($all_rows as $row):
                $url = $row['slug'] ? home_url('/hisse/?h=' . rawurlencode($row['slug'])) : '#';
              ?>
                <tr class="<?php echo esc_attr($row['key']); ?>_bg" data-direction="<?php echo esc_attr($row['direction']); ?>">
                  <td><a class="pv-live-symbol hisse_name <?php echo esc_attr($row['key']); ?>_name" data-name="<?php echo esc_attr($row['key']); ?>" href="<?php echo esc_url($url); ?>"><span><?php echo esc_html(substr($row['symbol'],0,1)); ?></span><b><?php echo esc_html($row['symbol']); ?></b></a></td>
                  <td><strong class="<?php echo esc_attr($row['key']); ?>_fiyat"><?php echo esc_html($row['price']); ?></strong></td>
                  <td><em class="pv-live-change <?php echo esc_attr($row['direction']); ?> <?php echo esc_attr($row['key']); ?>_yuzde"><?php echo $row['direction'] === 'increase' ? '▲ ' : '▼ '; ?><?php echo esc_html($row['percent']); ?></em></td>
                  <td><time class="<?php echo esc_attr($row['key']); ?>_zaman"><?php echo esc_html($row['time']); ?></time></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else : ?>
        <div class="pv-live-empty"><h2>Veri alınamadı.</h2><p>Kaynak servisten anlık veri alınamadı. Birkaç dakika sonra yeniden deneyin.</p></div>
      <?php endif; ?>
    </section>
  </div>
</main>
<script>
(function($){
  if (!$) return;
  function canli() {
    $.get("<?php echo esc_url(get_template_directory_uri() . '/api/canli_borsa.php'); ?>", function(data) {
      var obj;
      try { obj = $.parseJSON(data); } catch(e) { obj = null; }
      if (!obj) return;
      $('.hisse_name').each(function(){
        var name = $(this).data('name');
        if (!name || !obj[name]) return;
        var $price = $('.' + name + '_fiyat');
        var oldPrice = parseFloat(($price.first().text() || '').replace(',', '.'));
        var newPrice = parseFloat(String(obj[name].fiyat || '').replace(',', '.'));
        if (!isFinite(oldPrice) || !isFinite(newPrice) || oldPrice === newPrice) return;
        $price.text(obj[name].fiyat);
        $('.' + name + '_yuzde').text((oldPrice < newPrice ? '▲ ' : '▼ ') + obj[name].yuzde).removeClass('increase decrease').addClass(oldPrice < newPrice ? 'increase' : 'decrease');
        $('.' + name + '_zaman').text(obj[name].zaman);
        var $row = $('.' + name + '_bg');
        $row.addClass(oldPrice < newPrice ? 'pv-live-flash-up' : 'pv-live-flash-down');
        setTimeout(function(){ $row.removeClass('pv-live-flash-up pv-live-flash-down'); }, 1600);
      });
    });
  }
  setInterval(canli, 3000);
})(window.jQuery);
</script>
<?php get_footer(); ?>
