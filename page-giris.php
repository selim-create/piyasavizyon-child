<?php if ( ! defined('ABSPATH') ) { exit; } get_header(); ?>
<main class="wrap pv-login-page">
  <section class="pv-login-card">
    <div class="pv-login-intro">
      <span class="kicker">Piyasa Vizyon Üye Girişi</span>
      <h1>Finans paneline giriş yapın.</h1>
      <p>Favori piyasalarınızı, alarm listenizi ve özel içeriklerinizi tek ekrandan takip edin.</p>
    </div>
    <div class="pv-login-formbox">
      <?php if ( is_user_logged_in() ) : ?>
        <h2>Zaten giriş yaptınız</h2>
        <p>Profilinize veya ana sayfaya dönebilirsiniz.</p>
        <a class="primary" href="<?php echo esc_url(home_url('/')); ?>">Ana Sayfaya Dön</a>
      <?php else : ?>
        <h2>Giriş Yap</h2>
        <?php wp_login_form(array(
          'redirect' => home_url('/'),
          'label_username' => 'Kullanıcı adı veya e-posta',
          'label_password' => 'Şifre',
          'label_remember' => 'Beni hatırla',
          'label_log_in' => 'Giriş Yap',
          'remember' => true,
        )); ?>
        <div class="pv-login-links">
          <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Şifremi unuttum</a>
          <a href="<?php echo esc_url(wp_registration_url()); ?>">Yeni hesap oluştur</a>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
