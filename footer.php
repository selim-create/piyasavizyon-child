<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php
$pv_footer_market_items = array_slice( pv_v7_ticker_items(), 0, 5 );
$pv_footer_network_sites = array(
    array( 'Piyasa Vizyon', 'https://piyasavizyon.com', 'Ekonomi & Finans' ),
    array( 'Haber Notları', 'https://www.habernotlari.com', 'Haber' ),
    array( 'Sektörel Ajanda', 'https://www.sektorelagenda.com', 'Sektörel Haber' ),
    array( 'Ajanda Mag', 'https://www.ajandamag.com', 'Ajanda' ),
    array( 'Genç Mag', 'https://www.gencmag.com', 'Gençlik' ),
    array( 'Favorimag', 'https://www.favorimag.com/', 'Magazin' ),
    array( 'Luxexmag', 'https://www.luxexmag.com', 'Lüks Yaşam' ),
    array( 'Cinesos', 'https://www.cinesos.com', 'Sinema' ),
    array( 'Home Trendsetter', 'https://www.hometrendsetter.com/', 'Ev & Dekorasyon' ),
    array( 'Hip Tekno', 'https://www.hiptekno.com', 'Teknoloji' ),
    array( 'Hipinup', 'https://www.hipinup.com/', 'Dijital Kültür' ),
    array( 'Tepkisel', 'https://www.tepkisel.com/', 'Sosyal İçerik' ),
    array( 'Tariften', 'https://www.tariften.com', 'Gastronomi' ),
    array( 'KidsGourmet', 'https://www.kidsgourmet.com.tr', 'Çocuk & Yemek' ),
    array( 'Doktorland', 'https://doktorland.com', 'Sağlık' ),
    array( 'Rejimde', 'https://www.rejimde.com/', 'Sağlıklı Yaşam' ),
    array( 'DirektSpor', 'https://direktspor.com/', 'Spor' ),
    array( 'Bi Otomobil', 'https://www.biotomobil.com', 'Otomotiv' ),
    array( 'Üniversite Burada', 'https://www.universiteburada.com', 'Eğitim' ),
    array( 'Patikolik', 'https://www.patikolik.com', 'Evcil Hayvan' ),
    array( 'İndirim Kap', 'https://www.indirimkap.com', 'Alışveriş' ),
);
?>
<footer class="pv-footer pv-footer-v250" aria-label="Site alt bilgi alanı">
  <section class="pv-footer-market" aria-label="Piyasa verileri">
    <div class="pv-footer-wrap pv-footer-market-grid">
      <?php foreach ( $pv_footer_market_items as $item ) : $cls = pv_v7_market_classes( $item['rate'] ); ?>
        <a class="pv-footer-market-item" href="<?php echo esc_url( $item['url'] ); ?>">
          <small><?php echo esc_html( mb_strtoupper( $item['name'], 'UTF-8' ) ); ?></small>
          <b><?php echo esc_html( $item['value'] ); ?></b>
          <em class="<?php echo esc_attr( $cls ); ?>"><?php echo $cls === 'down' ? '▼' : '▲'; ?> %<?php echo esc_html( pv_v7_num( $item['rate'] ) ); ?></em>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="pv-footer-identity-section">
    <div class="pv-footer-wrap pv-footer-identity-grid">
      <article class="pv-footer-identity-card pv-footer-brand-card">
        <a class="pv-footer-brand-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="PiyasaVizyon ana sayfa">
          <?php pv_v7_footer_logo( 'pv' ); ?>
        </a>
        <h2>Ekonomi ve finans dünyasını tek ekranda takip edin.</h2>
        <p>PiyasaVizyon; borsa, döviz, altın, kripto para, halka arzlar, kredi ürünleri ve ekonomi haberlerini gerçek zamanlı verilerle bir araya getiren dijital finans yayın platformudur.</p>

        <div class="pv-footer-socials" aria-label="Sosyal medya bağlantıları">
          <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M14 8h3V4h-3c-3.1 0-5 1.9-5 5v2H6v4h3v5h4v-5h3.2l.8-4h-4V9c0-.7.3-1 1-1z"/></svg></a>
          <a href="#" aria-label="X"><svg viewBox="0 0 24 24"><path d="M4 4h4.7l3.5 5 4.3-5H21l-6.7 7.7L21 20h-4.7l-4-5.6L7.5 20H3l7.1-8.2L4 4zm3.1 2 10.2 12h1.5L8.6 6H7.1z"/></svg></a>
          <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M7.5 3h9A4.5 4.5 0 0 1 21 7.5v9a4.5 4.5 0 0 1-4.5 4.5h-9A4.5 4.5 0 0 1 3 16.5v-9A4.5 4.5 0 0 1 7.5 3zm0 2A2.5 2.5 0 0 0 5 7.5v9A2.5 2.5 0 0 0 7.5 19h9a2.5 2.5 0 0 0 2.5-2.5v-9A2.5 2.5 0 0 0 16.5 5h-9zM12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg></a>
          <a href="#" aria-label="LinkedIn"><svg viewBox="0 0 24 24"><path d="M6.5 8.5H3.2V21h3.3V8.5zM4.9 3a1.9 1.9 0 1 0 0 3.8A1.9 1.9 0 0 0 4.9 3zM21 14.2c0-3.4-1.8-5-4.2-5-1.9 0-2.8 1.1-3.3 1.8V8.5h-3.2V21h3.3v-6.2c0-1.6.3-3.2 2.3-3.2s2 1.8 2 3.3V21H21v-6.8z"/></svg></a>
          <a href="#" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M21 8.2s-.2-1.5-.8-2.1c-.8-.8-1.7-.8-2.1-.9C15.2 5 12 5 12 5s-3.2 0-6.1.2c-.4.1-1.3.1-2.1.9C3.2 6.7 3 8.2 3 8.2S2.8 10 2.8 12 3 15.8 3 15.8s.2 1.5.8 2.1c.8.8 1.8.8 2.3.9 1.7.2 5.9.2 5.9.2s3.2 0 6.1-.2c.4-.1 1.3-.1 2.1-.9.6-.6.8-2.1.8-2.1s.2-1.8.2-3.8S21 8.2 21 8.2zM10 15V9l5.2 3L10 15z"/></svg></a>
          <a href="#" aria-label="Telegram"><svg viewBox="0 0 24 24"><path d="M21.6 4.4 18.4 20c-.2 1-.8 1.2-1.6.7l-4.5-3.3-2.2 2.1c-.2.2-.4.4-.9.4l.3-4.7 8.6-7.8c.4-.3-.1-.5-.6-.2L6.9 13.9 2.3 12.5c-1-.3-1-1 .2-1.5L20.4 4c.8-.3 1.5.2 1.2 1.4z"/></svg></a>
        </div>

        <div class="pv-footer-warning-box">
          <div class="pv-footer-warning-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 3 4 6.5v5.7c0 4.9 3.3 7.4 8 8.8 4.7-1.4 8-3.9 8-8.8V6.5L12 3z"/><path d="m8.8 12 2.1 2.1 4.4-4.6"/></svg></div>
          <div><b>Yatırım Uyarısı</b><span>Burada yer alan veriler yatırım tavsiyesi değildir. Finansal kararlarınızı kendi araştırmanız ve yetkili uzman görüşleri doğrultusunda vermeniz önerilir.</span></div>
        </div>
      </article>

      <article class="pv-footer-identity-card pv-footer-hip-card">
        <a class="pv-footer-hip-logo" href="https://hipmedya.com/" target="_blank" rel="noopener" aria-label="Hip Medya">
          <?php pv_v7_footer_logo( 'hip' ); ?>
        </a>
        <span class="pv-footer-hip-badge">Bir Hip Medya Yayınıdır</span>
        <h2>İçerik. Teknoloji. Etki.</h2>
        <p>Hip Medya; içerik üretimi, dijital yayıncılık ve medya teknolojileri alanlarında faaliyet gösteren yeni nesil medya şirketidir.</p>
        <a class="pv-footer-hip-button" href="https://hipmedya.com/" target="_blank" rel="noopener">Hip Medya'yı Keşfet →</a>
      </article>
    </div>
  </section>

  <section class="pv-footer-mid-section">
    <div class="pv-footer-wrap pv-footer-mid-grid">
      <div class="pv-footer-quick-card">
        <div class="pv-footer-section-head"><span class="pv-footer-icon-circle"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M13 2 4 14h7l-1 8 10-13h-7l1-7z"/></svg></span><h3>Hızlı Erişim</h3></div>
        <div class="pv-footer-quick-grid">
          <a class="pv-footer-quick-link" href="<?php echo esc_url( home_url( '/borsa/' ) ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M4 19V5"/><path d="M4 19h16"/><path d="m7 15 4-4 3 3 5-7"/></svg>Borsa</a>
          <a class="pv-footer-quick-link" href="<?php echo esc_url( home_url( '/doviz/' ) ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 3v18"/><path d="M17 7.5c0-1.7-2.2-3-5-3s-5 1.3-5 3 2.2 3 5 3 5 1.3 5 3-2.2 3-5 3-5-1.3-5-3"/></svg>Döviz</a>
          <a class="pv-footer-quick-link" href="<?php echo esc_url( home_url( '/altin/' ) ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 3 3 10l9 11 9-11-9-7z"/></svg>Altın</a>
          <a class="pv-footer-quick-link" href="<?php echo esc_url( home_url( '/kripto-para/' ) ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M9 3v18"/><path d="M15 3v18"/><path d="M7 7h8a3 3 0 0 1 0 6H7"/><path d="M7 13h9a3 3 0 0 1 0 6H7"/></svg>Kripto Para</a>
          <a class="pv-footer-quick-link" href="<?php echo esc_url( home_url( '/halka-arz/' ) ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 21a9 9 0 1 0-9-9"/><path d="M12 3v9l6 3"/></svg>Halka Arz</a>
          <a class="pv-footer-quick-link" href="<?php echo esc_url( home_url( '/kredi-hesapla/' ) ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h3"/></svg>Kredi Hesaplama</a>
        </div>
      </div>

      <div class="pv-footer-newsletter-card">
        <h3>Piyasa bültenine abone olun</h3>
        <p>Günün öne çıkan piyasa gelişmeleri, halka arzlar, döviz-altın hareketleri ve ekonomi haberleri e-posta kutunuza gelsin.</p>
        <form class="pv-footer-subscribe" action="#" method="post"><input type="email" placeholder="E-posta adresiniz" aria-label="E-posta adresiniz"><button type="button">Abone Ol</button></form>
      </div>
    </div>
  </section>

  <section class="pv-footer-network-section">
    <div class="pv-footer-wrap">
      <div class="pv-footer-network-head"><h3>Hip Medya Yayın Ağı</h3><p>Hip Medya ekosistemindeki yayınlara hızlı erişim.</p></div>
      <div class="pv-footer-network-grid">
        <?php foreach ( $pv_footer_network_sites as $site ) : ?>
          <a href="<?php echo esc_url( $site[1] ); ?>" target="_blank" rel="noopener"><span><?php echo esc_html( $site[0] ); ?></span><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="pv-footer-bottom-section">
    <div class="pv-footer-wrap pv-footer-bottom-grid">
      <div class="pv-footer-bottom-group"><h4>Kurumsal</h4><div class="pv-footer-bottom-links"><a href="<?php echo esc_url( home_url( '/hakkimizda/' ) ); ?>">Hakkımızda</a><span>•</span><a href="<?php echo esc_url( home_url( '/kunye/' ) ); ?>">Künye</a><span>•</span><a href="<?php echo esc_url( home_url( '/iletisim/' ) ); ?>">İletişim</a></div></div>
      <div class="pv-footer-bottom-group"><h4>Yasal</h4><div class="pv-footer-bottom-links"><button type="button" id="pvOpenDisclaimer">Sorumluluk Reddi ⓘ</button><span>•</span><a href="<?php echo esc_url( home_url( '/cerez-politikasi/' ) ); ?>">Çerez Politikası</a><span>•</span><a href="<?php echo esc_url( home_url( '/kullanim-sartlari/' ) ); ?>">Kullanım Koşulları</a><span>•</span><a href="<?php echo esc_url( home_url( '/aydinlatma-metni/' ) ); ?>">Aydınlatma Metni</a><span>•</span><a href="<?php echo esc_url( home_url( '/kvkk-aydinlatma-metni/' ) ); ?>">KVKK</a></div></div>
      <div class="pv-footer-copy">© <?php echo esc_html( date( 'Y' ) ); ?> <b>PiyasaVizyon</b><br>Bir Hip Medya Yayınıdır.</div>
    </div>
  </section>
