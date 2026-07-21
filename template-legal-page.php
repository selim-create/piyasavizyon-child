<?php
/* Template Name: PiyasaVizyon - Yasal Metin */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<main class="pv-corp pv-legal-page pv-corp-no-ads">
  <div class="pv-corp-wrap">
    <section class="pv-corp-hero pv-legal-hero">
      <span class="pv-corp-kicker">Yasal Metinler</span>
      <h1><?php the_title(); ?></h1>
      <p>PiyasaVizyon kullanım, veri, gizlilik ve bilgilendirme süreçlerine ilişkin kurumsal metinler.</p>
    </section>
    <div class="pv-legal-layout">
      <aside class="pv-legal-nav" aria-label="Yasal metinler">
        <a href="<?php echo esc_url( home_url('/aydinlatma-metni/') ); ?>">Aydınlatma Metni</a>
        <a href="<?php echo esc_url( home_url('/kvkk/') ); ?>">KVKK</a>
        <a href="<?php echo esc_url( home_url('/cerez-politikasi/') ); ?>">Çerez Politikası</a>
        <a href="<?php echo esc_url( home_url('/kullanim-kosullari/') ); ?>">Kullanım Koşulları</a>
        <a href="<?php echo esc_url( home_url('/sorumluluk-reddi/') ); ?>">Sorumluluk Reddi</a>
      </aside>
      <article class="pv-legal-content pv-corp-card">
        <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
      </article>
    </div>
  </div>
</main>
<?php get_footer(); ?>
