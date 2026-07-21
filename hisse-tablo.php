<?php
/*
  Template Name: Hisse Tablo
*/

// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern sidebar added for Piyasa Vizyon styling.
get_header();

$hisse = get_data_service('hisseler' );

?>
<style>
    .currencyTable tr th {
        font-weight: 500;
    }

    .currencyTable tr td b {
        color: #3b72de !important;
    }
</style>
<!-- Site Wrapper -->
<div class="site-wrapper pv-market-native pv-market-stocks-native">
    
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
                            <li class="post bg"><span><?php the_title() ?></span></li>
                        </ul>
                    </div>
                    
                    <h1 class="postTitle"><?php the_title() ?></h1>
                    
                    
                    <div class="pv-market-list-content pv-stocks-content">
                        
                        <!-- Main Content -->
                        <div class="mainContent">
                            
                            <!-- Main -->
                            <div class="main">
                                
                                <!-- widget -->
                                <div class="widget">
                                    <!-- Currency Showcase -->
                                    <div class="currencyShowcase fullShowcase mobileBottomNo">
										<?php if ( ! wp_is_mobile() ) { ?>
                                            <table class="currencyTable currencyFullTable">
                                                <tr>
                                                    <th><b>Hisse</b></th>
                                                    <th><b style="position: relative; left: 3px;">Son</b></th>
                                                    <th><b style="position:relative; right: 5px;">Değişim</b></th>
                                                    <th><b>Hacim(TL)</b></th>
                                                    <th><b style="position:relative; right:5px;">Güncelleme</b></th>
                                                </tr>
												<?php
												
												preg_match_all( '@<table class=" scrollable wfull search-table table-data ">(.*?)</table>@si', $hisse, $doviz_tablo );
												echo "<pre>";
												preg_match_all( '@<tr>(.*?)</tr>@si', $doviz_tablo[1][0], $borsa );
												foreach ( array_unique( $borsa[1] ) as $key => $val ):
													preg_match_all( '@<a href="https://finans.mynet.com/borsa/hisseler/(.*?)" title="(.*?)">(.*?)</a>@si', $val, $hisse_name );
													preg_match_all( '@<td class="text-center">(.*?)</td>@si', $val, $borsa_data );
													if ( empty( $hisse_name[1][0] ) ) {
														continue;
													}
                                                    
													if ( $borsa_data[1][2] > 0 ) {
														$crease_status = "increase";
														$color         = "#40bc9a";
													} else {
														$crease_status = "decrease";
														$color         = "#fc4b67";
													}
													?>
                                                    <tr>
                                                        <td>
                                                            <a href='<?= get_bloginfo( "home" ) ?>/<?= $bp_options['page_hisse'] ?>/?h=<?= $hisse_name[1][0] ?>'><b><?= $hisse_name[2][0]; ?>
                                                            </a></b></td>
                                                        <td style="color: <?= $color ?>;"><i class="<?= $crease_status ?>"></i><?= $borsa_data[1][1]; ?></td>
                                                        <td style="font-weight: normal;"><span class="subtract <?= $crease_status ?>"><?= $borsa_data[1][2] ?></span></td>
                                                        <td style="font-weight: normal;"><?= $borsa_data[1][3] ?></td>
                                                        <td style="padding: 0 15px;font-weight: normal;"><?= $borsa_data[1][4] ?></td>
                                                    </tr>
												<?php endforeach; ?>
                                            </table>
										<?php } else {
											?>
                                            <table class="currencyTable currencyFullTable">
                                                <tr>
                                                    <th style="width: 70% !important;display: inline-block;"><b>Hisse</b></th>
                                                    <th style="width: 20%;display: inline-block;padding-left: 0;"><b style="">Son</b></th>
                                                </tr>
												<?php
												preg_match_all( '@<table class=" scrollable wfull search-table table-data ">(.*?)</table>@si', $hisse, $doviz_tablo );
												
												preg_match_all( '@<tr>(.*?)</tr>@si', $doviz_tablo[1][0], $borsa );
												foreach ( array_unique( $borsa[1] ) as $key => $val ):
													preg_match_all( '@<a href="https://finans.mynet.com/borsa/hisseler/(.*?)" title="(.*?)">(.*?)</a>@si', $val, $hisse_name );
													preg_match_all( '@<td class="text-center">(.*?)</td>@si', $val, $borsa_data );
													if ( empty( $hisse_name[1][0] ) ) {
														continue;
													}
													if ( $borsa_data[1][2] > 0 ) {
														$crease_status = "increase";
														$color         = "#40bc9a";
													} else {
														$crease_status = "decrease";
														$color         = "#fc4b67";
													}
													?>
                                                    <tr class="alt dKurlariS2 dKurlariSaa">
                                                        <td style="width: 70% !important;display: inline-block;">
                                                            <a href='<?= get_bloginfo( "home" ) ?>/<?= $bp_options['page_hisse'] ?>/?h=<?= $hisse_name[1][0] ?>'><b><?= mb_substr( $hisse_name[2][0],
																		0, 20, "UTF-8" ); ?></b></a></td>
                                                        <td style="width: 20%;display: inline-block;padding-left:0px;">
                                                            <i class="<?= $crease_status ?>" style="<?php if ( ! is_user_logged_in() ): ?>position: relative; top: 22px;<?php endif; ?> margin-right:5px;"></i><span><?= $borsa_data[1][1]; ?></span>
                                                        </td>
                                                    
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
    pv_v7_market_sidebar('tum-hisseler');
} ?>
        
        </div>
		
		<?php dynamic_sidebar( 'Sayfa Alt (Tüm Hisseler)' ); ?>
    </section>
    <!-- Content -->
    <div class="clear"></div>

</div>
<!-- #Site Wrapper -->
<?php get_footer(); ?>
