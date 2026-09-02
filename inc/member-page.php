<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
pv_member_require_login();
$pv_member_mode = isset( $pv_member_mode ) ? (string) $pv_member_mode : 'profile';
$user = wp_get_current_user();
$js = get_stylesheet_directory() . '/assets/js/pv-member.js';
wp_enqueue_script( 'pv-member-ui', get_stylesheet_directory_uri() . '/assets/js/pv-member.js', array( 'jquery' ), is_file( $js ) ? filemtime( $js ) : '1', true );
wp_localize_script( 'pv-member-ui', 'pvMember', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => pv_member_ajax_nonce() ) );
get_header();
?>
<main class="pv-member-shell">
  <div class="pv-member-grid">
    <?php pv_member_nav( $pv_member_mode ); ?>
    <section class="pv-member-card">
      <?php if ( $pv_member_mode === 'profile' ) : ?>
        <h1>Profil Ayarlarım</h1>
        <form class="pv-member-form pv-member-ajax-form" data-type="edit_profile">
          <div class="pv-member-field"><label for="pv-first-name">Adınız</label><input id="pv-first-name" name="user_firstname" value="<?php echo esc_attr( get_user_meta( $user->ID, 'first_name', true ) ); ?>"></div>
          <div class="pv-member-field"><label for="pv-last-name">Soyadınız</label><input id="pv-last-name" name="user_lastname" value="<?php echo esc_attr( get_user_meta( $user->ID, 'last_name', true ) ); ?>"></div>
          <div class="pv-member-field"><label>Kullanıcı Adınız</label><input value="<?php echo esc_attr( $user->user_login ); ?>" disabled></div>
          <div class="pv-member-field"><label for="pv-email">E-posta Adresiniz</label><input id="pv-email" type="email" name="user_email" value="<?php echo esc_attr( $user->user_email ); ?>" required></div>
          <div class="pv-member-field"><label for="pv-bio">Biyografi notunuz</label><textarea id="pv-bio" name="user_biyografi"><?php echo esc_textarea( get_user_meta( $user->ID, 'biyografi', true ) ); ?></textarea></div>
          <div class="pv-member-message"></div><button class="pv-member-button" type="submit">Kaydet</button>
        </form>
      <?php elseif ( $pv_member_mode === 'photo' ) : ?>
        <h1>Profil Fotoğrafım</h1>
        <img class="pv-member-photo" src="<?php echo esc_url( pv_member_profile_photo_url( $user->ID ) ); ?>" alt="Profil fotoğrafı">
        <?php if ( isset( $_GET['updated'] ) ) : ?><div class="pv-member-message" style="display:block">Profil fotoğrafınız güncellendi.</div><?php endif; ?>
        <?php if ( isset( $_GET['error'] ) ) : ?><div class="pv-member-message is-error" style="display:block">Fotoğraf yüklenemedi. JPG, PNG veya WebP dosyası deneyin.</div><?php endif; ?>
        <form class="pv-member-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="pv_member_upload_profile"><?php wp_nonce_field( 'pv-member-upload-profile' ); ?>
          <div class="pv-member-field"><label for="pv-photo">Yeni fotoğraf</label><input id="pv-photo" type="file" name="userfile" accept="image/jpeg,image/png,image/webp" required></div>
          <button class="pv-member-button" type="submit">Fotoğrafı Yükle</button>
        </form>
      <?php elseif ( $pv_member_mode === 'password' ) : ?>
        <h1>Şifre Değiştir</h1>
        <form class="pv-member-form pv-member-ajax-form" data-type="update_password">
          <div class="pv-member-field"><label for="pv-old-password">Eski Şifreniz</label><input id="pv-old-password" type="password" name="last_password" required></div>
          <div class="pv-member-field"><label for="pv-new-password">Yeni Şifreniz</label><input id="pv-new-password" type="password" name="new_password" required></div>
          <div class="pv-member-field"><label for="pv-new-password-retry">Yeni Şifre Tekrar</label><input id="pv-new-password-retry" type="password" name="new_password_retry" required></div>
          <div class="pv-member-message"></div><button class="pv-member-button" type="submit">Kaydet</button>
        </form>
      <?php elseif ( $pv_member_mode === 'social' ) : ?>
        <h1>Sosyal Medya Hesapları</h1>
        <form class="pv-member-form pv-member-ajax-form" data-type="update_social">
          <?php foreach ( array( 'facebook' => 'Facebook', 'twitter' => 'Twitter / X', 'instagram' => 'Instagram' ) as $key => $label ) : ?>
            <div class="pv-member-field"><label for="pv-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?> profil URL</label><input id="pv-<?php echo esc_attr( $key ); ?>" type="url" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>" placeholder="https://"></div>
          <?php endforeach; ?>
          <div class="pv-member-message"></div><button class="pv-member-button" type="submit">Kaydet</button>
        </form>
      <?php elseif ( $pv_member_mode === 'list' ) : ?>
        <h1>Döviz Listem</h1>
        <?php $items = get_user_meta( $user->ID, 'uye_liste', true ); $items = is_array( $items ) ? $items : array(); ?>
        <div class="pv-member-table-wrap"><table class="pv-member-table"><thead><tr><th>Döviz</th><th>Alış</th><th>Satış</th><th>Fark</th><th></th></tr></thead><tbody>
        <?php foreach ( $items as $currency ) : $detail = function_exists( 'pv_market_currency_resolve_query' ) ? pv_market_currency_resolve_query( $currency ) : array(); if ( empty( $detail['name'] ) ) { continue; } ?>
          <tr><td><a href="<?php echo esc_url( pv_market_currency_detail_url( $detail['key'] ) ); ?>"><?php echo esc_html( strtoupper( $detail['code'] ) . ' - ' . $detail['name'] ); ?></a></td><td><?php echo esc_html( $detail['buying'] ); ?></td><td><?php echo esc_html( $detail['selling'] ); ?></td><td><?php echo esc_html( $detail['change_pct'] ); ?>%</td><td><button class="pv-member-remove" data-pv-member-remove="delete_liste" data-currency="<?php echo esc_attr( $detail['key'] ); ?>">Çıkar</button></td></tr>
        <?php endforeach; ?></tbody></table></div>
        <?php if ( ! $items ) : ?><p>Listenizde henüz döviz bulunmuyor.</p><?php endif; ?>
      <?php elseif ( $pv_member_mode === 'alarms' ) : ?>
        <h1>Döviz Alarmlarım</h1>
        <?php $meta = get_user_meta( $user->ID, 'uye_alarm', true ); $currencies = is_array( $meta ) && ! empty( $meta['doviz'] ) && is_array( $meta['doviz'] ) ? $meta['doviz'] : array(); $amounts = is_array( $meta ) && ! empty( $meta['miktar'] ) && is_array( $meta['miktar'] ) ? $meta['miktar'] : array(); ?>
        <div class="pv-member-table-wrap"><table class="pv-member-table"><thead><tr><th>Döviz</th><th>Güncel</th><th>Alarm Miktarı</th><th></th></tr></thead><tbody>
        <?php foreach ( $currencies as $i => $currency ) : $detail = function_exists( 'pv_market_currency_resolve_query' ) ? pv_market_currency_resolve_query( $currency ) : array(); if ( empty( $detail['name'] ) ) { continue; } ?>
          <tr><td><a href="<?php echo esc_url( pv_market_currency_detail_url( $detail['key'] ) ); ?>"><?php echo esc_html( strtoupper( $detail['code'] ) . ' - ' . $detail['name'] ); ?></a></td><td><?php echo esc_html( $detail['selling'] ); ?></td><td><?php echo esc_html( isset( $amounts[ $i ] ) ? $amounts[ $i ] : '-' ); ?></td><td><button class="pv-member-remove" data-pv-member-remove="delete_alarm" data-currency="<?php echo esc_attr( $detail['key'] ); ?>">Kaldır</button></td></tr>
        <?php endforeach; ?></tbody></table></div>
        <?php if ( ! $currencies ) : ?><p>Henüz aktif döviz alarmınız bulunmuyor.</p><?php endif; ?>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php get_footer();
