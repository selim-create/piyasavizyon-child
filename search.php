<?php
require_once get_stylesheet_directory() . '/template-corporate-parts.php';
get_header();
pv_v252_inner_masthead();
$query = get_search_query();
?>
<main class="pv-corp pv-search-page">
  <div class="pv-corp-wrap">
    <section class="pv-corp-hero pv-search-hero">
      <span class="pv-corp-kicker">Arama Sonuçları</span>
      <h1><?php echo $query ? '“' . esc_html( $query ) . '” için sonuçlar' : 'PiyasaVizyon’da ara'; ?></h1>
      <p>Haberler, piyasa ekranları ve finansal içerikler arasında hızlı arama yapın.</p>
      <form class="pv-search-form-large" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <input type="search" name="s" value="<?php echo esc_attr( $query ); ?>" placeholder="Haber, hisse, döviz veya konu ara" required>
        <button type="submit">Ara</button>
      </form>
    </section>

    <div class="pv-corp-grid">
      <section class="pv-corp-main">
        <?php if ( have_posts() ) : ?>
          <div class="pv-search-list">
            <?php while ( have_posts() ) : the_post(); $img = function_exists( 'pv_v7_img' ) ? pv_v7_img( get_the_ID(), 'medium_large' ) : get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ); $cat = get_the_category(); ?>
              <a class="pv-search-item" href="<?php the_permalink(); ?>">
                <div class="pv-search-thumb" <?php echo $img ? 'style="background-image:url(' . esc_url( $img ) . ')"' : ''; ?>></div>
                <div class="pv-search-body">
                  <small><?php echo esc_html( $cat ? $cat[0]->name : get_post_type_object( get_post_type() )->labels->singular_name ); ?></small>
                  <h2><?php the_title(); ?></h2>
                  <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
                </div>
              </a>
            <?php endwhile; ?>
          </div>
          <div class="pv-corp-card"><?php the_posts_pagination(); ?></div>
        <?php else : ?>
          <div class="pv-search-empty"><h2>Sonuç bulunamadı.</h2><p>Aradığınız kelimeyle eşleşen içerik bulamadık. Daha kısa veya farklı bir arama terimi deneyebilirsiniz.</p></div>
        <?php endif; ?>
      </section>
      <aside class="pv-corp-side">
        <?php pv_v252_market_side_card(); ?>
        <div class="pv-corp-side-card"><h3>Popüler Sayfalar</h3>
          <a href="<?php echo esc_url( home_url( '/borsa/' ) ); ?>"><b>Borsa</b><span>›</span></a>
          <a href="<?php echo esc_url( home_url( '/doviz/' ) ); ?>"><b>Döviz Kurları</b><span>›</span></a>
          <a href="<?php echo esc_url( home_url( '/altin/' ) ); ?>"><b>Altın Piyasaları</b><span>›</span></a>
          <a href="<?php echo esc_url( home_url( '/kripto-para/' ) ); ?>"><b>Kripto Paralar</b><span>›</span></a>
          <a href="<?php echo esc_url( home_url( '/halka-arz/' ) ); ?>"><b>Halka Arz</b><span>›</span></a>
        </div>
      </aside>
    </div>
  </div>
</main>
<?php get_footer(); ?>
