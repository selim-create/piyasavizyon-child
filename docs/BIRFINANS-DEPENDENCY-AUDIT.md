# Piyasa Vizyon – BirFinans Dependency Audit

Date: 2026-09-01

## Goal

Remove the runtime dependency on the legacy `birfinans` parent theme without breaking production behavior, then make Piyasa Vizyon a standalone theme that can run on PHP 8.3.

## Current blocker

The active parent theme `wp-content/themes/birfinans/functions.php` is ionCube encoded with the PHP 7.2 encoder. CLI tests confirm that the current file cannot run on PHP 8.1, 8.2 or 8.3. PHP 8.0 on the host does not have the ionCube Loader enabled. Therefore the current parent theme is the hard blocker for any PHP 8.x migration.

The child theme still declares:

```css
Template: birfinans
```

Removing that line now would break multiple runtime paths. The migration must be phased.

## Executive summary

The child theme is already substantially independent in its presentation layer: it has its own `header.php`, `footer.php`, `single.php`, `page.php`, `archive.php`, front page implementation, CSS/JS bundles, advertising slots, corporate pages and many finance templates.

The remaining high-risk dependency clusters are:

1. Market-data bootstrap and legacy helper functions.
2. Parent API files and direct parent asset URLs.
3. `$bp_options` values produced by the BirFinans admin framework.
4. Login/register/profile/member flows.
5. Parent-only template parts and legacy live-chat/favorites functions.
6. Parent metabox/admin functionality that still writes legacy `bf_*` metadata.
7. Rewrite/page-slug assumptions inherited from BirFinans.

## Dependency cluster A – market data and network helpers

### Critical runtime dependency

`functions-legacy.php` loads the following from the parent directory using `get_template_directory()`:

- `api/DataCache.php`
- `api/api_helper.php`

These provide or support:

- `DataCache`
- `get_data_service()`
- `get_url_curl()`
- legacy remote market-data calls

The child market bootstrap then uses those helpers for:

- currency data
- gold data
- parity data
- crypto data
- BIST / stock-market data

### Child templates depending on these helpers

Known direct consumers include:

- `ekonomik-takvim.php`
- `hisse-tablo.php`
- `hisse-detay.php`
- `endeksler-tablo.php`
- `endeks-detay.php`
- `parite-detay.php`
- `borsa-page.php`
- `canli-borsa.php`
- `doviz-arsiv.php`
- `doviz-detay.php`
- `altin-detay.php`
- `coin-data.php`

### Migration action

Create a new child-owned data layer under `inc/market/` and migrate callers behind Piyasa Vizyon namespaced helpers. Do not keep BirFinans licensing/proxy behavior as an architectural dependency.

Suggested target:

- `inc/market/cache.php`
- `inc/market/http.php`
- `inc/market/providers.php`
- `inc/market/normalize.php`
- `inc/market/bootstrap.php`

The first migration should preserve existing return shapes so templates can be moved without a simultaneous frontend rewrite.

## Dependency cluster B – direct parent API and asset paths

The child still points directly to parent files via `get_template_directory()` / `get_template_directory_uri()` or `bloginfo('template_directory')`.

Confirmed examples:

- `faiz-oranlari.php` → `/api/faiz-oranlari.php`
- `parite-tablo.php` → `/api/parite-data.php`
- `canli-borsa.php` → `/api/canli_borsa.php`
- `template-credit-helpers.php` → `/api/kredi.php`
- `doviz-detay.php` / `altin-detay.php` / `hisse-detay.php` / `endeks-detay.php` / `parite-detay.php` / `borsa-page.php` → `/api/highcharts.php`
- several market templates → `/js/highcharts.js`
- credit templates → `/img/banka/`
- currency templates → `/img/flag/`

### Important finding

The parent `api/highcharts.php` currently only returns `OK`, while several child templates still call it. That call path should be treated as legacy/dead integration until production behavior is verified.

### Migration action

Move only actually-used assets/data endpoints into the child. Replace parent URI helpers with child-owned helpers. Prefer WordPress AJAX/REST endpoints for dynamic data rather than directly executing PHP files inside a theme directory.

## Dependency cluster C – `$bp_options`

A large part of the legacy finance templates still read global `$bp_options` values supplied by the BirFinans admin framework.

Known categories include:

- page slugs for currency, gold, parity, stocks, indices and crypto
- credit page slugs
- rewrite text such as `dovizHesaplaRewrite`
- member/profile page slugs
- live-chat toggle
- cache time
- legacy visual/login settings

### Migration strategy

Do not replace every `$bp_options` read manually in one PR.

Create a compatibility layer first:

```php
function pv_legacy_options() : array
```

This layer should:

1. Read the existing saved BirFinans option payload while the parent is active.
2. Supply explicit Piyasa Vizyon defaults for required keys.
3. Expose small typed helpers such as `pv_market_page_slug()` and `pv_credit_page_slug()`.
4. Allow templates to migrate away from direct global access gradually.

Before the final standalone switch, migrate the required persisted values to a child-owned option namespace.

## Dependency cluster D – login, registration and member system

This is high priority because public registration is an active Piyasa Vizyon feature.

### Current parent responsibilities

The parent currently provides:

