<?php
/*
  Template Name: Döviz Detay
*/
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern market sidebar added for Piyasa Vizyon styling.

global $wpdb;
$key             = $_GET['c'];
$current_page_id = get_queried_object_id();

$new_title = $currency_data['full_name'][$key] . " - " . get_bloginfo('name');

function generate_custom_title($title)
{
    global $new_title;
    $title = $new_title;

    return $title;
}

add_filter('pre_get_document_title', 'generate_custom_title', 10);
add_filter('wpseo_title', 'generate_custom_title', 15);
if (empty(@$_GET['c'])) {
?>
    <script>
        window.location.href = "<?php bloginfo("home") ?>";
    </script><?php
            }
            get_header();

            if (str_replace(",", ".", $currency_data['change_rate'][$key]) > 0) {
                $crease_status = "#32ba5b";
            } else {
                $crease_status = "#ef291f";
            }

            if (@$_GET['banka']) {
                $banka_name = $_GET['banka'];
            } else {
                $banka_name = "Serbest Piyasa";
            }

            $kaynak = get_url_curl("https://kur.doviz.com/");

            $doviz = get_url_curl($ad = "https://kur.doviz.com/serbest-piyasa/" . permalink_bf($currency_data['full_name'][$key]));
            preg_match_all('@<div class="dropdown-content">(.*?)</div>@si', $doviz, $banka_area);
            preg_match_all('@<a href="(.*?)" onclick="trackEvent(.*?)">(.*?)</a>@si', $banka_area[1][1], $banka);

            preg_match_all('@<table data-sortable>(.*?)</table>@si', $doviz, $banka_tablo);


            if ($banka_name != "Serbest Piyasa") {
                $banka_link = array_search($banka_name, $banka[3]);
                $banka_api  = permalink($_GET['banka']);
            }

            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();
                $liste_data   = get_user_meta($current_user->ID, "uye_liste", true);
                if (empty($liste_data)) {
                    $liste_data = [];
                }
                $status_liste = array_search($_GET['c'], $liste_data);
                if ($status_liste !== false) {
                    $status_liste = 1;
                }

                $alarm_liste = get_user_meta($current_user->ID, "uye_alarm", true)['doviz'];


                if (empty($alarm_liste)) {
                    $alarm_liste = [];
                }

                $alarm_liste = array_search($key, $alarm_liste);
                if ($alarm_liste !== false) {
                    $alarm_liste = 1;
                } else {
                    $alarm_liste = 0;
                }
            }

                ?>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="<?php bloginfo('template_directory'); ?>/js/highcharts.js"></script>


