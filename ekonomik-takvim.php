<?php
/*
  Template Name: Ekonomik Takvim
*/

// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern sidebar added for Piyasa Vizyon styling.
get_header();

switch ($_GET['date']) {
    case 'dun':
        $kaynak = get_data_service("mynet?url=https://finans.mynet.com/api/ekonomiktakvim/events/yesterday");
        break;

    case 'yarin':
        $kaynak = get_data_service("mynet?url=https://finans.mynet.com/api/ekonomiktakvim/events/tomorrow");
        break;

    case 'bugun':
        $kaynak = get_data_service("mynet?url=https://finans.mynet.com/api/ekonomiktakvim/events/today");
        break;

    case '1-hafta':
        $kaynak = get_data_service("mynet?url=https://finans.mynet.com/api/ekonomiktakvim/events/week");
        break;

    case '1-ay':
        $kaynak = get_data_service("mynet?url=https://finans.mynet.com/api/ekonomiktakvim/events/month");
        break;

    default:
        $kaynak = get_data_service("mynet?url=https://finans.mynet.com/api/ekonomiktakvim/events/yesterday");
        break;
}
$dataJson = json_decode($kaynak, true);

?>
<style>
    .currencyTable tr th {
        font-weight: 500;
    }

    .currencyTable tr td b {
        color: #3b72de !important;
    }

    .dateTable {
        display: block;
        height: 35px;
        margin-bottom: 8px;
    }

    .dateTable ul {
        margin: 0 auto;
    }

    .dateTable ul li {
        cursor: pointer;
        float: left;
        margin-right: 20px;
    }

    .dateTable ul li:last-child {
        margin-right: 0px;
    }

    .dateTable ul li a {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: rgba(36, 36, 36, .4);
        text-transform: Uppercase;
        margin-top: 12px;
        position: relative;
    }

    .dateTable ul li.active a {
        color: #242424;
    }

    .dateTable ul li.active a:after {
        position: Absolute;
        content: "";
        bottom: -10px;
        left: 0;
        right: 0;
        width: 100%;
        height: 2px;
        background: #fab915;
    }
