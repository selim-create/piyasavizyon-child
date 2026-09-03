<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Piyasa Vizyon standalone theme bootstrap.
 *
 * Historical theme helpers remain in functions-legacy.php while runtime
 * compatibility layers are kept in isolated, reviewable modules.
 */
require_once __DIR__ . '/inc/market/bootstrap.php';
require_once __DIR__ . '/inc/credit-runtime.php';
require_once __DIR__ . '/inc/credit-rewrites.php';
require_once __DIR__ . '/inc/global-assets.php';
require_once __DIR__ . '/inc/gam-runtime.php';
require_once __DIR__ . '/inc/member-runtime.php';
require_once __DIR__ . '/inc/member-avatar.php';
require_once __DIR__ . '/inc/member-market-runtime.php';
require_once __DIR__ . '/inc/author-runtime.php';
require_once __DIR__ . '/inc/legacy-widget-compat.php';
require_once __DIR__ . '/inc/editorial-runtime.php';
require_once __DIR__ . '/functions-legacy.php';
require_once __DIR__ . '/inc/registration-security.php';
require_once __DIR__ . '/inc/admin-control-center.php';
