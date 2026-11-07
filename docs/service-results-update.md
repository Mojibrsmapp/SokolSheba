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

The old `order_service.php` and `order_file.php` links now redirect to the canonical `service_order.php` form (or service catalog when no selection exists), so they cannot bypass validation. Individual custom-service forms use the exact service name; category catalogs use the selected category. Dedicated handlers retain their existing owner/service-filtered latest-20 queries. Catalog/general order forms show the owner's orders. The aggregate `work_history.php` page offers paginated orders (20/page) and a separate latest-50 balance ledger. Older API-specific services that use separate history tables keep their existing histories/download workflows; they are not silently copied into `orders`.

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
# Generate the additional follow-up fixtures listed below before running the extended UI suite.
node tests/typography_browser_test.cjs
```

All new PHP fixtures are CLI-only and return 404 when requested over HTTP. The browser test serves rendered templates with mock data, not production credentials or a real database. It checks 13 templates at 320/390/768/1366px, result text and file visibility, a single history section, mobile recharge placement, no horizontal document overflow, sidebar keyboard focus/scroll and JavaScript errors. Font regression tests cover the shared customer styles and print/icon isolation.

Live MySQL, real account switching, an actual admin file upload, payment submission/refunds and government-issued certificates still require staging/production smoke testing. Tests here do not issue a registration or execute real payments.

## Deployment

Merge the feature branch first, then pull `main` using the existing deployment process. Check `git status` first; preserve local production changes (especially any local `receipt.php` changes) rather than resetting them. Refresh the browser cache after deployment. No new writable folders are needed beyond the existing result upload folders.


## Follow-up: admin-driven application fields and notice redesign

No SQL migration is needed. In **Admin → Services list → Edit service**, the editor now displays the effective fields, including defaults when the service has no saved configuration. Edit labels, type, required status, input hint and file accept list, or add custom fields. Saved nonempty JSON takes precedence over built-in defaults. Empty configuration uses defaults. Field keys survive label edits. Sparse row indices prevent unchecked/deleted rows from shifting required flags onto another field.

Business rules for first applications are applied after admin configuration:

- **New birth registration:** own name, father, mother, village/address line, thana/upazila, district and division are supplied when absent; defaults also include birth date and contact number. No already-issued BRN is requested. Existing field labels/optional flags can be edited; the core address/parent fields are restored if omitted entirely.
- **New NID application:** an already-issued NID is not requested. A birth-certificate PDF is mandatory even if an old saved configuration omitted it. Both the extension and PDF header are checked, maximum 5MB. An image renamed to `.pdf` is rejected. This is type validation, not malware scanning or government certificate verification.
- **New passport application:** asks for NID rather than an already-issued passport number. Renewal/correction/verification services are not classified as first applications.
- **Mobile banking information:** built-in info fields no longer contain payment TrxID; old TrxID info fields are omitted. The separate mobile-payment gateway still requires its own genuine payment reference as before. Wallet orders do not add a payment TrxID field to the service form.

The main form now renders required stars, native `required` attributes and accessible labels. Server-side validation runs before uploads/transactions; invalid/missing mandatory values never start a balance deduction. File accept restrictions are checked on the server as well as shown in the picker. Successful input uploads retain their relative paths for admin review; failed requests clean up files they saved.

**Scope of admin fields:** these controls govern services in `custom_services` that use the canonical order flow. Auto-service catalog cards show the same resolved fields. A configured link to a separate API-specific page retains that page's own form/processing; arbitrary extra fields cannot automatically be passed to an unrelated API. Clear the auto access link to use the configurable catalog form. No API-specific database/form migration is included in this patch.

The unrelated mock government/NID graphic has been removed from the shared order hero for every service. A compact document/wallet/passport icon replaces it, with the price and three feature items laid out in a responsive grid. A shared customer notice strip opens a redesigned full notice dialog; its text remains editable through the existing admin branding setting. Public login/register/index behavior and single-line header are preserved.

### Additional tests

```sh
php tests/service_form_test.php
php tests/service_order_validation_test.php
```

The first tests field defaults, admin JSON precedence, sparse checkbox indices, first-application contracts and PDF validation. The second executes the actual main controller's invalid POST paths with a no-write connection and verifies that missing text/PDF, fake PDF and missing admin-required fields do not start a transaction.

To run the extended browser suite, generate the original fixtures above, plus:

```sh
php tests/customer_ui_fixture.php service_order.php 'নতুন NID জন্য আবেদন' > "$SOKO_UI_FIXTURE_DIR/nid_application.html"
php tests/customer_ui_fixture.php service_order.php 'নতুন পাসপোর্ট আবেদন' > "$SOKO_UI_FIXTURE_DIR/passport_application.html"
php tests/customer_ui_fixture.php service_order.php 'উপায় ইনফো' > "$SOKO_UI_FIXTURE_DIR/mfs_info.html"
php tests/customer_ui_fixture.php service_order.php 'কাস্টম সেবা' '[{"title":"বিশেষ নোট","type":"textarea","required":0,"placeholder":"এখানে লিখুন"}]' > "$SOKO_UI_FIXTURE_DIR/admin_custom.html"
php tests/customer_ui_fixture.php admin/add_service.php > "$SOKO_UI_FIXTURE_DIR/admin_add.html"
php tests/customer_ui_fixture.php admin/edit_service.php > "$SOKO_UI_FIXTURE_DIR/admin_edit.html"
# Include ./admin/*.php when compiling Tailwind for these admin fixtures.
PHP_BIN=php node tests/customer_ui_browser_test.cjs
```

The extended suite covers 17 route/variant previews at four widths (68 cases), plus six real admin-editor DOM → submitted data → PHP field parser → customer resolver round trips. Compatibility URLs preview the canonical target rather than rendering their former duplicate templates. Staging still needs a real admin save/read-back against MySQL and an actual multipart upload/payment smoke test.
