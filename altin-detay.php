<?php
/*
  Template Name: Altın Sayfası
*/
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern market sidebar added for Piyasa Vizyon styling.
@$altin = str_replace("--", "-", $_GET['a']);
$altin = str_replace('ons-altin-usd-fiyati', 'altin-ons-fiyati', $altin);
$borsa = get_url_curl($ad = "https://bigpara.hurriyet.com.tr/altin/$altin/");

$current_page_id = get_queried_object_id();

preg_match_all('@<h1 class="pageTitle mBot10">(.*?)</h1>@si', $borsa, $parite_name);
preg_match_all('@<span class="value(.*?)">(.*?)</span>@si', $borsa, $borsa_value);
preg_match_all('@<div class="col-3 smaller">Son (.*?)</div>@si', $borsa, $borsa_update);
preg_match_all('@<span class="text3">(.*?)</span>@si', $borsa, $borsa_rate);
preg_match_all('@<h2>(.*?)</h2>@si', $borsa, $borsa_aname);
preg_match_all('@/v1/chart/exchangegold/(.*?)/@si', $borsa, $exchange_id);
$borsa_rate[1][1] = explode(" /", $borsa_rate[1][1]);
$borsa_rate[1][1] = str_replace("( ", '', $borsa_rate[1][0]);


$borsa_value[1][0] = $borsa_value[2][0];

if (empty($borsa_rate[1][1])) {
    $borsa_rate[1][1] = "0,00";
}
$new_title = $parite_name[1][0] . " - " . get_bloginfo('name');
function generate_custom_title($title)
{
    global $new_title;
    $title = $new_title;
    return $title;
}

add_filter('pre_get_document_title', 'generate_custom_title', 10);
add_filter('wpseo_title', 'generate_custom_title', 15);

if (empty(@$_GET['a'])) {
    ?><script>
        window.location.href = "<?php bloginfo("home") ?>";
    </script><?php
}

            get_header();

                ?>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="<?php bloginfo('template_directory'); ?>/js/highcharts.js"></script>
<style>
    .currencyTable tr td {
        font-weight: normal;
    }
