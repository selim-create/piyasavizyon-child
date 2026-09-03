<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Piyasa Vizyon Theme Control Center.
 *
 * A focused operational dashboard for the standalone theme. It intentionally
 * surfaces existing runtime state instead of creating a second theme-options
 * framework or duplicating settings that already exist elsewhere.
 */

function pv_control_center_page_url( $tab = 'overview' ) {
    return add_query_arg(
        array(
            'page' => 'pv-control-center',
            'tab'  => sanitize_key( $tab ),
        ),
        admin_url( 'admin.php' )
    );
}

function pv_control_center_menu() {
    add_menu_page(
        __( 'Piyasa Vizyon', 'piyasavizyon-v7' ),
        __( 'Piyasa Vizyon', 'piyasavizyon-v7' ),
        'manage_options',
        'pv-control-center',
        'pv_control_center_render',
        'dashicons-chart-line',
        3
    );
}
add_action( 'admin_menu', 'pv_control_center_menu', 5 );

function pv_control_center_assets( $hook ) {
    if ( $hook !== 'toplevel_page_pv-control-center' ) {
        return;
    }

    $path = get_stylesheet_directory() . '/assets/css/pv-admin-control.css';
    wp_enqueue_style(
        'pv-admin-control',
        get_stylesheet_directory_uri() . '/assets/css/pv-admin-control.css',
        array(),
        is_file( $path ) ? filemtime( $path ) : '1.0.0'
    );
}
add_action( 'admin_enqueue_scripts', 'pv_control_center_assets' );

function pv_control_center_status( $state, $label = '' ) {
    $state = in_array( $state, array( 'ok', 'warning', 'danger', 'neutral' ), true ) ? $state : 'neutral';
    if ( $label === '' ) {
        $labels = array(
            'ok'      => __( 'Sağlıklı', 'piyasavizyon-v7' ),
            'warning' => __( 'Kontrol et', 'piyasavizyon-v7' ),
            'danger'  => __( 'Sorun', 'piyasavizyon-v7' ),
            'neutral' => __( 'Bilgi', 'piyasavizyon-v7' ),
        );
        $label = $labels[ $state ];
    }

    return '<span class="pvcc-status pvcc-status--' . esc_attr( $state ) . '"><span class="pvcc-status-dot"></span>' . esc_html( $label ) . '</span>';
}

function pv_control_center_cache_snapshot() {
    $uploads = wp_upload_dir( null, false );
    $base    = ! empty( $uploads['basedir'] ) ? untrailingslashit( $uploads['basedir'] ) : '';
    $dir     = $base ? $base . '/piyasa-vizyon-market-cache' : '';
    $ttl     = function_exists( 'pv_market_cache_minutes' ) ? pv_market_cache_minutes() * MINUTE_IN_SECONDS : 5 * MINUTE_IN_SECONDS;
    $now     = time();

    $resources = array(
        'doviz.json'  => __( 'Döviz', 'piyasavizyon-v7' ),
        'altin.json'  => __( 'Altın', 'piyasavizyon-v7' ),
        'parite.json' => __( 'Parite', 'piyasavizyon-v7' ),
        'coin.json'   => __( 'Kripto', 'piyasavizyon-v7' ),
        'borsa.json'  => __( 'Borsa', 'piyasavizyon-v7' ),
    );

    $rows = array();
    foreach ( $resources as $file => $label ) {
        $path       = $dir ? $dir . '/' . $file : '';
        $exists     = $path && is_readable( $path );
        $payload    = $exists ? json_decode( (string) file_get_contents( $path ), true ) : null;
        $cached_at  = is_array( $payload ) && ! empty( $payload['time'] ) ? (int) $payload['time'] : 0;
        $has_data   = is_array( $payload ) && array_key_exists( 'data', $payload ) && $payload['data'] !== array() && $payload['data'] !== '' && $payload['data'] !== null;
        $age        = $cached_at ? max( 0, $now - $cached_at ) : null;
        $fresh      = $has_data && $age !== null && $age < $ttl;

        $rows[] = array(
            'label'      => $label,
            'file'       => $file,
            'exists'     => (bool) $exists,
            'has_data'   => (bool) $has_data,
            'cached_at'  => $cached_at,
            'age'        => $age,
            'fresh'      => $fresh,
            'state'      => $fresh ? 'ok' : ( $has_data ? 'warning' : 'danger' ),
        );
    }

    return $rows;
}

