<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Piyasa Vizyon child theme bootstrap.
 *
 * The historical theme functions are kept in functions-legacy.php so security
 * and compatibility modules can be maintained as isolated, reviewable files.
 */
require_once __DIR__ . '/inc/market/bootstrap.php';
require_once __DIR__ . '/inc/credit-runtime.php';
require_once __DIR__ . '/inc/credit-rewrites.php';
require_once __DIR__ . '/inc/global-assets.php';
require_once __DIR__ . '/functions-legacy.php';
require_once __DIR__ . '/inc/registration-security.php';
require_once __DIR__ . '/inc/registration-security-hotfix.php';
