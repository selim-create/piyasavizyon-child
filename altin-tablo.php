<?php
/*
  Template Name: Altın Tablo
*/
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern market sidebar added for Piyasa Vizyon styling.

get_header();
?>
<!-- Site Wrapper -->
<style>
    .currencyTable tr th {
        font-weight: 500;
    }

    .currencyTable tr td b {
        color: #3b72de !important;
    }

    .currencyTable tr td a b {
        color: #3b72de !important;
    }
</style>
<div class="site-wrapper pv-market-native pv-market-gold-native">

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

                    <h1 class="postTitle centerli"><?php the_title() ?></h1>


                    <div class="pv-market-list-content pv-gold-list-content">

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
                                                    <th style="width: 70% !important;display: inline-block;">Altın</th>
                                                    <th class="sagagit"
                                                        style="width: 20%;display: inline-block;padding-left: 0;">Satış
                                                    </th>
                                                </tr>
                                                <?php foreach (array_unique($altin_data['altin_price']) as $key => $val):
                                                    if (str_replace(",", ".", $altin_data['altin_rate'][$key]) > 0) {
                                                        $crease_status = "increase";
                                                    } else {
                                                        $crease_status = "decrease";
                                                    }
                                                    ?>
                                                    <tr class="alt">
                                                        <td style="width: 70% !important;display: inline-block;"><a
                                                                    href="<?php bloginfo("home") ?>/<?= $bp_options['page_altin'] ?>/?a=<?= $altin_data['altin_key'][$key] ?>"
                                                                    style="width: 100%;"><b><?= ($altin_data['altin_name'][$key]) ?></b></a>
                                                        </td>
                                                        <td style="width: 20%;display: inline-block;padding-left:0px;">
                                                            <i class="<?= $crease_status ?>"
                                                               style="<?php if (!is_user_logged_in()): ?>position: relative; top: 26px;<?php endif; ?>"></i>
                                                            <span><?= $altin_data['altin_price_buying'][$key] ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </table>
                                        <?php } else {
                                            ?>
                                            <table class="currencyTable currencyFullTable">
                                                <tr>
                                                    <th>Altın</th>
                                                    <th>Alış</th>
                                                    <th>Satış</th>
                                                    <th>Fark</th>
                                                    <th>Saat</th>
                                                </tr>
                                                <?php foreach (array_unique($altin_data['altin_price']) as $key => $val):
                                                    if (str_replace(",", ".", $altin_data['altin_rate'][$key]) > 0) {
                                                        $crease_status = "increase";
                                                        $color = "#40bc9a";
                                                    } else {
                                                        $crease_status = "decrease";
                                                        $color = "#fc4b67";
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <a href="<?php bloginfo("home") ?>/<?= $bp_options['page_altin'] ?>/?a=<?= $altin_data['altin_key'][$key] ?>"><b><?= ($altin_data['altin_name'][$key]) ?></b></a>
                                                        </td>
                                                        <td style="color: <?= $color; ?>;"><i
                                                                    class="<?= $crease_status ?>"></i> <?= $altin_data['altin_price_buying'][$key] ?>
                                                        </td>
                                                        <td style="font-weight:  normal;"><?= $altin_data['altin_price_selling'][$key] ?></td>
                                                        <td style="font-weight:  normal;"><span
                                                                    class="<?= $crease_status ?> subtract">% <?= $altin_data['altin_rate'][$key] ?></span>
                                                        </td>
                                                        <td style="padding: 0 15px;font-weight: normal;"><?= $altin_data['altin_update'][$key] ?></td>
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
                pv_v7_market_sidebar('altin-piyasalari');
            } ?>

        </div>
    </section>
    <!-- Content -->
    <div class="clear"></div>

</div>
<!-- #Site Wrapper -->
<?php get_footer(); ?>