function pv_control_center_ad_snapshot() {
    $areas = array(
        'pv_header_ad'          => __( 'Header Masthead', 'piyasavizyon-v7' ),
        'pv_mobile_masthead'    => __( 'Mobil Masthead', 'piyasavizyon-v7' ),
        'pv_right_ad'           => __( 'Sağ Üst', 'piyasavizyon-v7' ),
        'pv_content_ad'         => __( 'İçerik Arası', 'piyasavizyon-v7' ),
        'pv_mobile_content_ad'  => __( 'Mobil İçerik', 'piyasavizyon-v7' ),
        'pv_sidebar_top'        => __( 'Sidebar Üst', 'piyasavizyon-v7' ),
        'pv_sidebar_mid'        => __( 'Sidebar Orta', 'piyasavizyon-v7' ),
        'pv_sidebar_sky'        => __( 'Sidebar Skyscraper', 'piyasavizyon-v7' ),
        'pv_mobile_sticky_ad'   => __( 'Mobil Sticky', 'piyasavizyon-v7' ),
        'pv_footer_ad'          => __( 'Footer', 'piyasavizyon-v7' ),
    );

    $rows = array();
    foreach ( $areas as $id => $label ) {
        $rows[] = array(
            'id'     => $id,
            'label'  => $label,
            'active' => is_active_sidebar( $id ),
        );
    }
    return $rows;
}

function pv_control_center_runtime_snapshot() {
    $theme      = wp_get_theme();
    $parent     = $theme->parent();
    $standalone = get_template() === get_stylesheet() && ! $parent;

    return array(
        'theme_name'    => $theme->get( 'Name' ),
        'theme_version' => $theme->get( 'Version' ),
        'php_version'   => PHP_VERSION,
        'wp_version'    => get_bloginfo( 'version' ),
        'template'      => get_template(),
        'stylesheet'    => get_stylesheet(),
        'standalone'    => $standalone,
        'registration'  => (bool) get_option( 'users_can_register' ),
        'turnstile'     => function_exists( 'pv_registration_turnstile_enabled' ) && pv_registration_turnstile_enabled(),
        'rewrite'       => (string) get_option( 'pv_credit_rewrite_version', '' ),
    );
}

function pv_control_center_header( $active_tab ) {
    $tabs = array(
        'overview' => array( __( 'Genel Bakış', 'piyasavizyon-v7' ), 'dashicons-dashboard' ),
        'markets'  => array( __( 'Piyasa Verileri', 'piyasavizyon-v7' ), 'dashicons-chart-area' ),
        'ads'      => array( __( 'Reklam Yönetimi', 'piyasavizyon-v7' ), 'dashicons-megaphone' ),
        'security' => array( __( 'Üyelik & Güvenlik', 'piyasavizyon-v7' ), 'dashicons-shield-alt' ),
        'content'  => array( __( 'İçerik & Anasayfa', 'piyasavizyon-v7' ), 'dashicons-welcome-write-blog' ),
        'system'   => array( __( 'Sistem', 'piyasavizyon-v7' ), 'dashicons-admin-tools' ),
    );
    ?>
    <div class="pvcc-hero">
        <div>
            <div class="pvcc-eyebrow"><?php esc_html_e( 'Theme Control Center', 'piyasavizyon-v7' ); ?></div>
            <h1><?php esc_html_e( 'Piyasa Vizyon', 'piyasavizyon-v7' ); ?></h1>
            <p><?php esc_html_e( 'Piyasa verileri, reklam alanları, üyelik güvenliği ve standalone tema sağlığını tek merkezden izleyin.', 'piyasavizyon-v7' ); ?></p>
        </div>
        <div class="pvcc-hero-brand">PV<span>.</span></div>
    </div>
    <nav class="pvcc-tabs" aria-label="<?php esc_attr_e( 'Tema yönetimi bölümleri', 'piyasavizyon-v7' ); ?>">
        <?php foreach ( $tabs as $key => $tab ) : ?>
            <a href="<?php echo esc_url( pv_control_center_page_url( $key ) ); ?>" class="<?php echo $active_tab === $key ? 'is-active' : ''; ?>">
                <span class="dashicons <?php echo esc_attr( $tab[1] ); ?>"></span><?php echo esc_html( $tab[0] ); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php
}

