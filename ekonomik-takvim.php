<?php
/*
  Template Name: Ekonomik Takvim
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

$period = isset( $_GET['date'] ) ? pv_market_economic_calendar_period( wp_unslash( $_GET['date'] ) ) : 'dun';
$data   = pv_market_economic_calendar( $period );
$events = isset( $data['events'] ) && is_array( $data['events'] ) ? $data['events'] : array();
$tabs   = array(
    'dun'     => 'Dün',
    'bugun'   => 'Bugün',
    'yarin'   => 'Yarın',
    '1-hafta' => '1 Hafta',
    '1-ay'    => '1 Ay',
);

get_header();
?>
<style>
.pv-market-calendar-native .currencyTable tr th{font-weight:500;}
.pv-market-calendar-native .dateTable{display:block;margin-bottom:14px;}
.pv-market-calendar-native .dateTable ul{display:flex;flex-wrap:wrap;gap:18px;margin:0;padding:0;list-style:none;}
.pv-market-calendar-native .dateTable a{display:block;padding:8px 0;color:rgba(36,36,36,.48);font-size:13px;font-weight:700;text-transform:uppercase;text-decoration:none;}
.pv-market-calendar-native .dateTable li.active a{color:#242424;border-bottom:2px solid #fab915;}
.pv-market-calendar-native .pv-calendar-importance{color:#fbb916;font-size:18px;letter-spacing:1px;white-space:nowrap;}
.pv-market-calendar-native .pv-calendar-empty{padding:24px;text-align:center;color:#667085;}
@media(max-width:760px){
  .pv-market-calendar-native .pv-calendar-desktop{display:none;}
  .pv-market-calendar-native .pv-calendar-mobile{display:table;width:100%;}
}
@media(min-width:761px){
  .pv-market-calendar-native .pv-calendar-mobile{display:none;}
}
</style>
<div class="site-wrapper pv-market-native pv-market-calendar-native">
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

          <h1 class="postTitle"><?php the_title(); ?></h1>

          <div class="pv-market-list-content pv-calendar-content">
            <div class="mainContent"><div class="main"><div class="widget">
              <div class="dateTable"><ul>
                <?php foreach ( $tabs as $key => $label ) : ?>
                  <li class="<?php echo $period === $key ? 'active' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( 'date', $key, home_url( '/ekonomik-takvim/' ) ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
                <?php endforeach; ?>
              </ul></div>

              <?php if ( $events ) : ?>
                <div class="currencyShowcase fullShowcase mobileBottomNo">
                  <table class="currencyTable currencyFullTable pv-calendar-desktop">
                    <thead><tr><th>Tarih</th><th>Ülke</th><th>Önem</th><th>Olay</th><th>Beklenti</th><th>Gerçekleşen</th><th>Önceki</th></tr></thead>
                    <tbody>
                    <?php foreach ( $events as $event ) : ?>
                      <?php
                      $ts = isset( $event['d'] ) ? (int) floor( (float) $event['d'] / 1000 ) : 0;
                      $importance = isset( $event['i'] ) ? max( 0, min( 5, (int) $event['i'] ) ) : 0;
                      $expected = isset( $event['did'] ) && (string) $event['did'] !== '0' ? $event['did'] : '-';
                      ?>
                      <tr>
                        <td><?php echo $ts ? esc_html( wp_date( 'd F H:i', $ts ) ) : '-'; ?></td>
                        <td><?php echo esc_html( isset( $event['c'] ) ? $event['c'] : '-' ); ?></td>
                        <td><span class="pv-calendar-importance"><?php echo esc_html( str_repeat( '●', $importance ) ); ?></span></td>
                        <td><?php echo esc_html( isset( $event['e'] ) ? $event['e'] : '-' ); ?></td>
                        <td><?php echo esc_html( $expected ); ?></td>
                        <td><?php echo esc_html( isset( $event['a'] ) ? $event['a'] : '-' ); ?></td>
                        <td><?php echo esc_html( isset( $event['p'] ) ? $event['p'] : '-' ); ?></td>
                      </tr>
                    <?php endforeach; ?>
                    </tbody>
                  </table>

                  <table class="currencyTable currencyFullTable pv-calendar-mobile">
                    <thead><tr><th>Ülke</th><th>Olay</th></tr></thead>
                    <tbody>
                    <?php foreach ( $events as $event ) : ?>
                      <tr><td><?php echo esc_html( isset( $event['c'] ) ? $event['c'] : '-' ); ?></td><td><?php echo esc_html( isset( $event['e'] ) ? $event['e'] : '-' ); ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else : ?>
                <div class="pv-calendar-empty">Seçilen dönem için ekonomik takvim verisi şu anda alınamıyor.</div>
              <?php endif; ?>
            </div></div></div>
          </div>
        </div>
      </div>

      <?php if ( ! wp_is_mobile() && function_exists( 'pv_v7_market_sidebar' ) ) { pv_v7_market_sidebar( 'ekonomik-takvim' ); } ?>
    </div>
  </section>
  <div class="clear"></div>
</div>
<?php get_footer(); ?>
