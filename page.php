<?php get_header(); ?>
<div class="wrap pv-inner-masthead-wrap">
  <?php pv_v7_ad('pv_header_ad','970×250 / 970×90 Üst Banner Reklam Alanı','ad ad-970 pv-header-masthead pv-ad-desktop'); ?>
  <?php pv_v7_ad('pv_mobile_masthead','320×100 / 320×150 Mobil Masthead Reklam','ad ad-mobile-masthead pv-ad-mobile'); ?>
</div>
<main class="wrap pv-inner-layout pv-page-layout">
  <article class="pv-inner-card pv-page-card">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <h1 class="pv-inner-title"><?php the_title(); ?></h1>
      <div class="pv-entry-content"><?php the_content(); ?></div>
    <?php endwhile; endif; ?>
  </article>
  <?php pv_v7_inner_sidebar('page'); ?>
</main>
<?php get_footer(); ?>