function pv_control_center_render_overview() {
    $runtime = pv_control_center_runtime_snapshot();
    $ads     = pv_control_center_ad_snapshot();
    $caches  = pv_control_center_cache_snapshot();
    $active_ads = count( array_filter( $ads, static function( $item ) { return $item['active']; } ) );
    $healthy_market = count( array_filter( $caches, static function( $item ) { return $item['state'] === 'ok'; } ) );
    ?>
    <div class="pvcc-kpis">
        <div class="pvcc-kpi"><div class="pvcc-kpi-icon"><span class="dashicons dashicons-admin-appearance"></span></div><div><span><?php esc_html_e( 'Tema', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $runtime['theme_version'] ); ?></strong><?php echo wp_kses_post( pv_control_center_status( $runtime['standalone'] ? 'ok' : 'danger', $runtime['standalone'] ? __( 'Standalone', 'piyasavizyon-v7' ) : __( 'Parent bağlı', 'piyasavizyon-v7' ) ) ); ?></div></div>
        <div class="pvcc-kpi"><div class="pvcc-kpi-icon"><span class="dashicons dashicons-editor-code"></span></div><div><span><?php esc_html_e( 'PHP', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $runtime['php_version'] ); ?></strong><?php echo wp_kses_post( pv_control_center_status( version_compare( PHP_VERSION, '8.3', '>=' ) ? 'ok' : 'warning' ) ); ?></div></div>
        <div class="pvcc-kpi"><div class="pvcc-kpi-icon"><span class="dashicons dashicons-chart-area"></span></div><div><span><?php esc_html_e( 'Piyasa Cache', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $healthy_market . '/5' ); ?></strong><?php echo wp_kses_post( pv_control_center_status( $healthy_market === 5 ? 'ok' : 'warning' ) ); ?></div></div>
        <div class="pvcc-kpi"><div class="pvcc-kpi-icon"><span class="dashicons dashicons-megaphone"></span></div><div><span><?php esc_html_e( 'Reklam Alanları', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $active_ads . '/10' ); ?></strong><?php echo wp_kses_post( pv_control_center_status( $active_ads > 0 ? 'ok' : 'warning', $active_ads > 0 ? __( 'Aktif slotlar var', 'piyasavizyon-v7' ) : __( 'Aktif slot yok', 'piyasavizyon-v7' ) ) ); ?></div></div>
        <div class="pvcc-kpi"><div class="pvcc-kpi-icon"><span class="dashicons dashicons-groups"></span></div><div><span><?php esc_html_e( 'Üyelik', 'piyasavizyon-v7' ); ?></span><strong><?php echo $runtime['registration'] ? esc_html__( 'Açık', 'piyasavizyon-v7' ) : esc_html__( 'Kapalı', 'piyasavizyon-v7' ); ?></strong><?php echo wp_kses_post( pv_control_center_status( $runtime['registration'] ? 'ok' : 'warning' ) ); ?></div></div>
        <div class="pvcc-kpi"><div class="pvcc-kpi-icon"><span class="dashicons dashicons-shield-alt"></span></div><div><span><?php esc_html_e( 'Turnstile', 'piyasavizyon-v7' ); ?></span><strong><?php echo $runtime['turnstile'] ? esc_html__( 'Aktif', 'piyasavizyon-v7' ) : esc_html__( 'Pasif', 'piyasavizyon-v7' ); ?></strong><?php echo wp_kses_post( pv_control_center_status( $runtime['turnstile'] ? 'ok' : 'neutral' ) ); ?></div></div>
    </div>

    <div class="pvcc-grid pvcc-grid--2">
        <section class="pvcc-card">
            <div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Operasyon', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Hızlı işlemler', 'piyasavizyon-v7' ); ?></h2></div></div>
            <div class="pvcc-actions">
                <a class="pvcc-action" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><span class="dashicons dashicons-edit-page"></span><span><strong><?php esc_html_e( 'Yeni haber ekle', 'piyasavizyon-v7' ); ?></strong><small><?php esc_html_e( 'WordPress editörünü aç', 'piyasavizyon-v7' ); ?></small></span></a>
                <a class="pvcc-action" href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><span class="dashicons dashicons-screenoptions"></span><span><strong><?php esc_html_e( 'Reklam alanlarını düzenle', 'piyasavizyon-v7' ); ?></strong><small><?php esc_html_e( 'Widget / sidebar yerleşimleri', 'piyasavizyon-v7' ); ?></small></span></a>
                <a class="pvcc-action" href="<?php echo esc_url( pv_control_center_page_url( 'security' ) ); ?>"><span class="dashicons dashicons-lock"></span><span><strong><?php esc_html_e( 'Üyelik güvenliği', 'piyasavizyon-v7' ); ?></strong><small><?php esc_html_e( 'Turnstile ve kayıt durumu', 'piyasavizyon-v7' ); ?></small></span></a>
                <a class="pvcc-action" href="<?php echo esc_url( admin_url( 'themes.php?page=pv-v7-codes' ) ); ?>"><span class="dashicons dashicons-editor-code"></span><span><strong><?php esc_html_e( 'Global kodlar', 'piyasavizyon-v7' ); ?></strong><small><?php esc_html_e( 'Analytics, GTM ve head/body kodları', 'piyasavizyon-v7' ); ?></small></span></a>
            </div>
        </section>
        <section class="pvcc-card pvcc-card--dark">
            <div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Mimari', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Standalone tema durumu', 'piyasavizyon-v7' ); ?></h2></div><?php echo wp_kses_post( pv_control_center_status( $runtime['standalone'] ? 'ok' : 'danger' ) ); ?></div>
            <div class="pvcc-system-list">
                <div><span><?php esc_html_e( 'Template', 'piyasavizyon-v7' ); ?></span><code><?php echo esc_html( $runtime['template'] ); ?></code></div>
                <div><span><?php esc_html_e( 'Stylesheet', 'piyasavizyon-v7' ); ?></span><code><?php echo esc_html( $runtime['stylesheet'] ); ?></code></div>
                <div><span><?php esc_html_e( 'BirFinans runtime', 'piyasavizyon-v7' ); ?></span><strong><?php echo $runtime['standalone'] ? esc_html__( 'Bağımlılık yok', 'piyasavizyon-v7' ) : esc_html__( 'Kontrol gerekli', 'piyasavizyon-v7' ); ?></strong></div>
                <div><span><?php esc_html_e( 'WordPress', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $runtime['wp_version'] ); ?></strong></div>
            </div>
        </section>
    </div>
    <?php
}

