<?php
/*
  Template Name: Döviz Hesapla Ajax
*/
// Child theme override: original BirFinans data/parsing logic preserved; scoped wrapper and modern market sidebar added for Piyasa Vizyon styling.

global $wp;

if ( ! empty( $_GET['doviz'] ) ) {
	$dovizClean = str_replace(
		[ "cad", "aud", "usd", "eur", "gbp", "chf", "cny", "rub", "nok", "jpy", "dkk", "pln" ],
		[
			"kanada-dolari",
			"avustralya-dolari",
			"dolar",
			"euro",
			"sterlin",
			"isvicre-frangi",
			"cin-yuani",
			"rus-rublesi",
			"norvec-kronu",
			"japon-yeni",
			"danimarka-kronu",
			"polonya-zlotisi",
		],
		$_GET['doviz'] );
	wp_redirect( home_url() . "/" . $_GET['miktar'] . "-" . $dovizClean . "-" . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) );
}

$full_url_exp = explode( "/", home_url( $wp->request ) );

$full_url = explode( "-", str_replace(
	[
		"kanada-dolari",
		"avustralya-dolari",
		"dolar",
		"euro",
		"sterlin",
		"isvicre-frangi",
		"cin-yuani",
		"rus-rublesi",
		"norvec-kronu",
		"japon-yeni",
		"danimarka-kronu",
		"polonya-zlotisi",
	],
	[ "cad", "aud", "usd", "eur", "gbp", "chf", "cny", "rub", "nok", "jpy", "dkk", "pln" ],
	$full_url_exp[ count( $full_url_exp ) - 1 ]
) );


$key            = $full_url[1];
$_GET['doviz']  = $key;
$_GET['miktar'] = $full_url[0];
$miktar         = $_GET['miktar'];
$new_title      = $miktar . " " . $currency_data['full_name'][ $key ] . " " . $bp_options['dovizHesaplaRewrite'] . " - " . get_bloginfo( 'name' );
$page_title     = $miktar . " " . $currency_data['full_name'][ $key ] . " " . $bp_options['dovizHesaplaRewrite'];
$dovizClean     = str_replace(
	[ "cad", "aud", "usd", "eur", "gbp", "chf", "cny", "rub", "nok", "jpy", "dkk", "pln" ],
	[
		"kanada-dolari",
		"avustralya-dolari",
		"dolar",
		"euro",
		"sterlin",
		"isvicre-frangi",
		"cin-yuani",
		"rus-rublesi",
		"norvec-kronu",
		"japon-yeni",
		"danimarka-kronu",
		"polonya-zlotisi",
	],
	$_GET['doviz'] );

function generate_custom_title( $title ) {
	global $new_title;
	$title = $new_title;
	
	return $title;
}

add_filter( 'pre_get_document_title', 'generate_custom_title', 10 );
add_filter( 'wpseo_title', 'generate_custom_title', 15 );

get_header();


$converterResult = str_replace( ".", ",", str_replace( ",", ".", $currency_data['selling'][ $key ] ) * $miktar );