</footer>

<div class="pv-footer-modal-backdrop" id="pvDisclaimerModal" aria-hidden="true">
  <div class="pv-footer-modal" role="dialog" aria-modal="true" aria-labelledby="pvDisclaimerTitle">
    <div class="pv-footer-modal-head">
      <div class="pv-footer-doc-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M8 13h8M8 17h8M8 9h3"/></svg></div>
      <div><h3 id="pvDisclaimerTitle">Sorumluluk Reddi Beyanı</h3><p>Son güncelleme: 01 Aralık, 2023</p></div>
      <button class="pv-footer-modal-close" type="button" id="pvCloseDisclaimer" aria-label="Kapat">×</button>
    </div>
    <div class="pv-footer-modal-body">
      <hr>
      <p>Piyasa verileri FOREX Bilgi İletişim Hizmetleri A.Ş. tarafından sağlanmaktadır. BIST hisse Senedi verileri 15 dakika, VİOP, Tahvil-Bono-Repo özet verileri 15 dakika gecikmeli olarak yansır.</p>
      <p>Sitede yer alan bilgi, yorum, haber ve tavsiyeler yatırım danışmanlığı kapsamında değildir. Yatırım danışmanlığı hizmetleri aracı kurumlar, portföy yönetim şirketleri, mevduat kabul etmeyen bankalar ile müşteri arasında imzalanacak “Yatırım Danışmanlığı Sözleşmesi” çerçevesince sunulmaktadır. Burada yer alan yorum, haber, bilgi ve tavsiyeler bu kapsamda değerlendirilemez. Bu nedenle sitede yer alan bilgiler mali durumunuz ile risk ve getiri tercihlerinize uygun olmayabilir. Sadece sitedeki bilgilere dayanılarak yatırım kararı verilmesi beklentilerinize uygun sonuçlar doğurmayabilir.</p>
      <p>Burada yer alan bilgiler, güvenilir olduğuna inanılan halka açık kaynaklardan elde edilmiş olup bu kaynaklardaki bilgilerin hata ve eksikliğinden ve ticari amaçlı işlemlerde kullanılmasından doğabilecek zararlardan piyasavizyon.com yöneticileri hiçbir şekilde sorumlu tutulamaz.</p>
      <p>Tüm borsa fiyatları, endeksler, vadeli işlemler, FOREX ve kripto para fiyatları piyasa düzenleyicileri tarafından oluşturulur. Bu nedenle fiyatlar isabetli olmayabilir ve gerçek piyasa/borsa fiyatlarından farklı olabilir. Bu sebeple piyasavizyon.com veya herhangi bir sağlayıcı, sitedeki bilgilerin kullanılması sonucu oluşabilecek olası risklerden ötürü sorumlu tutulamaz. Fikri mülkiyet hakkı, sitede yer alan verileri sağlayanlara ve/veya borsalara aittir.</p>
      <p>BIST isim ve logosu “Koruma Marka Belgesi” altında korunmakta olup izinsiz kullanılamaz, iktibas edilemez, değiştirilemez. BIST ismi altında açıklanan tüm bilgilerin telif hakları tamamen BIST’e ait olup tekrar yayınlanamaz. BIST veri yayınında oluşabilecek aksaklıklar, verinin ulaşmaması, gecikmesi, eksik ulaşması, yanlış olması, veri yayın sistemindeki performansın düşmesi veya kesintili olması gibi hallerde Alıcı, Alt Alıcı ve/veya kullanıcılarda oluşabilecek herhangi bir zarardan BIST sorumlu değildir.</p>
    </div>
    <div class="pv-footer-modal-actions"><button class="pv-footer-understood" type="button" id="pvUnderstoodDisclaimer">Anladım</button></div>
  </div>
</div>

<div class="crypto-sticky"><div class="crypto-track">
  <?php $coins = array_filter( pv_v7_ticker_items(), function( $it ) { return in_array( $it['type'], array( 'coin', 'doviz', 'altin', 'bist' ), true ); } ); $coins = array_merge( $coins, $coins, $coins ); foreach ( $coins as $item ) : $cls = pv_v7_market_classes( $item['rate'] ); ?><a href="<?php echo esc_url( $item['url'] ); ?>"><span class="coin"><?php echo esc_html( mb_substr( $item['name'], 0, 1, 'UTF-8' ) ); ?></span><?php echo esc_html( $item['name'] . ' ' . $item['value'] ); ?> <span class="<?php echo esc_attr( $cls ); ?>"><?php echo $cls === 'down' ? '▼' : '▲'; ?> %<?php echo esc_html( pv_v7_num( $item['rate'] ) ); ?></span></a><?php endforeach; ?>
</div></div>
<script id="pv-converter-rates" type="application/json"><?php echo wp_json_encode( pv_v7_converter_json() ); ?></script>
<?php wp_footer(); ?>
</body></html>
