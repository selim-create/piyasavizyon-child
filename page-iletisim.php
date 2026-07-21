<?php
/* Template Name: PiyasaVizyon - İletişim */
require_once get_stylesheet_directory() . '/template-corporate-parts.php';
get_header();
?>
<main class="pv-corp pv-corp-contact-page pv-corp-no-ads">
  <div class="pv-corp-wrap">
    <section class="pv-contact-hero-new">
      <div>
        <span class="pv-corp-kicker">İletişim</span>
        <h1>PiyasaVizyon ve HİP Medya ekiplerine ulaşın.</h1>
        <p>Haber, reklam, bülten, iş birliği ve kurumsal talepleriniz için doğru iletişim kanalını seçerek bize ulaşabilirsiniz.</p>
        <div class="pv-contact-hero-actions"><a href="#pv-contact-form" class="pv-corp-btn pv-corp-btn-primary">E-posta Gönder</a><a href="tel:08504501105" class="pv-corp-btn">0850 450 11 05</a></div>
      </div>
      <div class="pv-contact-card-main">
        <span>HİP Medya</span>
        <a href="https://www.hipmedya.com/" target="_blank" rel="noopener">www.hipmedya.com</a>
        <a href="mailto:iletisim@hipmedya.com">iletisim@hipmedya.com</a>
        <a href="tel:08504501105">0850 450 11 05</a>
      </div>
    </section>

    <div class="pv-corp-grid">
      <div class="pv-corp-main">
        <section class="pv-corp-card">
          <div class="pv-corp-section-head"><div><h2>İletişim Kanalları</h2><p>Talebinizin ilgili ekibe daha hızlı ulaşması için aşağıdaki iletişim başlıklarını kullanabilirsiniz.</p></div><span class="pv-corp-badge">Hızlı Erişim</span></div>
          <div class="pv-contact-channel-grid">
            <a class="pv-contact-channel" href="mailto:iletisim@hipmedya.com"><span>GENEL</span><h3>Genel İletişim</h3><p>Site, kurumsal bilgi, teknik bildirim ve genel talepler.</p><b>iletisim@hipmedya.com</b></a>
            <a class="pv-contact-channel" href="mailto:iletisim@hipmedya.com?subject=Reklam%20Talebi"><span>REKLAM</span><h3>Reklam</h3><p>Banner, sponsorluk, native içerik ve medya planlama.</p><b>iletisim@hipmedya.com</b></a>
            <a class="pv-contact-channel" href="mailto:iletisim@hipmedya.com?subject=Haber%20Talebi"><span>HABER</span><h3>Haber</h3><p>Haber önerisi, editoryal bildirim ve düzeltme talepleri.</p><b>iletisim@hipmedya.com</b></a>
            <a class="pv-contact-channel" href="mailto:bulten@hipmedya.com"><span>BÜLTEN</span><h3>Bülten Gönderimleri</h3><p>Bülten, duyuru ve özel gönderim süreçleri.</p><b>bulten@hipmedya.com</b></a>
          </div>
        </section>

        <section class="pv-corp-card pv-contact-form-card" id="pv-contact-form">
          <div class="pv-corp-section-head"><div><h2>Mesajınızı Hazırlayın</h2><p>Formu doldurduğunuzda varsayılan e-posta uygulamanız açılır ve mesaj taslağı oluşturulur.</p></div><span class="pv-corp-badge">Mailto</span></div>
          <form class="pv-corp-form pv-contact-mailto-form" action="mailto:iletisim@hipmedya.com" method="post" enctype="text/plain">
            <div class="pv-corp-form-row"><input type="text" name="ad_soyad" placeholder="Ad Soyad" required><input type="email" name="email" placeholder="E-posta" required></div>
            <div class="pv-corp-form-row"><input type="text" name="telefon" placeholder="Telefon"><select name="konu"><option>Genel iletişim</option><option>Reklam</option><option>Haber</option><option>Bülten</option></select></div>
            <textarea name="mesaj" placeholder="Mesajınız" required></textarea>
            <button class="pv-corp-btn pv-corp-btn-dark" type="submit">Mesaj Taslağı Oluştur</button>
          </form>
        </section>
        <?php pv_v252_page_content(); ?>
      </div>
      <?php pv_v252_side_nav( 'iletisim' ); ?>
    </div>
  </div>
</main>
<?php get_footer(); ?>