function pv_control_center_render_markets() {
    $caches = pv_control_center_cache_snapshot();
    ?>
    <section class="pvcc-card">
        <div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Runtime health', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Piyasa veri cache durumu', 'piyasavizyon-v7' ); ?></h2><p><?php esc_html_e( 'Bu ekran harici servislere yeni istek atmaz; mevcut child-owned cache dosyalarını okur.', 'piyasavizyon-v7' ); ?></p></div></div>
        <div class="pvcc-table-wrap"><table class="pvcc-table"><thead><tr><th><?php esc_html_e( 'Kaynak', 'piyasavizyon-v7' ); ?></th><th><?php esc_html_e( 'Durum', 'piyasavizyon-v7' ); ?></th><th><?php esc_html_e( 'Son cache', 'piyasavizyon-v7' ); ?></th><th><?php esc_html_e( 'Dosya', 'piyasavizyon-v7' ); ?></th></tr></thead><tbody>
        <?php foreach ( $caches as $row ) : ?>
            <tr><td><strong><?php echo esc_html( $row['label'] ); ?></strong></td><td><?php echo wp_kses_post( pv_control_center_status( $row['state'], $row['fresh'] ? __( 'Taze', 'piyasavizyon-v7' ) : ( $row['has_data'] ? __( 'Süresi geçmiş', 'piyasavizyon-v7' ) : __( 'Veri yok', 'piyasavizyon-v7' ) ) ) ); ?></td><td><?php echo $row['cached_at'] ? esc_html( wp_date( 'd.m.Y H:i:s', $row['cached_at'] ) ) : '—'; ?></td><td><code><?php echo esc_html( $row['file'] ); ?></code></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <div class="pvcc-note"><span class="dashicons dashicons-info-outline"></span><p><?php echo esc_html( sprintf( __( 'Ana piyasa cache süresi şu anda %d dakika. Süresi geçmiş cache otomatik olarak ilgili child-owned provider üzerinden yenilenir.', 'piyasavizyon-v7' ), function_exists( 'pv_market_cache_minutes' ) ? pv_market_cache_minutes() : 5 ) ); ?></p></div>
    </section>
    <?php
}

