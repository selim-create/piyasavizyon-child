<?php
/*
  Template Name: Parite Sayfası
*/
@$parite = $_GET['p'];
$current_page_id = get_queried_object_id();

$borsa = get_data_service( "mynet?url=https://finans.mynet.com/parite/$parite/" );

preg_match_all( '@<h1 class="mr-3">(.*?)</h1@si', $borsa, $parite_name );
preg_match_all( '@<div class="data-value"><span class="change-icon change-up mr-2 dynamic-direction-icon-(.*?)"></span><span class="dynamic-price-(.*?)">(.*?)</span></div><span class="label">Son@si',
	$borsa, $borsa_value );
preg_match_all( '@<div class="data-value"><span class="change-icon change-up mr-1 dynamic-daily-direction-icon-(.*?)"></span><span class=" dynamic-direction-(.*?)">(.*?)</span></div><span class="label">Günlük Değişim</span>@si', $borsa, $borsa_rate );
preg_match_all( '@<h2>(.*?)</h2>@si', $borsa, $borsa_aname );
if ( empty( $borsa_rate[3][0] ) ) {
	$borsa_rate[3][0] = "0,00";
}

$new_title = $parite_name[1][0] . " - " . get_bloginfo( 'name' );
function generate_custom_title( $title ) {
	global $new_title;
	$title = $new_title;
	
	return $title;
}