<div class="site-wrapper pv-market-native pv-market-currency-detail-native">

    <!-- Content -->
    <section class="content home">
        <div class="container-wrap">

            <!-- WideBar -->
            <div class="widebar floatLeft">

                <div class="singleWrapper">

                    <section class="pv-currency-detail-hero">
                    <!-- BreadCrumb -->
                    <div class="breadcrumb">
                        <ul class="block">
                            <li><a href="<?php bloginfo('home') ?>">Anasayfa<i>/</i></a></li>
                            <li><a href="<?php bloginfo('home') ?>/<?= $bp_options['page_dovizkurlari'] ?>/">Döviz Kurları<i>/</i></a></li>
                            <li class="post bg"><span><?= $currency_data['full_name'][$key] ?></span></li>
                        </ul>
                    </div>

                    <h1 class="singlePageTitle floatLeft">
                        <span class="dropCustomCurrency"><?= strtoupper($currency_data['code'][$key]) ?> - <?= $currency_data['full_name'][$key] ?> <i class="dropdown-arrow"></i></span>
                        <div class="mCustomScrollbar changeCurrency">
                            <?php foreach (array_unique($currency_data['code']) as $key2 => $val) : ?>
                                <a href="<?php bloginfo("home") ?>/<?= $bp_options['page_doviz'] ?>/?c=<?= $key2 ?>"><?= $currency_data['full_name'][$key2] ?></a>
                            <?php endforeach; ?>
                        </div>

                    </h1>

                    <div class="changeCurrencySource">
                        <span><?= $banka_name ?> <i class="dropdown-arrow"></i></span>
                        <div class="mCustomScrollbar changeCurrency">
                            <?php if ($banka_name != "Serbest Piyasa") {
                            ?><a href="<?php bloginfo("home") ?>/<?= $bp_options['page_doviz'] ?>/?c=<?= $key ?>">Serbest Piyasa</a><?php
                                                                                                                                } ?>
                            <?php foreach ($banka[3] as $key2 => $value) : if ($banka_name == $value) : continue;
                                endif; ?>
                                <a href="<?php bloginfo("home") ?>/<?= $bp_options['page_doviz'] ?>/?c=<?= $key ?>&banka=<?= ($value) ?>"><?= $value ?></a>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <div class="clearfix"></div>
                    </section>

                    <div class="pv-market-detail-content pv-currency-detail-content">

                        <!-- Main Content -->
                        <div class="mainContent">

                            <!-- Main -->
                            <div class="main">

                                <!-- Widget -->
                                <div class="widget">
                                    <div class="borsaValue kurTrade">
                                        <span>Alış</span><?= $currency_data['selling'][$key] ?>
                                    </div>
                                    <div class="borsaValue kurTrade ">
                                        <span>Satış</span><?= $currency_data['buying'][$key] ?>
                                        <div class="borsaRate" style="color: <?= $crease_status ?> !important;">
                                            <i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate'][$key] ?> %)
                                        </div>
                                    </div>
                                    <div class="lastUpdate2">Son Güncelleme: <?= $currency_data['time'][$key] ?></div>
                                    <div class="clear"></div>
                                    <!-- Tab Head -->
                                    <div class="borsaTimerTabHead bg">
                                        <ul>
                                            <li><span>BUGÜN</span></li>
                                            <?php if ($banka_name == "Serbest Piyasa") : ?>
                                                <li><span>BU HAFTA</span></li>
                                                <li><span>BU AY</span></li>
                                                <li><span>BU YIL</span></li>
                                                <li><span>5 YILLIK</span></li>
                                            <?php endif; ?>
                                        </ul>
                                        <div class="userNotification">
                                            <?php if (is_user_logged_in()) {
                                                if (! empty($status_liste)) {
                                            ?><a href="javascript:;" onclick="listedenCikar('<?= $key ?>')" class="addList">ÇIKAR<i class="remove"></i></a><?php
                                                                                                                                                        } else {
                                                                                                                                                            ?><a href="javascript:;" onclick="listemeEkle('<?= $key ?>')" class="addList">LİSTEME EKLE<i class="add"></i></a><?php
                                                                                                                                                                                                                                                                            } ?>
                                                <?php if (empty($alarm_liste)) { ?>
                                                    <a href="javascript:;" onclick="alarmKur('<?= $key ?>')" class="alarmKur">ALARM KUR <i class="ring"></i></a>
                                                <?php } else {
                                                ?><a href="javascript:;" onclick="alarmCikar('<?= $key ?>')" class="alarmKur">ÇIKAR <i class="remove"></i></a><?php
                                                                                                                                                            } ?>
                                            <?php
                                            } else {
                                            ?>
                                                <a href="javascript:;" onclick="girisYap();" class="addList">LİSTEME EKLE <i class="add"></i></a>
                                                <a href="javascript:;" onclick="girisYap();" class="alarmKur">ALARM KUR <i class="ring"></i></a>
                                            <?php
                                            } ?>

                                        </div>
                                    </div>
                                    <?php if ($banka_name == "Serbest Piyasa") { ?>
                                        <div class="borsaTimerTabContent">

                                            <div class="currencyChart" id="container_daily"></div>
                                            <script>
                                                $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php?code=<?= strtoupper($key) ?>", function(values) {


                                                    Highcharts.chart('container_daily', {
                                                        chart: {
                                                            zoomType: 'x'
                                                        },
                                                        title: {
                                                            text: '<?= strtoupper($key) ?> - TRY Günlük Grafik Tablosu'
                                                        },
                                                        subtitle: {
                                                            text: document.ontouchstart === undefined ?
                                                                '' : ''
                                                        },
                                                        xAxis: {
                                                            type: 'datetime'
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: ''
                                                            }
                                                        },
                                                        legend: {
                                                            enabled: false
                                                        },
                                                        plotOptions: {
                                                            area: {
                                                                fillColor: {
                                                                    linearGradient: {
                                                                        x1: 0,
                                                                        y1: 0,
                                                                        x2: 0,
                                                                        y2: 1
                                                                    },
                                                                    stops: [
                                                                        [0, Highcharts.getOptions().colors[0]],
                                                                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                                                    ]
                                                                },
                                                                marker: {
                                                                    radius: 2
                                                                },
                                                                lineWidth: 1,
                                                                states: {
                                                                    hover: {
                                                                        lineWidth: 1
                                                                    }
                                                                },
                                                                threshold: null
                                                            }
                                                        },

                                                        series: [{
                                                            type: 'area',
                                                            name: '<?= strtoupper($key) ?> - TRY',
                                                            data: [
                                                                <?php
                                                                $dovizData = get_url_curl("https://finans.mynet.com/doviz/$key/");

                                                                preg_match_all('@initChartData\({(.*?)}\)@si', $dovizData, $gunluk_data);
                                                                $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);

                                                                $continue_gunluk = false;
                                                                foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {

                                                                    if (($value[0] / 1000) < (time() - (88400))) {
                                                                        continue;
                                                                    }
                                                                    $continue_gunluk = true;

                                                                ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                                                    }

                                                                                                    if ($continue_gunluk != true) :
                                                                                                        foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                                                            if (($value[0] / 1000) < (time() - (88400 * 2.5))) {
                                                                                                                continue;
                                                                                                            }
                                                                                                            $continue_gunluk = true;

                                                                                                        ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                                                                                            }
                                                                                                                                        endif;
                                                                                                                                                ?>

                                                            ]
                                                        }]
                                                    });
                                                });
                                            </script>


                                            <p>* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                        </div>
                                        <div class="borsaTimerTabContent">
                                            <div class="currencyChart" id="container_weekly"></div>
                                            <script>
                                                $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php?code=<?= strtoupper($key) ?>", function(values) {


                                                    Highcharts.chart('container_weekly', {
                                                        chart: {
                                                            zoomType: 'x'
                                                        },
                                                        title: {
                                                            text: '<?= strtoupper($key) ?> - TRY Haftalık Grafik Tablosu'
                                                        },
                                                        subtitle: {
                                                            text: document.ontouchstart === undefined ?
                                                                '' : ''
                                                        },
                                                        xAxis: {
                                                            type: 'datetime'
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: ''
                                                            }
                                                        },
                                                        legend: {
                                                            enabled: false
                                                        },
                                                        plotOptions: {
                                                            area: {
                                                                fillColor: {
                                                                    linearGradient: {
                                                                        x1: 0,
                                                                        y1: 0,
                                                                        x2: 0,
                                                                        y2: 1
                                                                    },
                                                                    stops: [
                                                                        [0, Highcharts.getOptions().colors[0]],
                                                                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                                                    ]
                                                                },
                                                                marker: {
                                                                    radius: 2
                                                                },
                                                                lineWidth: 1,
                                                                states: {
                                                                    hover: {
                                                                        lineWidth: 1
                                                                    }
                                                                },
                                                                threshold: null
                                                            }
                                                        },

                                                        series: [{
                                                            type: 'area',
                                                            name: '<?= strtoupper($key) ?> - TRY',
                                                            data: [
                                                                <?php
                                                                foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                    if (($value[0] / 1000) < (time() - (86400 * 7))) {
                                                                        continue;
                                                                    } ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                                                            }
                                                                                                                ?>
                                                            ]

                                                        }]
                                                    });
                                                });
                                            </script>
                                        </div>
                                        <div class="borsaTimerTabContent">
                                            <div class="currencyChart" id="container_monthly"></div>
                                            <script>
                                                $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php?code=<?= strtoupper($key) ?>", function(values) {


                                                    Highcharts.chart('container_monthly', {
                                                        chart: {
                                                            zoomType: 'x'
                                                        },
                                                        title: {
                                                            text: '<?= strtoupper($key) ?> - TRY Aylık Grafik Tablosu'
                                                        },
                                                        subtitle: {
                                                            text: document.ontouchstart === undefined ?
                                                                '' : ''
                                                        },
                                                        xAxis: {
                                                            type: 'datetime'
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: ''
                                                            }
                                                        },
                                                        legend: {
                                                            enabled: false
                                                        },
                                                        plotOptions: {
                                                            area: {
                                                                fillColor: {
                                                                    linearGradient: {
                                                                        x1: 0,
                                                                        y1: 0,
                                                                        x2: 0,
                                                                        y2: 1
                                                                    },
                                                                    stops: [
                                                                        [0, Highcharts.getOptions().colors[0]],
                                                                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                                                    ]
                                                                },
                                                                marker: {
                                                                    radius: 2
                                                                },
                                                                lineWidth: 1,
                                                                states: {
                                                                    hover: {
                                                                        lineWidth: 1
                                                                    }
                                                                },
                                                                threshold: null
                                                            }
                                                        },

                                                        series: [{
                                                            type: 'area',
                                                            name: '<?= strtoupper($key) ?> - TRY',
                                                            data: [
                                                                <?php
                                                                foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                    if (($value[0] / 1000) < (time() - (86400 * 30))) {
                                                                        continue;
                                                                    } ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                                                            }
                                                                                                                ?>
                                                            ]

                                                        }]
                                                    });
                                                });
                                            </script>
                                        </div>
                                        <div class="borsaTimerTabContent">
                                            <div class="currencyChart" id="container_yearly"></div>
                                            <script>
                                                $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php?code=<?= strtoupper($key) ?>", function(values) {
                                                    Highcharts.chart('container_yearly', {
                                                        chart: {
                                                            zoomType: 'x'
                                                        },
                                                        title: {
                                                            text: '<?= strtoupper($key) ?> - TRY Yıllık Grafik Tablosu'
                                                        },
                                                        subtitle: {
                                                            text: document.ontouchstart === undefined ?
                                                                '' : ''
                                                        },
                                                        xAxis: {
                                                            type: 'datetime'
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: ''
                                                            }
                                                        },
                                                        legend: {
                                                            enabled: false
                                                        },
                                                        plotOptions: {
                                                            area: {
                                                                fillColor: {
                                                                    linearGradient: {
                                                                        x1: 0,
                                                                        y1: 0,
                                                                        x2: 0,
                                                                        y2: 1
                                                                    },
                                                                    stops: [
                                                                        [0, Highcharts.getOptions().colors[0]],
                                                                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                                                    ]
                                                                },
                                                                marker: {
                                                                    radius: 2
                                                                },
                                                                lineWidth: 1,
                                                                states: {
                                                                    hover: {
                                                                        lineWidth: 1
                                                                    }
                                                                },
                                                                threshold: null
                                                            }
                                                        },

                                                        series: [{
                                                            type: 'area',
                                                            name: '<?= strtoupper($key) ?> - TRY',
                                                            data: [
                                                                <?php
                                                                foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                    if (($value[0] / 1000) < (time() - (86400 * 365))) {
                                                                        continue;
                                                                    } ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                                                            }
                                                                                                                ?>
                                                            ]

                                                        }]
                                                    });
                                                });
                                            </script>
                                        </div>
                                        <div class="borsaTimerTabContent">
                                            <div class="currencyChart" id="container_5y"></div>

                                            <script>
                                                $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php?code=<?= strtoupper($key) ?>", function(values) {


                                                    Highcharts.chart('container_5y', {
                                                        chart: {
                                                            zoomType: 'x'
                                                        },
                                                        title: {
                                                            text: '<?= strtoupper($key) ?> - TRY 5 Yıllık Grafik Tablosu'
                                                        },
                                                        subtitle: {
                                                            text: document.ontouchstart === undefined ?
                                                                '' : ''
                                                        },
                                                        xAxis: {
                                                            type: 'datetime'
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: ''
                                                            }
                                                        },
                                                        legend: {
                                                            enabled: false
                                                        },
                                                        plotOptions: {
                                                            area: {
                                                                fillColor: {
                                                                    linearGradient: {
                                                                        x1: 0,
                                                                        y1: 0,
                                                                        x2: 0,
                                                                        y2: 1
                                                                    },
                                                                    stops: [
                                                                        [0, Highcharts.getOptions().colors[0]],
                                                                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                                                    ]
                                                                },
                                                                marker: {
                                                                    radius: 2
                                                                },
                                                                lineWidth: 1,
                                                                states: {
                                                                    hover: {
                                                                        lineWidth: 1
                                                                    }
                                                                },
                                                                threshold: null
                                                            }
                                                        },

                                                        series: [{
                                                            type: 'area',
                                                            name: '<?= strtoupper($key) ?> - TRY',
                                                            data: [
                                                                <?php
                                                                foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                    if ($key3 > 700 && $key3 < 2000) {
                                                                ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                                                    }
                                                                                                }
                                                                                                        ?>
                                                            ]

                                                        }]
                                                    });
                                                });
                                            </script>
                                        </div>
                                    <?php } else {
                                    ?>
                                        <div class="borsaTimerTabContent">

                                            <div class="currencyChart" id="container_bank"></div>
                                            <script>
                                                $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php?code=<?= strtoupper($key) ?>", function(values) {


                                                    Highcharts.chart('container_bank', {
                                                        chart: {
                                                            zoomType: 'x'
                                                        },
                                                        title: {
                                                            text: '<?= strtoupper($key) ?> - TRY <?= $banka_name ?> Grafik Tablosu'
                                                        },
                                                        subtitle: {
                                                            text: document.ontouchstart === undefined ?
                                                                '' : ''
                                                        },
                                                        xAxis: {
                                                            type: 'datetime'
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: ''
                                                            }
                                                        },
                                                        legend: {
                                                            enabled: false
                                                        },
                                                        plotOptions: {
                                                            area: {
                                                                fillColor: {
                                                                    linearGradient: {
                                                                        x1: 0,
                                                                        y1: 0,
                                                                        x2: 0,
                                                                        y2: 1
                                                                    },
                                                                    stops: [
                                                                        [0, Highcharts.getOptions().colors[0]],
                                                                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                                                    ]
                                                                },
                                                                marker: {
                                                                    radius: 2
                                                                },
                                                                lineWidth: 1,
                                                                states: {
                                                                    hover: {
                                                                        lineWidth: 1
                                                                    }
                                                                },
                                                                threshold: null
                                                            }
                                                        },

                                                        series: [{
                                                            type: 'area',
                                                            name: '<?= strtoupper($key) ?> - TRY',
                                                            data: [
                                                                <?php $data = get_url_banka(
                                                                    $ad = "https://www.doviz.com/api/v11/assets/" . strtoupper($key) . "/daily"
                                                                );
                                                                if (empty(json_decode($data, true)[0])) {
                                                                    $data = get_url_curl("https://www.doviz.com/api/v11/assets/" . strtoupper($key) . "/daily");
                                                                }
                                                                //echo $ad;
                                                                echo $data;
                                                                foreach (json_decode($data, true)['data'] as $key3 => $value) {
                                                                ?>[<?= $value['update_date'] * 1000 + 10850000 ?>, <?= str_replace(",", ".", $value['close']) ?>], <?php
                                                                                                                                                                } ?>

                                                            ]
                                                        }]
                                                    });
                                                });
                                            </script>


                                            <p>* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                        </div>
                                    <?php
                                    } ?>
                                </div>
                                <!-- #Widget -->

                                <!-- Widget -->
                                <style>
                                    .financeTable {
                                        line-height: 42px;
                                        height: 42px !important;
                                    }
                                </style>
                                <?php if(!empty($banka_tablo[1][0])) : ?>
                                <div class="widget">
                                    <div class="financeBar">
                                        <div class="financeBlockBig lastFinanceBlock">
                                            <div class="financeBlockHead kur"><?= mb_strtoupper($currency_data['full_name'][$key], "UTF-8") ?> BANKA KURLARI</div>
                                            <div style="display: flex;" class="bank-table-collapse">
                                                <table class="financeTable kurTable firstKur">
                                                    <tr>
                                                        <th>Banka</th>
                                                        <th>Alış</th>
                                                        <th>Satış</th>
                                                    </tr>
                                                    <?php

                                                    preg_match_all('@<td>                        <a href="(.*?)" data-ga-event="asset_detail_other_sources_click">(.*?)</a>                    </td> @si', $banka_tablo[1][0], $banka_adi);
                                                    preg_match_all('@<td class="text-bold">(.*?)</td>@si', $banka_tablo[1][0], $banka_data);
                                                    // $banka_adi[3] split 2 array equally
                                                    $banka_list = array_chunk($banka_adi[2], ceil(count($banka_adi[2]) / 2));


                                                    foreach ($banka_list[0] as $key => $val) :
                                                        if ($key == 0) {
                                                            $baslangic = 0;
                                                        } else {
                                                            $baslangic = $key * 4;
                                                        }
                                                        if (empty($val)) {
                                                            continue;
                                                        }
                                                    ?>
                                                        <tr>
                                                            <td class="bank-name" style="white-space: nowrap;">
                                                                <?= str_replace([
                                                                    "QNB Finansbank",
                                                                    "Merkez Bankası",
                                                                    "Kuveyt Türk Senin Bankan",
                                                                ], ["Finansbank", "M. Bank.", "Kuveyt Türk"], trim(strip_tags($val))) ?></td>
                                                            <td><?= $banka_data[1][$baslangic] ?></td>
                                                            <td><?= $banka_data[1][$baslangic + 1] ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>


                                                </table>
                                                <table class="financeTable kurTable lastKur" style="overflow: hidden;line-height:42px">
                                                    <?php if (! wp_is_mobile()) { ?>
                                                        <tr>
                                                            <th>Banka</th>
                                                            <th style="">Alış</th>
                                                            <th style="">Satış</th>
                                                        </tr>
                                                    <?php } ?>

                                                    <?php

                                                    foreach ($banka_list[1] as $key => $val) :

                                                        if ($key == 0) {
                                                            $baslangic = 0;
                                                        } else {
                                                            $baslangic = $key * 4;
                                                        }

                                                    ?>
                                                        <tr>
                                                            <td class="bank-name" style="white-space: nowrap;">
                                                                <?= str_replace([
                                                                    "QNB Finansbank",
                                                                    "Merkez Bankası",
                                                                    "Kuveyt Türk Senin Bankan",
                                                                ], ["Finansbank", "M. Bank.", "Kuveyt Türk"], trim(strip_tags($val))) ?></td>
                                                            <?php if (wp_is_mobile()) {
                                                            ?>
                                                                <td style="padding-left: 12px;"><?= $banka_data[1][$baslangic] ?></td>
                                                                <td style="padding-left: 12px"><?= $banka_data[1][$baslangic + 1] ?></td>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <td style="padding-left: 12px !important;"><?= $banka_data[1][$baslangic] ?></td>
                                                                <td style="padding-left: 12px !important;"><?= $banka_data[1][$baslangic + 1] ?></td>
                                                            <?php
                                                            } ?>

                                                        </tr>
                                                    <?php endforeach; ?>

                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- #Widget -->
                                <?php endif; ?>
                                <?php
                                // $bp_options['veri_sayfalari_text'] find by type value and kisa_kod value by array
                                foreach ($bp_options['veri_sayfalari_text'] as $value) {
                                    if ($value['type'] == 'doviz' && $value['kisa_kod'] == htmlspecialchars($_GET['c'])) {
                                ?>
                                        <div class="widget">
                                            <div class="sayfaAltMakale">
                                                <h2><?= $value['baslik'] ?></h2>

                                                <p style="padding: 20px; line-height: 2"><?= (strip_tags($value['content'])) ?></p>
                                            </div>

                                        </div>
                                        <!-- #Widget -->
                                        <div class="clear"></div>
                                <?php
                                    }
                                }

                                ?>
                            </div>

                            <?php if ($bp_options['canliSohbet'] == true) : get_template_part("inc/widgets/live_chat");
                            endif; ?>


                        </div>
                        <!-- #MainBar -->


                    </div>

                </div>

            </div>
            <?php if (!wp_is_mobile() && function_exists('pv_v7_market_sidebar')) {
                pv_v7_market_sidebar('doviz-detay');
            } ?>
        </div>

        <?php dynamic_sidebar('Sayfa Alt (Döviz Detay)'); ?>
    </section>
    <!-- Content -->
    <div class="clear"></div>

