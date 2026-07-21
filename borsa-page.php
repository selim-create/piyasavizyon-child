<?php
/*
  Template Name: Borsa Sayfası
*/
// Child theme override: original BirFinans data/parsing logic preserved; only scoped wrapper class is added for Piyasa Vizyon styling.
get_header();
$borsa = get_url_curl("https://finans.mynet.com/borsa/");
$current_page_id = get_queried_object_id();

preg_match_all('@<h1 class="mr-3">(.*?)</h1@si', $borsa, $endeks_name);
preg_match_all('@<span class="name">BIST </span><span class="dynamic-price-XU100">(.*?)</span>@si', $borsa, $borsa_value);
preg_match_all('@</div><span class="label">Son: <span class="dynamic-last-updated-date-XU100">(.*?)</span></span></div>@si', $borsa, $borsa_update);
preg_match_all('@<span class="change-icon  change-(.*?)   mr-1 dynamic-daily-direction-icon-XU100"></span><span class="dynamic-daily-direction-XU100">(.*?)</span> /<span class="dynamic-direction-XU100">(.*?)</span></div><span class="label">Günlük Değişim</span></div>@si', $borsa, $borsa_rate);
preg_match_all('@<h2>(.*?)</h2>@si', $borsa, $borsa_aname);
?>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="<?php bloginfo('template_directory'); ?>/js/highcharts.js"></script>
<style>
    .currencyTable tr td {
        font-weight: normal;
    }

    .currencyTable tr td b {
        color: #3b72de;
    }
