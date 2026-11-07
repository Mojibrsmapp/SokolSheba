/* Offline template/layout regression test. See docs/service-results-update.md.
 * SOKO_UI_FIXTURE_DIR=/path/to/rendered/html NODE_PATH=/path/to/node_modules \
 * CHROMIUM_PATH=/path/to/chromium node tests/customer_ui_browser_test.cjs
 */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { chromium } = require('playwright');
const root = path.resolve(__dirname, '..');
const fixtureDir = process.env.SOKO_UI_FIXTURE_DIR;
const pages = ['service_order', 'recharge', 'services', 'auto_tin', 'numbertobio',
    'mfs_service', 'location_service', 'imei_service', 'call_sms_list',
    'order_file', 'order_text', 'order_service', 'auto_service'];
const contentTypes = { '.css': 'text/css', '.js': 'application/javascript',
    '.html': 'text/html', '.png': 'image/png', '.jpg': 'image/jpeg',
    '.ttf': 'font/ttf', '.woff2': 'font/woff2', '.woff': 'font/woff', '.svg': 'image/svg+xml' };

async function main() {
    assert(fixtureDir, 'Set SOKO_UI_FIXTURE_DIR to rendered HTML and compiled tailwind.css');
    const server = http.createServer((request, response) => {
        const name = new URL(request.url, 'http://localhost').pathname;
        const fixture = [...pages.map(page => `/${page}.html`), '/tailwind.css'].includes(name);
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
                assert.equal(await page.locator('.hero-media').isVisible(), false, 'New birth must not display a fake NID');
            }
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
        console.log(`PASS: ${pages.length} actual templates, ${count} viewport cases; layout, histories, drawer keyboard/focus/scroll`);
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
}
main().catch(error => { console.error(error); process.exitCode = 1; });
