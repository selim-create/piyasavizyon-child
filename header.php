<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<?php
$pv_path = isset($_SERVER['REQUEST_URI']) ? trim((string) wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH), '/') : '';

$pv_latest = get_posts(array(
    'post_type' => 'post',
    'posts_per_page' => 5,
    'ignore_sticky_posts' => true,
    'no_found_rows' => true,
));
$pv_headlines = array();
foreach ($pv_latest as $pv_post) { $pv_headlines[] = get_the_title($pv_post); }
if (empty($pv_headlines)) { $pv_headlines = array('Ekonomide günün öne çıkanları', 'BIST 100 canlı takipte', 'Halka arz takvimi güncellendi'); }

$pv_logo_file = 'piyasavizyon-logo.svg';
$pv_logo_path = get_stylesheet_directory() . '/assets/img/' . $pv_logo_file;
$pv_logo_url  = get_stylesheet_directory_uri() . '/assets/img/' . $pv_logo_file;
if (file_exists($pv_logo_path)) {
    $pv_logo_url = add_query_arg('v', filemtime($pv_logo_path), $pv_logo_url);
}

// Compact sticky state uses the square PV/favicon mark. If the exact favicon asset is later replaced
// with the production file under this name, the header will pick it up automatically.
$pv_mark_file = 'piyasavizyon-mark.svg';
$pv_mark_path = get_stylesheet_directory() . '/assets/img/' . $pv_mark_file;
$pv_mark_url  = get_stylesheet_directory_uri() . '/assets/img/' . $pv_mark_file;
if (file_exists($pv_mark_path)) {
    $pv_mark_url = add_query_arg('v', filemtime($pv_mark_path), $pv_mark_url);
} else {
    $pv_mark_url = $pv_logo_url;
}

$pv_user = is_user_logged_in() ? wp_get_current_user() : null;
$pv_user_name = $pv_user ? ($pv_user->display_name ?: $pv_user->user_login) : '';

function pv_h_icon_svg($key) {
    $icons = array(
        'news' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h10"/></svg>',
        'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M4 19V5"/><path d="M4 19h16"/><path d="m7 15 4-4 3 3 5-7"/></svg>',
        'bars' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M5 19h14"/><path d="M7 17V9"/><path d="M12 17V5"/><path d="M17 17v-7"/></svg>',
        'money' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M12 3v18"/><path d="M17 7.5c0-1.7-2.2-3-5-3s-5 1.3-5 3 2.2 3 5 3 5 1.3 5 3-2.2 3-5 3-5-1.3-5-3"/></svg>',
        'gold' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M12 3 3 10l9 11 9-11-9-7z"/></svg>',
        'finance' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/><path d="M7 7h10"/><path d="M9 17h6"/></svg>',
        'ipo' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M12 21a9 9 0 1 0-9-9"/><path d="M12 3v9l6 3"/></svg>',
        'crypto' => '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M9 3v18"/><path d="M15 3v18"/><path d="M7 7h8a3 3 0 0 1 0 6H7"/><path d="M7 13h9a3 3 0 0 1 0 6H7"/></svg>',
        'chev' => '<svg class="pv-h-chevron" viewBox="0 0 24 24" fill="none" stroke-width="2.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>',
    );
    return $icons[$key] ?? $icons['news'];
}

