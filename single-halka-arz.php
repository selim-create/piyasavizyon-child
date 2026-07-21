<?php
if (!defined('ABSPATH')) { exit; }
get_header();
while (have_posts()) : the_post();
$post_id = get_the_ID();
$meta = pv_v7_ipo_safe_meta($post_id);
$thumb = get_the_post_thumbnail_url($post_id, 'medium');
$code = pv_v7_ipo_get($meta, 'bist-kodu');
$status = pv_v7_ipo_status_label($post_id, $meta);
$price = pv_v7_ipo_get($meta, 'arz-fiyat-aralik') ?: pv_v7_ipo_get($meta, 'arz-fiyat');
$value = pv_v7_ipo_get($meta, 'arz-deger');
$date_label = pv_v7_ipo_date_range_label($meta);
$rows = pv_v7_ipo_detail_rows($post_id, $meta);
$ekler = !empty($meta['halka-arz-ekler']) && is_array($meta['halka-arz-ekler']) ? $meta['halka-arz-ekler'] : array();
$bilgiler = !empty($meta['halka-arz-bilgiler']) && is_array($meta['halka-arz-bilgiler']) ? $meta['halka-arz-bilgiler'] : array();
$investors = array(
    'Yurt İçi Bireysel' => array('arz-yurt-ici-bireysel-kisi','arz-yurt-ici-bireysel-lot','arz-yurt-ici-bireysel-oran'),
    'Yurt Dışı Bireysel' => array('arz-yurt-disi-bireysel-kisi','arz-yurt-disi-bireysel-lot','arz-yurt-disi-bireysel-oran'),
    'Grup Çalışanları' => array('arz-grup-calisanlari-kisi','arz-grup-calisanlari-lot','arz-grup-calisanlari-oran'),
    'Yurt İçi Kurumsal' => array('arz-yurt-ici-kurumsal-kisi','arz-yurt-ici-kurumsal-lot','arz-yurt-ici-kurumsal-oran'),
    'Yurt Dışı Kurumsal' => array('arz-yurt-disi-kurumsal-kisi','arz-yurt-disi-kurumsal-lot','arz-yurt-disi-kurumsal-oran'),
);
?>
<div class="site-wrapper pv-ipo-page-shell pv-ipo-single-page">
    <section class="pv-ipo-content-section">
        <div class="container-wrap pv-ipo-container">
            <div class="breadcrumb pv-ipo-breadcrumb">
                <ul class="pv-ipo-breadcrumb-list">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa<i>/</i></a></li>
                    <li><a href="<?php echo esc_url(home_url('/halka-arz/')); ?>">Halka Arz<i>/</i></a></li>
                    <li class="post bg"><span><?php the_title(); ?></span></li>
                </ul>
            </div>

            <article <?php post_class('pv-ipo-single-article'); ?>>
                <header class="pv-ipo-single-hero">
                    <div class="pv-ipo-company-side">
                        <div class="pv-ipo-logo-large">
                            <?php if ($thumb) : ?>
                                <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                            <?php else : ?>
                                <span><?php echo esc_html($code ?: mb_substr(get_the_title(), 0, 2, 'UTF-8')); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="pv-ipo-title-side">
                            <div class="pv-ipo-status-line">
                                <span class="pv-ipo-status-pill"><?php echo esc_html($status); ?></span>
                                <?php if ($code) : ?><span class="pv-ipo-code-pill"><?php echo esc_html($code); ?></span><?php endif; ?>
                                <?php echo function_exists('pv_v7_ipo_badges') ? pv_v7_ipo_badges($meta, 'takvim') : ''; ?>
                            </div>
                            <h1><?php the_title(); ?></h1>
                            <?php if (has_excerpt()) : ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
                        </div>
                    </div>
                    <div class="pv-ipo-hero-metrics">
                        <?php if ($date_label) : ?><div><span>Halka Arz Tarihi</span><strong><?php echo esc_html($date_label); ?></strong></div><?php endif; ?>
                        <?php if ($price) : ?><div><span>Fiyat</span><strong><?php echo esc_html($price); ?></strong></div><?php endif; ?>
                        <?php if ($value) : ?><div><span>Halka Arz Büyüklüğü</span><strong><?php echo esc_html($value); ?></strong></div><?php endif; ?>
                    </div>
                </header>

                <?php pv_v7_ipo_render_ad('pv-ipo-ad-top'); ?>

                <section class="pv-ipo-tab-card" data-pv-ipo-single-tabs>
                    <div class="pv-ipo-single-tabs" role="tablist" aria-label="Halka arz detay sekmeleri">
                        <button type="button" class="active" data-pv-single-tab="overview">Genel Bakış</button>
                        <button type="button" data-pv-single-tab="results">Sonuçlar</button>
                        <button type="button" data-pv-single-tab="company">Şirket Bilgileri</button>
                        <button type="button" data-pv-single-tab="news">Haberler</button>
                        <button type="button" data-pv-single-tab="files">Ekler</button>
                        <button type="button" data-pv-single-tab="comments">Yorumlar</button>
                    </div>

                    <div class="pv-ipo-single-panel active" data-pv-single-panel="overview">
                        <div class="pv-ipo-detail-grid">
                            <?php foreach ($rows as $label => $value_row) : ?>
                                <div class="pv-ipo-detail-item">
                                    <span><?php echo esc_html($label); ?></span>
                                    <strong><?php echo wp_kses_post($value_row); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (get_the_content()) : ?>
                            <div class="pv-ipo-rich-content">
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($meta['halka-arz-ozet-bilgiler'])) : ?>
                            <div class="pv-ipo-info-box"><h2>Özet Bilgiler</h2><?php echo wp_kses_post($meta['halka-arz-ozet-bilgiler']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($meta['halka-arz-basvuru-yerleri'])) : ?>
                            <div class="pv-ipo-info-box"><h2>Başvuru Yerleri</h2><?php echo wp_kses_post($meta['halka-arz-basvuru-yerleri']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="pv-ipo-single-panel" data-pv-single-panel="results">
                        <?php if (!empty($meta['halka-arz-sonuclar'])) : ?><div class="pv-ipo-info-box"><?php echo wp_kses_post($meta['halka-arz-sonuclar']); ?></div><?php endif; ?>
                        <?php if (!empty($meta['arz-sonuc-mesaj'])) : ?><p class="pv-ipo-note"><?php echo esc_html($meta['arz-sonuc-mesaj']); ?></p><?php endif; ?>
                        <div class="pv-ipo-table-wrap">
                            <table class="pv-ipo-results-table">
                                <thead><tr><th>Yatırımcı Grubu</th><th>Kişi</th><th>Lot</th><th>Oran</th></tr></thead>
                                <tbody>
                                    <?php $has_investor = false; foreach ($investors as $group => $keys) :
                                        $person = pv_v7_ipo_get($meta, $keys[0]);
                                        $lot = pv_v7_ipo_get($meta, $keys[1]);
                                        $rate = pv_v7_ipo_get($meta, $keys[2]);
                                        if (!$person && !$lot && !$rate) continue;
                                        $has_investor = true;
                                    ?>
                                        <tr><th><?php echo esc_html($group); ?></th><td><?php echo esc_html($person ?: '—'); ?></td><td><?php echo esc_html($lot ?: '—'); ?></td><td><?php echo esc_html($rate ?: '—'); ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if (!$has_investor) : ?><tr><td colspan="4">Sonuç verisi henüz açıklanmadı.</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pv-ipo-single-panel" data-pv-single-panel="company">
                        <?php if (!empty($meta['halka-arz-sirket-hakkinda'])) : ?>
                            <div class="pv-ipo-info-box"><h2>Şirket Hakkında</h2><?php echo wp_kses_post($meta['halka-arz-sirket-hakkinda']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($meta['halka-arz-grafik'])) : ?>
                            <div class="pv-ipo-info-box"><h2>Grafik</h2><?php echo wp_kses_post($meta['halka-arz-grafik']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($bilgiler)) : ?>
                            <div class="pv-ipo-detail-grid pv-ipo-extra-grid">
                                <?php foreach ($bilgiler as $bilgi) : if (empty($bilgi['bilgi-baslik'])) continue; ?>
                                    <div class="pv-ipo-detail-item"><span><?php echo esc_html($bilgi['bilgi-baslik']); ?></span><strong><?php echo wp_kses_post($bilgi['bilgi-aciklama'] ?? ''); ?></strong></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($meta['halka-arz-sirket-hakkinda']) && empty($meta['halka-arz-grafik']) && empty($bilgiler)) : ?>
                            <div class="pv-ipo-empty">Şirket bilgisi henüz eklenmedi.</div>
                        <?php endif; ?>
                    </div>

                    <div class="pv-ipo-single-panel" data-pv-single-panel="news">
                        <?php pv_v7_ipo_related_news($post_id); ?>
                    </div>

                    <div class="pv-ipo-single-panel" data-pv-single-panel="files">
                        <?php if (!empty($ekler)) : ?>
                            <div class="pv-ipo-file-list">
                                <?php foreach ($ekler as $ek) : if (empty($ek['ek-link'])) continue; ?>
                                    <a target="_blank" rel="noopener" href="<?php echo esc_url($ek['ek-link']); ?>"><i class="fa fa-file-pdf"></i><span><?php echo esc_html($ek['ek-baslik'] ?: 'Ek dosya'); ?></span></a>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="pv-ipo-empty">Ek dosya bulunmuyor.</div>
                        <?php endif; ?>
                    </div>

                    <div class="pv-ipo-single-panel" data-pv-single-panel="comments">
                        <?php if (comments_open() || get_comments_number()) { comments_template(); } else { echo '<div class="pv-ipo-empty">Yorum alanı kapalı.</div>'; } ?>
                    </div>
                </section>

                <div class="pv-ipo-bottom-ads">
                    <?php pv_v7_ipo_render_ad('pv-ipo-ad-inline'); ?>
                    <?php pv_v7_ipo_render_ad('pv-ipo-ad-inline'); ?>
                </div>
            </article>
        </div>
    </section>
</div>
<?php endwhile; get_footer(); ?>