function pv_control_center_render_ads() {
    $ads = pv_control_center_ad_snapshot();
    ?>
    <section class="pvcc-card">
        <div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Inventory', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Reklam alanları', 'piyasavizyon-v7' ); ?></h2><p><?php esc_html_e( 'Aktiflik, WordPress sidebar/widget atamalarından canlı olarak okunur.', 'piyasavizyon-v7' ); ?></p></div><a class="button button-primary" href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><?php esc_html_e( 'Widget alanlarını yönet', 'piyasavizyon-v7' ); ?></a></div>
        <div class="pvcc-slot-grid">
        <?php foreach ( $ads as $row ) : ?>
            <div class="pvcc-slot"><div class="pvcc-slot-icon"><span class="dashicons dashicons-megaphone"></span></div><div><strong><?php echo esc_html( $row['label'] ); ?></strong><code><?php echo esc_html( $row['id'] ); ?></code></div><?php echo wp_kses_post( pv_control_center_status( $row['active'] ? 'ok' : 'neutral', $row['active'] ? __( 'Aktif', 'piyasavizyon-v7' ) : __( 'Boş', 'piyasavizyon-v7' ) ) ); ?></div>
        <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function pv_control_center_render_security() {
    $enabled      = function_exists( 'pv_registration_security_option' ) ? (bool) pv_registration_security_option( 'enabled', false ) : false;
    $site_key     = function_exists( 'pv_registration_security_option' ) ? (string) pv_registration_security_option( 'site_key', '' ) : '';
    $has_secret   = function_exists( 'pv_registration_security_option' ) && trim( (string) pv_registration_security_option( 'secret_key', '' ) ) !== '';
    $fully_active = function_exists( 'pv_registration_turnstile_enabled' ) && pv_registration_turnstile_enabled();
    $registration = (bool) get_option( 'users_can_register' );
    ?>
    <?php if ( isset( $_GET['updated'] ) ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Güvenlik ayarları kaydedildi.', 'piyasavizyon-v7' ); ?></p></div><?php endif; ?>
    <div class="pvcc-grid pvcc-grid--2">
        <section class="pvcc-card">
            <div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Registration', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Üyelik güvenliği', 'piyasavizyon-v7' ); ?></h2></div><?php echo wp_kses_post( pv_control_center_status( $registration ? 'ok' : 'warning', $registration ? __( 'Kayıt açık', 'piyasavizyon-v7' ) : __( 'Kayıt kapalı', 'piyasavizyon-v7' ) ) ); ?></div>
            <div class="pvcc-security-state <?php echo $fully_active ? 'is-active' : ''; ?>"><span class="dashicons dashicons-shield-alt"></span><div><strong><?php echo $fully_active ? esc_html__( 'Cloudflare Turnstile aktif', 'piyasavizyon-v7' ) : esc_html__( 'Turnstile isteğe bağlı', 'piyasavizyon-v7' ); ?></strong><p><?php echo $fully_active ? esc_html__( 'Kayıt istekleri bot doğrulamasından ve oran sınırlamadan geçiyor.', 'piyasavizyon-v7' ) : esc_html__( 'Oran sınırlama çalışmaya devam eder. Turnstile için iki anahtar da gereklidir.', 'piyasavizyon-v7' ); ?></p></div></div>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pvcc-form">
                <input type="hidden" name="action" value="pv_registration_security_save">
                <input type="hidden" name="pv_return_to_control_center" value="1">
                <?php wp_nonce_field( 'pv_registration_security_save' ); ?>
                <label class="pvcc-switch-row"><span><strong><?php esc_html_e( 'Cloudflare Turnstile', 'piyasavizyon-v7' ); ?></strong><small><?php esc_html_e( 'Kayıt formunda bot doğrulamasını etkinleştir.', 'piyasavizyon-v7' ); ?></small></span><span class="pvcc-switch"><input type="checkbox" name="pv_registration_enabled" value="1" <?php checked( $enabled ); ?>><span></span></span></label>
                <label><span><?php esc_html_e( 'Site key', 'piyasavizyon-v7' ); ?></span><input class="regular-text" type="text" name="pv_registration_site_key" value="<?php echo esc_attr( $site_key ); ?>" autocomplete="off"></label>
                <label><span><?php esc_html_e( 'Secret key', 'piyasavizyon-v7' ); ?></span><input class="regular-text" type="password" name="pv_registration_secret_key" value="" autocomplete="new-password" placeholder="<?php echo esc_attr( $has_secret ? '••••••••••••••••' : '' ); ?>"><small><?php esc_html_e( 'Boş bırakırsanız mevcut değer korunur.', 'piyasavizyon-v7' ); ?></small></label>
                <?php if ( $has_secret ) : ?><label class="pvcc-check"><input type="checkbox" name="pv_registration_clear_secret" value="1"> <?php esc_html_e( 'Kayıtlı secret key değerini sil', 'piyasavizyon-v7' ); ?></label><?php endif; ?>
                <?php submit_button( __( 'Güvenlik Ayarlarını Kaydet', 'piyasavizyon-v7' ), 'primary', 'submit', false ); ?>
            </form>
        </section>
        <section class="pvcc-card">
            <div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Sabit korumalar', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Kayıt limitleri', 'piyasavizyon-v7' ); ?></h2></div></div>
            <div class="pvcc-metric-list"><div><strong>5</strong><span><?php esc_html_e( 'IP başına / 15 dakika', 'piyasavizyon-v7' ); ?></span></div><div><strong>20</strong><span><?php esc_html_e( 'IP başına / gün', 'piyasavizyon-v7' ); ?></span></div><div><strong>100</strong><span><?php esc_html_e( 'Site geneli / 10 dakika', 'piyasavizyon-v7' ); ?></span></div></div>
            <div class="pvcc-note"><span class="dashicons dashicons-yes-alt"></span><p><?php esc_html_e( 'Bu limitler standalone kayıt güvenlik katmanında sabittir; tema paneli bunları gevşetmez.', 'piyasavizyon-v7' ); ?></p></div>
            <a class="button" href="<?php echo esc_url( home_url( '/giris-kayit-sayfasi/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Giriş / kayıt sayfasını aç', 'piyasavizyon-v7' ); ?></a>
        </section>
    </div>
    <?php
}

