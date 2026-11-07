# Shared navigation & branding update

## Deployment

1. Back up the website files and database.
2. Deploy this branch's changed/new files. Preserve existing user uploads.
3. Ensure PHP can write to `uploads/branding/` (usually ownership matching the PHP user and directory permission `0755`; do not use `0777`).
4. Open **Admin → Settings** (`admin/settings.php`). The settings table is created automatically if missing.
5. Set the site name, tagline, logo, optional navigation logo, optional favicon, support email, WhatsApp/Telegram details and notice; then save.
6. Clear browser/CDN caches and check dashboard, service categories, profile and recharge on mobile.

### Optional SQL

Run `sql/upgrade_2026_11_branding.sql` in the site's existing database if you prefer to provision defaults using phpMyAdmin. It is repeatable and preserves existing values. **No `ALTER TABLE` or changes to the users table are required.** The earlier `sql/upgrade_2026_11.sql` remains separate and unchanged.

## Behaviour

- The same header, bottom navigation, auto-service popup, menu, notice and draggable support widget are shared by customer pages (including older pages that use `header.php` / `footer.php`). Login, registration and the public landing page also use the shared navigation.
- The mobile header stays in one row. Long names/balances are truncated rather than wrapping; the wallet remains clickable.
- Navigation logo and favicon fall back to the main logo if left empty.
- A profile without an uploaded photo uses the current main site logo. Existing custom profile photos are preserved.
- Support uses the direct WhatsApp helpline, with a separate community-group link, Telegram and email. Settings apply across the site on the next request.
- Logo uploads accept JPG, PNG, WebP and GIF, up to 2 MB and 4096×4096. Alternatively supply an HTTP(S) URL or an `assets/` / `uploads/` path. SVG/HTML/executable uploads are not accepted.
- Apache upload execution restrictions are included. For Nginx, ensure your server configuration never executes scripts from `uploads/`.
- Government document/card generation templates are intentionally not wrapped in site navigation; receipt navigation is hidden when printing.
- Admin retains its management layout, but site branding/logos come from the same settings.

## Architecture

- `includes/site_brand.php`: defaults, cached settings, URL safety and escaping.
- `includes/site_header.php`, `includes/site_footer.php`, `includes/site_icons.php`: shared UI.
- `assets/css/site_shell.css`, `assets/js/site_shell.js`: namespaced styling and dependency-free interactions (no Tailwind/jQuery dependency for navigation).
- Settings changes require an authenticated administrator and CSRF token. Uploads are validated before settings are saved.

## Checks

Run with PHP 7.4+ CLI:

```sh
php tests/site_shell_test.php defaults
php tests/site_shell_test.php custom
php tests/site_shell_test.php missing
node --check assets/js/site_shell.js
```

The fixtures check defaults, missing-table fallback, custom branding, logo inheritance, escaping/unsafe URLs, WhatsApp normalization, query caching, active navigation and duplicate-include protection. They are CLI-only and do not connect to the production database.

During this update, dashboard/services/profile were rendered with fixture database results and checked in Chromium at 320, 360, 390, 565, 768 and 1280 px, plus the shared shell fixture (24 page/viewport combinations). Menu, notices, auto-service popup, search, support toggling/dragging and print visibility were checked. The live database, real admin uploads, payments and hosting permissions still require deployment smoke testing.