- `login.php` template
- `ajaxlogin` handler
- historical `ajaxregister` handler
- login/register JS registration
- member profile templates
- `user_api.php`
- favorites/likes routines
- member lists and alarms

The child now securely overrides registration with:

- rate limits
- honeypot
- Turnstile
- explicit Subscriber role
- hardened AJAX registration

However the login form/template itself remains parent-owned, and the child authentication JavaScript still sends `ajaxlogin`, which is handled by the parent.

### Migration action

Move this cluster before the final parent removal:

1. Create child-owned login/register template.
2. Add child-owned secure `ajaxlogin` handler.
3. Preserve the existing secure registration handler.
4. Replace `user_api.php` direct theme endpoint calls with authenticated WordPress AJAX/REST actions using nonces and capability/current-user checks.
5. Rebuild only the member functions still required by the current product.

### Security note

The old `user_api.php` is not suitable as the long-term architecture because it directly includes `wp-config.php`, switches behavior from a query-string `type`, and handles profile/password/list/alarm mutations outside normal WordPress AJAX/REST routing.

## Dependency cluster E – parent template parts

Some child market detail pages request parent template parts that do not exist in the child repository.

Confirmed examples:

- `inc/widgets/live_chat`
- `inc/widgets/hisse-alt-news`

The live-chat template depends on the legacy `bt_live_chat` table. Production WP-CLI already showed that this table does not exist on the current database.

### Migration action

Inventory each parent template-part call and classify it:

- still visible and required
- already effectively dead
- replace with child component
- remove

Live chat should not be migrated automatically until product need is confirmed.

## Dependency cluster F – legacy post metadata/admin controls

The current child front page still uses legacy metadata such as:

- `bf_anasayfa_slider`
- `bf_anasayfa_kayan`

The parent theme contains the metabox definitions that create/manage these values in the WordPress editor.

### Migration action

Before standalone mode, reproduce only the metadata controls actually used by the current Piyasa Vizyon editorial workflow. Keep the same meta keys initially so existing content keeps working without a data migration.

## Dependency cluster G – parent framework and saved settings

The parent contains a Codestar-based admin framework using the `birpara` option namespace and many settings that the old templates reference.

The current child presentation no longer needs the entire framework. The migration must determine which saved values are still business-critical and copy only those into child-owned settings.

Do not port the whole BirFinans admin framework.

## Dependency cluster H – standard template coverage

Good news: several core WordPress templates are already child-owned, including:

- `header.php`
- `footer.php`
- `single.php`
- `page.php`
- `archive.php`
- `404.php`
- `search.php`
- `front-page.php`

This substantially reduces the final standalone risk.

However parent fallback behavior must still be checked for templates not present in the child, especially:

- category
- tag
- author
- date/taxonomy variants
- attachment or special legacy templates

Where a child file is absent today, WordPress may currently fall back to the parent theme.

## Proposed migration phases

### Phase 1 – audit and compatibility foundation

No production behavior change.

Deliverables:

- dependency inventory
- `$bp_options` compatibility helper
- parent-path helper inventory
- runtime smoke-test checklist

### Phase 2 – market data extraction

Move:

- cache
- HTTP helper
- market provider layer
- currency/gold/parity/crypto/BIST bootstrap
- credit data access
- required bank/flag/chart assets

Then replace direct `get_template_directory*()` market references.

### Phase 3 – member/auth extraction

Move:

- login template
- AJAX login
- registration UI
- profile/account mutations
- required list/alarm/favorite functionality

Keep Turnstile/rate-limit protection intact.

### Phase 4 – editorial/admin compatibility

Recreate only required:

- homepage meta switches
- page/rewrite settings still in use
- any required widget/sidebar registration

Migrate required settings out of `birpara`.

### Phase 5 – template fallback closure

Add child-owned equivalents for all WordPress templates that currently fall through to the parent.

Run route-by-route visual and functional regression checks.

### Phase 6 – standalone cutover

Only after dependency search is clean:

1. Remove `Template: birfinans` from `style.css`.
2. Verify the theme activates standalone on PHP 7.4 first.
3. Run CLI and staging smoke tests on PHP 8.3.
4. Fix PHP 8.3 deprecations/fatals in child code.
5. Switch production PHP only after staging parity.

## Required validation before standalone cutover

The following searches should return no runtime parent dependency:

- `get_template_directory(` for parent-only files
- `get_template_directory_uri(` for parent-only assets
- `bloginfo('template_directory')`
- `bloginfo("template_directory")`
- direct references to `birfinans`
- unresolved BirFinans-only helper function calls
- unresolved parent-only template parts

`Template: birfinans` is removed only after those checks pass.

## First implementation target

The safest first code phase is the **market data extraction**, because it is the largest shared dependency and can be migrated while the parent remains active as a fallback.

Recommended first implementation PR after this audit:

**“Introduce child-owned market compatibility layer”**

Scope:

- add child-owned cache/HTTP/provider bootstrap
- keep old data shapes
- add fallback to existing parent provider while production parity is measured
- migrate `functions-legacy.php` away from direct parent `api/DataCache.php` and `api/api_helper.php`
- do not change templates or PHP version yet

This establishes the seam needed for every later migration without requiring a risky all-at-once cutover.