function pv_control_center_render_content() {
    $slider_count = (int) ( new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => 'bf_anasayfa_slider', 'meta_value' => array( '1','on','yes','true','checked','evet','Evet','EVET' ), 'meta_compare' => 'IN' ) ) )->found_posts;
    $ticker_count = (int) ( new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => 'bf_anasayfa_kayan', 'meta_value' => array( '1','on','yes','true','checked','evet','Evet','EVET' ), 'meta_compare' => 'IN' ) ) )->found_posts;
    ?>
    <div class="pvcc-grid pvcc-grid--2">
        <section class="pvcc-card"><div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Homepage', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Anasayfa yayın akışı', 'piyasavizyon-v7' ); ?></h2></div></div><div class="pvcc-metric-list"><div><strong><?php echo esc_html( $slider_count ); ?></strong><span><?php esc_html_e( 'Manşete işaretli yayın', 'piyasavizyon-v7' ); ?></span></div><div><strong><?php echo esc_html( $ticker_count ); ?></strong><span><?php esc_html_e( '4’lü kayan alana işaretli yayın', 'piyasavizyon-v7' ); ?></span></div></div><div class="pvcc-note"><span class="dashicons dashicons-info-outline"></span><p><?php esc_html_e( 'Bu kontroller yazı düzenleme ekranındaki “Piyasa Vizyon Anasayfa” kutusundan yönetilir.', 'piyasavizyon-v7' ); ?></p></div><a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'Yazıları yönet', 'piyasavizyon-v7' ); ?></a></section>
        <section class="pvcc-card"><div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Theme assets', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Marka ve menüler', 'piyasavizyon-v7' ); ?></h2></div></div><div class="pvcc-actions"><a class="pvcc-action" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><span class="dashicons dashicons-menu-alt3"></span><span><strong><?php esc_html_e( 'Menüler', 'piyasavizyon-v7' ); ?></strong><small><?php esc_html_e( 'Üst ve footer navigasyonu', 'piyasavizyon-v7' ); ?></small></span></a><a class="pvcc-action" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><span class="dashicons dashicons-format-image"></span><span><strong><?php esc_html_e( 'Logo / site kimliği', 'piyasavizyon-v7' ); ?></strong><small><?php esc_html_e( 'WordPress native tema ayarları', 'piyasavizyon-v7' ); ?></small></span></a></div></section>
    </div>
    <?php
}

