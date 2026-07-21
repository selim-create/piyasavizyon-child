<?php
if (!defined('ABSPATH')) { exit; }
require_once get_stylesheet_directory() . '/template-credit-helpers.php';
$service_title = isset($pv_credit_service_title) ? $pv_credit_service_title : get_the_title();
$service_desc = isset($pv_credit_service_desc) ? $pv_credit_service_desc : 'Finansal kararlarınız için karşılaştırma ve hesaplama araçlarını tek ekranda kullanın.';
$service_icon = isset($pv_credit_service_icon) ? $pv_credit_service_icon : 'fa-solid fa-calculator';
get_header();
?>
<main class="pv-credit-page pv-credit-service-page">
  <section class="pv-credit-result-hero">
    <div class="pv-credit-container pv-credit-result-hero-grid">
      <div>
        <nav class="pv-credit-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa</a><span>/</span><a href="<?php echo esc_url(home_url('/kredi/')); ?>">Kredi</a><span>/</span><strong><?php echo esc_html($service_title); ?></strong></nav>
        <span class="pv-credit-eyebrow">Finans aracı</span>
        <h1><?php echo esc_html($service_title); ?></h1>
        <p><?php echo esc_html($service_desc); ?></p>
      </div>
      <div class="pv-credit-service-icon"><i class="<?php echo esc_attr($service_icon); ?>"></i><strong><?php echo esc_html($service_title); ?></strong><span>Güncel başlıkları, hesaplama araçlarını ve karşılaştırma bağlantılarını tek ekranda inceleyin.</span></div>
    </div>
  </section>
  <div class="pv-credit-container">
    <?php pv_v7_credit_render_ad('pv-credit-ad-top'); ?>
    <?php pv_v7_credit_render_form('ihtiyac', 'pv-credit-service-calculator'); ?>
    <?php pv_v7_credit_render_tools_grid(); ?>
    <?php pv_v7_credit_render_news(4); ?>
  </div>
</main>
<?php get_footer(); ?>