</style>
<!-- Site Wrapper -->
<div class="site-wrapper pv-market-native pv-market-gold-detail-native">

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
                            <li><a href="<?php bloginfo('home') ?>/<?= $bp_options['page_altinlar'] ?>/">Altın Fiyatları<i>/</i></a></li>
                            <li class="post bg"><span><?= $parite_name[1][0] ?></span></li>
                        </ul>
                    </div>

                    <h1 class="singlePageTitle"><?= $parite_name[1][0] ?></h1>

                    <div class="pv-market-detail-content pv-gold-detail-content">

                        <!-- Main Content -->
                        <div class="mainContent onsAltin">

                            <!-- Main -->
                            <div class="main">

                                <!-- Widget -->
                                <div class="widget" style="margin-bottom: 15px;">
                                    <div class="categoryTab">


                                        <!-- Cat Tab 1 -->
                                        <div class="catTabContent">
                                            <?php if (wp_is_mobile()) : ?>
                                            <?php else : ?>
                                                <div class="borsaValue">
                                                    <?= $borsa_value[1][0] ?>

                                                    <?php if (trim(str_replace(array("%" . ","), array("", "."), $borsa_rate[1][0])) > 0) {
                    $crease_status = "increase";
                    $crease_color = "#32ba5b";
                } else {
                    $crease_status = "decrease";
                    $crease_color = "#ef291f";
                } ?>
                                                    <!--<div class="borsaRate" style="color: <?= $crease_color ?> !important;"><i class="<?= $crease_status ?>"></i>(<?= $borsa_rate[1][0] ?>)</div>-->
                                                </div><?php endif; ?>
                                            <div class="clear"></div>
                                            <!-- Tab Head -->
                                            <div class="borsaTimerTabHead bg">
                                                <ul>
                                                    <li><span>GÜNLÜK</span></li>
                                                    <li><span>HAFTA</span></li>
                                                    <li><span>AY</span></li>
                                                    <li><span>BU YIL</span></li>
                                                    <li><span>3 YILLIK</span></li>
                                                </ul>
                                            </div>
                                            <div class="borsaTimerTabContent">

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
                                                                type: 'datetime',
                                                                dateTimeLabelFormats: {
                                                                    day: '%d %b %Y' //ex- 01 Jan 2016
                                                                }
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
                                                                    <?php

                                                                    $gunluk_data = json_decode(get_bigpara($ad = "https://bigpara.hurriyet.com.tr/api/v1/chart/exchangegold/" . $exchange_id[1][0] . "/1"), true);


                                                                    foreach ($gunluk_data as $key3 => $value) {
                                                                        ?>[<?= (strtotime($value['tarih']) * 1000) + 10850000 ?>, <?= $value['kapanis'] ?>], <?php
                                                                    }
                                                                                                                                                                ?>
                                                                ]

                                                            }]
                                                        });
                                                    });
                                                </script>

                                                <div class="clear"></div>
                                                <p>* Piyasaların kapalı olduğu gün ve saatlerde veri akışı bulunmamaktadır.</p>
                                            </div>
                                            <div class="borsaTimerTabContent">
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
                                                                    <?php

                                                                    $gunluk_data = json_decode(get_bigpara($ad = "https://bigpara.hurriyet.com.tr/api/v1/chart/exchangegold/" . $exchange_id[1][0] . "/3"), true);


                                                                    foreach ($gunluk_data as $key3 => $value) {
                                                                        ?>[<?= (strtotime($value['tarih']) * 1000) + 10850000 ?>, <?= $value['kapanis'] ?>], <?php
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
                                                                    <?php

                                                                    $gunluk_data = json_decode(get_bigpara($ad = "https://bigpara.hurriyet.com.tr/api/v1/chart/exchangegold/" . $exchange_id[1][0] . "/4"), true);


                                                                    foreach ($gunluk_data as $key3 => $value) {
                                                                        ?>[<?= (strtotime($value['tarih']) * 1000) + 10850000 ?>, <?= $value['kapanis'] ?>], <?php
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
                                                                    <?php

                                                                    $gunluk_data = json_decode(get_bigpara($ad = "https://bigpara.hurriyet.com.tr/api/v1/chart/exchangegold/" . $exchange_id[1][0] . "/8"), true);


                                                                    foreach ($gunluk_data as $key3 => $value) {
                                                                        ?>[<?= (strtotime($value['tarih']) * 1000) + 10850000 ?>, <?= $value['kapanis'] ?>], <?php
                                                                    }
                                                                                                                                                                ?>

                                                                ]

                                                            }]
                                                        });
                                                    });
                                                </script>
                                            </div>
                                            <div class="borsaTimerTabContent">
                                                <div class="currencyChart" id="container_10y"></div>
                                                <script>
                                                    $.get("<?php bloginfo("template_directory"); ?>/api/highcharts.php", function(values) {


                                                        Highcharts.chart('container_10y', {
                                                            chart: {
                                                                zoomType: 'x'
                                                            },
                                                            title: {
                                                                text: '<?= $endeks_name[1][0] ?> 3 Yıllık'
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
                                                                    <?php

                                                                    $gunluk_data = json_decode(get_bigpara($ad = "https://bigpara.hurriyet.com.tr/api/v1/chart/exchangegold/" . $exchange_id[1][0] . "/9"), true);


                                                                    foreach ($gunluk_data as $key3 => $value) {
                                                                        ?>[<?= (strtotime($value['tarih']) * 1000) + 10850000 ?>, <?= $value['kapanis'] ?>], <?php
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

                                <?php
                                preg_match_all('@<li class="flex align-items-center justify-content-between"><span>(.*?)</span><span>(.*?)</span></li>@si', $borsa, $alt_data);
                                ?>

                                <!-- Widget -->
                                <div class="widget">
                                    <!-- Currency Showcase -->
                                    <div class="currencyShowcase mobileBottomNo" style="width: 100%;">
                                        <?php if (wp_is_mobile()) {
                                    ?>
                                            <table class="currencyTable">

                                                <?php foreach ($alt_data[2] as $key => $value) : ?>


                                                    <tr>
                                                        <td><b><?= $alt_data[1][$key] ?></b></td>
                                                        <td><?= $alt_data[2][$key] ?></td>
                                                    </tr>


                                                <?php endforeach; ?>
                                            </table>
                                        <?php
                                } else {
                                    ?>
                                            <table class="currencyTable">

                                                <?php $i = 1;
                                    foreach ($alt_data[2] as $key => $value) : if ($i == $key) : continue;
                                    endif; ?>

                                                    <tr>
                                                        <td><b><?= $alt_data[1][$key] ?></b></td>
                                                        <td><?= $alt_data[2][$key] ?></td>
                                                        <td><b><?= $alt_data[1][$key + 1] ?></b></td>
                                                        <td><?= $alt_data[2][$key + 1] ?></td>
                                                    </tr>


                                                <?php $i = $key + 1;
                                    endforeach; ?>



                                            </table>
                                        <?php
                                } ?>
                                        <!-- #Widget -->

                                    </div>

                                </div>
                                <!-- #Widget -->
	                            <?php
	                            // $bp_options['veri_sayfalari_text'] find by type value and kisa_kod value by array
	                            foreach ( $bp_options['veri_sayfalari_text'] as $value ) {
		                            if ( $value['type'] == 'altin' && $value['kisa_kod'] == str_replace('-fiyati', '', htmlspecialchars( $_GET['a'] )) ) {
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
                pv_v7_market_sidebar('altin-detay');
            } ?>

        </div>
        <?php dynamic_sidebar('Sayfa Alt (Altın Detay)'); ?>
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
                    type: "<?= $_GET['a'] ?? '' ?>"
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
                type: '<?= $_GET['a'] ?? '' ?>'
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


<?php
get_footer();
