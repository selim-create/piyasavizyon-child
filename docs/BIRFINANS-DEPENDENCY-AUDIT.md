# Piyasa Vizyon – BirFinans Dependency Audit

Date: 2026-09-02
Baseline: `main` at `9b58f09d2e69c75d13007386ef5e5a8f30040664` (PR #26)

## Goal

Remove every runtime dependency on the legacy `birfinans` parent theme, make Piyasa Vizyon a standalone WordPress theme on PHP 7.4 first, then validate and switch production to PHP 8.3.

The parent `functions.php` is ionCube encoded with the PHP 7.2 encoder and cannot be the runtime foundation for PHP 8.3. The child still intentionally declares `Template: birfinans`; do not remove it until the dependency audit is clean.

## Already child-owned / verified after PR #26

The following market surfaces no longer require the old parent data/runtime path when rendered through the current child routes:

- currency payload: child BirTema provider
- gold payload: child BirTema provider
- parity payload: child BirTema provider
- crypto payload: CoinGecko provider
- BIST 100 header snapshot: child Mynet parser
- all indices list: child Mynet parser/view
- all stocks list: child Mynet parser/view
- stock detail: child Mynet parser/view
- live borsa: child Uzmanpara parser + WordPress AJAX
- main `/borsa/`: child Mynet BIST 100/50/30 parser/view
- rendered `/borsa/` and stock-detail Highcharts: official versioned CDN rather than parent asset

Legacy files such as `borsa-page.php`, `hisse-tablo.php`, `hisse-detay.php` and `endeksler-tablo.php` may still contain parent calls in source, but current `template_include` routing must be considered before classifying those references as live runtime dependencies.

## Remaining critical runtime clusters

### A. Market detail/network extraction

Confirmed live or potentially live parent dependencies still present in `main`:

- `endeks-detay.php`
  - `get_data_service()`
  - parent Highcharts asset
  - parent `/api/highcharts.php`
  - `$bp_options`
- `parite-detay.php`
  - `get_data_service()`
  - parent Highcharts asset
  - parent `/api/highcharts.php`
- `altin-detay.php`
  - `get_url_curl()`
  - parent Highcharts asset
  - parent `/api/highcharts.php`
- `doviz-detay.php`
  - `get_url_curl()`
  - parent Highcharts asset
  - parent `/api/highcharts.php`
  - parent `user_api.php` list/favorites mutations
- `coin-data.php`
  - `get_url_curl()` for legacy daily-chart transport
  - parent Highcharts asset
- `doviz-arsiv.php`
  - `get_url_curl()`
- `ekonomik-takvim.php`
  - direct `get_data_service()` Mynet proxy calls
- `parite-tablo.php`
  - direct parent `/api/parite-data.php` include
- `faiz-oranlari.php`
  - parent `/api/faiz-oranlari.php`
- `template-credit-helpers.php`
  - parent `/api/kredi.php`
  - parent `/img/banka/`

Priority remains: migrate one surface at a time, preserve URLs/data shapes, test production after each merge.

### B. Legacy market bootstrap fallback

`functions-legacy.php` still contains parent bootstrap code using:

- `get_template_directory() . '/api/DataCache.php'`
- `get_template_directory() . '/api/api_helper.php'`
- `DataCache`
- `get_data_service()`

`inc/market/provider.php` still deliberately retains `get_data_service()` as the final fallback for resources not yet migrated.

Do not remove these fallbacks until all remaining market surfaces are child-owned. Final Phase 2D target:

- parent DataCache include: none
- parent api_helper include: none
- `get_data_service()` fallback: none
- `get_url_curl()` runtime dependency: none

### C. `$bp_options` / `birpara`

Direct `$bp_options` reads remain widespread and are still a standalone blocker. Confirmed uses include:

- finance/detail/list page slugs
- `dovizHesaplaRewrite`
- credit page slugs
- cache time
- member/profile related slugs and legacy settings

Required approach:

1. introduce a child-owned compatibility/options layer;
2. read the existing `birpara` option payload while parent is active;
3. provide explicit safe defaults;
4. migrate only business-critical values into a child-owned option namespace;
5. move templates away from direct `$bp_options` reads incrementally.

Do not port the whole BirFinans admin framework.

### D. Auth/member

Registration is child-owned and hardened, but login/member functionality is not fully independent.

Confirmed remaining dependencies:

- `assets/js/pv-auth-security.js` still submits `ajaxlogin`;
- the login handler/template can still be parent-owned;
- `doviz-detay.php` still calls parent `user_api.php` for list mutations;
- favorites/likes/profile/list/alarm functionality must be verified for actual production use before porting.

Required approach:

- child-owned login template/handler;
- nonce + rate-limit/bruteforce protection;
- keep current secure registration intact;
- replace direct `user_api.php` calls with WordPress AJAX/REST;
- port only member features that are actually used.

### E. Parent-only template parts

Previously found parent-only parts include:

- `inc/widgets/live_chat`
- `inc/widgets/hisse-alt-news`

The expected legacy `bt_live_chat` table was not present in production. Classify each usage as active, dead, replaceable, or removable before standalone cutover.

### F. Editorial/admin compatibility

The front page still consumes legacy metadata such as:

- `bf_anasayfa_slider`
- `bf_anasayfa_kayan`

Existing stored values can remain, but parent removal may remove the editor UI that writes them. Verify the production editorial workflow and recreate only the controls that are still required, keeping the same meta keys initially.

Also audit parent-registered WordPress features used by the child:

- nav menus
- sidebars/widgets
- theme support
- image sizes
- rewrites/query vars
- cron hooks
- AJAX actions
- shortcodes
- CPT/taxonomy registrations
- body classes
- enqueued assets

### G. Standard template fallback closure

Child-owned core templates currently include:

- `header.php`
- `footer.php`
- `single.php`
- `page.php`
- `archive.php`
- `404.php`
- `search.php`
- `front-page.php`

Before removing the parent, test/add coverage for:

- category
- tag
- author
- date
- taxonomy
- attachment
- home/index
- comments/searchform
- special legacy templates

File-presence checks are not enough; route-level smoke tests are required.

## Current Phase 2C order

1. BIST 50 / BIST 30 `change_pct` parser fix.
2. Endeks detail extraction.
3. Parity detail extraction.
4. Gold detail extraction.
5. Currency detail extraction.
6. Coin detail daily-chart transport.
7. Currency archive.
8. Economic calendar.
9. Parity table.
10. Interest rates.
11. Credit helpers.
12. Parent flag/bank/icon assets.

The current branch `phase-2c/index-detail-parent-extraction` implements items 1–2 together because they use the same Mynet index-detail parser. It must not expand into parity/auth/options work.

## Final static search before standalone cutover

Classify every match for:

- `get_template_directory(`
- `get_template_directory_uri(`
- `bloginfo('template_directory')`
- `bloginfo("template_directory")`
- `/themes/birfinans`
- `birfinans/`
- `get_data_service(`
- `get_url_curl(`
- `DataCache`
- `api_helper`
- `user_api.php`
- `ajaxlogin`
- `get_template_part(`
- `bp_options`
- `birpara`

Each match must be one of:

- active runtime dependency
- child-safe WordPress API usage
- dead/legacy file not on a render path
- docs/comment only

Target: zero active runtime parent dependencies.

## Standalone/PHP sequence

Do not change the order:

1. finish runtime dependency removal;
2. remove `Template: birfinans`;
3. validate standalone theme on production PHP 7.4;
4. run PHP 8.3 CLI syntax/runtime/plugin compatibility checks while web PHP stays 7.4;
5. perform full route/admin/auth regression testing;
6. take fresh pre-cutover backups;
7. switch production PHP to 8.3 through Hostinger hPanel;
8. run immediate smoke tests and keep rollback ready;
9. only later consider archiving/deleting the inactive parent theme.

Production PHP must remain 7.4 until standalone completion.
