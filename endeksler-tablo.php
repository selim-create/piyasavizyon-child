<?php
/*
  Template Name: Endeksler Tablo
*/

// Child theme override: original BirFinans data/parsing logic preserved; only scoped wrapper class is added for Piyasa Vizyon styling.
get_header();

$doviz = get_data_service("mynet?url=https://finans.mynet.com/borsa/endeks/");

preg_match_all('@<table class="scrollable wfull table-data ">(.*?)</table>@si', $doviz, $doviz_tablo);
preg_match_all('@<tr>(.*?)</tr>@si', $doviz_tablo[1][0], $borsa);

?>
<style>
  .currencyTable tr th{
    font-weight: 500;
  }
  .currencyTable tr td b{
    color: #3b72de !important;
  }
</style>
<!-- Site Wrapper -->
	<div class="site-wrapper pv-market-native pv-market-indices-native">

		<!-- Content -->
		<section class="content home">
			<div class="container-wrap">

				<!-- WideBar -->
				<div class="widebar floatLeft">

					<div class="singleWrapper">

						<!-- BreadCrumb -->
						<div class="breadcrumb">
							<ul class="block">
								<li><a href="<?php bloginfo('home')?>">Anasayfa<i>/</i></a></li>
								<li class="post bg"><span><?php the_title() ?></span></li>
							</ul>
						</div>

						<h1 class="postTitle centerli"><?php the_title() ?></h1>



						<div class="pv-market-list-content pv-indices-content">

							<!-- Main Content -->
							<div class="mainContent">

								<!-- Main -->
								<div class="main">

									<!-- widget -->
									<div class="widget">
										<!-- Currency Showcase -->
										<div class="currencyShowcase fullShowcase mobileBottomNo">
                      <?php if(wp_is_mobile()){
                        ?>
                        <table class="currencyTable currencyFullTable">
                          <tr>
                            <th style="width: 70% !important;display: inline-block;">Endeks</th>
                            <th class="sagagit3" style="width: 20%;display: inline-block;padding-left: 0;">Değişim</th>
                          </tr>
                          <?php foreach(array_unique($borsa[1]) as $key=>$val):
                            preg_match_all('@href="https://finans.mynet.com/borsa/endeks/(.*?)/"@si', $val, $hisse_seflink);
                            preg_match_all('@<td><strong class="mr-4"><a href="https://finans.mynet.com/borsa/endeks/(.*?)/" title="(.*?)">(.*?)</a></strong></td>@si', $val, $hisse_name);
                            preg_match_all('@<td class="text-center">(.*?)</td>@si', $val, $borsa_data);
                            if($key == 0): continue; endif;
                            $hisse_name[1][0] = trim(strip_tags($hisse_name[3][0]));

                            if($borsa_data[1][2] > 0)
                            {
                              $crease_status = "increase";
                            }else{
                              $crease_status = "decrease";
                            }


                          ?>
                            <tr class="alt dKurlariS dKurlariS2">
                              <td style="width: 70% !important;display: inline-block;"><a href='<?=get_bloginfo("home")?>/<?=$bp_options['page_endeks']?>/?e=<?=$hisse_seflink[1][0]?>'><b><?=$hisse_name[1][0];?></b></a></td>
                              <td class="ikincitd" style="width: 20%;display: inline-block;padding-left:0px;position:relative; bottom: 7px;"><i class="<?=$crease_status?>" style="<?php if(!is_user_logged_in()): ?>position: relative; top: 26px;<?php endif; ?>"></i><span><?=$borsa_data[1][3];?></span></td>
                            </tr>
                          <?php endforeach; ?>
                        </table>
                        <?php
                      }else{
                        ?>
                        <table class="currencyTable currencyFullTable">
                          <tr>
                            <th>Endeks</th>
                            <th style="padding-left: 65px;">Son</th>
                            <th>Değişim</th>
                            <th>Güncelleme</th>
                          </tr>
                          <?php foreach(array_unique($borsa[1]) as $key=>$val):
                            preg_match_all('@href="https://finans.mynet.com/borsa/endeks/(.*?)"@si', $val, $hisse_seflink);
                            preg_match_all('@<td><strong class="mr-4"><a href="https://finans.mynet.com/borsa/endeks/(.*?)/" title="(.*?)">(.*?)</a></strong></td>@si', $val, $hisse_name);
                            preg_match_all('@<td class="text-center">(.*?)</td>@si', $val, $borsa_data);

                            if($key == 0): continue; endif;
                            $hisse_name[1][0] = trim(strip_tags($hisse_name[3][0]));

                            if($borsa_data[1][2] > 0)
                            {
                              $crease_status  = "increase";
                              $color          = "#40bc9a";
                            }else{
                              $crease_status = "decrease";
                              $color          = "#fc4b67";
                            }
                            if($key <= 3){
                              $hisse_name[1][0] = $hisse_name[1][0];
                            }
                          ?>
                            <tr>
                              <td><a href='<?=get_bloginfo("home")?>/<?=$bp_options['page_endeks']?>/?e=<?=$hisse_seflink[1][0]?>'><b><?=mb_substr($hisse_name[1][0],0,40,"UTF-8");?></b></a></td>
                              <td style="color: <?=$color;?>;"><i class="<?=$crease_status?>"></i><?=$borsa_data[1][3];?></td>
                              <td style="font-weight: normal;"><span class="subtract <?=$crease_status?>"><?=$borsa_data[1][2]?></span></td>
                              <td style="padding: 0 15px;font-weight: normal;"><?=$borsa_data[1][5]?></td>
                            </tr>
                          <?php endforeach; ?>
                        </table>
                        <?php
                      }?>

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
        <?php if(!wp_is_mobile() && function_exists('pv_v7_market_sidebar')){
        pv_v7_market_sidebar('tum-endeksler');
      } ?>

			</div>

      <?php dynamic_sidebar('Sayfa Alt (Tüm Endeksler)'); ?>
		</section>
		<!-- Content -->
		<div class="clear"></div>

	</div>
	<!-- #Site Wrapper -->
  <?php get_footer(); ?>