</div>
<script>
    function listemeEkle(doviz) {

        $.ajax({
            type: "POST",
            url: "<?php bloginfo("template_directory") ?>/user_api.php?type=insert_liste&_" + $.now(),
            data: "doviz=" + doviz,
            success: function(result) {

                if (result == "Ok") {
                    $(".success").show(300);
                    $(".addList").html("ÇIKAR <i class='remove'></i>");
                    $(".addList").attr("onclick", "listedenCikar('" + doviz + "')");
                } else {
                    alert("Bir hata oluştu.");
                }


            }
        });
    }

    function listedenCikar(doviz) {

        $.ajax({
            type: "POST",
            url: "<?php bloginfo("template_directory") ?>/user_api.php?type=delete_liste&_" + $.now(),
            data: "doviz=" + doviz,
            success: function(result) {
                if (result == "Ok") {
                    $(".success").show(300);
                    $(".addList").html("LİSTEME EKLE <i class='add'></i>");
                    $(".addList").attr("onclick", "listemeEkle('" + doviz + "')");
                } else {
                    alert("Bir hata oluştu.");
                }


            }
        });
    }

    function alarmCikar(doviz) {

        $.ajax({
            type: "POST",
            url: "<?php bloginfo("template_directory") ?>/user_api.php?type=delete_alarm&_" + $.now(),
            data: "doviz=" + doviz,
            success: function(result) {
                if (result == "Ok") {
                    $(".success").show(300);
                    $(".alarmKur").html('ALARM KUR <i class="ring">');
                    $(".alarmKur").attr("onclick", "alarmKur('" + doviz + "')");
                } else {
                    alert("Bir hata oluştu.");
                }


            }
        });
    }

    function alarmKur(doviz) {
        var miktar = prompt("Haberdar olmak istediğiniz miktarı girin");

        if (miktar != null) {
            $.ajax({
                type: "POST",
                url: "<?php bloginfo("template_directory") ?>/user_api.php?type=insert_alarm&_" + $.now(),
                data: "doviz=" + doviz + "&miktar=" + miktar,
                success: function(result) {

                    if (result == "Ok") {
                        $(".success").show(300);
                        $(".alarmKur").html("ÇIKAR <i class='remove'></i>");
                        $(".alarmKur").attr("onclick", "alarmCikar('" + doviz + "')");
                    } else {
                        alert("Bir hata oluştu.");
                    }


                }
            });
        }
    }

    function girisYap() {
        alert("Bu özelliği kullanmak için lütfen giriş yapınız.");
    }
