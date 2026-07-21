<?php get_header(); ?>
<div class="wrap pv-inner-masthead-wrap">
  <?php pv_v7_ad('pv_header_ad','970×250 / 970×90 Üst Banner Reklam Alanı','ad ad-970 pv-header-masthead pv-ad-desktop'); ?>
  <?php pv_v7_ad('pv_mobile_masthead','320×100 / 320×150 Mobil Masthead Reklam','ad ad-mobile-masthead pv-ad-mobile'); ?>
</div>
<main class="wrap pv-inner-layout pv-archive-layout">
  <section class="pv-inner-card pv-archive-card">
    <header class="pv-archive-head"><h1 class="pv-inner-title"><?php the_archive_title(); ?></h1><?php if ( get_the_archive_description() ) : ?><div class="pv-archive-desc"><?php the_archive_description(); ?></div><?php endif; ?></header>
    <div class="pv-archive-list">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); $img = pv_v7_img(get_the_ID()); ?>
      <a class="pv-archive-item" href="<?php the_permalink(); ?>">
        <div class="pv-archive-thumb" <?php if($img) echo 'style="background-image:url('.esc_url($img).')"'; ?>></div>
        <div><span class="pv-archive-cat"><?php $cat=get_the_category(); echo esc_html($cat?$cat[0]->name:'Haber'); ?></span><h2><?php the_title(); ?></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 28)); ?></p></div>
      </a>
    <?php endwhile; the_posts_pagination(); else: ?><p>Kayıt bulunamadı.</p><?php endif; ?>
    </div>
  </section>
  <?php pv_v7_inner_sidebar('archive'); ?>
</main>
<?php get_footer(); ?>
