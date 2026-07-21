<?php
if (!defined('ABSPATH')) { exit; }
require_once get_stylesheet_directory() . '/template-credit-helpers.php';
$type = isset($pv_credit_type) ? $pv_credit_type : 'ihtiyac';
pv_v7_credit_redirect_pretty_if_needed($type);
$params = pv_v7_credit_route_params($type);
$title = number_format($params['amount'], 0, ',', '.') . ' TL ' . $params['term'] . ' Ay Vadeli ' . pv_v7_credit_type_label($type);
add_filter('pre_get_document_title', function($old) use ($title) { return $title . ' - ' . get_bloginfo('name'); }, 15);
add_filter('wpseo_title', function($old) use ($title) { return $title . ' - ' . get_bloginfo('name'); }, 15);
get_header();
$data = pv_v7_credit_fetch_offers($type, $params['amount'], $params['term']);
?>
<main class="pv-credit-page pv-credit-results-page pv-credit-results-<?php echo esc_attr($type); ?>">
  <section class="pv-credit-result-hero">
    <div class="pv-credit-container pv-credit-result-hero-grid">
      <div>
        <nav class="pv-credit-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa</a><span>/</span><a href="<?php echo esc_url(home_url('/kredi/')); ?>">Kredi</a><span>/</span><strong><?php echo esc_html(pv_v7_credit_type_label($type)); ?></strong></nav>
        <span class="pv-credit-eyebrow">Banka teklifleri</span>
        <h1><?php echo esc_html($title); ?></h1>
        <p><?php echo esc_html(pv_v7_credit_type_desc($type)); ?></p>
        <div class="pv-credit-summary-pills">
          <span><small>Tutar</small><strong><?php echo esc_html(number_format($params['amount'], 0, ',', '.')); ?> TL</strong></span>
          <span><small>Vade</small><strong><?php echo esc_html($params['term']); ?> Ay</strong></span>
          <span><small>Kredi Türü</small><strong><?php echo esc_html(pv_v7_credit_type_label($type)); ?></strong></span>
        </div>
      </div>
      <div class="pv-credit-hero-card pv-credit-result-search">
        <?php pv_v7_credit_render_form($type, 'pv-credit-mini-calculator'); ?>
      </div>
    </div>
  </section>
  <div class="pv-credit-container">
    <?php pv_v7_credit_render_ad('pv-credit-ad-top'); ?>
    <?php pv_v7_credit_render_offers($data, $params['amount'], $params['term'], $type); ?>
    <?php pv_v7_credit_render_popular(); ?>
    <?php pv_v7_credit_render_ad('pv-credit-ad-mid'); ?>
    <?php pv_v7_credit_render_tools_grid(); ?>
  </div>
</main>
<?php get_footer(); ?>
