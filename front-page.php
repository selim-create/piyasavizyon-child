<?php get_header(); ?>
<?php
/* v2.72: Pre-fetch pinned post IDs once so they can be excluded from Haberler and Gündem.
   This replaces the hard-coded offset approach that caused new posts to be skipped. */
$_pv_slider_pre = pv_v7_flagged_posts( 'bf_anasayfa_slider', array( 'posts_per_page' => 3, 'no_found_rows' => true ) );
$_pv_kayan_pre  = pv_v7_flagged_posts( 'bf_anasayfa_kayan',  array( 'posts_per_page' => 4, 'no_found_rows' => true ) );
$pv_excl = array();
if ( ! empty( $_pv_slider_pre->posts ) ) { foreach ( $_pv_slider_pre->posts as $_pv_p ) { $pv_excl[] = (int) $_pv_p->ID; } }
if ( ! empty( $_pv_kayan_pre->posts ) )  { foreach ( $_pv_kayan_pre->posts  as $_pv_p ) { $pv_excl[] = (int) $_pv_p->ID; } }
$pv_excl = array_unique( $pv_excl );
?>
<main class="wrap hero pv-hero-area">
<?php pv_v7_ad('pv_header_ad','970×250 / 970×90 Üst Banner Reklam Alanı','ad ad-970 pv-header-masthead pv-ad-desktop'); ?>
<?php pv_v7_ad('pv_mobile_masthead','320×100 / 320×150 Mobil Masthead Reklam','ad ad-mobile-masthead pv-ad-mobile'); ?>
<section class="top-showcase top-showcase-v73">
  <article class="live-center">
    <div>
      <span class="kicker">CANLI PİYASA MERKEZİ • ANLIK VERİ</span>
      <h1>Piyasalar, haberler ve analizler tek ekranda.</h1>
      <p>Döviz, altın, kripto, BIST, halka arz takvimi, çeviriciler ve editör haberleri modern finans paneli deneyimiyle ana sayfada.</p>
    </div>
    <div class="hero-actions">
      <a href="<?php echo esc_url(home_url('/canli-piyasa/')); ?>">Canlı Piyasaları Aç</a>
      <a href="<?php echo esc_url(home_url('/halka-arz/')); ?>">Halka Arz Takvimi</a>
      <a href="<?php echo esc_url(home_url('/ekonomi/')); ?>">Ekonomi Haberleri</a>
    </div>
  </article>
  <div class="headline-right">
    <div class="headline-grid">
<?php $top=$_pv_slider_pre; if($top->have_posts()): while($top->have_posts()): $top->the_post(); $img=pv_v7_img(get_the_ID(),'medium_large'); ?>
      <a class="story" href="<?php the_permalink(); ?>" <?php if($img) echo 'style="background-image:linear-gradient(to top,rgba(0,0,0,.72),rgba(0,0,0,.05)),url('.esc_url($img).');background-size:cover;background-position:center"'; ?>><span class="story-cat"><?php $cat=get_the_category(); echo esc_html($cat ? $cat[0]->name : 'Haber'); ?></span><h3><?php the_title(); ?></h3></a>
<?php endwhile; wp_reset_postdata(); endif; ?>
    </div>
    <?php pv_v7_ad('pv_right_ad','Piyasa verilerini anlık takip et • Reklam Alanı','ad right-ad below-news-ad'); ?>
  </div>
</section>
</main>

<section class="wrap main"><div class="content">
  <?php $s=$_pv_kayan_pre; ?>
  <div class="slider" id="heroSlider"><div class="dots"><?php for($i=0; $i < (int) $s->post_count; $i++): ?><span class="dot<?php echo $i === 0 ? ' active' : ''; ?>"></span><?php endfor; ?></div>
