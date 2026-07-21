<?php
/*
  Template Name: Parite Tablo
*/
include get_template_directory() . '/api/parite-data.php';
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern sidebar added for Piyasa Vizyon styling.
get_header();
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
	<div class="site-wrapper pv-market-native pv-market-parities-native">

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



						<div class="pv-market-list-content pv-parities-content">

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
                            <th style="width: 70% !important;display: inline-block;">Döviz</th>
                            <th class="alisSo" style="width: 20%;display: inline-block;padding-left: 0;">Alış</th>
													</tr>
                          <?php foreach(array_unique($parite_data['code']) as $key=>$val):
                            if(str_replace(",",".",$parite_data['change_rate'][$key]) > 0){
                              $crease_status = "increase";
                            }else{
                              $crease_status = "decrease";
                            }
                             ?>
                            <tr class="alt dKurlariS2 sonn">
  								<td style="width: 70% !important;display: inline-block;"><a href="<?php bloginfo("home")?>/parite/?p=<?=$key?>"><b><?=strtoupper($parite_data['code'][$key])?></b></a></td>
  								<td style="width: 20%;display: inline-block;padding-left:0px;"><i class="<?=$crease_status?>" style="<?php if(!is_user_logged_in()): ?>position: relative; top: 26px;<?php endif; ?>"></i> <span><?=$parite_data['buying'][$key]?></span></td>
  							</tr>
                          <?php endforeach; ?>
												</table>

                      <?php }else{
                        ?>
                        <table class="currencyTable currencyFullTable">
													<tr>
														<th>Döviz</th>
														<th>Alış</th>
                            <th>Satış</th>
                            <th>Fark</th>
                            <th>Saat</th>
													</tr>
                          <?php foreach(array_unique($parite_data['code']) as $key=>$val):
                            if(str_replace(",",".",$parite_data['change_rate'][$key]) > 0){
                              $crease_status = "increase";
                              $color          = "#40bc9a";
                            }else{
                              $crease_status = "decrease";
                              $color          = "#fc4b67";
                            }
                             ?>
                            <tr>
  															<td><a href="<?php bloginfo("home")?>/<?=$bp_options['page_parite']?>/?p=<?=$key?>"> <b><?=$parite_data['full_name'][$key]?></b></a></td>
  															<td style="font-weight: 500;color:<?=$color?>;"><i class="<?=$crease_status?>"></i> <?=$parite_data['buying'][$key]?></td>
                                <td style="font-weight: normal;"><?=$parite_data['selling'][$key]?></td>
                                <td style="font-weight: normal;"><span class="<?=$crease_status?> subtract">% <?=$parite_data['change_rate'][$key]?></span></td>
                                <td style="padding: 0 15px;font-weight: normal;"><?=$parite_data['time'][$key]?></td>
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
        <?php if (!wp_is_mobile() && function_exists('pv_v7_market_sidebar')) {
    pv_v7_market_sidebar('tum-pariteler');
} ?>
			</div>
      <?php dynamic_sidebar('Sayfa Alt (Tüm Pariteler)'); ?>
		</section>
		<!-- Content -->
		<div class="clear"></div>

	</div>
	<!-- #Site Wrapper -->
  <?php get_footer(); ?>