$pv_nav_items = array(
    array('label'=>'Gündem','url'=>'/gundem/','icon'=>'news','paths'=>array('gundem','dunya','son-dakika'),'subs'=>array(
        'Dünya'=>'/dunya/','Çevre'=>'/gundem/cevre/','Eğitim'=>'/gundem/egitim/','Teknoloji'=>'/gundem/teknoloji/','Dijital'=>'/gundem/dijital/','Çalışma Hayatı'=>'/gundem/calisma-hayati/','Yaşam'=>'/gundem/yasam/'
    )),
    array('label'=>'Ekonomi','url'=>'/ekonomi/','icon'=>'chart','paths'=>array('ekonomi','piyasalar/emlak','piyasalar/enerji-habeleri'),'subs'=>array(
        'Politika'=>'/ekonomi/politika-haberleri/','Sanayi'=>'/ekonomi/sanayi-haberleri/','Yatırımcı'=>'/ekonomi/yatirimci-haberler/','Girişim'=>'/ekonomi/girisim-haberleri/','Şirket'=>'/ekonomi/sirket-haberleri/','Kobi'=>'/ekonomi/kobi-haberleri/','Sektörel'=>'/ekonomi/sektorel-haberler/','Perakende'=>'/ekonomi/perakende/','Tarım'=>'/ekonomi/tarim-haberleri/','Gıda'=>'/ekonomi/gida/','Vergi'=>'/ekonomi/vergi/','Tekstil'=>'/ekonomi/tekstil/','Turizm'=>'/ekonomi/turizm/','Emlak'=>'/piyasalar/emlak/','Enerji'=>'/piyasalar/enerji-habeleri/'
    )),
    array('label'=>'Borsa','url'=>'/borsa/','icon'=>'bars','paths'=>array('borsa','canli-borsa','tum-hisseler','hisse','tum-endeksler','endeks','tum-pariteler','ekonomik-takvim','piyasalar/borsa-haberleri'),'subs'=>array(
        'Hisse Senetleri'=>'/tum-hisseler/','Endeks'=>'/tum-endeksler/','Pariteler'=>'/tum-pariteler/','Ekonomik Takvim'=>'/ekonomik-takvim/','Borsa Haberleri'=>'/piyasalar/borsa-haberleri/'
    )),
    array('label'=>'Döviz','url'=>'/doviz-kurlari/','icon'=>'money','paths'=>array('doviz-kurlari','doviz-cevirici','doviz-arsiv','doviz','piyasalar/doviz-haberleri'),'subs'=>array(
        'Döviz Çevirici'=>'/doviz-cevirici/','Döviz Arşivi'=>'/doviz-arsiv/','Döviz Haberleri'=>'/piyasalar/doviz-haberleri/'
    )),
    array('label'=>'Altın','url'=>'/altin-fiyatlari/','icon'=>'gold','paths'=>array('altin-fiyatlari','altin','piyasalar/altin-haberleri'),'subs'=>array(
        'Altın Haberleri'=>'/piyasalar/altin-haberleri/'
    )),
    array('label'=>'Finans','url'=>'/finans/','icon'=>'finance','paths'=>array('finans','kredi','kredi-hesapla','ihtiyac-kredisi','konut-kredisi','tasit-kredisi','kobi-kredisi','faiz-oranlari','mevduat-oranlari'),'subs'=>array(
        'Bankacılık'=>'/finans/bankacilik/','Kredi'=>'/finans/kredi-haberleri/','Faiz'=>'/finans/faiz/','Kredi Hesapla'=>'/kredi/'
    )),
    array('label'=>'Halka Arz','url'=>'/halka-arz-takvimi/','icon'=>'ipo','paths'=>array('halka-arz','halka-arz-takvimi','gelecek-halka-arzlar','finans/halka-arz-haberleri'),'subs'=>array(
        'Halka Arz Haberleri'=>'/finans/halka-arz-haberleri/'
    )),
    array('label'=>'Kripto Para','url'=>'/kriptoparalar/','icon'=>'crypto','paths'=>array('kripto-para','kripto-paralar','kriptoparalar','coin','piyasalar/kripto-para-haberleri'),'subs'=>array(
        'Kripto Para Haberleri'=>'/piyasalar/kripto-para-haberleri/'
    )),
);
?>
<header class="pv-header-v260" id="pvHeaderV260" aria-label="PiyasaVizyon header">
  <section class="pv-h-command" aria-label="Üst bilgi">
    <div class="pv-h-wrap pv-h-command-inner">
      <div class="pv-h-command-left">
        <span class="pv-h-today"><?php echo esc_html(date_i18n('j F Y l')); ?></span>
        <a class="pv-h-breaking" href="<?php echo esc_url(home_url('/son-dakika/')); ?>">Son Dakika</a>
      </div>
      <div class="pv-h-marquee" aria-label="Öne çıkan haberler">
        <span><?php echo esc_html(implode(' • ', $pv_headlines) . ' • ' . implode(' • ', $pv_headlines)); ?></span>
      </div>
      <div class="pv-h-command-right">
        <a href="<?php echo esc_url(home_url('/hakkimizda/')); ?>">Hakkımızda</a>
        <a href="<?php echo esc_url(home_url('/reklam/')); ?>">Reklam</a>
        <a href="<?php echo esc_url(home_url('/iletisim/')); ?>">İletişim</a>
        <a href="<?php echo esc_url(home_url('/bulten/')); ?>">Bültene Katıl</a>
        <a href="<?php echo esc_url(home_url('/mobil/')); ?>">Mobil Uygulama</a>
      </div>
    </div>
  </section>

  <section class="pv-h-identity" aria-label="Site kimliği ve arama">
    <div class="pv-h-wrap pv-h-identity-inner">
      <a class="pv-h-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <img class="pv-h-logo-full" src="<?php echo esc_url($pv_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" width="270" height="72">
        <img class="pv-h-logo-mark" src="<?php echo esc_url($pv_mark_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" width="52" height="52" aria-hidden="true">
      </a>

      <form class="pv-h-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" name="s" placeholder="Borsa, dolar, halka arz, kredi veya haber ara..." autocomplete="off" value="<?php echo esc_attr(get_search_query()); ?>">
        <button type="submit">Ara</button>
      </form>

      <nav class="pv-h-identity-menu" aria-label="Sticky menü">
        <?php foreach ($pv_nav_items as $pv_item) :
          $pv_active = false;
          foreach ($pv_item['paths'] as $pv_check) {
            if ($pv_path === trim($pv_check, '/') || strpos($pv_path, trim($pv_check, '/') . '/') === 0) { $pv_active = true; break; }
          }
        ?>
          <div class="pv-h-identity-menu-item <?php echo $pv_active ? 'is-active' : ''; ?>">
            <a class="pv-h-identity-menu-link" href="<?php echo esc_url(home_url($pv_item['url'])); ?>">
              <?php echo pv_h_icon_svg($pv_item['icon']); ?>
              <span><?php echo esc_html($pv_item['label']); ?></span>
              <?php echo pv_h_icon_svg('chev'); ?>
            </a>
            <?php if (!empty($pv_item['subs'])) : ?>
              <div class="pv-h-identity-submenu">
                <?php foreach ($pv_item['subs'] as $pv_sub_label => $pv_sub_url) : ?>
                  <a href="<?php echo esc_url(home_url($pv_sub_url)); ?>"><?php echo esc_html($pv_sub_label); ?></a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </nav>

      <div class="pv-h-actions">
        <a class="pv-h-live" href="<?php echo esc_url(home_url('/canli-borsa/')); ?>"><span></span>Canlı Borsa</a>
        <?php if (is_user_logged_in()) : $u = wp_get_current_user(); ?>
          <div class="pv-profile-menu pv-h-profile-wrap" id="pvHeaderProfile">
            <button class="pv-profile-trigger pv-h-user-chip pv-h-avatar-only" type="button" aria-haspopup="true" aria-expanded="false" aria-label="Profil menüsünü aç">
              <?php echo get_avatar(get_current_user_id(), 38); ?>
            </button>
            <div class="pv-profile-dropdown pv-h-profile-menu" role="menu">
              <a href="<?php echo esc_url(get_author_posts_url(get_current_user_id())); ?>"><i class="fa-regular fa-user" aria-hidden="true"></i> Profilim</a>
              <a href="<?php echo esc_url(home_url('/uye-profili/')); ?>"><i class="fa-solid fa-gear" aria-hidden="true"></i> Ayarlar</a>
              <a href="<?php echo esc_url(home_url('/uye-profil-fotografi/')); ?>"><i class="fa-regular fa-image" aria-hidden="true"></i> Fotoğraf</a>
              <a href="<?php echo esc_url(home_url('/uye-alarm-sayfasi/')); ?>"><i class="fa-regular fa-bell" aria-hidden="true"></i> Alarmlar</a>
              <a href="<?php echo esc_url(home_url('/uye-listesi/')); ?>"><i class="fa-regular fa-bookmark" aria-hidden="true"></i> Listelerim</a>
              <a class="pv-h-logout" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Çıkış Yap</a>
            </div>
          </div>
        <?php else : ?>
          <a class="pv-h-user-chip pv-h-login-chip pv-h-avatar-only" href="<?php echo esc_url(home_url('/giris/')); ?>" aria-label="Giriş yap">
            <span class="pv-h-user-avatar"><i class="fa-regular fa-user" aria-hidden="true"></i></span>
          </a>
        <?php endif; ?>
        <button class="pv-h-mobile-toggle" type="button" aria-controls="pvHeaderMobilePanel" aria-expanded="false" aria-label="Menüyü aç"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
      </div>
    </div>
  </section>
  <div class="pv-h-identity-spacer" aria-hidden="true"></div>

  <nav class="pv-h-rail" aria-label="Ana menü">
    <div class="pv-h-wrap pv-h-rail-inner">
      <a class="pv-h-sticky-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <img src="<?php echo esc_url($pv_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" width="220" height="58">
      </a>
      <div class="pv-h-rail-menu">
        <?php foreach ($pv_nav_items as $pv_item) :
          $pv_active = false;
          foreach ($pv_item['paths'] as $pv_check) {
            if ($pv_path === trim($pv_check, '/') || strpos($pv_path, trim($pv_check, '/') . '/') === 0) { $pv_active = true; break; }
          }
        ?>
          <div class="pv-h-rail-item <?php echo $pv_active ? 'is-active' : ''; ?>">
            <a class="pv-h-rail-link" href="<?php echo esc_url(home_url($pv_item['url'])); ?>">
              <?php echo pv_h_icon_svg($pv_item['icon']); ?>
              <span><?php echo esc_html($pv_item['label']); ?></span>
              <?php echo pv_h_icon_svg('chev'); ?>
            </a>
            <?php if (!empty($pv_item['subs'])) : ?>
              <div class="pv-h-submenu">
                <?php foreach ($pv_item['subs'] as $pv_sub_label => $pv_sub_url) : ?>
                  <a href="<?php echo esc_url(home_url($pv_sub_url)); ?>"><?php echo esc_html($pv_sub_label); ?></a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="pv-h-sticky-actions">
        <a class="pv-h-rail-live" href="<?php echo esc_url(home_url('/canli-borsa/')); ?>">Canlı Borsa</a>
        <button class="pv-search-toggle pv-h-rail-search" type="button" aria-label="Ara" aria-expanded="false"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="pv-search-panel pv-h-search-panel" aria-hidden="true">
      <div class="pv-h-wrap">
        <form class="pv-search-form pv-h-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          <input type="search" name="s" placeholder="Borsa, dolar, halka arz, kredi veya haber ara..." autocomplete="off" value="<?php echo esc_attr(get_search_query()); ?>">
          <button type="submit">Ara</button>
        </form>
      </div>
    </div>
  </nav>

  <section class="pv-h-mobile-panel" id="pvHeaderMobilePanel" aria-hidden="true">
    <div class="pv-h-wrap pv-h-mobile-grid">
      <form class="pv-h-mobile-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <input type="search" name="s" placeholder="Haber, hisse, döviz ara...">
        <button type="submit">Ara</button>
      </form>
      <?php foreach ($pv_nav_items as $pv_item) : ?>
        <a href="<?php echo esc_url(home_url($pv_item['url'])); ?>"><?php echo pv_h_icon_svg($pv_item['icon']); ?><?php echo esc_html($pv_item['label']); ?></a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="pv-h-market" aria-label="Canlı piyasa verileri">
    <div class="pv-h-wrap pv-h-market-board">
      <div class="pv-h-market-scroll" id="pvHeaderTicker">
        <?php foreach (pv_v7_ticker_items() as $pv_i => $pv_item) : $pv_cls = pv_v7_market_classes($pv_item['rate']); ?>
          <a class="<?php echo $pv_i === 0 ? 'pv-h-market-feature' : 'pv-h-market-tile'; ?> <?php echo esc_attr($pv_cls); ?>" href="<?php echo esc_url($pv_item['url']); ?>" data-symbol="<?php echo esc_attr(strtoupper($pv_item['key'] ?: $pv_item['name'])); ?>">
            <small><?php echo esc_html($pv_item['name']); ?></small>
            <b><?php echo esc_html($pv_item['value']); ?></b>
            <em><?php echo $pv_cls === 'down' ? '▼' : '▲'; ?> %<?php echo esc_html(pv_v7_num($pv_item['rate'])); ?></em>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</header>