function pv_control_center_render_system() {
    $runtime = pv_control_center_runtime_snapshot();
    ?>
    <div class="pvcc-grid pvcc-grid--2">
        <section class="pvcc-card"><div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Environment', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Sistem bilgileri', 'piyasavizyon-v7' ); ?></h2></div></div><div class="pvcc-system-list"><div><span><?php esc_html_e( 'Tema', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $runtime['theme_name'] . ' ' . $runtime['theme_version'] ); ?></strong></div><div><span><?php esc_html_e( 'PHP', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $runtime['php_version'] ); ?></strong></div><div><span><?php esc_html_e( 'WordPress', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $runtime['wp_version'] ); ?></strong></div><div><span><?php esc_html_e( 'Template', 'piyasavizyon-v7' ); ?></span><code><?php echo esc_html( $runtime['template'] ); ?></code></div><div><span><?php esc_html_e( 'Stylesheet', 'piyasavizyon-v7' ); ?></span><code><?php echo esc_html( $runtime['stylesheet'] ); ?></code></div><div><span><?php esc_html_e( 'Kredi rewrite', 'piyasavizyon-v7' ); ?></span><strong><?php echo esc_html( $runtime['rewrite'] !== '' ? $runtime['rewrite'] : '—' ); ?></strong></div></div></section>
        <section class="pvcc-card pvcc-card--dark"><div class="pvcc-card-head"><div><span class="pvcc-eyebrow"><?php esc_html_e( 'Architecture', 'piyasavizyon-v7' ); ?></span><h2><?php esc_html_e( 'Bağımsızlık kontrolü', 'piyasavizyon-v7' ); ?></h2></div><?php echo wp_kses_post( pv_control_center_status( $runtime['standalone'] ? 'ok' : 'danger' ) ); ?></div><div class="pvcc-standalone-mark"><span class="dashicons <?php echo $runtime['standalone'] ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span><div><strong><?php echo $runtime['standalone'] ? esc_html__( 'Standalone tema aktif', 'piyasavizyon-v7' ) : esc_html__( 'Parent tema ilişkisi algılandı', 'piyasavizyon-v7' ); ?></strong><p><?php echo $runtime['standalone'] ? esc_html__( 'Template ve stylesheet aynı temayı işaret ediyor. WordPress parent theme ilişkisi yok.', 'piyasavizyon-v7' ) : esc_html__( 'Template/stylesheet yapılandırmasını kontrol edin.', 'piyasavizyon-v7' ); ?></p></div></div><div class="pvcc-note pvcc-note--dark"><span class="dashicons dashicons-lock"></span><p><?php esc_html_e( 'Bu panel sistem seçeneklerini otomatik değiştirmez; operasyonel görünürlük sağlar.', 'piyasavizyon-v7' ); ?></p></div></section>
    </div>
    <?php
}

function pv_control_center_render() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $allowed = array( 'overview', 'markets', 'ads', 'security', 'content', 'system' );
    $tab     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
    if ( ! in_array( $tab, $allowed, true ) ) {
        $tab = 'overview';
    }
    ?>
    <div class="wrap pvcc-wrap">
        <?php pv_control_center_header( $tab ); ?>
        <main class="pvcc-main">
            <?php
            switch ( $tab ) {
                case 'markets':  pv_control_center_render_markets(); break;
                case 'ads':      pv_control_center_render_ads(); break;
                case 'security': pv_control_center_render_security(); break;
                case 'content':  pv_control_center_render_content(); break;
                case 'system':   pv_control_center_render_system(); break;
                default:         pv_control_center_render_overview(); break;
            }
            ?>
        </main>
    </div>
    <?php
}
