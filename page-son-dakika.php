<?php
/* Template Name: PiyasaVizyon - Son Dakika */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$paged = max( 1, (int) get_query_var( 'paged' ) );
$q = new WP_Query( array(
  'post_type' => 'post',
  'post_status' => 'publish',
  'posts_per_page' => 18,
  'paged' => $paged,
  'ignore_sticky_posts' => true,
) );
?>
<main class="pv-corp pv-breaking-page pv-corp-no-ads">
  <div class="pv-corp-wrap">
    <section class="pv-corp-hero pv-breaking-hero">
      <span class="pv-corp-kicker">Son Dakika</span>
      <h1>Ekonomi ve finans gündeminden en yeni gelişmeler.</h1>
      <p>Piyasa, şirket, bankacılık, döviz, altın, kripto ve halka arz gündemindeki sıcak gelişmeleri tek ekranda takip edin.</p>
    </section>
    <?php if ( $q->have_posts() ) : ?>
      <section class="pv-breaking-grid" aria-label="Son dakika haberleri">
        <?php while ( $q->have_posts() ) : $q->the_post();
          $thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
          if ( ! $thumb ) { $thumb = get_stylesheet_directory_uri() . '/assets/img/pv-news-fallback.svg'; }
        ?>
          <article class="pv-breaking-card">
            <a class="pv-breaking-img" href="<?php the_permalink(); ?>" style="background-image:url('<?php echo esc_url( $thumb ); ?>')"></a>
            <div class="pv-breaking-body">
              <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd M Y H:i' ) ); ?></time>
              <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
              <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
            </div>
          </article>
        <?php endwhile; wp_reset_postdata(); ?>
      </section>
      <nav class="pv-breaking-pagination">
        <?php echo paginate_links( array( 'total' => $q->max_num_pages, 'current' => $paged ) ); ?>
      </nav>
    <?php else : ?>
      <section class="pv-corp-card"><h2>Şu anda listelenecek haber bulunamadı.</h2><p>Kısa süre sonra tekrar kontrol edin.</p></section>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>
