<?php
/**
 * Template Name: Halka Arz Takvimi
 */
if (!defined('ABSPATH')) { exit; }
get_header();
$path = isset($_SERVER['REQUEST_URI']) ? trim((string) wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH), '/') : '';
$active = ($path === 'gelecek-halka-arzlar') ? 'gelecek' : 'takvim';
?>
<div class="site-wrapper pv-ipo-page-shell pv-ipo-calendar-page">
    <section class="content home">
        <div class="container-wrap pv-ipo-container">
            <div class="breadcrumb pv-ipo-breadcrumb">
                <ul class="block">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa<i>/</i></a></li>
                    <li class="post bg"><span>Halka Arz Takvimi</span></li>
                </ul>
            </div>

            <header class="pv-ipo-page-hero">
                <div>
                    <span class="pv-eyebrow">Piyasa Vizyon</span>
                    <h1>Halka Arz Takvimi</h1>
                    <p>Yaklaşan, aktif, tamamlanan ve taslak halka arzları tek ekranda, sade ve okunabilir kart yapısıyla takip edin.</p>
                </div>
                <div class="pv-ipo-hero-stats">
                    <span><b><?php echo esc_html(count(pv_v7_ipo_collect_ids('takvim', 300))); ?></b> Takvim Kaydı</span>
                    <span><b><?php echo esc_html(count(pv_v7_ipo_collect_ids('gelecek', 300))); ?></b> Gelecek Arz</span>
                </div>
            </header>

            <?php pv_v7_ipo_render_ad('pv-ipo-ad-top'); ?>

            <main class="pv-ipo-main-full" role="main">
                <?php pv_v7_ipo_render_calendar_tabs($active, 120); ?>
            </main>

            <div class="pv-ipo-bottom-ads">
                <?php pv_v7_ipo_render_ad('pv-ipo-ad-inline'); ?>
                <?php pv_v7_ipo_render_ad('pv-ipo-ad-inline'); ?>
            </div>
        </div>
    </section>
</div>
<?php get_footer(); ?>
