<?php
/**
 * PiyasaVizyon custom 404 page.
 * Standalone layout: no theme header, no footer, no ads.
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class( 'pv-404-standalone' ); ?>>
  <?php wp_body_open(); ?>
  <main class="pv-404-full" aria-labelledby="pv-404-title">
    <div class="pv-404-orb pv-404-orb-a"></div>
    <div class="pv-404-orb pv-404-orb-b"></div>
    <section class="pv-404-panel">
      <a class="pv-404-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="PiyasaVizyon ana sayfa">
        <span>piyasa</span><b>vizyon</b>
      </a>
      <div class="pv-404-code-new">404</div>
      <span class="pv-404-kicker">Sayfa bulunamadı</span>
      <h1 id="pv-404-title">Aradığınız bağlantı artık burada değil.</h1>
      <p>Adres değişmiş, içerik kaldırılmış veya bağlantı hatalı yazılmış olabilir. Ana sayfaya dönebilir, piyasa ekranlarına geçebilir ya da arama yapabilirsiniz.</p>
      <form class="pv-404-search-new" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <input type="search" name="s" placeholder="Haber, hisse, döviz veya konu ara" required>
        <button type="submit">Ara</button>
      </form>
      <div class="pv-404-actions">
        <a class="pv-404-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Ana Sayfaya Dön</a>
        <a href="<?php echo esc_url( home_url( '/borsa/' ) ); ?>">Borsa</a>
        <a href="<?php echo esc_url( home_url( '/doviz/' ) ); ?>">Döviz</a>
        <a href="<?php echo esc_url( home_url( '/halka-arz/' ) ); ?>">Halka Arz</a>
      </div>
      <small class="pv-404-note">PiyasaVizyon finans ekranlarına kesintisiz erişim için ana sayfayı ziyaret edin.</small>
    </section>
  </main>
  <?php wp_footer(); ?>
</body>
</html>
