<?php
/* Template Name: PiyasaVizyon - Bülten */
require_once get_stylesheet_directory() . '/template-corporate-parts.php';
get_header();
?>
<main class="pv-corp pv-corp-newsletter-page pv-corp-no-ads">
  <div class="pv-corp-wrap">
    <section class="pv-corp-hero">
      <span class="pv-corp-kicker">Piyasa Bülteni</span>
      <h1>Günün piyasa özetini ve ekonomi başlıklarını kaçırmayın.</h1>
      <p>Döviz, altın, borsa, kripto para, halka arzlar ve kredi piyasasındaki önemli gelişmelerden haberdar olmak için PiyasaVizyon bültenine kaydolun.</p>
      <form class="pv-search-form-large" action="mailto:bulten@hipmedya.com" method="post" enctype="text/plain">
        <input type="email" name="email" placeholder="E-posta adresiniz" required>
        <button type="submit">Abone Ol</button>
      </form>
    </section>

    <div class="pv-corp-grid">
      <div class="pv-corp-main">
        <section class="pv-corp-card">
          <div class="pv-corp-section-head"><div><h2>Bültende neler var?</h2><p>Finans gündemini kısa, düzenli ve okunabilir başlıklarla takip edin.</p></div><span class="pv-corp-badge">E-posta</span></div>
          <div class="pv-corp-feature-grid">
            <article class="pv-corp-feature"><div class="pv-corp-icon">☀</div><h3>Sabah piyasa özeti</h3><p>Güne başlarken döviz, altın, borsa ve kripto piyasalarındaki ana hareketler.</p></article>
            <article class="pv-corp-feature"><div class="pv-corp-icon">IPO</div><h3>Halka arz takvimi</h3><p>Yeni arzlar, talep toplama tarihleri, fiyat aralıkları ve şirket detayları.</p></article>
            <article class="pv-corp-feature"><div class="pv-corp-icon">%</div><h3>Kredi ve faiz gündemi</h3><p>Kredi faizleri, mevduat oranları ve finansal hesaplama araçlarından öne çıkanlar.</p></article>
          </div>
        </section>
        <?php pv_v252_latest_posts( 'Bültenden Önce Okunacaklar' ); ?>
        <?php pv_v252_page_content(); ?>
      </div>
      <?php pv_v252_side_nav( 'bulten' ); ?>
    </div>
  </div>
</main>
<?php get_footer(); ?>
