<?php
/*
  Template Name: Faiz Oranları
*/
if (!defined('ABSPATH')) { exit; }
require_once get_stylesheet_directory() . '/template-credit-helpers.php';
get_header();
$type = isset($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : 'try';
if (!in_array($type, array('try','usd','eur'), true)) { $type = 'try'; }
?>
<main class="pv-credit-page pv-credit-rates-page">
  <section class="pv-credit-result-hero pv-credit-rates-hero">
    <div class="pv-credit-container pv-credit-result-hero-grid">
      <div>
        <nav class="pv-credit-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa</a><span>/</span><a href="<?php echo esc_url(home_url('/kredi/')); ?>">Kredi</a><span>/</span><strong><?php the_title(); ?></strong></nav>
        <span class="pv-credit-eyebrow">Mevduat ve faiz ekranı</span>
        <h1><?php the_title(); ?></h1>
        <p>TL, dolar ve euro mevduat oranlarını dönemlere göre takip edin. Oranlar kaynak veriye bağlı olarak değişebilir.</p>
      </div>
      <div class="pv-credit-rate-switch-card">
        <strong>Para Birimi</strong>
        <div class="pv-credit-rate-tabs">
          <a class="<?php echo $type === 'try' ? 'active' : ''; ?>" href="<?php echo esc_url(home_url('/faiz-oranlari/?type=try')); ?>">Türk Lirası</a>
          <a class="<?php echo $type === 'usd' ? 'active' : ''; ?>" href="<?php echo esc_url(home_url('/faiz-oranlari/?type=usd')); ?>">Dolar</a>
          <a class="<?php echo $type === 'eur' ? 'active' : ''; ?>" href="<?php echo esc_url(home_url('/faiz-oranlari/?type=eur')); ?>">Euro</a>
        </div>
      </div>
    </div>
  </section>
  <div class="pv-credit-container">
    <?php pv_v7_credit_render_ad('pv-credit-ad-top'); ?>
    <section class="pv-credit-rates-card">
      <div class="pv-credit-results-head"><div><span>Güncel oranlar</span><h2>Mevduat Oranları</h2></div><p>Tablo otomatik olarak kaynak veriden yüklenir.</p></div>
      <div class="pv-credit-table-wrap">
        <table class="currencyTable currencyFullTable pv-credit-rates-table" data-pv-credit-rates-table>
          <tbody><tr><td class="pv-credit-loading">Yükleniyor...</td></tr></tbody>
        </table>
      </div>
    </section>
    <?php pv_v7_credit_render_tools_grid(); ?>
    <?php pv_v7_credit_render_popular(); ?>
  </div>
</main>
<script>
(function(){
  var table = document.querySelector('[data-pv-credit-rates-table]');
  if (!table) return;
  var url = <?php echo wp_json_encode(get_template_directory_uri() . '/api/faiz-oranlari.php?type=' . rawurlencode($type)); ?>;
  fetch(url, {credentials:'same-origin'}).then(function(r){ return r.text(); }).then(function(html){ table.innerHTML = html; }).catch(function(){ table.innerHTML = '<tbody><tr><td>Oranlar şu anda yüklenemedi.</td></tr></tbody>'; });
})();
</script>
<?php get_footer(); ?>
