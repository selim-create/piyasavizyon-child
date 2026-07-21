<?php
/*
  Template Name: Döviz Toplu Hesapla
*/
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern market sidebar added for Piyasa Vizyon styling.
get_header();

?>
<!-- Site Wrapper -->
	<div class="site-wrapper pv-market-native pv-market-currency-bulk-converter-native">

		<!-- Content -->
		<section class="content home">
			<div class="container-wrap">

				<!-- WideBar -->
				<div class="widebar floatLeft">

					<div class="singleWrapper">

						<!-- BreadCrumb -->
						<div class="breadcrumb">
							<ul class="block">
								<li><a href="#">Anasayfa<i>/</i></a></li>
								<li class="post bg"><span><?php the_title() ?></span></li>
							</ul>
						</div>

						<h1 class="postTitle"><?php the_title() ?></h1>



						<div class="pv-market-detail-content pv-currency-bulk-converter-content">

							<!-- Main Content -->
							<div class="mainContent">

								<!-- Main -->
								<div class="main">

									<!-- widget -->
									<div class="widget">
										<!-- Currency Showcase -->
										<div class="currencyShowcase fullShowcase mobileBottomNo" <?php if(wp_is_mobile()){ ?> style="margin-bottom:15px !important;" <?php } ?>>
											<?php if(wp_is_mobile()){
												?>
												<table class="currencyTable currencyFullTable">
													<tr>
														<th style="display: inline-block;width: 48%;font-weight:500;">Döviz</th>
														<th style="display: inline-block;width: 48%;padding-left:15px;font-weight:500;">Miktar</th>
													</tr>
                          <tr>
                            <td style="width: 48%;display: inline-block;color: #3b72de;position:relative;bottom:2px;"><img src="<?php bloginfo("template_directory")?>/img/flag/tr.png" width="24" height="16" alt="Türk Lirası">Türk Lirası</td>
                            <td style="width: 48%;display: inline-block;"><input style="position:relative; bottom:1px;" type="text" class="currency-quantity" data-price="1" placeholder="0,00"></td>
                          </tr>
                          <?php foreach(array_unique($currency_data['buying']) as $key=>$val):
														$dovizClean = str_replace(
													    array("cad","aud","usd", "eur", "gbp","chf", "cny", "rub", "nok","jpy","dkk","pln"),
													    array("kanada-dolari","avustralya-dolari","dolar", "euro", "sterlin", "isvicre-frangi", "cin-yuani", "rus-rublesi", "norvec-kronu", "japon-yeni", "danimarka-kronu", "polonya-zlotisi"),
													    $key);
														 if(is_int($key)): continue; endif; ?>
                            <tr>
															<td style="width: 48%;display: inline-block;position:relative;bottom:2px;"><a href="<?=home_url()."/1-".$dovizClean."-".permalink_bf($bp_options['dovizHesaplaRewrite'])?>" style="color:#3b72de !important"><img src="<?php bloginfo("template_directory")?>/img/flag/<?=$key?>.png" width="24" height="16" alt="<?=$currency_data['full_name'][$key]?>"> <?=mb_substr($currency_data['full_name'][$key],0,10, "UTF-8")?></a></td>
															<td style="width: 48%;display: inline-block;"><input type="text" class="currency-quantity" data-price="<?=$currency_data['selling'][$key]?>" placeholder="0,00" style="float:right;position:relative; top:1px;"></td>
														</tr>
                          <?php endforeach; ?>
												</table>
												<?php
											}else{
												?>
												<table class="currencyTable currencyFullTable" style="margin-bottom: 5px;">
													<tr>
														<th style="font-weight: 500;">Döviz</th>
														<th style="font-weight: 500;">Miktar</th>
														<th style="font-weight: 500;">Alış</th>
														<th style="font-weight: 500;">Satış</th>
														<th style="padding-left: 0;font-weight:500;">Fark</th>
													</tr>
                          <tr>
                            <td style="color:#3b72de !important"><img src="<?php bloginfo("template_directory")?>/img/flag/tr.png" width="24" height="16" alt="Türk Lirası"> Türk Lirası - TRY</td>
                            <td style="padding-left: 60px;font-weight:normal;"><input type="text" class="currency-quantity" data-price="1" placeholder="0,00"></td>
														<td style="font-weight: normal;">-</td>
														<td style="font-weight: normal;">-</td>
														<td style="font-weight: normal;">-</td>
                          </tr>
                          <?php foreach(array_unique($currency_data['buying']) as $key=>$val):

														$dovizClean = str_replace(
													    array("cad","aud","usd", "eur", "gbp","chf", "cny", "rub", "nok","jpy","dkk","pln"),
													    array("kanada-dolari","avustralya-dolari","dolar", "euro", "sterlin", "isvicre-frangi", "cin-yuani", "rus-rublesi", "norvec-kronu", "japon-yeni", "danimarka-kronu", "polonya-zlotisi"),
													    $key);

														if(str_replace(",",".",$currency_data['change_rate'][$key]) > 0){
                              $crease_status = "increase";
                            }else{
                              $crease_status = "decrease";
                            }
														if(is_int($key)): continue; endif;
														 ?>
                            <tr>
															<td><a href="<?=home_url()."/1-".$dovizClean."-".permalink_bf($bp_options['dovizHesaplaRewrite'])?>" style="color:#3b72de !important"><img src="<?php bloginfo("template_directory")?>/img/flag/<?=$key?>.png" width="24" height="16" alt="<?=$currency_data['full_name'][$key]?>"> <?=str_replace("mirlik",null,$currency_data['full_name'][$key])?> - <?=strtoupper($key)?></a></td>
															<td style="padding-left: 60px;"><input type="text" class="currency-quantity" data-price="<?=$currency_data['buying'][$key]?>" placeholder="0,00"></td>
															<td style="font-weight: normal;"><i class="<?=$crease_status?>"></i><?=$currency_data['selling'][$key]?></td>
															<td style="font-weight: normal;"><?=$currency_data['buying'][$key]?></td>
															<td style="font-weight: normal;"><span class="<?=$crease_status?> subtract">% <?=$currency_data['change_rate'][$key]?></span></td>
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
                pv_v7_market_sidebar('doviz-toplu-cevirici');
            } ?>
			</div>
		</section>
		<!-- Content -->
		<div class="clear"></div>

	</div>
	<!-- #Site Wrapper -->
<?php
get_footer();
?>
