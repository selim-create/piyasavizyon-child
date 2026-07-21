<?php
/*
  Template Name: Piyasanın Nabzı
*/
require_once get_stylesheet_directory() . '/template-credit-helpers.php';
get_header();
?>
<main class="pv-credit-page pv-credit-news-page">
  <section class="pv-credit-result-hero"><div class="pv-credit-container"><nav class="pv-credit-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa</a><span>/</span><strong>Piyasanın Nabzı</strong></nav><span class="pv-credit-eyebrow">Finans gündemi</span><h1>Piyasanın Nabzı</h1><p>Kredi, faiz, mevduat ve ekonomi gündemindeki öne çıkan haberler.</p></div></section>
  <div class="pv-credit-container"><?php pv_v7_credit_render_news(12); ?></div>
</main>
<?php get_footer(); ?>
