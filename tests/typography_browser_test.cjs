/* Requires Playwright. No database, external CDN or application credentials.
 * NODE_PATH=/path/to/node_modules node tests/typography_browser_test.cjs
 * Optionally set CHROMIUM_PATH to an existing Chromium executable. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { chromium } = require('playwright');
const root = path.resolve(__dirname, '..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const styles = source => [...source.matchAll(/<style\b[^>]*>([\s\S]*?)<\/style>/g)].map(match => match[1]).join('\n');
const hasCustomerLayout = source => source.includes('includes/site_header.php') || /include(?:_once)?\s*\(?\s*['"]header\.php['"]/.test(source);
const pages = fs.readdirSync(root).filter(file => file.endsWith('.php') && hasCustomerLayout(read(file)));
const icons = [
    ['fas fa-wallet', 'Font Awesome 6 Free'], ['fa-brands fa-whatsapp', 'Font Awesome 6 Brands'],
    ['mdi mdi-home', 'Material Design Icons'], ['ti-home', 'themify'],
    ['glyphicon glyphicon-user', 'Glyphicons Halflings'], ['icon-heart', 'feather'],
    ['material-icons', 'Material Icons'], ['material-symbols-rounded', 'Material Symbols Rounded'],
];
const textSelectors = ['body', 'h1', 'p', 'strong', '.text-copy', 'label', 'input', 'textarea', 'select', 'option', 'button', 'th', 'td', 'code'];
const sample = '<h1>সকল সেবা English 123</h1><p>একই বাংলা ফন্ট <strong>সকল পৃষ্ঠায়</strong></p><span class="text-copy">বাংলা টেক্সট</span>' +
    '<label>আপনার নাম<input class="form-control smooth-input" placeholder="নাম লিখুন"></label>' +
    '<textarea class="form-control">বাংলা লেখা</textarea><select class="form-select"><option>সেবা নির্বাচন</option></select>' +
    '<button class="btn btn-primary">অর্ডার করুন</button><table><thead><tr><th>সেবা</th></tr></thead><tbody><tr><td>ব্যালেন্স</td></tr></tbody></table>' +
    '<code>ABC 123</code>' + icons.map(([classes], index) => `<span id="icon-${index}" class="${classes}">x</span>`).join('');
async function main() {
    const server = http.createServer((request, response) => {
        const url = new URL(request.url, 'http://localhost');
        if (!['/fonts/Bangla.ttf', '/assets/css/site_typography.css', '/assets/css/site_shell.css'].includes(url.pathname)) {
            response.writeHead(404); response.end(); return;
        }
        response.setHeader('Content-Type', url.pathname.endsWith('.css') ? 'text/css' : 'font/ttf');
        response.end(fs.readFileSync(path.join(root, url.pathname)));
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    let browser;
    try {
        browser = await chromium.launch({
            headless: true,
            ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}),
            args: ['--no-sandbox', '--disable-dev-shm-usage', '--no-zygote'],
        });
        const base = `http://127.0.0.1:${server.address().port}`;
        let count = 0;
        for (const file of pages) {
            const source = read(file);
            const legacy = file !== 'header.php' && !source.includes('includes/site_header.php');
            const page = await browser.newPage();
            await page.goto(base);
            const iconCss = icons.map(([classes, family]) => `[class="${classes}"]{font-family:"${family}"!important}`).join('\n');
            await page.setContent(`<html><head><style>${legacy ? styles(read('header.php')) : ''}\n${styles(source)}\n${iconCss}
code{font-size:13px}</style></head><body>${sample}</body></html>`);
            const typography = await page.addStyleTag({ url: `${base}/assets/css/site_typography.css` });
            for (const width of [320, 768, 1366]) {
                await page.setViewportSize({ width, height: 868 });
                // Compare at the same viewport so page media queries are respected.
                await typography.evaluate(element => { element.sheet.disabled = true; });
                const before = await page.evaluate(selectors => selectors.map(selector => {
                    const style = getComputedStyle(document.querySelector(selector));
                    return [style.fontSize, style.fontWeight];
                }), textSelectors);
                await typography.evaluate(element => { element.sheet.disabled = false; });
                await page.evaluate(() => document.fonts.ready);
                const result = await page.evaluate(({ selectors, count }) => ({
                    text: selectors.map(selector => {
                        const style = getComputedStyle(document.querySelector(selector));
                        return [style.fontFamily, style.fontSize, style.fontWeight];
                    }),
                    icons: Array.from({ length: count }, (_, index) => getComputedStyle(document.getElementById(`icon-${index}`)).fontFamily),
                    loaded: document.fonts.check('16px SokolBangla', 'বাংলা English 123'),
                }), { selectors: textSelectors, count: icons.length });
                assert.ok(result.loaded, `${file}: local font loaded`);
                result.text.forEach(([family, size, weight], index) => {
                    assert.ok(family.startsWith('SokolBangla'), `${file} at ${width}: ${textSelectors[index]} uses ${family}`);
                    assert.deepEqual([size, weight], before[index], `${file}: ${textSelectors[index]} unchanged size/weight`);
                });
                result.icons.forEach((family, index) => assert.equal(family.replaceAll('"', ''), icons[index][1], `${file}: icon font preserved`));
                count++;
            }
            // A dynamic modal inserted after page load must use the same family.
            await page.evaluate(() => document.body.insertAdjacentHTML('beforeend', '<div class="swal2-popup"><h2 class="swal2-title">সহায়তা</h2></div>'));
            assert.ok((await page.locator('.swal2-title').evaluate(element => getComputedStyle(element).fontFamily)).startsWith('SokolBangla'));
            await page.close();
        }
        // Screen overrides must not leak into a printable receipt.
        const print = await browser.newPage();
        await print.goto(base);
        await print.setContent('<style>body,p{font-family:"Receipt Original",serif!important}</style><p>রশিদ</p>');
        await print.addStyleTag({ url: `${base}/assets/css/site_typography.css` });
        await print.emulateMedia({ media: 'print' });
        assert.equal(await print.locator('p').evaluate(element => getComputedStyle(element).fontFamily), '"Receipt Original", serif');
        await print.close();
        console.log(`PASS: ${pages.length} customer page styles, ${count} viewport cases; local font, icon protection, dynamic dialogs and print isolation`);
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
}
main().catch(error => { console.error(error); process.exitCode = 1; });
