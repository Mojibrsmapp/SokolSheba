# Service history, issued results and responsive customer UI

## No database migration

Uses existing `orders`, `users`, `custom_services` and `history_work` columns. **No SQL update is required.** Does not change payment deduction, government submission or approval APIs.

## Birth-registration workflow

- Services explicitly named **new birth-registration applications** now use application fields without asking for an already-issued BRN. Existing-registration verification/correction services retain their existing-number fields.
- Main and legacy custom order forms explain that the order ID is **not** the government-issued birth-registration number.
- The website does **not** generate a government BRN. After the actual result is issued, the administrator edits the order in `admin/orders.php` or `admin/all_orders_history.php`:
  1. Enter the issued number/application reference and instructions in **Result text**.
  2. Attach the actual certificate/result file, if available (PDF, JPG, JPEG, PNG, WebP, ZIP or TXT).
  3. Set the appropriate order status and save.
- The customer sees **both text and file**, not one or the other, under the service page and in the receipt. Pending, cancelled and completed-without-result states have explicit guidance.

## History coverage

The shared footer adds history to the 12 customer pages backed by `orders`: `service_order`, `order_service`, `order_file`, `order_text`, `services`, `auto_service`, `auto_tin`, `numbertobio`, `mfs_service`, `location_service`, `imei_service` and `call_sms_list`.

Individual custom-service forms use the exact service name; category catalogs use the selected category. Dedicated handlers retain their existing owner/service-filtered latest-20 queries. Catalog/general order forms show the owner's orders. The aggregate `work_history.php` page offers paginated orders (20/page) and a separate latest-50 balance ledger. Older API-specific services that use separate history tables keep their existing histories/download workflows; they are not silently copied into `orders`.

## Downloads and compatibility

- `/order_result.php?order=ID` binds both the order ID and the session owner's token, checks the matching user/order relationship, and returns a private/no-store attachment response. Guests redirect to login; other users, nonexistent files and input attachments receive 404.
- Accepts only supported files within `uploads/results` or `admin/uploads/results`, checks real paths and rejects traversal, external URLs and escaping symlinks. A customer's `uploads/orders` input must never appear as an issued result.
- Both historical upload roots are supported. New uploads through `admin/orders.php` now store an explicit `admin/uploads/results/...` path. If an old ambiguous path resolves to different files in both roots, downloading fails closed; reattach the correct file through the admin editor instead of guessing ownership.
- Existing directly accessible upload URLs/storage and admin authentication/upload policy are unchanged; the new ownership check applies to the new download route, not a claim that every legacy static upload is private. A separate private-storage migration would be needed to remove direct URL access.

## UI

- Replaced the recharge page's body-level horizontal flex with a scoped responsive layout: one column on mobile/tablet, two on desktop. Preserves payment selection, form and recharge history.
- Removed absolute mobile price/title overlap on the service order hero. New birth applications no longer show the unrelated sample NID graphic.
- Filled wallet icon remains visible at small widths. Shared one-line header, public-page bottom-navigation exceptions and local front-page font remain intact.
- Sidebar now has a branded sticky header, grouped icon links, active states and a scrollable body; keeps all previous destinations plus birth-service and auto-service shortcuts. Retains existing dialog, Escape, focus-trap and focus-restoration hooks.

## Validation

Run PHP fixtures with native PHP, or `@php-wasm/cli` when native PHP is unavailable. For WASM lint, check for `No syntax errors detected` in output; do not trust its exit status alone.

```sh
php tests/customer_history_test.php
for case in owner other guest missing input; do php tests/order_result_test.php "$case"; done
for mode in defaults custom missing; do
  for page in profile.php login.php register_user.php index.php; do
    php tests/site_shell_test.php "$mode" "$page"
  done
done
```

Offline browser checks require Playwright, jQuery 3, Tailwind CSS 3 and a Chromium executable. Keep dependencies, fixture HTML, compiled CSS and screenshots outside source control (for example `.cache/soko-tests`). Example after installing these packages into a test-tool directory:

```sh
export TOOLS=/path/to/test-tools
export SOKO_UI_FIXTURE_DIR="$TOOLS/preview"
export NODE_PATH="$TOOLS/node_modules"
export CHROMIUM_PATH=/path/to/chromium
mkdir -p "$SOKO_UI_FIXTURE_DIR"
for page in service_order recharge services auto_tin numbertobio mfs_service location_service imei_service call_sms_list order_file order_text order_service auto_service; do
  php tests/customer_ui_fixture.php "$page.php" > "$SOKO_UI_FIXTURE_DIR/$page.html"
done
printf '@tailwind base;\n@tailwind components;\n@tailwind utilities;\n' > "$TOOLS/input.css"
"$TOOLS/node_modules/.bin/tailwindcss" --content './*.php' -i "$TOOLS/input.css" -o "$SOKO_UI_FIXTURE_DIR/tailwind.css" --minify
node tests/customer_ui_browser_test.cjs
node tests/typography_browser_test.cjs
```

All new PHP fixtures are CLI-only and return 404 when requested over HTTP. The browser test serves rendered templates with mock data, not production credentials or a real database. It checks 13 templates at 320/390/768/1366px, result text and file visibility, a single history section, mobile recharge placement, no horizontal document overflow, sidebar keyboard focus/scroll and JavaScript errors. Font regression tests cover the shared customer styles and print/icon isolation.

Live MySQL, real account switching, an actual admin file upload, payment submission/refunds and government-issued certificates still require staging/production smoke testing. Tests here do not issue a registration or execute real payments.

## Deployment

Merge the feature branch first, then pull `main` using the existing deployment process. Check `git status` first; preserve local production changes (especially any local `receipt.php` changes) rather than resetting them. Refresh the browser cache after deployment. No new writable folders are needed beyond the existing result upload folders.
