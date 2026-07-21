<?php get_header(); ?>
<div class="wrap pv-inner-masthead-wrap">
  <?php pv_v7_ad('pv_header_ad','970×250 / 970×90 Üst Banner Reklam Alanı','ad ad-970 pv-header-masthead pv-ad-desktop'); ?>
  <?php pv_v7_ad('pv_mobile_masthead','320×100 / 320×150 Mobil Masthead Reklam','ad ad-mobile-masthead pv-ad-mobile'); ?>
</div>
<main class="wrap pv-inner-layout pv-single-layout">
  <article class="pv-inner-card pv-single-card">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <div class="pv-entry-content pv-single-content">
        <header class="pv-single-head pv-content-head">
          <?php $cats = get_the_category(); if($cats): ?>
            <div class="pv-single-cats"><?php foreach($cats as $cat): ?><a href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a><?php endforeach; ?></div>
          <?php endif; ?>
          <h1 class="pv-inner-title pv-single-title"><?php the_title(); ?></h1>
          <div class="pv-entry-meta">
            <span><i class="fa-regular fa-calendar"></i> <?php echo esc_html(get_the_date()); ?></span>
            <span><i class="fa-regular fa-clock"></i> <?php echo esc_html(get_the_time()); ?></span>
            <?php if(get_the_author()): ?><span><i class="fa-regular fa-user"></i> <?php echo esc_html(get_the_author()); ?></span><?php endif; ?>
          </div>
          <?php if ( has_excerpt() ) : ?><p class="pv-single-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
        </header>
        <?php if ( has_post_thumbnail() ) : ?><figure class="pv-inner-featured"><?php the_post_thumbnail('large'); ?></figure><?php endif; ?>
        <?php the_content(); ?>
        <?php wp_link_pages(array('before'=>'<div class="pv-page-links">','after'=>'</div>')); ?>
      </div>
      <?php $tags = get_the_tags(); if($tags): ?><div class="pv-tag-list"><strong>Etiketler:</strong><?php foreach($tags as $tag): ?><a href="<?php echo esc_url(get_tag_link($tag)); ?>">#<?php echo esc_html($tag->name); ?></a><?php endforeach; ?></div><?php endif; ?>
      <?php pv_v7_related_posts_block(get_the_ID(), 12); ?>
    <?php endwhile; endif; ?>
  </article>
  <?php pv_v7_inner_sidebar('single'); ?>
</main>
<?php get_footer(); ?>