if ( wp_is_mobile() ) {
	?>
    <style>
        .widebar .widget .currencyBoxes .currencyBox .title span {
            max-height: 48px;
            overflow: hidden;
        }
    </style>
	<?php
}
?>
<!-- Site Wrapper -->
<div class="site-wrapper pv-market-native pv-market-currency-converter-detail-native">
    
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
                            <li class="post bg"><span><?= $currency_data['full_name'][ $key ] ?></span></li>
                        </ul>
                    </div>
                    
                    <h1 class="postTitle"><?= $page_title ?></h1>
                    
                    
                    <div class="pv-market-detail-content pv-currency-converter-detail-content">
                        
                        <!-- Main Content -->
                        <div class="mainContent">
                            
                            <!-- Main -->
                            <div class="main">
								<?php
								$change_rate = str_replace( ",", ".", $currency_data['change_rate'][ $_GET['doviz'] ] );
								
								if ( $change_rate > 0 ) {
									$crease_status = "increase";
									$crease_color  = "#32ba5b";
								} else {
									$crease_status = "decrease";
									$crease_color  = "#ef291f";
								}
								?>
                                <!-- Widget -->
                                <div class="widget">
                                    <div class="currencyConverter">
                                        <div class="currencyConverterIcon"><img src="<?php bloginfo( "template_directory" ) ?>/img/icons/currencyconverter.png" alt=""></div>
                                        <div class="currencyConverterContent">
                                            <form action="<?= home_url( "/doviz-hesapla-ajax/" ) ?>" method="get">
                                                <input type="hidden" name="doviz" value="<?= $_GET['doviz'] ?>"/>
                                                <input type="hidden" class="dovizMiktar" name="dovizMiktar" value="<?= str_replace( ",", ".",
													$currency_data['selling'][ $_GET['doviz'] ] ) ?>"/>
                                                <input type="text" name="miktar" class="doviz-type" onkeyup="dovizHesapla();" value="<?= $_GET['miktar'] ?>">
                                                <label for=""><?= strtoupper( $_GET['doviz'] ) ?></label>
                                                <button>HESAPLA</button>
                                                <div class="currencyConverterResult"><?= $converterResult ?> TL</div>
                                                <div class="currencyConverterRate" style="color: <?= $crease_color ?> !important;">
                                                    <i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate'][ $_GET['doviz'] ] ?> %)
                                                </div>
                                            </form>
                                        </div>
                                        <div class="clear"></div>
                                        <div class="currencyPriceBar">
                                            <div class="currencyPriceBarName"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                            <div class="currencyPriceBarBuy"><span>ALIŞ FİYATI :

                        </span><?= $currency_data['selling'][ $key ] ?> TL
                                            </div>
                                            <div class="currencyPriceBarSell"><span>
                            SATIŞ FİYATI :

                        </span><?php echo $currency_data['buying'][ $key ] ?> TL <?php ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Widget -->
                                <div class="widget noMargin">
                                    <h1 class="postTitle noMarginTop"><?= $page_title ?></h1>
                                    <div class="clear"></div>
                                    <div class="currencyBoxes">
										<?php if ( $key != "usd" ):
											$change_rate = (int) str_replace( ",", ".", $currency_data['change_rate']['usd'] );
											if ( str_replace( ",", ".", $currency_data['change_rate']['usd'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['usd'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=usd&miktar=1">
                                                <div class="title"><img src="<?php bloginfo( "template_directory" ) ?>/img/flag/usd.png" width="24" height="16" alt="">
                                                    <span>DOLAR</span></div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> USD</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['usd'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
										<?php endif; ?>
										
										<?php if ( $key != "eur" ):
											if ( str_replace( ",", ".", $currency_data['change_rate']['eur'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['eur'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=eur&miktar=1">
                                                    <div class="title">
                                                        <img src="<?php bloginfo( "template_directory" ) ?>/img/flag/eur.png" width="24" height="16" alt=""> <span>EURO</span>
                                                    </div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> EUR</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['eur'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
										<?php endif; ?>
										
										<?php if ( $key != "gbp" ):
											if ( str_replace( ",", ".", $currency_data['change_rate']['gbp'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['gbp'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=gbp&miktar=1">
                                                <div class="title"><img src="<?php bloginfo( "template_directory" ) ?>/img/flag/gbp.png" width="24" height="16" alt=""> <span>İNGİLİZ STERLİNİ</span>
                                                </div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> GBP</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['gbp'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
										<?php endif; ?>
										
										
										<?php if ( $key != "chf" ):
											if ( str_replace( ",", ".", $currency_data['change_rate']['chf'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['chf'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=chf&miktar=1">
                                                <div class="title"><img src="<?php bloginfo( "template_directory" ) ?>/img/flag/chf.png" width="24" height="16" alt=""> <span>İSVİÇRE FRANGI</span>
                                                </div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> CHF</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['chf'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
										<?php endif; ?>
										
										<?php if ( $key != "cad" ):
											if ( str_replace( ",", ".", $currency_data['change_rate']['cad'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['cad'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=cad&miktar=1">
                                                <div class="title"><img src="<?php bloginfo( "template_directory" ) ?>/img/flag/cad.png" width="24" height="16" alt=""> <span>KANADA DOLARI</span>
                                                </div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> CAD</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['cad'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
										<?php endif; ?>
										
										<?php if ( $key != "cny" ):
											if ( str_replace( ",", ".", $currency_data['change_rate']['cny'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['cny'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=cny&miktar=1">
                                                <div class="title"><img src="<?php bloginfo( "template_directory" ) ?>/img/flag/cny.png" width="24" height="16" alt=""> <span>ÇİN YUANI</span>
                                                </div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> CNY</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['cny'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
										<?php endif; ?>
										
										<?php if ( $key != "aud" ):
											if ( str_replace( ",", ".", $currency_data['change_rate']['aud'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['aud'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=aud&miktar=1">
                                                <div class="title"><img src="<?php bloginfo( "template_directory" ) ?>/img/flag/aud.png" width="24" height="16" alt=""> <span>AVUSTRALYA D.</span>
                                                </div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> AUD</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['aud'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
										<?php endif; ?>
										
										<?php if ( $key != "rub" ):
											if ( str_replace( ",", ".", $currency_data['change_rate']['rub'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['rub'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=rub&miktar=1">
                                                <div class="title"><img src="<?php bloginfo( "template_directory" ) ?>/img/flag/rub.png" width="24" height="16" alt=""> <span>RUS RUBLESİ</span>
                                                </div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> RUB</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['rub'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
										<?php endif; ?>
										
										
										<?php if ( $key == "rub" || $key == "eur" || $key == "usd" || $key == "cny" || $key == "aud" || $key == "cad" || $key == "chf"
										           || $key == "gbp" ) {
											if ( str_replace( ",", ".", $currency_data['change_rate']['sek'] ) > 0 ) {
												$crease_status = "increase";
												$crease_color  = "inc";
											} else {
												$crease_status = "decrease";
												$crease_color  = "dec";
											}
											$currency_value = number_format( $converterResult / str_replace( ",", ".", $currency_data['selling']['sek'] ), 4 );
											?>
                                            <div class="currencyBox">
                                                <a href="<?= home_url( '/' . $bp_options['page_dovizhesapla'] ) ?>?doviz=sek&miktar=1">
                                                <div class="title"><img src="<?php bloginfo( "template_directory" ) ?>/img/flag/sek.png" width="24" height="16" alt=""> <span>İSVEÇ KRONU</span>
                                                </div>
                                                </a>
                                                <div class="content">
                                                    <div class="tlValue"><?= $currency_value ?> SEK</div>
                                                    <div class="unitValue"><?= $miktar ?> <?= strtoupper( $key ) ?></div>
                                                    <div class="rate <?= $crease_color ?>"><i class="<?= $crease_status ?>"></i>(<?= $currency_data['change_rate']['sek'] ?> %)
                                                    </div>
                                                </div>
                                            </div>
											<?php
										} ?>
                                    
                                    </div>
                                </div>
                                
                                
                                <!-- Widget -->
                                <div class="widget noMargin">
                                    <div class="shortcutConverter">
                                        <a href="<?= home_url() . "/1-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>"><?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                        <a href="<?= home_url() . "/5-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>">5 <?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                        <a href="<?= home_url() . "/10-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>">10 <?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                        <a href="<?= home_url() . "/20-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>">20 <?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                        <a href="<?= home_url() . "/50-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>">50 <?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                        <a href="<?= home_url() . "/100-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>">100 <?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                        <a href="<?= home_url() . "/500-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>">500 <?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                        <a href="<?= home_url() . "/1000-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>">1000 <?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                        <a href="<?= home_url() . "/5000-" . $dovizClean . "-"
										             . permalink_bf( $bp_options['dovizHesaplaRewrite'] ) ?>">5000 <?= $currency_data['full_name'][ $key ] ?> Kaç TL</a>
                                    </div>
                                </div>
                                <!-- //Widget -->
                            
                            </div>
                        
                        
                        </div>
                        <!-- #MainBar -->
                    
                    
                    </div>
                
                </div>
            
            </div>
            <?php if (!wp_is_mobile() && function_exists('pv_v7_market_sidebar')) {
                pv_v7_market_sidebar('doviz-cevirici-detay');
            } ?>
        
        
        </div>
    </section>
    <!-- Content -->
    <div class="clear"></div>

</div>

<script>
    function dovizHesapla() {
        var kurValue = $(".dovizMiktar").val();

        var miktar = $(".doviz-type").val();

        var newValue = (kurValue * miktar).toFixed(4);
        if (isNaN(newValue)) {
            newValue = "0,0000";
        }

        newValue = newValue.replace(".", ",");


        $(".currencyConverterResult").html(newValue + " TL");
    }
</script>
<!-- #Site Wrapper -->
<?php get_footer(); ?>
