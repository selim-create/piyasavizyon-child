<?php
/*
  Template Name: Faiz Oranları
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }
require_once get_stylesheet_directory() . '/template-credit-helpers.php';

$type = isset( $_GET['type'] ) ? pv_market_interest_type( wp_unslash( $_GET['type'] ) ) : 'try';
$rows = pv_market_interest_rows( $type );

get_header();
?>
<main class="pv-credit-page pv-credit-rates-page">
  <section class="pv-credit-result-hero pv-credit-rates-hero">
    <div class="pv-credit-container pv-credit-result-hero-grid">
      <div>
        <nav class="pv-credit-breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa</a><span>/</span><a href="<?php echo esc_url( home_url( '/kredi/' ) ); ?>">Kredi</a><span>/</span><strong><?php the_title(); ?></strong></nav>
        <span class="pv-credit-eyebrow">Mevduat ve faiz ekranı</span>
        <h1><?php the_title(); ?></h1>
        <p>TL, dolar ve euro mevduat oranlarını dönemlere göre takip edin. Oranlar kaynak veriye bağlı olarak değişebilir.</p>
      </div>
      <div class="pv-credit-rate-switch-card">
        <strong>Para Birimi</strong>
        <div class="pv-credit-rate-tabs">
          <a class="<?php echo $type === 'try' ? 'active' : ''; ?>" href="<?php echo esc_url( home_url( '/faiz-oranlari/?type=try' ) ); ?>">Türk Lirası</a>
          <a class="<?php echo $type === 'usd' ? 'active' : ''; ?>" href="<?php echo esc_url( home_url( '/faiz-oranlari/?type=usd' ) ); ?>">Dolar</a>
          <a class="<?php echo $type === 'eur' ? 'active' : ''; ?>" href="<?php echo esc_url( home_url( '/faiz-oranlari/?type=eur' ) ); ?>">Euro</a>
        </div>
      </div>
    </div>
  </section>
  <div class="pv-credit-container">
    <?php pv_v7_credit_render_ad( 'pv-credit-ad-top' ); ?>
    <section class="pv-credit-rates-card">
      <div class="pv-credit-results-head"><div><span>Güncel oranlar</span><h2>Mevduat Oranları</h2></div><p>Tablo Mynet Finans kaynak verisinden yüklenir.</p></div>
      <div class="pv-credit-table-wrap">
        <table class="currencyTable currencyFullTable pv-credit-rates-table" data-pv-credit-rates-table>
          <?php if ( $rows ) : ?>
            <?php if ( wp_is_mobile() ) : ?>
              <thead><tr><th style="text-align:left;width:80%;">Banka</th><th style="text-align:left;width:20%;">1 Aylık</th></tr></thead>
              <tbody>
                <?php foreach ( $rows as $row ) : ?>
                  <tr><td><?php echo esc_html( $row['bank'] ); ?></td><td><?php echo esc_html( $row['m1'] ); ?></td></tr>
                <?php endforeach; ?>
              </tbody>
            <?php else : ?>
              <thead><tr><th>Banka</th><th>1 Aylık</th><th>3 Aylık</th><th>6 Aylık</th><th>12 Aylık</th></tr></thead>
              <tbody>
                <?php foreach ( $rows as $row ) : ?>
                  <tr>
                    <td><?php echo esc_html( $row['bank'] ); ?></td>
                    <td><?php echo esc_html( $row['m1'] ); ?></td>
                    <td><?php echo esc_html( $row['m3'] ); ?></td>
                    <td><?php echo esc_html( $row['m6'] ); ?></td>
                    <td><?php echo esc_html( $row['m12'] ); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            <?php endif; ?>
          <?php else : ?>
            <tbody><tr><td>Oranlar şu anda yüklenemedi.</td></tr></tbody>
          <?php endif; ?>
        </table>
      </div>
    </section>
    <?php pv_v7_credit_render_tools_grid(); ?>
    <?php pv_v7_credit_render_popular(); ?>
  </div>
</main>
<?php get_footer(); ?>
