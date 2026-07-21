<?php
/*
  Template Name: Döviz Arşiv
*/
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern market sidebar added for Piyasa Vizyon styling.

get_header();
$_GET['tarih'] = htmlentities($_GET['tarih']);
if(empty($_GET['tarih'])) {
  $kaynak = get_url_curl("https://finans.mynet.com/doviz/arsiv/");
  $_GET['tarih'] = date("Y-m-d");
}else{
  $cleanDate = date("d.m.Y", strtotime($_GET['tarih']));
  $kaynak = get_url_curl("https://finans.mynet.com/doviz/arsiv/".$cleanDate . '/');

}

preg_match_all('@<table class="scrollable wfull table-data search-table ">(.*?)</table>@si', $kaynak, $doviz_area);
preg_match_all('@<strong class="mr-4"><a href="https://finans.mynet.com/doviz/(.*?)/" title="(.*?)">(.*?)</a></strong>@si', $doviz_area[1][0], $doviz_name);
preg_match_all('@<td class="text-center">(.*?)</td>@si', $doviz_area[1][0], $table);

?>
<style>
  .currencyTable tr th{
    font-weight: 500;
  }

  .currencyTable tr td b{
    color: #3b72de !important;
  }
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}
</style>
<!-- Site Wrapper -->
	<div class="site-wrapper pv-market-native pv-market-currency-archive-native">

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

						<h1 class="postTitle" style="width: auto; float:left;"><?php the_title() ?></h1>
            <input type="date" value="<?=$_GET['tarih']?>" onchange="changeDate(this.value)" id="DateSelection" style="float: right;

padding: 10px;
border: 1px solid #dcdcdc;
<?php if(wp_is_mobile()) {
  ?>

  width: 140px;
  margin-top: 0px;
  <?php }else{ ?>
      margin-top: 18px;
      width: 200px;
      <?php } ?>
"/>



						<div class="pv-market-list-content pv-currency-archive-content">

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
                            <th class="sagagit2" style="padding-left: 15px !important;width: 20%;display: inline-block;padding-left: 0;">Açılış</th>
													</tr>
                          <?php foreach(array_unique($doviz_name[1]) as $key=>$val):
                              $doviz_name[2][$key] = str_replace("&Ouml;zel &Ccedil;ekme Hakkı", "Ö. Çekme Hakkı",$doviz_name[2][$key]);
                              ?>

                            <tr class="alt dKurlariS">
                              <?php
                              if(10 > $key){
                                ?>
                                <td style="width: 70% !important;float: left;"><a href="<?php bloginfo("home")?>/<?=$bp_options['page_doviz']?>?c=<?=explode("-",$doviz_name[1][$key])[0]?>"><img src="<?php bloginfo('template_directory'); ?>/img/flag/<?=explode("-",$doviz_name[1][$key])[0]?>.png" width="24" height="16" alt="<?=$doviz_name[2][$key]?>"> <b><?=$doviz_name[2][$key]?></b></a></td>
                                <?php
                              }else{
                                ?>
                                  <td style="width: 70% !important;float: left;"><a href="<?php bloginfo("home")?>/<?=$bp_options['page_doviz']?>?c=<?=explode("-",$doviz_name[1][$key])[0]?>"><img src="<?php bloginfo('template_directory'); ?>/img/dgr.png" width="24" height="16" alt="<?=$doviz_name[2][$key]?>"> <b><?=$doviz_name[2][$key]?></b></a></td>
                                <?php
                              }

                              if($key == 0){
                                $basla = 1;
                              }else{
                                $basla = $key*5+1;
                              }
                               ?>
  							                      <td style="width: 20%;display: inline-block;padding-left:0px;"></i> <span><?=$table[1][$basla]?></td>
  													</tr>
                          <?php endforeach; ?>
												</table>

                      <?php }else{
                        ?>
                        <table class="currencyTable currencyFullTable">
													<tr>
														<th>Döviz</th>
														<th>Açılış</th>
                            <th>En Düşük</th>
                            <th>En Yüksek</th>
                            <th>Kapanış</th>
													</tr>
                          <?php foreach(array_unique($doviz_name[1]) as $key=>$val):
                              $doviz_name[2][$key] = str_replace("&Ouml;zel &Ccedil;ekme Hakkı", "Ö. Çekme Hakkı",$doviz_name[2][$key]);
                              ?>
                            <tr>
                              <?php
                              if(10 > $key){
                                ?>
                                <td><a href="<?php bloginfo("home")?>/<?=$bp_options['page_doviz']?>?c=<?=explode("-",$doviz_name[1][$key])[0]?>"><img src="<?php bloginfo('template_directory'); ?>/img/flag/<?=explode("-",$doviz_name[1][$key])[0]?>.png" width="24" height="16" alt="<?=$doviz_name[2][$key]?>"> <b><?=$doviz_name[2][$key]?></b></a></td>
                                <?php
                              }else{
                                ?>
                                  <td style="width: 70% !important;float: left;"><a href="<?php bloginfo("home")?>/<?=$bp_options['page_doviz']?>?c=<?=explode("-",$doviz_name[1][$key])[0]?>"><img src="<?php bloginfo('template_directory'); ?>/img/dgr.png" width="24" height="16" alt="<?=$doviz_name[2][$key]?>"> <b><?=$doviz_name[2][$key]?></b></a></td>
                                <?php
                              }

                              if($key == 0){
                                $basla = 1;
                              }else{
                                $basla = $key*5+1;
                              }
                               ?>

  															<td style="font-weight: 500;"> <?=$table[1][$basla]?></td>
                                <td style="font-weight: normal;"><?=$table[1][$basla+1]?></td>
                                <td style="font-weight: normal;"><?=$table[1][$basla+2]?></td>
                                <td style="padding: 0 15px;font-weight: normal;"><?=$table[1][$basla+3]?></td>
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
                pv_v7_market_sidebar('doviz-arsivi');
            } ?>
			</div>
		</section>
		<!-- Content -->
		<div class="clear"></div>

	</div>
	<!-- #Site Wrapper -->
  <script>
function changeDate(val){
    window.location.href = "<?=home_url("/doviz-arsiv/?tarih=")?>"+val;

}
  </script>
  <?php get_footer(); ?>