</style>
<!-- Site Wrapper -->
<div class="site-wrapper pv-market-native pv-market-borsa-native">

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
                            <li class="post bg"><span>Borsa</span></li>
                        </ul>
                    </div>
                    <?php if (!wp_is_mobile()) {
    ?><h1 class="singlePageTitle"><?php the_title() ?></h1><?php
} else {
        ?><center>
                            <h1 class="singlePageTitle"><?php the_title() ?></h1>
                        </center><?php
    } ?>


                    <div class="singleContent block">

                        <!-- Main Content -->
                        <div class="mainContent">

                            <!-- Main -->
                            <div class="main">

                                <!-- Widget -->
                                <div class="widget">
                                    <div class="categoryTab">

                                        <!-- Tab Head -->
                                        <div class="tabHead borsaTabHead bg">
                                            <ul>
                                                <li><span>BIST 100 VERİLERİ</span></li>
                                                <li><span>BIST 50 VERİLERİ</span></li>
                                                <li><span>BIST 30 VERİLERİ</span></li>
                                            </ul>
                                            <a href="<?php bloginfo('home') ?>/<?= $bp_options['page_tumendeksler'] ?>/" class="allCurrencyData">Bütün Endeksler »</a>
                                        </div>

                                        <!-- Cat Tab 1 -->
                                        <div class="catTabContent">
                                            <div class="borsaValue">
                                                <span>Son</span><?= $borsa_value[1][0] ?>

                                                <?php if (trim(str_replace(array("%" . ","), array("", "."), $borsa_rate[3][0])) > 0) {
        $crease_status = "increase";
        $crease_color = "#32ba5b";
    } else {
        $crease_status = "decrease";
        $crease_color = "#ef291f";
    } ?>
                                                <div class="borsaRate" style="color: <?= $crease_color ?> !important;"><i class="<?= $crease_status ?>"></i>(<?= $borsa_rate[3][0] ?>)</div>
                                            </div>
                                            <div class="lastUpdate">Son Güncelleme: <?= $borsa_update[1][0] ?></div>
                                            <div class="clear"></div>
                                            <!-- Tab Head -->
                                            <div class="borsaTimerTabHead borsaTimerTabHead1 bg">
                                                <ul>
                                                    <li><span>BUGÜN</span></li>
                                                    <li><span>BU HAFTA</span></li>
                                                    <li><span>BU AY</span></li>
                                                    <li><span>BU YIL</span></li>
                                                    <li><span>12 YILLIK</span></li>
                                                </ul>
                                            </div>
                                            <div class="borsaTimerTabContent borsaTimerTabContent1">

                                                <div class="currencyChart" id="container_daily"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_daily', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Günlük'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


                                                                    foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                        if (($value[0] / 1000) < time() - 86400 * 3) {
                                                                            continue;
                                                                        } ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                    }
                                                                                                                    ?>
                                                                ]

                                                            }]
                                                        });
                                                    });
                                                </script>

                                                <div class="clear"></div>
                                                <p style="margin-bottom: 15px; margin-top: 15px;">* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                                <div class="widget" style="margin-top: 10px;">
                                                    <?php
                                                    $data_page = get_url_curl("https://finans.mynet.com/borsa/endeks/xu100-bist-100/");

                                                    preg_match_all('@<li class="flex align-items-center justify-content-between"><span>(.*?)</span><span>(.*?)</span></li>@si', $borsa, $alt_data);

                                                    ?>
                                                    <!-- Currency Showcase -->

                                                </div>
                                            </div>
                                            <div class="borsaTimerTabContent borsaTimerTabContent1">
                                                <div class="currencyChart" id="container_weekly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_weekly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Haftalık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent1">
                                                <div class="currencyChart" id="container_monthly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_monthly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Aylık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent1">
                                                <div class="currencyChart" id="container_yearly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_yearly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Yıllık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent1">
                                                <div class="currencyChart" id="container_10y"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_10y', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> 12 Yıllık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


                                                                    foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                        if (($value[0] / 1000) < (time() - (86400 * 365 * 12))) {
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

                                        </div>

                                        <?php
                                        $borsa = get_url_curl("https://finans.mynet.com/borsa/endeks/xu050-bist-50/");
                                        
                                        preg_match_all('@<h1 class="mr-3">(.*?)</h1@si', $borsa, $endeks_name);
                                        preg_match_all('@<div class="heading-new-bar-col-item unit-price"><div class="data-value"><span class="change-icon  change-(.*?)   mr-2"></span>(.*?)</div>@si', $borsa, $borsa_value);
                                        preg_match_all('@</div><span class="label">Son:(.*?)</span></div><div class="heading-new-bar-col-item daily-change">@si', $borsa, $borsa_update);
                                        preg_match_all('@<div class="heading-new-bar-col-item daily-change"><div class="data-value"><span class="change-icon  change-(.*?)   mr-1"></span>(.*?)</div><span class="label">Günlük Değişim </span>@si', $borsa, $borsa_rate);
                                        preg_match_all('@<h2>(.*?)</h2>@si', $borsa, $borsa_aname);

                                        ?>
                                        <!-- Cat Tab 2 -->

                                        <div class="catTabContent">
                                            <div class="borsaValue">
                                                <span>Son</span><?= $borsa_value[2][0] ?>

                                                <?php if (trim(str_replace(array("%" . ","), array("", "."), $borsa_rate[2][0])) > 0) {
                                            $crease_status = "increase";
                                            $crease_color = "#32ba5b";
                                        } else {
                                            $crease_status = "decrease";
                                            $crease_color = "#ef291f";
                                        } ?>
                                                <div class="borsaRate" style="color: <?= $crease_color ?> !important;"><i class="<?= $crease_status ?>"></i>(<?= $borsa_rate[2][0] ?>)</div>
                                            </div>
                                            <div class="lastUpdate">Son Güncelleme: <?= $borsa_update[1][0] ?></div>
                                            <div class="clear"></div>
                                            <!-- Tab Head -->
                                            <div class="borsaTimerTabHead borsaTimerTabHead2 bg">
                                                <ul>
                                                    <li><span>BUGÜN</span></li>
                                                    <li><span>BU HAFTA</span></li>
                                                    <li><span>BU AY</span></li>
                                                    <li><span>BU YIL</span></li>
                                                    <li><span>12 YILLIK</span></li>
                                                </ul>
                                            </div>
                                            <div class="borsaTimerTabContent borsaTimerTabContent2">

                                                <div class="currencyChart" id="container_50_daily"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_50_daily', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Günlük'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


                                                                    foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                        if (($value[0] / 1000) < time() - 86400 * 3) {
                                                                            continue;
                                                                        } ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                    }
                                                                                                                    ?>
                                                                ]

                                                            }]
                                                        });
                                                    });
                                                </script>

                                                <div class="clear"></div>
                                                <p style="margin-bottom: 15px; margin-top: 15px;">* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                                <div class="widget" style="margin-top: 10px;">
                                                    <?php
                                                    $data_page = get_url_curl("https://finans.mynet.com/borsa/endeks/xu050-bist-50/");
                                                    preg_match_all('@<li class="clr"><span class="dtColOne">(.*?)</span><span class="dtColTwo">(.*?)</span></li>@si', $data_page, $alt_data);
                                                    ?>
                                                    <!-- Currency Showcase -->


                                                </div>
                                            </div>
                                            <div class="borsaTimerTabContent borsaTimerTabContent2">
                                                <div class="currencyChart" id="container_50_weekly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_50_weekly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Haftalık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent2">
                                                <div class="currencyChart" id="container_50_monthly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_50_monthly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Aylık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent2">
                                                <div class="currencyChart" id="container_50_yearly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_50_yearly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Yıllık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent2">
                                                <div class="currencyChart" id="container_50_10y"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_50_10y', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> 12 Yıllık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


                                                                    foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                        if (($value[0] / 1000) < (time() - (86400 * 365 * 12))) {
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

                                        </div>

                                        <?php
                                        $borsa = get_url_curl("https://finans.mynet.com/borsa/endeks/xu030-bist-30/");
                                        
                                        
                                        preg_match_all('@<h1 class="mr-3">(.*?)</h1@si', $borsa, $endeks_name);
                                        preg_match_all('@<div class="heading-new-bar-col-item unit-price"><div class="data-value"><span class="change-icon  change-(.*?)   mr-2"></span>(.*?)</div>@si', $borsa, $borsa_value);
                                        preg_match_all('@</div><span class="label">Son:(.*?)</span></div><div class="heading-new-bar-col-item daily-change">@si', $borsa, $borsa_update);
                                        preg_match_all('@<div class="heading-new-bar-col-item daily-change"><div class="data-value"><span class="change-icon  change-(.*?)   mr-1"></span>(.*?)</div><span class="label">Günlük Değişim </span>@si', $borsa, $borsa_rate);
                                        preg_match_all('@<h2>(.*?)</h2>@si', $borsa, $borsa_aname);

                                        ?>
                                        <!-- Cat Tab 2 -->
                                        <div class="catTabContent">
                                            <div class="borsaValue">
                                                <span>Son</span><?= $borsa_value[2][0] ?>

                                                <?php if (trim(str_replace(array("%" . ","), array("", "."), $borsa_rate[2][0])) > 0) {
                                            $crease_status = "increase";
                                            $crease_color = "#32ba5b";
                                        } else {
                                            $crease_status = "decrease";
                                            $crease_color = "#ef291f";
                                        } ?>
                                                <div class="borsaRate" style="color: <?= $crease_color ?> !important;"><i class="<?= $crease_status ?>"></i>(<?= $borsa_rate[2][0] ?>)</div>
                                            </div>
                                            <div class="lastUpdate">Son Güncelleme: <?= $borsa_update[1][0] ?></div>
                                            <div class="clear"></div>
                                            <!-- Tab Head -->
                                            <div class="borsaTimerTabHead borsaTimerTabHead3 bg">
                                                <ul>
                                                    <li><span>BUGÜN</span></li>
                                                    <li><span>BU HAFTA</span></li>
                                                    <li><span>BU AY</span></li>
                                                    <li><span>BU YIL</span></li>
                                                    <li><span>12 YILLIK</span></li>
                                                </ul>
                                            </div>
                                            <div class="borsaTimerTabContent borsaTimerTabContent3">

                                                <div class="currencyChart" id="container_100_daily"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_100_daily', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Günlük'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


                                                                    foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                        if (($value[0] / 1000) < time() - 86400 * 3) {
                                                                            continue;
                                                                        } ?>[<?= $value[0] ?>, <?= $value[1] ?>], <?php
                                                                    }
                                                                                                                    ?>
                                                                ]

                                                            }]
                                                        });
                                                    });
                                                </script>

                                                <div class="clear"></div>
                                                <p style="margin-bottom: 15px; margin-top: 15px;">* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                                <div class="widget" style="margin-top: 10px;">
                                                    <?php
                                                    $data_page = get_url_curl("https://finans.mynet.com/borsa/endeks/xu030-bist-30/");
                                                    preg_match_all('@<li class="clr"><span class="dtColOne">(.*?)</span><span class="dtColTwo">(.*?)</span></li>@si', $data_page, $alt_data);
                                                    ?>


                                                </div>
                                            </div>
                                            <div class="borsaTimerTabContent borsaTimerTabContent3">
                                                <div class="currencyChart" id="container_100_weekly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_100_weekly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Haftalık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent3">
                                                <div class="currencyChart" id="container_100_monthly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_100_monthly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Aylık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent3">
                                                <div class="currencyChart" id="container_100_yearly"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_100_yearly', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> Yıllık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


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
                                            <div class="borsaTimerTabContent borsaTimerTabContent3">
                                                <div class="currencyChart" id="container_100_10y"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_100_10y', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> 12 Yıllık'
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
                                                                name: '<?= $endeks_name[1][0] ?>',
                                                                data: [
                                                                    <?php preg_match_all('@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data);
                                                                    $gunluk_data[1][0] = json_decode("{" . $gunluk_data[1][0] . "}", true);


                                                                    foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
                                                                        if (($value[0] / 1000) < (time() - (86400 * 365 * 12))) {
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

                                        </div>

                                    </div>
                                </div>
                                <!-- #Widget -->



                                <!-- Widget -->
                                <?php dynamic_sidebar("Borsa Alt (Content)"); ?>
                                <!-- #Widget -->

                            </div>


                        </div>
                        <!-- #MainBar -->


                    </div>

                </div>

                <?php if ($bp_options['canliSohbet'] == true) : get_template_part("inc/widgets/live_chat");
                endif; ?>

            </div>

            <!-- //Currency Showcase -->
            <?php
            if (!wp_is_mobile() && function_exists('pv_v7_market_sidebar')) {
                pv_v7_market_sidebar('borsa');
            }
            ?>

        </div>

        <?php dynamic_sidebar('Sayfa Alt (Borsa)'); ?>
    </section>
    <!-- Content -->
    <div class="clear"></div>

</div>
<!-- #Site Wrapper -->
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

        if (comment.length > 1 && name.length > 1) {
            $(".live_chat_message").html("");
            $.post("<?= admin_url('admin-ajax.php') ?>", {
                    action: "live_chat",
                    page_id: <?= $current_page_id ?>,
                    name: name,
                    comment: comment,
                    random_key: random_key,
                    type: "borsa"
                })
                .done(function(data) {

                    $(".commentForm .submit").val("Yorumu Gönder");
                    /*$(".commentListing").prepend(data);
                    var $el = $(".comment_live_"+random_key),
                        x = 1000,
                        originalColor = $el.css("background");

                    $el.fadeIn("fast", function() {
                        $el.css("background", "#fffce7");
                    });

                    setTimeout(function(){
                      $el.fadeIn("fast", function() {
                          $el.css("background", originalColor);
                      });

                    }, x);*/

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
                type: "borsa"
            })
            .done(function(data) {
                $(".commentListing .loading").remove();
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
    }, 1500);
</script>
<?php
get_footer();
