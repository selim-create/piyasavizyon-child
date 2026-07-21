<?php
/*
  Template Name: Kredi Hesapla
*/
if (!defined('ABSPATH')) { exit; }
require_once get_stylesheet_directory() . '/template-credit-helpers.php';
get_header();
?>
<main class="pv-credit-page pv-credit-calc-page">
  <section class="pv-credit-result-hero pv-credit-calc-hero">
    <div class="pv-credit-container pv-credit-result-hero-grid">
      <div>
        <nav class="pv-credit-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa</a><span>/</span><a href="<?php echo esc_url(home_url('/kredi/')); ?>">Kredi</a><span>/</span><strong>Kredi Hesapla</strong></nav>
        <span class="pv-credit-eyebrow">Gelişmiş hesaplama</span>
        <h1>Kredi hesaplama ve ödeme planı simülasyonu</h1>
        <p>İhtiyaç, konut, taşıt ve KOBİ kredileri için tutar, vade, faiz ve masraf alanlarını değiştirerek ödeme planınızı hızlıca simüle edin.</p>
      </div>
      <div class="pv-credit-hero-card pv-credit-result-search">
        <?php pv_v7_credit_render_form('ihtiyac', 'pv-credit-mini-calculator'); ?>
      </div>
    </div>
  </section>
  <div class="pv-credit-container">
    <?php pv_v7_credit_render_ad('pv-credit-ad-top'); ?>
    <?php pv_v7_credit_render_advanced_calculator(); ?>
    <?php pv_v7_credit_render_type_cards(); ?>
    <?php pv_v7_credit_render_popular(); ?>
    <?php pv_v7_credit_render_tools_grid(); ?>
    <?php pv_v7_credit_render_news(6); ?>
  </div>
</main>
<?php get_footer(); ?>
