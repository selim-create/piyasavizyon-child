<?php
/*
  Template Name: Döviz Tablo
*/
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern market sidebar added for Piyasa Vizyon styling.

get_header();
?>
<style>
    .currencyTable tr th {
        font-weight: 500;
    }

    .currencyTable tr td b {
        color: #3b72de !important;
    }
</style>
<!-- Site Wrapper -->
<div class="site-wrapper pv-market-native pv-market-currency-native">

    <!-- Content -->
    <section class="content home">
        <div class="container-wrap">

            <!-- WideBar -->
            <div class="widebar floatLeft">

                <div class="singleWrapper">

                    <!-- BreadCrumb -->
                    <div class="breadcrumb">
                        <ul class="block">
                            <li><a href="<?php bloginfo('home') ?>">Anasayfa<i>/</i></a></li>
                            <li class="post bg"><span><?php the_title() ?></span></li>
                        </ul>
                    </div>

                    <h1 class="postTitle"><?php the_title() ?></h1>



                    <div class="pv-market-list-content pv-currency-list-content">

                        <!-- Main Content -->
                        <div class="mainContent">

                            <!-- Main -->
                            <div class="main">

                                <!-- widget -->
                                <div class="widget">
                                    <!-- Currency Showcase -->
                                    <div class="currencyShowcase fullShowcase mobileBottomNo">
                                        <?php if (wp_is_mobile()) {
    ?>
                                            <table class="currencyTable currencyFullTable">
                                                <tr>
                                                    <th style="width: 70% !important;display: inline-block;">Döviz</th>
                                                    <th class="sagagit2" style="width: 20%;display: inline-block;padding-left: 0;">Alış</th>
                                                </tr>
                                                <?php foreach (array_unique($currency_data['code']) as $key => $val) :
                                                    if (str_replace(",", ".", $currency_data['change_rate'][$key]) > 0) {
                                                        $crease_status = "increase";
                                                    } else {
                                                        $crease_status = "decrease";
                                                    } ?>
                                                    <tr class="alt dKurlariS">
                                                        <td style="width: 70% !important;display: inline-block;"><a href="<?php bloginfo("home") ?>/<?= $bp_options['page_doviz'] ?>?c=<?= $key ?>"><img src="<?php bloginfo('template_directory'); ?>/img/flag/<?= str_replace("csk", "czk", $key) ?>.png" width="24" height="16" alt="<?= $currency_data['full_name'][$key] ?> - <?= strtoupper($currency_data['code'][$key]) ?>"> <b><?= strtoupper($currency_data['code'][$key]) ?></b></a></td>
                                                        <td style="width: 20%;display: inline-block;padding-left:0px;"><i class="<?= $crease_status ?>" style="<?php if (!is_user_logged_in()) : ?>position: relative; top: 26px;<?php endif; ?>"></i> <span><?= $currency_data['selling'][$key] ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </table>

                                        <?php
} else {
                                                        ?>
                                            <table class="currencyTable currencyFullTable">
                                                <tr>
                                                    <th>Döviz</th>
                                                    <th>Alış</th>
                                                    <th>Satış</th>
                                                    <th>Fark</th>
                                                    <th>Saat</th>
                                                </tr>
                                                <?php foreach (array_unique($currency_data['code']) as $key => $val) :
                                                    if (str_replace(",", ".", $currency_data['change_rate'][$key]) > 0) {
                                                        $crease_status = "increase";
                                                        $color          = "#40bc9a";
                                                    } else {
                                                        $crease_status = "decrease";
                                                        $color          = "#fc4b67";
                                                    } ?>
                                                    <tr>
                                                        <td><a href="<?php bloginfo("home") ?>/<?= $bp_options['page_doviz'] ?>?c=<?= $key ?>"><img src="<?php bloginfo('template_directory'); ?>/img/flag/<?= str_replace("csk", "czk", $key) ?>.png" width="24" height="16" alt="<?= $currency_data['full_name'][$key] ?> - <?= strtoupper($currency_data['code'][$key]) ?>"> <b><?= $currency_data['full_name'][$key] ?> - <?= strtoupper($currency_data['code'][$key]) ?></b></a></td>
                                                        <td style="font-weight: 500;color:<?= $color ?>;"><i class="<?= $crease_status ?>"></i> <?= $currency_data['selling'][$key] ?></td>
                                                        <td style="font-weight: normal;"><?= $currency_data['buying'][$key] ?></td>
                                                        <td style="font-weight: normal;"><span class="<?= $crease_status ?> subtract">% <?= $currency_data['change_rate'][$key] ?></span></td>
                                                        <td style="padding: 0 15px;font-weight: normal;"><?= $currency_data['time'][$key] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </table>
                                        <?php
                                                    } ?>
                                    </div>
                                    <!-- //Currency Showcase -->
                                </div>
                                <!-- //widget -->

                            </div>


                        </div>
                        <!-- #MainBar -->


                    </div>

                </div>

            </div>
            <?php if (!wp_is_mobile() && function_exists('pv_v7_market_sidebar')) {
                pv_v7_market_sidebar('doviz-kurlari');
            } ?>
        </div>
    </section>
    <!-- Content -->
    <div class="clear"></div>

</div>
<!-- #Site Wrapper -->
<?php get_footer(); ?>
