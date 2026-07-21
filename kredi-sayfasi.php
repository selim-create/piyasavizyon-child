<?php
/*
  Template Name: Kredi Sayfası
*/
if (!defined('ABSPATH')) { exit; }
require_once get_stylesheet_directory() . '/template-credit-helpers.php';
get_header();
?>
<main class="pv-credit-page pv-credit-home">
  <section class="pv-credit-hero">
    <div class="pv-credit-container pv-credit-hero-grid">
      <div class="pv-credit-hero-copy">
        <nav class="pv-credit-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa</a><span>/</span><strong><?php the_title(); ?></strong></nav>
        <span class="pv-credit-eyebrow">Kredi Merkezi</span>
        <h1>Kredi hesaplama, banka karşılaştırma ve faiz oranları tek ekranda</h1>
        <p>İhtiyaç, konut, taşıt ve KOBİ kredileri için güncel teklifleri karşılaştırın; aylık taksit, toplam geri ödeme ve faiz oranlarını modern bir finans paneli üzerinden inceleyin.</p>
        <div class="pv-credit-hero-actions">
          <a href="#pv-kredi-hesapla">Kredi Hesapla</a>
          <a href="<?php echo esc_url(home_url('/faiz-oranlari/')); ?>">Faiz Oranları</a>
        </div>
      </div>
      <div id="pv-kredi-hesapla" class="pv-credit-hero-card">
        <?php pv_v7_credit_render_form('ihtiyac', 'pv-credit-hero-calculator'); ?>
      </div>
    </div>
  </section>

  <div class="pv-credit-container">
    <?php pv_v7_credit_render_ad('pv-credit-ad-top'); ?>
    <?php pv_v7_credit_render_type_cards(); ?>
    <?php pv_v7_credit_render_estimator(); ?>
    <?php pv_v7_credit_render_tools_grid(); ?>
    <?php pv_v7_credit_render_popular(); ?>
    <?php pv_v7_credit_render_ad('pv-credit-ad-mid'); ?>
    <?php pv_v7_credit_render_news(4); ?>
  </div>
</main>
<?php get_footer(); ?>
