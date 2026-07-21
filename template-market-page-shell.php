<?php
/**
 * Market/Borsa page shell for Piyasa Vizyon child theme.
 * Keeps the finance plugin shortcode/content intact, but gives the page a stable
 * wrapper so CSS can target these pages without relying on fragile body classes.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$pv_market_page_class = isset($pv_market_page_class) ? $pv_market_page_class : 'pv-market-generic';
$pv_market_kicker = isset($pv_market_kicker) ? $pv_market_kicker : 'Piyasa Vizyon';
?>
<div class="wrap pv-inner-masthead-wrap pv-market-masthead-wrap">
  <?php pv_v7_ad('pv_header_ad','970×250 / 970×90 Üst Banner Reklam Alanı','ad ad-970 pv-header-masthead pv-ad-desktop'); ?>
  <?php pv_v7_ad('pv_mobile_masthead','320×100 / 320×150 Mobil Masthead Reklam','ad ad-mobile-masthead pv-ad-mobile'); ?>
</div>
<main class="wrap pv-inner-layout pv-page-layout pv-market-page-layout <?php echo esc_attr($pv_market_page_class); ?>">
  <article class="pv-inner-card pv-page-card pv-market-card">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <header class="pv-market-hero-card">
        <div>
          <span class="pv-market-kicker"><?php echo esc_html($pv_market_kicker); ?></span>
          <h1 class="pv-inner-title pv-market-title"><?php the_title(); ?></h1>
        </div>
      </header>
      <div class="pv-entry-content pv-market-content"><?php the_content(); ?></div>
    <?php endwhile; endif; ?>
  </article>
  <?php pv_v7_inner_sidebar('page'); ?>
</main>
<?php get_footer(); ?>