<?php $active=' active'; if($s->have_posts()): while($s->have_posts()): $s->the_post(); $img=pv_v7_img(get_the_ID(),'large'); $cat=get_the_category(); ?>
<a class="slide<?php echo esc_attr($active); ?>" href="<?php the_permalink(); ?>" <?php if($img) echo 'style="background-image:linear-gradient(to top,rgba(0,0,0,.66),transparent 64%),url('.esc_url($img).');background-size:cover;background-position:center"'; ?>><span class="kicker"><?php echo esc_html($cat ? $cat[0]->name : 'Haber'); ?></span><h2><?php the_title(); ?></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),22)); ?></p></a><?php $active=''; endwhile; wp_reset_postdata(); endif; ?>
  </div>
  <?php pv_v7_gam_content_ad(); ?>

  <section class="panel ipo-panel pv-ipo-original"><div class="pv-ipo-tabbar" data-tabs="ipo"><button class="pv-ipo-tab active" data-tab="takvim">Halka Arz Takvimi</button><button class="pv-ipo-tab" data-tab="gelecek">Gelecek Halka Arzlar <sup class="taslak-number"><?php echo esc_html(pv_v7_ipo_future_count()); ?></sup></button></div><div class="tabpane active" data-pane="takvim"><?php pv_v7_ipo_calendar('takvim'); ?></div><div class="tabpane" data-pane="gelecek"><?php pv_v7_ipo_calendar('gelecek'); ?></div></section>
  <?php pv_v7_gam_content_ad(); ?>

  <section class="widget-grid pv-market-table-grid">
    <section class="panel"><div class="panel-h"><h2>Döviz Piyasası</h2><a class="badge" href="<?php echo esc_url(home_url('/doviz/')); ?>">Tümü</a></div><?php pv_v7_market_table_currency(); ?></section>
    <section class="panel"><div class="panel-h"><h2>Altın Fiyatları</h2><a class="badge" href="<?php echo esc_url(home_url('/altin/')); ?>">Tümü</a></div><?php pv_v7_market_table_gold(); ?></section>
  </section>
  <section class="panel pv-home-stock-volume"><div class="panel-h"><h2>En Çok İşlem Gören Hisseler</h2><span class="badge">BIST</span></div><?php pv_v7_stock_table('volume'); ?></section>
  <?php pv_v7_gam_content_ad(); ?>
  <section class="panel"><div class="panel-h"><h2>Banka ve Kredi Merkezi</h2><a class="badge" href="<?php echo esc_url(home_url('/kredi/')); ?>">Tüm Krediler</a></div><div class="loan-grid"><a class="loan" href="<?php echo esc_url(home_url('/faiz-oranlari/')); ?>"><small>Bankalar</small><b>Mevduat ve oranlar</b><span class="up">Güncel liste</span></a><a class="loan" href="<?php echo esc_url(home_url('/kredi/')); ?>"><small>Kredi</small><b>Kredi karşılaştır</b><span>En iyi oranlar</span></a><a class="loan" href="<?php echo esc_url(home_url('/ihtiyac-kredisi/')); ?>"><small>İhtiyaç Kredisi</small><b>36 ay vade</b><span>Hızlı hesapla</span></a><a class="loan" href="<?php echo esc_url(home_url('/konut-kredisi/')); ?>"><small>Konut Kredisi</small><b>120 ay vade</b><span class="up">En iyi oranlar</span></a><a class="loan" href="<?php echo esc_url(home_url('/tasit-kredisi/')); ?>"><small>Taşıt Kredisi</small><b>Sıfır / ikinci el</b><span>Oran karşılaştır</span></a><a class="loan" href="<?php echo esc_url(home_url('/kobi-kredisi/')); ?>"><small>KOBİ Kredisi</small><b>İşletme finansmanı</b><span>Başvuru rehberi</span></a><a class="loan" href="<?php echo esc_url(home_url('/banka-gise/')); ?>"><small>Banka Şubeleri</small><b>En yakın banka</b><span>Listele</span></a><a class="loan" href="<?php echo esc_url(home_url('/faiz-oranlari/')); ?>"><small>Mevduat</small><b>Vadeli hesap</b><span class="up">Yüksek getiri</span></a></div></section>
  <?php pv_v7_gam_content_ad(); ?>

  <section class="panel"><div class="panel-h"><h2>Haberler</h2><a class="badge" href="<?php echo esc_url(home_url('/haberler/')); ?>">Tümünü Gör</a></div><div class="news-list">
