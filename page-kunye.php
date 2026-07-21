<?php
/* Template Name: PiyasaVizyon - Künye */
require_once get_stylesheet_directory() . '/template-corporate-parts.php';
get_header();
?>
<main class="pv-corp pv-corp-masthead-page pv-corp-no-ads">
  <div class="pv-corp-wrap">
    <section class="pv-corp-hero">
      <span class="pv-corp-kicker">Künye</span>
      <h1>PiyasaVizyon yayın ve iletişim bilgileri.</h1>
      <p>Online finans platformu olarak yayın yapan piyasavizyon.com bir HİP Network markasıdır.</p>
    </section>

    <div class="pv-corp-grid">
      <div class="pv-corp-main">
        <section class="pv-corp-card pv-masthead-card">
          <div class="pv-corp-section-head"><div><h2>Yayın Bilgileri</h2><p>PiyasaVizyon’un kurumsal yayın ve iletişim bilgileri.</p></div><span class="pv-corp-badge">HİP Network</span></div>
          <div class="pv-corp-info-list pv-corp-info-list-large">
            <div class="pv-corp-info-row"><strong>Yayıncı</strong><span>HİP Medya</span></div>
            <div class="pv-corp-info-row"><strong>Web Adresi</strong><a href="https://www.hipmedya.com/" target="_blank" rel="noopener">www.hipmedya.com</a></div>
            <div class="pv-corp-info-row"><strong>Telefon</strong><a href="tel:08504501105">0850 450 11 05</a></div>
            <div class="pv-corp-info-row"><strong>E-mail</strong><a href="mailto:iletisim@hipmedya.com">iletisim@hipmedya.com</a></div>
            <div class="pv-corp-info-row"><strong>Bülten Gönderimleri</strong><a href="mailto:bulten@hipmedya.com">bulten@hipmedya.com</a></div>
            <div class="pv-corp-info-row"><strong>Reklam</strong><a href="mailto:reklam@hipmedya.com">reklam@hipmedya.com</a></div>
          </div>
        </section>

        <?php pv_v252_page_content(); ?>
      </div>
      <?php pv_v252_side_nav( 'kunye' ); ?>
    </div>
  </div>
</main>
<?php get_footer(); ?>