add_filter( 'pre_get_document_title', 'generate_custom_title', 10 );
add_filter( 'wpseo_title', 'generate_custom_title', 15 );
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern sidebar added for Piyasa Vizyon styling.
get_header();
if ( empty( @$_GET['p'] ) ) {
	?>
    <script>
        window.location.href = "<?php bloginfo( "home" ) ?>";
    </script><?php
}
?>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="<?php bloginfo( 'template_directory' ); ?>/js/highcharts.js"></script>
    <style>
        .currencyTable tr td {
            font-weight: normal;
        }
    </style>
    <!-- Site Wrapper -->
    <div class="site-wrapper pv-market-native pv-market-parity-detail-native">
        
        <!-- Content -->
        <section class="content home">
            <div class="container-wrap">
                
                <!-- WideBar -->
                <div class="widebar floatLeft">
                    
                    <div class="singleWrapper">
                        
                        <!-- BreadCrumb -->
                        <div class="breadcrumb">
                            <ul class="block">
                                <li><a href="<?php bloginfo( 'home' ) ?>">Anasayfa<i>/</i></a></li>
                                <li><a href="<?php bloginfo( 'home' ) ?>/<?= $bp_options['page_pariteler'] ?>/">Pariteler<i>/</i></a></li>
                                <li class="post bg"><span><?= $parite_name[1][0] ?></span></li>
                            </ul>
                        </div>
                        
                        <h1 class="singlePageTitle"><?= $parite_name[1][0] ?></h1>
                        
                        <div class="pv-market-detail-content pv-parity-detail-content">
                            
                            <!-- Main Content -->
                            <div class="mainContent onsAltin">
                                
                                <!-- Main -->
                                <div class="main">
                                    
                                    <!-- Widget -->
                                    <div class="widget" style="margin-bottom: 15px;">
                                        <div class="categoryTab">
                                            
                                            
                                            <!-- Cat Tab 1 -->
                                            <div class="catTabContent">
												<?php if ( wp_is_mobile() ) : ?><?php else : ?>
                                                    <div class="borsaValue">
													<?= $borsa_value[3][0] ?>₺
													
													<?php if ( trim( str_replace( [ "%", "," ], [ "", "." ], $borsa_rate[3][0] ) ) > 0 ) {
														$crease_status = "increase";
														$crease_color  = "#32ba5b";
													} else {
														$crease_status = "decrease";
														$crease_color  = "#ef291f";
													} ?>
                                                    <div class="borsaRate" style="color: <?= $crease_color ?> !important;">
                                                        <i class="<?= $crease_status ?>"></i>(<?= $borsa_rate[3][0] ?>)
                                                    </div>
                                                    </div><?php endif; ?>
                                                <div class="clear"></div>
                                                <!-- Tab Head -->
                                                <div class="borsaTimerTabHead bg">
                                                    <ul>
                                                        <li><span>BUGÜN</span></li>
                                                        <li><span>BU HAFTA</span></li>
                                                        <li><span>BU AY</span></li>
                                                        <li><span>BU YIL</span></li>
                                                        <li><span>5 YILLIK</span></li>
                                                    </ul>
                                                </div>
                                                <div class="borsaTimerTabContent">
                                                    
                                                    <div class="currencyChart" id="container_daily"></div>
                                                    <script>
                                                        $.get("<?php bloginfo( "template_directory" ); ?>/api/highcharts.php", function (values) {


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
																			<?php preg_match_all( '@initChartData\({(.*?)}\)@si', $borsa, $gunluk_data );
																			$gunluk_data[1][0] = json_decode( "{" . $gunluk_data[1][0] . "}", true );
																			
																			
																			foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
																			if ( ( $value[0] / 1000 ) < time() - 86400 ) {
																				continue;
																			} ?>[<?= $value[0] + 10850000 ?>, <?= $value[1] ?>], <?php
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
                                                        $.get("<?php bloginfo( "template_directory" ); ?>/api/highcharts.php", function (values) {


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
																			foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
																			if ( ( $value[0] / 1000 ) < ( time() - ( 86400 * 7 ) ) ) {
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
                                                        $.get("<?php bloginfo( "template_directory" ); ?>/api/highcharts.php", function (values) {


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
																			foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
																			if ( ( $value[0] / 1000 ) < ( time() - ( 86400 * 30 ) ) ) {
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
                                                        $.get("<?php bloginfo( "template_directory" ); ?>/api/highcharts.php", function (values) {


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
																			foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
																			if ( ( $value[0] / 1000 ) < ( time() - ( 86400 * 365 ) ) ) {
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
                                                    <div class="currencyChart" id="container_10y"></div>
                                                    <script>
                                                        $.get("<?php bloginfo( "template_directory" ); ?>/api/highcharts.php", function (values) {


                                                            Highcharts.chart('container_10y', {
                                                                chart: {
                                                                    zoomType: 'x'
                                                                },
                                                                title: {
                                                                    text: '<?= $endeks_name[1][0] ?> 5 Yıllık'
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
																			foreach ($gunluk_data[1][0]['data'] as $key3 => $value) {
																			if ( ( $value[0] / 1000 ) < ( time() - ( ( 86400 * 365 ) * 5 ) ) ) {
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
									
									<?php
									preg_match_all( '@<li class="flex align-items-center justify-content-between"><span>(.*?)</span><span>(.*?)</span></li>@si', $borsa,
										$alt_data );
									?>
                                    
                                    <!-- Widget -->
                                    <div class="widget">
                                        <!-- Currency Showcase -->
                                        <div class="currencyShowcase mobileBottomNo" style="width: 100%;">
											<?php if ( wp_is_mobile() ) {
												?>
                                                <table class="currencyTable">
													
													<?php foreach ( $alt_data[2] as $key => $value ) : ?>
                                                        
                                                        
                                                        <tr>
                                                            <td><b><?= $alt_data[1][ $key ] ?></b></td>
                                                            <td><?= $alt_data[2][ $key ] ?></td>
                                                        </tr>
													
													
													<?php endforeach; ?>
                                                </table>
												<?php
											} else {
												?>
                                                <table class="currencyTable">
													
													<?php $i = 1;
													foreach ( $alt_data[2] as $key => $value ) : if ( $i == $key ) : continue;
													endif; ?>
                                                        
                                                        <tr>
                                                            <td><b><?= $alt_data[1][ $key ] ?></b></td>
                                                            <td><?= $alt_data[2][ $key ] ?></td>
                                                            <td><b><?= $alt_data[1][ $key + 1 ] ?></b></td>
                                                            <td><?= $alt_data[2][ $key + 1 ] ?></td>
                                                        </tr>
														
														
														<?php $i = $key + 1;
													endforeach; ?>
                                                
                                                
                                                </table>
												<?php
											} ?>
                                            <!-- #Widget -->
                                        </div>
                                    </div>
									<?php if ( $bp_options['canliSohbet'] == true ) : get_template_part( "inc/widgets/live_chat" );
									endif; ?>
                                
                                
                                </div>
                                <!-- #MainBar -->
                            
                            
                            </div>
                        
                        </div>
                    
                    </div>
                </div>
				<?php if (!wp_is_mobile() && function_exists('pv_v7_market_sidebar')) {
    pv_v7_market_sidebar('parite-detay');
} ?>
            </div>
			
			<?php dynamic_sidebar( 'Sayfa Alt (Parite Detay)' ); ?>
        </section>
        <!-- Content -->
        <div class="clear"></div>
    
    </div>
    
    
    <!-- #Site Wrapper -->
    <script>
        /*
	  Tab (Borsa Timer Tab)
	  */
        $(document).ready(function () {
            $("section.content .widebar .widget .borsaTimerTabContent").hide();
            $("section.content .widebar .widget .borsaTimerTabContent:first").show();
            $("section.content .widebar .widget .borsaTimerTabHead ul li:first").addClass("active");
            $("section.content .widebar .widget .borsaTimerTabHead ul li").click(function () {
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
                $.post("<?= admin_url( 'admin-ajax.php' ) ?>", {
                    action: "live_chat",
                    page_id: <?= $current_page_id ?>,
                    name: name,
                    comment: comment,
                    random_key: random_key,
                    type: "<?= $_GET['p'] ?? '' ?>"
                })
                    .done(function (data) {

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

            $('.commentListing .comment').each(function () {
                live_id.push($(this).data('id'));
            })

            var current_data = $(".commentListing").html();
            $.post("<?= admin_url( 'admin-ajax.php' ) ?>", {
                action: "get_live_chat",
                page_id: page_id,
                type: "<?= $_GET['p'] ?? '' ?>"
            })
                .done(function (data) {
                    $('.commentListing .loading').remove()
                    var parseJson = jQuery.parseJSON(data);
                    var difference_json = arr_diff(live_id, parseJson['search_id']);

                    $(difference_json).each(function (index, value) {

                        $(".commentListing").prepend(parseJson[value]['html']).text();
                        var $el = $(".newChat_" + value),
                            x = 1000,
                            originalColor = $el.css("background");

                        $el.fadeIn("fast", function () {
                            $el.css("background", "#fffce7");
                        });

                        setTimeout(function () {
                            $el.fadeIn("fast", function () {
                                $el.css("background", originalColor);
                            });

                        }, x);

                    })

                });
        }

        setInterval(function () {
            get_live_chat(<?= $current_page_id ?>)
        }, 1500);
    </script>
<?php
get_footer();