<?php
$_n_args = array( 'posts_per_page' => 7 );
if ( ! empty( $pv_excl ) ) { $_n_args['post__not_in'] = $pv_excl; }
$n = pv_v7_posts( $_n_args );
/* collect Haberler IDs so Gündem doesn't repeat them */
if ( ! empty( $n->posts ) ) { foreach ( $n->posts as $_pv_p ) { $pv_excl[] = (int) $_pv_p->ID; } $pv_excl = array_unique( $pv_excl ); }
if($n->have_posts()): while($n->have_posts()): $n->the_post(); $img=pv_v7_img(get_the_ID()); ?>
<a class="news" href="<?php the_permalink(); ?>"><div class="thumb" <?php if($img) echo 'style="background-image:url('.esc_url($img).');background-size:cover;background-position:center"'; ?>></div><div><h3><?php the_title(); ?></h3><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),24)); ?></p></div></a>
<?php endwhile; wp_reset_postdata(); endif; ?>
</div></section>

  <section class="pv-category-block"><div class="section-title"><h2>Gündem</h2><div class="cat-tabs pv-cat-tabs"><button class="active" type="button" data-cat="all">Gündem</button><button type="button" data-cat="ekonomi">Ekonomi</button><button type="button" data-cat="borsa">Borsa</button><button type="button" data-cat="piyasalar">Piyasalar</button><button type="button" data-cat="finans">Finans</button></div></div><div class="cards-grid pv-cat-grid">
<?php
$_c_args = array( 'posts_per_page' => 12 );
if ( ! empty( $pv_excl ) ) { $_c_args['post__not_in'] = $pv_excl; }
$c = pv_v7_posts( $_c_args );
if($c->have_posts()): while($c->have_posts()): $c->the_post(); $img=pv_v7_img(get_the_ID()); $cats=get_the_category(); $slugs=array(); if($cats){ foreach($cats as $cat){ $slugs[]=$cat->slug; } } ?><a class="mini-card" data-cats="<?php echo esc_attr(implode(' ', $slugs)); ?>" href="<?php the_permalink(); ?>"><div class="mini-img" <?php if($img) echo 'style="background-image:url('.esc_url($img).');background-size:cover;background-position:center"'; ?>></div><h4><?php the_title(); ?></h4></a><?php endwhile; wp_reset_postdata(); endif; ?>
</div></section>

  <section class="widget-grid stock-bottom"><div class="panel"><div class="panel-h"><h2>En Çok Artanlar</h2></div><?php pv_v7_stock_table('up'); ?></div><div class="panel"><div class="panel-h"><h2>En Çok Azalanlar</h2></div><?php pv_v7_stock_table('down'); ?></div></section>
  <?php pv_v7_converter_links_widget(); ?>