</style>
<!-- Site Wrapper -->
<div class="site-wrapper pv-market-native pv-market-calendar-native">

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


                    <div class="pv-market-list-content pv-calendar-content">

                        <!-- Main Content -->
                        <div class="mainContent">

                            <!-- Main -->
                            <div class="main">

                                <!-- widget -->
                                <div class="widget">
                                    <!-- Currency Showcase -->
                                    <div class="currencyShowcase fullShowcase mobileBottomNo">
                                        <?php if (wp_is_mobile()) { ?>
                                            <div class="dateTable">
                                                <ul>
                                                    <li class="<?php if (@$_GET['date'] == "dun" || empty(@$_GET['date'])) {
    echo "active";
} ?>">
                                                        <a href="<?= home_url("/ekonomik-takvim/?date=dun") ?>">Dün</a>
                                                    </li>
                                                    <li class="<?php if (@$_GET['date'] == "bugun") {
    echo "active";
} ?>">
                                                        <a href="<?= home_url("/ekonomik-takvim/?date=bugun") ?>">Bugün</a>
                                                    </li>
                                                    <li class="<?php if (@$_GET['date'] == "yarin") {
    echo "active";
} ?>">
                                                        <a href="<?= home_url("/ekonomik-takvim/?date=yarin") ?>">Yarın</a>
                                                    </li>
                                                    <li class="<?php if (@$_GET['date'] == "1-hafta") {
    echo "active";
} ?>">
                                                        <a href="<?= home_url("/ekonomik-takvim/?date=1-hafta") ?>">1
                                                            Hafta</a>
                                                    </li>
                                                    <li class="<?php if (@$_GET['date'] == "1-ay") {
    echo "active";
} ?>"><a href="<?= home_url("/ekonomik-takvim/?date=1-ay") ?>">1
                                                            Ay</a></li>
                                                </ul>
                                            </div>

                                            <table class="currencyTable currencyFullTable" style="overflow: hidden;">
                                                <tr>
                                                    <th style="text-align: left;padding-left: 10px;float: left;width: 20%;">
                                                        Ülke
                                                    </th>
                                                    <th style="text-align: center;display: block;padding-left: 15px;float: left;width: 70%;">
                                                        Olay
                                                    </th>
                                                </tr>
                                                <?php foreach ($dataJson['events'] as $key => $val) :

                                                    $date_clean = ($val['d'] / 1000) + 3600 * 3;

                                                    $data_date = date_i18n("d F", $val['d']);
                                                    $current_date = date_i18n("d F", time() + (3600));
                                                    if (!empty($_GET['date'])) {
                                                        if ($_GET['date'] == "dun") {
                                                            $current_time = time() + (3600);

                                                            if (date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600)) && date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() - (3600 * 24))) {
                                                                continue;
                                                            }
                                                        } elseif ($_GET['date'] == "bugun") {
                                                            $current_time = time() + (3600);
                                                            $date_clean = ($val['d'] / 1000) + 3600 * 3;
                                                            if (date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600))) {
                                                                continue;
                                                            }
                                                        } elseif ($_GET['date'] == "yarin") {
                                                            if (date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 24))) {
                                                                continue;
                                                            }
                                                        } elseif ($_GET['date'] == "1-hafta") {
                                                            if (
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 24)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 48)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 72)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 96)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 120)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 144)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 168))

                                                            ) {
                                                                continue;
                                                            }
                                                        } elseif ($_GET['date'] == "1-hafta") {
                                                        }
                                                    } else {
                                                        $current_time = time() + (3600);

                                                        if (date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600)) && date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() - (3600 * 24))) {
                                                            continue;
                                                        }
                                                    }


                                                    if ($val['did'] == 0) {
                                                        $val['did'] = "-";
                                                    }
                                                    $star_area = "";
                                                    for ($i = 0; $i < $val['i']; $i++) {
                                                        $star_area .= "&#11089;";
                                                    }

                                                ?>
                                                    <tr style="width: 100%;display: block;float: right;z-index: 1;">

                                                        <td style="width: 20%;font-weight: 500;padding-left: 10px;text-align: left;float:left;white-space: nowrap;"><?= $val['c'] ?></td>
                                                        <td style="width: 70%;font-weight: normal;line-height: 43px;height: 43px;padding-bottom: 10px;white-space: break-spaces;overflow: hidden;text-align: left;float: right;display: table-cell !important;"><?= $val['e'] ?></td>

                                                    </tr>
                                                <?php endforeach; ?>
                                            </table>

                                        <?php } else {
                                                    ?>

                                            <div class="dateTable">
                                                <ul>
                                                    <li class="<?php if (@$_GET['date'] == "dun" || empty(@$_GET['date'])) {
                                                        echo "active";
                                                    } ?>">
                                                        <a href="<?= home_url("/ekonomik-takvim/?date=dun") ?>">Dün</a>
                                                    </li>
                                                    <li class="<?php if (@$_GET['date'] == "bugun") {
                                                        echo "active";
                                                    } ?>">
                                                        <a href="<?= home_url("/ekonomik-takvim/?date=bugun") ?>">Bugün</a>
                                                    </li>
                                                    <li class="<?php if (@$_GET['date'] == "yarin") {
                                                        echo "active";
                                                    } ?>">
                                                        <a href="<?= home_url("/ekonomik-takvim/?date=yarin") ?>">Yarın</a>
                                                    </li>
                                                    <li class="<?php if (@$_GET['date'] == "1-hafta") {
                                                        echo "active";
                                                    } ?>">
                                                        <a href="<?= home_url("/ekonomik-takvim/?date=1-hafta") ?>">1
                                                            Hafta</a>
                                                    </li>
                                                    <li class="<?php if (@$_GET['date'] == "1-ay") {
                                                        echo "active";
                                                    } ?>"><a href="<?= home_url("/ekonomik-takvim/?date=1-ay") ?>">1
                                                            Ay</a></li>
                                                </ul>
                                            </div>

                                            <table class="currencyTable currencyFullTable">

                                                <tr>
                                                    <th style="text-align: left;">Tarih</th>
                                                    <th style="padding-left: 0px;text-align: left;">Ülke</th>
                                                    <th style="text-align: center;">Önem</th>
                                                    <th style="text-align: left;">Olay</th>
                                                    <th style="text-align: center;">Beklenti</th>
                                                    <th style="text-align: center;">Gerçekleşen</th>
                                                    <th style="text-align: center;">Önceki</th>
                                                </tr>
                                                <?php foreach ($dataJson['events'] as $key => $val) :

                                                    $date_clean = ($val['d'] / 1000) + 3600 * 3;

                                                    $data_date = date_i18n("d F", $val['d']);
                                                    $current_date = date_i18n("d F", time() + (3600));
                                                    if (!empty($_GET['date'])) {
                                                        if ($_GET['date'] == "dun") {
                                                            $current_time = time() + (3600);

                                                            if (date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600)) && date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() - (3600 * 24))) {
                                                                continue;
                                                            }
                                                        } elseif ($_GET['date'] == "bugun") {
                                                            $current_time = time() + (3600);
                                                            $date_clean = ($val['d'] / 1000) + 3600 * 3;
                                                            if (date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600))) {
                                                                continue;
                                                            }
                                                        } elseif ($_GET['date'] == "yarin") {
                                                            if (date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 24))) {
                                                                continue;
                                                            }
                                                        } elseif ($_GET['date'] == "1-hafta") {
                                                            if (
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 24)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 48)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 72)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 96)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 120)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 144)) &&
                                                                date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600 * 168))

                                                            ) {
                                                                continue;
                                                            }
                                                        } elseif ($_GET['date'] == "1-hafta") {
                                                        }
                                                    } else {
                                                        $current_time = time() + (3600);

                                                        if (date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() + (3600)) && date_i18n("d F Y", $date_clean) != date_i18n("d F Y", time() - (3600 * 24))) {
                                                            continue;
                                                        }
                                                    }


                                                    if ($val['did'] == 0) {
                                                        $val['did'] = "-";
                                                    }
                                                    $star_area = "";
                                                    for ($i = 0; $i < $val['i']; $i++) {
                                                        $star_area .= "&#11089;";
                                                    } ?>
                                                    <tr>
                                                        <td style="font-weight: 500;width: 121px;text-align: left;"><?= date_i18n("d F", $date_clean) ?></td>
                                                        <td style="font-weight: 500;padding-left: 0px;width: 100px;text-align: left;"><?= $val['c'] ?></td>
                                                        <td style="font-weight: normal;text-align: center;font-size: 23px; color: #fbb916;"><?= $star_area ?></td>
                                                        <td style="font-weight: normal;line-height: 21px;padding-bottom: 10px;text-align: center;"><?= $val['e'] ?></td>
                                                        <td style="font-weight: normal;text-align: center;"><?= $val['did'] ?></td>
                                                        <td style="font-weight: normal;text-align: center;"><?= $val['a'] ?></td>
                                                        <td style="font-weight: normal;text-align: center;"><?= $val['p'] ?></td>
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
    pv_v7_market_sidebar('ekonomik-takvim');
} ?>
        </div>
    </section>
    <!-- Content -->
    <div class="clear"></div>

</div>
<!-- #Site Wrapper -->
<?php get_footer(); ?>
