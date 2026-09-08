<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php
$pv_footer_market_items = array_slice( pv_v7_ticker_items(), 0, 5 );
$pv_footer_network_sites = array(
    array( 'Sektörel Ajanda', 'https://www.sektorelagenda.com/' ),
    array( 'Home Trendsetter', 'https://www.hometrendsetter.com/' ),
    array( 'Tariften', 'https://www.tariften.com/' ),
    array( 'KidsGourmet', 'https://www.kidsgourmet.com.tr/' ),
    array( 'Rejimde', 'https://www.rejimde.com/' ),
    array( 'DirektSpor', 'https://direktspor.com/' ),
);
?>
<footer class="pv-footer pv-footer-v250 pv-footer-v270" aria-label="Site alt bilgi alanı">
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

  <section class="pv-footer-main">
    <div class="pv-footer-wrap pv-footer-main-grid">
      <div class="pv-footer-brand-block">
        <a class="pv-footer-brand-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="PiyasaVizyon ana sayfa">
          <?php pv_v7_footer_logo( 'pv' ); ?>
        </a>
        <h2>Ekonomiyi rakamların ötesinde takip edin.</h2>
        <p>PiyasaVizyon; piyasa verileri, ekonomi haberleri, halka arzlar ve finansal araçları tek bir yayın deneyiminde bir araya getirir.</p>

        <nav class="pv-footer-primary-links" aria-label="Hızlı erişim">
          <a href="<?php echo esc_url( home_url( '/borsa/' ) ); ?>">Borsa</a>
          <a href="<?php echo esc_url( home_url( '/doviz/' ) ); ?>">Döviz</a>
          <a href="<?php echo esc_url( home_url( '/altin/' ) ); ?>">Altın</a>
          <a href="<?php echo esc_url( home_url( '/kripto-para/' ) ); ?>">Kripto</a>
          <a href="<?php echo esc_url( home_url( '/halka-arz/' ) ); ?>">Halka Arz</a>
          <a href="<?php echo esc_url( home_url( '/kredi-hesapla/' ) ); ?>">Kredi Hesapla</a>
        </nav>

        <div class="pv-footer-warning-inline">
          <span aria-hidden="true">i</span>
          <p><strong>Yatırım uyarısı:</strong> Burada yer alan veriler yatırım tavsiyesi değildir.</p>
        </div>
      </div>

      <div class="pv-footer-newsletter-card">
        <?php if ( function_exists( 'pv_hiposta_render_form' ) ) : ?>
          <?php echo pv_hiposta_render_form( 'piyasavizyon_footer', 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php else : ?>
          <div class="pv-footer-newsletter-fallback">
            <span>PiyasaVizyon Bülteni</span>
            <h3>Ekonomi ve piyasa gündemi, tek bir özette.</h3>
            <p>Bülten aboneliği geçici olarak kullanılamıyor.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="pv-footer-network-section">
    <div class="pv-footer-wrap pv-footer-network-row">
      <div class="pv-footer-network-copy">
        <span>Hip Medya Yayın Ağı</span>
        <p>Farklı ilgi alanları için seçilmiş yayınlar.</p>
      </div>
      <div class="pv-footer-network-links">
        <?php foreach ( $pv_footer_network_sites as $site ) : ?>
          <a href="<?php echo esc_url( $site[1] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $site[0] ); ?></a>
        <?php endforeach; ?>
      </div>
      <a class="pv-footer-network-all" href="https://hipmedya.com/" target="_blank" rel="noopener">Tüm ağı keşfet ↗</a>
    </div>
  </section>

  <section class="pv-footer-bottom-section">
    <div class="pv-footer-wrap pv-footer-bottom-grid">
      <div class="pv-footer-bottom-brand">
        <span>© <?php echo esc_html( date( 'Y' ) ); ?> PiyasaVizyon</span>
        <span>Bir Hip Medya yayınıdır.</span>
      </div>

      <nav class="pv-footer-bottom-links" aria-label="Kurumsal ve yasal bağlantılar">
        <a href="<?php echo esc_url( home_url( '/hakkimizda/' ) ); ?>">Hakkımızda</a>
        <a href="<?php echo esc_url( home_url( '/kunye/' ) ); ?>">Künye</a>
        <a href="<?php echo esc_url( home_url( '/iletisim/' ) ); ?>">İletişim</a>
        <button type="button" id="pvOpenDisclaimer">Sorumluluk Reddi</button>
        <a href="<?php echo esc_url( home_url( '/cerez-politikasi/' ) ); ?>">Çerez</a>
        <a href="<?php echo esc_url( home_url( '/kullanim-sartlari/' ) ); ?>">Kullanım Koşulları</a>
        <a href="<?php echo esc_url( home_url( '/kvkk-aydinlatma-metni/' ) ); ?>">KVKK</a>
      </nav>
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