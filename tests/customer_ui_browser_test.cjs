/* Offline template/layout regression test. See docs/service-results-update.md.
 * SOKO_UI_FIXTURE_DIR=/path/to/rendered/html NODE_PATH=/path/to/node_modules \
 * CHROMIUM_PATH=/path/to/chromium node tests/customer_ui_browser_test.cjs
 */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { chromium } = require('playwright');
const root = path.resolve(__dirname, '..');
const fixtureDir = process.env.SOKO_UI_FIXTURE_DIR;
const pages = ['service_order', 'recharge', 'services', 'auto_tin', 'numbertobio',
    'mfs_service', 'location_service', 'imei_service', 'call_sms_list',
    'order_file', 'order_text', 'order_service', 'auto_service', 'nid_application', 'passport_application', 'mfs_info', 'admin_custom'];
const contentTypes = { '.css': 'text/css', '.js': 'application/javascript',
    '.html': 'text/html', '.png': 'image/png', '.jpg': 'image/jpeg',
    '.ttf': 'font/ttf', '.woff2': 'font/woff2', '.woff': 'font/woff', '.svg': 'image/svg+xml' };

async function main() {
    assert(fixtureDir, 'Set SOKO_UI_FIXTURE_DIR to rendered HTML and compiled tailwind.css');
    const server = http.createServer((request, response) => {
        const name = new URL(request.url, 'http://localhost').pathname;
        const fixture = [...pages.map(page => `/${page}.html`), '/admin_add.html', '/admin_edit.html', '/tailwind.css'].includes(name);
        const file = path.join(fixture ? fixtureDir : root, name);
        if (!fs.existsSync(file) || fs.statSync(file).isDirectory()) {
            response.writeHead(404); response.end(); return;
        }
        response.setHeader('Content-Type', contentTypes[path.extname(file)] || 'application/octet-stream');
        response.end(fs.readFileSync(file));
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    let browser;
    try {
        browser = await chromium.launch({ headless: true,
            ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}),
            args: ['--no-sandbox', '--no-zygote', '--disable-dev-shm-usage'] });
        const page = await browser.newPage();
        // Test offline: actual local font/CSS/JS, compiled Tailwind, local jQuery.
        await page.route('https://**/*', async route => {
            const url = route.request().url();
            if (url.includes('cdn.tailwindcss.com')) return route.fulfill({
                contentType: 'application/javascript',
                body: "const s=document.createElement('link');s.rel='stylesheet';s.href='/tailwind.css';document.head.appendChild(s);",
            });
            if (url.includes('jquery')) return route.fulfill({
                contentType: 'application/javascript', body: fs.readFileSync(require.resolve('jquery/dist/jquery.min.js'), 'utf8'),
            });
            return route.fulfill({ body: '', contentType: route.request().resourceType() === 'stylesheet' ? 'text/css' : 'application/javascript' });
        });
        let count = 0;
        for (const name of pages) for (const width of [320, 390, 768, 1366]) {
            const errors = [];
            const onError = error => errors.push(error.message);
            page.on('pageerror', onError);
            await page.setViewportSize({ width, height: 900 });
            await page.goto(`http://127.0.0.1:${server.address().port}/${name}.html`, { waitUntil: 'networkidle' });
            await page.evaluate(() => document.fonts.ready);
            const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
            assert(scrollWidth <= width + 1, `${name}@${width}: horizontal overflow (${scrollWidth})`);
            const header = await page.locator('.ss-header-inner').boundingBox();
            assert(header.height <= (width <= 390 ? 70 : 90), `${name}: header wrapped`);
            if (name !== 'recharge') {
                assert.equal(await page.locator('.ss-order-result a[href*="order_result.php"]').count(), 1, `${name}: missing result file`);
                assert.match(await page.locator('.ss-history-grid').textContent(), /12345678901234567/, `${name}: missing result text`);
                assert.equal(await page.locator('#service-history').count(), 1, `${name}: duplicate history`);
            } else {
                const main = await page.locator('.ss-recharge-content').boundingBox();
                assert(main.y >= header.height - 2 && main.width <= width, 'Recharge displaced by body flex');
                const columns = await page.locator('.ss-recharge-grid').evaluate(element => getComputedStyle(element).gridTemplateColumns.split(' ').length);
                assert.equal(columns, width >= 1024 ? 2 : 1, 'Recharge responsive columns');
            }
            if (name === 'service_order') {
                assert.equal(await page.locator('#f_brn').count(), 0, 'New birth asks for existing BRN');
                assert.equal(await page.locator('.nid-mock').count(), 0, 'Never display the unrelated mock NID');
                assert(await page.locator('#f_father_name').getAttribute('required') !== null, 'Father name is required');
                assert.equal(await page.locator('.hero-feats .feat').count(), 3);
            }
            if (name === 'nid_application') {
                assert.equal(await page.locator('#f_birth_pdf[required]').count(), 1);
                assert.match(await page.locator('#f_birth_pdf').getAttribute('accept'), /pdf/);
                assert.equal(await page.locator('#f_nid').count(), 0);
                await page.locator('#f_name').fill('পরীক্ষার আবেদনকারী');
                await page.locator('#f_dob').fill('2000-01-01');
                await page.locator('#f_mobile').fill('01712345678');
                await page.locator('#f_address').fill('গ্রাম, থানা, জেলা');
                assert.equal(await page.locator('#orderForm').evaluate(form => form.reportValidity()), false, 'Missing PDF fails native validation');
                assert.equal(await page.evaluate(() => document.activeElement.id), 'f_birth_pdf', 'Required file is focusable');
                assert.equal(await page.locator('#orderBtn').isDisabled(), false, 'Invalid submission must not lock button');
                await page.locator('#f_birth_pdf').setInputFiles({name:'birth.pdf',mimeType:'application/pdf',buffer:Buffer.from('%PDF-1.4 browser fixture')});
                assert.equal(await page.locator('#orderForm').evaluate(form => form.checkValidity()), true, 'Complete required form is valid');
            }
            if (name === 'passport_application') {
                assert.equal(await page.locator('#f_nid[required]').count(), 1);
                assert.equal(await page.locator('#f_passport').count(), 0);
            }
            if (name === 'mfs_info') assert.equal(await page.locator('#f_trx').count(), 0);
            if (name === 'admin_custom') {
                assert.equal(await page.locator('#f_custom_0').getAttribute('placeholder'), 'এখানে লিখুন');
                assert.equal(await page.locator('#f_custom_0').getAttribute('required'), null);
            }
            if (['service_order','nid_application','passport_application','mfs_info','admin_custom'].includes(name)) {
                for (const input of await page.locator('#orderForm [aria-required="true"]').all()) {
                    const id = await input.getAttribute('id');
                    assert.match(await page.locator('label.flabel[for="'+id+'"]').textContent(), /\*/);
                }
                assert.equal(await page.locator('.nid-mock').count(), 0);
            }
            const announcement = page.locator('.ss-announcement-inner');
            await announcement.click();
            assert(await page.locator('#ss-notice').isVisible(), 'Notice strip opens full notice');
            await page.locator('.ss-notice-dismiss').click();
            assert(!await page.locator('#ss-notice').isVisible(), 'Notice closes');
            assert(await announcement.evaluate(element => element === document.activeElement), 'Notice focus restored');
            const openMenu = page.locator('[data-ss-open="ss-menu"]');
            await openMenu.click();
            assert(await page.locator('#ss-menu').isVisible(), 'Drawer did not open');
            await page.keyboard.press('Shift+Tab');
            assert.equal(await page.evaluate(() => document.activeElement.getAttribute('href')), '/logout.php', 'Drawer focus trap');
            await page.keyboard.press('Escape');
            assert(!await page.locator('#ss-menu').isVisible(), 'Drawer did not close');
            assert(await openMenu.evaluate(element => element === document.activeElement), 'Restore menu button focus');
            await openMenu.click();
            await page.locator('#ss-menu a[href="/logout.php"]').focus();
            assert(await page.evaluate(() => document.activeElement.getBoundingClientRect().bottom) <= 900, 'Drawer cannot scroll to last link');
            await page.keyboard.press('Escape');
            if (process.env.SOKO_UI_SCREENSHOTS_DIR) {
                fs.mkdirSync(process.env.SOKO_UI_SCREENSHOTS_DIR, { recursive: true });
                await page.screenshot({ path: path.join(process.env.SOKO_UI_SCREENSHOTS_DIR, `${name}-${width}.png`), fullPage: true });
            }
            assert.deepEqual(errors, [], `${name}: browser JavaScript errors`);
            page.off('pageerror', onError);
            count++;
        }
        for (const admin of ['admin_add','admin_edit']) for (const width of [320,390,1366]) {
            await page.setViewportSize({width,height:900});
            await page.goto(`http://127.0.0.1:${server.address().port}/${admin}.html`, {waitUntil:'networkidle'});
            if (admin === 'admin_add') {
                await page.locator('input[name="service_name"]').fill('পরীক্ষার কাস্টম সেবা');
                await page.evaluate(() => { addFormField(); addFormField(); addFormField(); });
                await page.locator('input[name="field_title[1]"]').fill('মুছব');
                await page.locator('input[name="field_title[2]"]').fill('ঐচ্ছিক নোট');
                await page.locator('select[name="field_type[2]"]').selectOption('textarea');
                await page.locator('input[name="field_required[2]"]').uncheck();
                await page.locator('input[name="field_title[3]"]').fill('প্রমাণপত্র');
                await page.locator('select[name="field_type[3]"]').selectOption('file');
                await page.locator('input[name="field_accept[3]"]').fill('.pdf');
                await page.evaluate(() => { removeFormField('fieldRow_1'); addFormField(); });
                await page.locator('input[name="field_title[4]"]').fill('যোগাযোগ');
            } else {
                await page.locator('input[name="field_title[0]"]').fill('পরিবর্তিত নিজের নাম');
                await page.locator('input[name="field_placeholder[0]"]').fill('সরকারি বানান লিখুন');
                await page.locator('input[name="field_required[1]"]').uncheck();
                await page.evaluate(() => { addFormField(); addFormField(); removeFormField('fieldRow_'+(fieldCount-1)); });
                const last = page.locator('.dynamic-field-row').last();
                await last.locator('input[name^="field_title"]').fill('অতিরিক্ত নোট');
                await last.locator('input[type="checkbox"]').uncheck();
            }
            assert(await page.evaluate(() => document.documentElement.scrollWidth) <= width+1, `${admin}@${width}: editor overflow`);
            const payload = await page.evaluate(() => {
                const result={};
                for(const [key,value] of new FormData(document.querySelector('form'))) {
                    const match=key.match(/^(field_[a-z]+)\[(\d+)\]$/);
                    if(match) { result[match[1]] ||= {}; result[match[1]][match[2]]=value; }
                    else if(key==='service_name') result[key]=value;
                }
                return result;
            });
            const output=execFileSync(process.env.PHP_BIN || 'php', [path.join(root,'tests/admin_fields_roundtrip.php'),JSON.stringify(payload)], {cwd:root,encoding:'utf8'});
            const fields=JSON.parse(output);
            if(admin==='admin_add') {
                assert.deepEqual(fields.map(f=>f.required), [0,1,1]);
                assert.equal(fields[0].type,'textarea'); assert.equal(fields[1].accept,'.pdf');
            } else {
                assert.equal(fields.find(f=>f.key==='name').title,'পরিবর্তিত নিজের নাম');
                assert.equal(fields.find(f=>f.key==='name').placeholder,'সরকারি বানান লিখুন');
                assert.equal(fields.find(f=>f.key==='father_name').required,0);
                assert.equal(fields.find(f=>f.title==='অতিরিক্ত নোট').required,0);
            }
        }
        console.log(`PASS: ${pages.length} page/variant previews, ${count} viewport cases; layout, fields, notices, histories and drawer; 6 admin editor/roundtrip cases`);
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
}
main().catch(error => { console.error(error); process.exitCode = 1; });
