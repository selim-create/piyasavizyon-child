<?php
/* Template Name: Giriş / Kayıt Sayfası */
if ( is_user_logged_in() ) { wp_safe_redirect( home_url( '/' ) ); exit; }
get_header();
?>
<main class="pv-auth-shell">
  <div class="pv-auth-card">
    <section class="pv-auth-panel">
      <h1>Üye Kaydı</h1>
      <form id="register" class="ajax-auth" action="register" method="post" novalidate>
        <input type="text" name="signonname" id="signonname" placeholder="Kullanıcı Adınız" autocomplete="username" required>
        <input type="password" name="signonpassword" id="signonpassword" placeholder="Şifreniz" autocomplete="new-password" required>
        <input type="email" name="email" id="email" placeholder="E-Posta Adresiniz" autocomplete="email" required>
        <input type="submit" class="submit_button" value="Kayıt Ol">
        <p class="check" id="check" style="display:none"></p>
        <?php wp_nonce_field( 'ajax-register-nonce', 'signonsecurity' ); ?>
      </form>
      <div class="pv-auth-help"><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Şifremi unuttum</a></div>
    </section>
    <section class="pv-auth-panel">
      <h2>Üye Girişi</h2>
      <form id="login" class="ajax-auth" action="login" method="post">
        <input type="text" name="username" id="username" placeholder="Kullanıcı Adınız" autocomplete="username" required>
        <input type="password" name="password" id="password" placeholder="Şifreniz" autocomplete="current-password" required>
        <input type="submit" class="submit_button" value="Giriş Yap">
        <p class="check" id="check" style="display:none"></p>
        <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
      </form>
      <div class="pv-auth-help"><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Şifremi unuttum</a></div>
    </section>
  </div>
</main>
<?php get_footer();