</script>

<script>
    /*
  Tab (Borsa Timer Tab)
  */
    $(document).ready(function() {
        $("section.content .widebar .widget .borsaTimerTabContent").hide();
        $("section.content .widebar .widget .borsaTimerTabContent:first").show();
        $("section.content .widebar .widget .borsaTimerTabHead ul li:first").addClass("active");
        $("section.content .widebar .widget .borsaTimerTabHead ul li").click(function() {
            $("section.content .widebar .widget .borsaTimerTabHead ul li").removeClass("active");
            $(this).addClass("active");
            $("section.content .widebar .widget .borsaTimerTabContent").hide();
            var tab = $(this).index();
            $("section.content .widebar .widget .borsaTimerTabContent:eq(" + tab + ")").fadeIn();
            return false;
        });
    });
</script>
<script>
    function randomStr(len, arr) {
        var ans = '';
        for (var i = len; i > 0; i--) {
            ans += arr[Math.floor(Math.random() * arr.length)];
        }

        return ans;
    }

    function live_chat() {
        $(".commentForm .submit").val("Gönderiliyor...");
        var random_key = randomStr(10, '1234567890');

        var comment = $(".ql-editor").html();
        var name = $(".nameText").val();

        if (comment.length > 7 && name.length > 1) {
            $(".live_chat_message").html("");
            $.post("<?= admin_url('admin-ajax.php') ?>", {
                    action: "live_chat",
                    page_id: <?= $current_page_id ?>,
                    name: name,
                    comment: comment,
                    random_key: random_key,
                    type: '<?= $_GET['c'] ?? '' ?>'
                })
                .done(function(data) {
                    $(".commentForm .submit").val("Yorumu Gönder");
                });
        } else {
            $(".commentForm .submit").val("Yorumu Gönder");
            alert("Lütfen tüm alanları doldurun.");
        }

    }

    function arr_diff(a1, a2) {
        var a = [],
            diff = [];

        for (var i = 0; i < a1.length; i++) {
            a[a1[i]] = true;
        }

        for (var i = 0; i < a2.length; i++) {
            if (a[a2[i]]) {
                delete a[a2[i]];
            } else {
                a[a2[i]] = true;
            }
        }

        for (var k in a) {
            diff.push(k);
        }

        return diff;
    }

    function get_live_chat(page_id) {
        var live_id = [];

        $('.commentListing .comment').each(function() {
            live_id.push($(this).data('id'));
        })

        var current_data = $(".commentListing").html();
        $.post("<?= admin_url('admin-ajax.php') ?>", {
                action: "get_live_chat",
                page_id: page_id,
                type: '<?= $_GET['c'] ?? '' ?>'
            })
            .done(function(data) {
                $('.commentListing .loading').remove();
                var parseJson = jQuery.parseJSON(data);
                var difference_json = arr_diff(live_id, parseJson['search_id']);

                $(difference_json).each(function(index, value) {

                    $(".commentListing").prepend(parseJson[value]['html']).text();
                    var $el = $(".newChat_" + value),
                        x = 1000,
                        originalColor = $el.css("background");

                    $el.fadeIn("fast", function() {
                        $el.css("background", "#fffce7");
                    });

                    setTimeout(function() {
                        $el.fadeIn("fast", function() {
                            $el.css("background", originalColor);
                        });

                    }, x);

                })

            });
    }

    setInterval(function() {
        get_live_chat(<?= $current_page_id ?>)
    }, 1000);
</script>


<!-- #Site Wrapper -->
<?php get_footer(); ?>