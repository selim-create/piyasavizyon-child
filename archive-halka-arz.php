<?php
if (!defined('ABSPATH')) { exit; }
get_header();
$archive_title = is_tax() ? single_term_title('', false) : 'Halka Arz';
$archive_desc = is_tax() ? term_description() : 'Halka arz takvimi, gelecek halka arzlar ve tamamlanan halka arz kayıtları.';
$extra_args = array();
if (is_tax()) {
    $term = get_queried_object();
    if ($term && !is_wp_error($term)) {
        $extra_args['tax_query'] = array(array(
            'taxonomy' => $term->taxonomy,
            'field' => 'term_id',
            'terms' => array($term->term_id),
        ));
    }
}
?>
<div class="site-wrapper pv-ipo-page-shell pv-ipo-archive-page">
    <section class="content home">
        <div class="container-wrap pv-ipo-container">
            <div class="breadcrumb pv-ipo-breadcrumb">
                <ul class="block">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>">Anasayfa<i>/</i></a></li>
                    <li class="post bg"><span><?php echo esc_html($archive_title); ?></span></li>
                </ul>
            </div>
            <header class="pv-ipo-page-hero">
                <div>
                    <span class="pv-eyebrow">Halka Arz</span>
                    <h1><?php echo esc_html($archive_title); ?></h1>
                    <?php if ($archive_desc) : ?><p><?php echo wp_kses_post(wp_strip_all_tags($archive_desc)); ?></p><?php endif; ?>
                </div>
                <a class="pv-ipo-hero-button" href="<?php echo esc_url(home_url('/halka-arz-takvimi/')); ?>">Takvimi Gör</a>
            </header>

            <?php pv_v7_ipo_render_ad('pv-ipo-ad-top'); ?>
            <main class="pv-ipo-main-full" role="main">
                <?php pv_v7_ipo_render_calendar_tabs('takvim', 160, $extra_args); ?>
            </main>
            <div class="pv-ipo-bottom-ads">
                <?php pv_v7_ipo_render_ad('pv-ipo-ad-inline'); ?>
                <?php pv_v7_ipo_render_ad('pv-ipo-ad-inline'); ?>
            </div>
        </div>
    </section>
</div>
<?php get_footer(); ?>