</div>
<aside class="sidebar pv-home-sidebar"><div class="sidebar-sticky-inner">
  <?php pv_v7_sidebar_gam_ad('pv_sidebar_top','300×250 Reklam','ad ad-300x250 pv-ad-desktop','div-gpt-300x250-mr1'); ?>

  <section class="panel"><div class="panel-h"><h2 class="popular-title">En Çok Okunan Haberler</h2></div><div class="popular-img"><?php $p=pv_v7_posts(array('posts_per_page'=>5)); $rank=1; if($p->have_posts()): while($p->have_posts()): $p->the_post(); $img=pv_v7_img(get_the_ID()); ?><a class="pop-item" href="<?php the_permalink(); ?>"><div class="pop-thumb" <?php if($img) echo 'style="background-image:url('.esc_url($img).');background-size:cover;background-position:center"'; ?>></div><h4><?php the_title(); ?></h4><span class="rank-soft"><?php echo esc_html($rank++); ?></span></a><?php endwhile; wp_reset_postdata(); endif; ?></div></section>

  <?php pv_v7_market_summary_widget(); ?>

  <section class="panel pv-sidebar-crypto"><div class="panel-h"><h2>Kripto Para</h2><a class="badge" href="<?php echo esc_url(home_url('/kripto-para/')); ?>">Tümü</a></div><?php pv_v7_market_table_crypto(); ?></section>

  <section class="panel converter pv-side-converter pv-side-converter-currency"><div class="panel-h"><h2>Döviz Çevirici</h2><a class="badge" href="<?php echo esc_url(home_url('/doviz-cevirici/')); ?>">Araç</a></div><div class="converter-box"><div class="formrow converter-row"><input class="pv-conv-amount" value="1" inputmode="decimal"><select class="pv-conv-from"><?php pv_v7_converter_options('currency','USD'); ?></select><button class="swap-btn" type="button">⇄</button><select class="pv-conv-to"><?php pv_v7_converter_options('currency','TRY'); ?></select></div><button class="primary pv-conv-btn" type="button">Hesapla</button><div class="pv-conv-result">Sonuç hesaplanacak</div></div></section>

  <section class="panel converter pv-side-converter pv-side-converter-gold"><div class="panel-h"><h2>Altın Çevirici</h2><a class="badge" href="<?php echo esc_url(home_url('/altin/')); ?>">Araç</a></div><div class="converter-box"><div class="formrow converter-row converter-row-single"><input class="pv-conv-amount" value="1"><select class="pv-conv-from"><?php pv_v7_converter_options('gold','GRAM_ALTIN'); ?></select><input type="hidden" class="pv-conv-to" value="TRY"><span class="fixed-to">TRY</span></div><button class="primary pv-conv-btn" type="button">Hesapla</button><div class="pv-conv-result">Sonuç hesaplanacak</div></div></section>

  <section class="panel converter pv-side-converter pv-side-converter-crypto"><div class="panel-h"><h2>Kripto Çevirici</h2><a class="badge" href="<?php echo esc_url(home_url('/kripto-para/')); ?>">Araç</a></div><div class="converter-box"><div class="formrow converter-row converter-row-single"><input class="pv-conv-amount" value="0.01"><select class="pv-conv-from"><?php pv_v7_converter_options('crypto','BTC'); ?></select><input type="hidden" class="pv-conv-to" value="TRY"><span class="fixed-to">TRY</span></div><button class="primary pv-conv-btn" type="button">Hesapla</button><div class="pv-conv-result">Sonuç hesaplanacak</div></div></section>

  <section class="panel pv-side-stock-tool"><div class="panel-h"><h2>Borsa Aracı</h2><a class="badge" href="<?php echo esc_url(home_url('/borsa/')); ?>">BIST</a></div><div class="pv-borsa-mini-tool"><label>Lot</label><input value="100" inputmode="numeric"><label>Hisse</label><select><option>THYAO</option><option>ASELS</option><option>ASTOR</option><option>YKBNK</option><option>EREGL</option></select><a class="primary" href="<?php echo esc_url(home_url('/borsa/')); ?>">Portföy Hesapla</a></div></section>

  <?php pv_v7_sidebar_gam_ad('pv_sidebar_mid','300×250 Reklam','ad ad-300x250 pv-ad-desktop','div-gpt-300x250-mr2'); ?>
  <?php pv_v7_sidebar_gam_ad('pv_sidebar_sky','300×600 Skyscraper Reklam','ad ad-300x600 pv-ad-desktop','div-gpt-300x600'); ?>
</div></aside></section>
<?php get_footer(); ?>
