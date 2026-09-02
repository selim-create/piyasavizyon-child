<?php
/*
  Template Name: Parite Tablo
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $parite_data;

$rows = array();
if ( is_array( $parite_data ) && isset( $parite_data['code'] ) && is_array( $parite_data['code'] ) ) {
    foreach ( $parite_data['code'] as $key => $code ) {
        $compact = strtoupper( preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $code ) ) );
        if ( $compact === '' ) {
            continue;
        }

        $slug = strlen( $compact ) === 6
            ? strtolower( substr( $compact, 0, 3 ) . '-' . substr( $compact, 3, 3 ) )
            : sanitize_title( (string) $code );

        if ( $slug === '' ) {
            continue;
        }

        $full_name = isset( $parite_data['full_name'][ $key ] ) ? (string) $parite_data['full_name'][ $key ] : $compact;
        $buying    = isset( $parite_data['buying'][ $key ] ) ? (string) $parite_data['buying'][ $key ] : '-';
        $selling   = isset( $parite_data['selling'][ $key ] ) ? (string) $parite_data['selling'][ $key ] : '-';
        $change    = isset( $parite_data['change_rate'][ $key ] ) ? (string) $parite_data['change_rate'][ $key ] : '0';
        $time      = isset( $parite_data['time'][ $key ] ) ? (string) $parite_data['time'][ $key ] : '-';

        $numeric_change = (float) str_replace( array( '.', ',', '%' ), array( '', '.', '' ), $change );
        $direction = $numeric_change > 0 ? 'increase' : ( $numeric_change < 0 ? 'decrease' : 'neutral' );
        $color = $numeric_change > 0 ? '#40bc9a' : ( $numeric_change < 0 ? '#fc4b67' : '#667085' );

        $rows[ $slug ] = array(
            'slug'      => $slug,
            'code'      => $compact,
            'full_name' => $full_name,
            'buying'    => $buying,
            'selling'   => $selling,
            'change'    => $change,
            'time'      => $time,
            'direction' => $direction,
            'color'     => $color,
        );
    }
}

get_header();
?>
<style>
  .currencyTable tr th{font-weight:500;}
  .currencyTable tr td b{color:#3b72de !important;}
</style>
<div class="site-wrapper pv-market-native pv-market-parities-native">
  <section class="content home">
    <div class="container-wrap">
      <div class="widebar floatLeft">
        <div class="singleWrapper">
          <div class="breadcrumb">
            <ul class="block">
              <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Anasayfa<i>/</i></a></li>
              <li class="post bg"><span><?php the_title(); ?></span></li>
            </ul>
          </div>

          <h1 class="postTitle centerli"><?php the_title(); ?></h1>

          <div class="pv-market-list-content pv-parities-content">
            <div class="mainContent">
              <div class="main">
                <div class="widget">
                  <div class="currencyShowcase fullShowcase mobileBottomNo">
                    <?php if ( empty( $rows ) ) : ?>
                      <div class="pv-market-empty">Parite verileri şu anda alınamıyor.</div>
                    <?php elseif ( wp_is_mobile() ) : ?>
                      <table class="currencyTable currencyFullTable">
                        <tr>
                          <th style="width:70% !important;display:inline-block;">Döviz</th>
                          <th class="alisSo" style="width:20%;display:inline-block;padding-left:0;">Alış</th>
                        </tr>
                        <?php foreach ( $rows as $row ) : ?>
                          <tr class="alt dKurlariS2 sonn">
                            <td style="width:70% !important;display:inline-block;">
                              <a href="<?php echo esc_url( home_url( '/parite/?p=' . rawurlencode( $row['slug'] ) ) ); ?>"><b><?php echo esc_html( $row['code'] ); ?></b></a>
                            </td>
                            <td style="width:20%;display:inline-block;padding-left:0;">
                              <?php if ( $row['direction'] !== 'neutral' ) : ?><i class="<?php echo esc_attr( $row['direction'] ); ?>"></i><?php endif; ?>
                              <span><?php echo esc_html( $row['buying'] ); ?></span>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </table>
                    <?php else : ?>
                      <table class="currencyTable currencyFullTable">
                        <tr>
                          <th>Döviz</th>
                          <th>Alış</th>
                          <th>Satış</th>
                          <th>Fark</th>
                          <th>Saat</th>
                        </tr>
                        <?php foreach ( $rows as $row ) : ?>
                          <tr>
                            <td>
                              <a href="<?php echo esc_url( home_url( '/parite/?p=' . rawurlencode( $row['slug'] ) ) ); ?>"><b><?php echo esc_html( $row['full_name'] ); ?></b></a>
                            </td>
                            <td style="font-weight:500;color:<?php echo esc_attr( $row['color'] ); ?>;">
                              <?php if ( $row['direction'] !== 'neutral' ) : ?><i class="<?php echo esc_attr( $row['direction'] ); ?>"></i><?php endif; ?>
                              <?php echo esc_html( $row['buying'] ); ?>
                            </td>
                            <td style="font-weight:normal;"><?php echo esc_html( $row['selling'] ); ?></td>
                            <td style="font-weight:normal;">
                              <span class="<?php echo esc_attr( $row['direction'] ); ?> subtract">% <?php echo esc_html( $row['change'] ); ?></span>
                            </td>
                            <td style="padding:0 15px;font-weight:normal;"><?php echo esc_html( $row['time'] ); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </table>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) {
          pv_v7_market_sidebar( 'tum-pariteler' );
      } ?>
    </div>

    <?php dynamic_sidebar( 'Sayfa Alt (Tüm Pariteler)' ); ?>
  </section>
  <div class="clear"></div>
</div>
<?php get_footer(); ?>
