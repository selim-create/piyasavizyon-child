<?php
/*
  Template Name: Canlı Borsa
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

$endex = isset($_GET['Endex']) ? sanitize_text_field(wp_unslash($_GET['Endex'])) : 'bist-100';
$endex = function_exists('pv_live_borsa_allowed_endex') ? pv_live_borsa_allowed_endex($endex) : 'bist-100';

$all_rows = function_exists('pv_live_borsa_rows') ? pv_live_borsa_rows($endex) : array();
$total_count = count($all_rows);
$up_count = count(array_filter($all_rows, static fn($r) => $r['direction'] === 'increase'));
$down_count = count(array_filter($all_rows, static fn($r) => $r['direction'] === 'decrease'));
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
                $detail_slug = ! empty( $row['detail_slug'] ) ? $row['detail_slug'] : $row['slug'];
                $url = $detail_slug ? home_url('/hisse/?h=' . rawurlencode($detail_slug)) : '#';
                $arrow = $row['direction'] === 'increase' ? '▲ ' : ($row['direction'] === 'decrease' ? '▼ ' : '• ');
              ?>
                <tr class="<?php echo esc_attr($row['key']); ?>_bg" data-direction="<?php echo esc_attr($row['direction']); ?>">
                  <td><a class="pv-live-symbol hisse_name <?php echo esc_attr($row['key']); ?>_name" data-name="<?php echo esc_attr($row['key']); ?>" href="<?php echo esc_url($url); ?>"><span><?php echo esc_html(substr($row['symbol'],0,1)); ?></span><b><?php echo esc_html($row['symbol']); ?></b></a></td>
                  <td><strong class="<?php echo esc_attr($row['key']); ?>_fiyat"><?php echo esc_html($row['price']); ?></strong></td>
                  <td><em class="pv-live-change <?php echo esc_attr($row['direction']); ?> <?php echo esc_attr($row['key']); ?>_yuzde"><?php echo esc_html($arrow . $row['percent']); ?></em></td>
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
  var endpoint = <?php echo wp_json_encode(admin_url('admin-ajax.php?action=pv_live_borsa&endex=' . rawurlencode($endex))); ?>;
  var busy = false;

  function arrowFor(direction) {
    if (direction === 'increase') return '▲ ';
    if (direction === 'decrease') return '▼ ';
    return '• ';
  }

  function canli() {
    if (busy || document.hidden) return;
    busy = true;

    $.getJSON(endpoint)
      .done(function(payload) {
        if (!payload || !payload.success || !payload.data) return;
        var obj = payload.data;

        $('.hisse_name').each(function(){
          var name = $(this).data('name');
          if (!name || !obj[name]) return;

          var $price = $('.' + name + '_fiyat');
          var $change = $('.' + name + '_yuzde');
          var oldPrice = parseFloat(($price.first().text() || '').replace(/\./g, '').replace(',', '.'));
          var newPrice = parseFloat(String(obj[name].fiyat || '').replace(/\./g, '').replace(',', '.'));
          var direction = obj[name].direction || 'neutral';

          if (isFinite(newPrice)) {
            $price.text(obj[name].fiyat);
          }

          $change
            .text(arrowFor(direction) + obj[name].yuzde)
            .removeClass('increase decrease neutral')
            .addClass(direction);

          if (isFinite(oldPrice) && isFinite(newPrice) && oldPrice !== newPrice) {
            var $row = $('.' + name + '_bg');
            $row.addClass(oldPrice < newPrice ? 'pv-live-flash-up' : 'pv-live-flash-down');
            setTimeout(function(){ $row.removeClass('pv-live-flash-up pv-live-flash-down'); }, 1600);
          }

          $('.' + name + '_zaman').text(obj[name].zaman);
        });
      })
      .always(function(){ busy = false; });
  }

  setInterval(canli, 30000);
  document.addEventListener('visibilitychange', function(){
    if (!document.hidden) canli();
  });
})(window.jQuery);
</script>
<?php get_footer(); ?>