import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile, access, mkdtemp, writeFile, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';
import { analyticsScript, build, render } from './build-static.mjs';

const site = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

test('escapes content and handles nested lists without executing expressions', () => {
    assert.equal(render('{{#rows}}{{name}}{{#tags}}[{{text}}]{{/tags}}{{/rows}}', {
        rows: [{name: '<script>"&', tags: [{text:'<tag>'}]}],
    }), '&lt;script&gt;&quot;&amp;[&lt;tag&gt;]');
    assert.throws(() => render('{{#rows}}{{/tags}}', {}), /Unmatched section/);
    assert.throws(() => render('{{missing}}', {}), /Missing template value/);
});

test('build produces HTML, linked assets and resume without PHP or secret files', async () => {
    const output = await build();
    const html = await readFile(path.join(output, 'index.html'), 'utf8');
    const data = JSON.parse(await readFile(path.join(site, 'resources/data/portfolio.json'), 'utf8'));
    assert.ok(html.includes(data.profiles[0].fullname));
    assert.ok(html.includes('src="/js/vercel-analytics.js"'));
    assert.ok((await readFile(path.join(output, 'js/vercel-analytics.js'), 'utf8')).includes('window.va'));
    assert.ok(!html.includes('https://localhost'));
    assert.ok(!/{{|@foreach|@extends|\$counter/.test(html));
    assert.equal((html.match(/class="folio-list__item column"/g) ?? []).length, data.projects.length);
    for (const id of ['intro', 'about', 'works', 'contact']) {
        assert.ok(html.includes(`id="${id}"`));
        assert.ok(html.includes(`href="#${id}"`));
    }
    const ids = new Set([...html.matchAll(/\bid="([^"]+)"/g)].map(match => match[1]));
    for (const match of html.matchAll(/href="#(modal-[^"]+)"/g)) assert.ok(ids.has(match[1]));
    for (const match of html.matchAll(/(?:src|href)="(\/[^"#]*)"/g)) {
        if (match[1] === '/' || match[1] === '/download-cv') continue;
        await access(path.join(output, decodeURIComponent(match[1])));
    }
    const resume = data.profiles[0].resume;
    const resumeSource = resume.startsWith('storage/cms/')
        ? path.join(site, 'storage/app/public', resume.slice(8))
        : path.join(site, 'public', resume);
    assert.deepEqual(await readFile(path.join(output, 'resume.pdf')), await readFile(resumeSource));
    for (const project of data.projects.filter(project => project.website_url)) {
        assert.ok(html.includes(`href="${project.website_url}"`));
    }
    await assert.rejects(access(path.join(output, 'index.php')));
    await assert.rejects(access(path.join(output, '.env')));
    await assert.rejects(access(path.join(output, 'resources/data/portfolio.json')));
    const config = JSON.parse(await readFile(path.join(site, 'vercel.json'), 'utf8'));
    assert.equal(config.outputDirectory, 'dist');
    assert.ok(config.rewrites.some(rule => rule.source === '/download-cv' && rule.destination === '/resume.pdf'));
});

test('rejects missing assets, unsafe paths and unsafe social links before emitting a site', async () => {
    const temporary = await mkdtemp(path.join(tmpdir(), 'portfolio-static-'));
    try {
        const snapshot = path.join(temporary, 'content.json');
        const original = JSON.parse(await readFile(path.join(site, 'resources/data/portfolio.json'), 'utf8'));
        for (const [field, value, error] of [
            ['resume', '../.env', /Unsafe asset/],
            ['resume', 'missing-resume.pdf', /Missing asset/],
            ['github_url', 'javascript:alert(1)', /Invalid github_url/],
        ]) {
            const content = structuredClone(original);
            content.profiles[0][field] = value;
            await writeFile(snapshot, JSON.stringify(content));
            await assert.rejects(build({snapshot}), error);
        }
    } finally { await rm(temporary, {recursive:true, force:true}); }
});


test('loads legacy snapshots and rejects unsafe project links or unknown versions', async () => {
    const temporary = await mkdtemp(path.join(tmpdir(), 'portfolio-legacy-'));
    try {
        const snapshot = path.join(temporary, 'portfolio.json');
        const content = JSON.parse(await readFile(path.join(site, 'resources/data/portfolio.json'), 'utf8'));
        delete content.profiles[0].headshot;
        delete content.tools;
        delete content.projects[0].website_url;
        content.projects[1].website_url = 'https://example.test/?a=1&b=2';
        await writeFile(snapshot, JSON.stringify(content));
        const output = await build({snapshot});
        const html = await readFile(path.join(output, 'index.html'), 'utf8');
        assert.ok(html.includes('/images/formal-photo.jpg'));
        assert.ok(html.includes('href="https://example.test/?a=1&amp;b=2"'));
        assert.ok(!html.includes('href="https://franksalt.com.mt/"'));

        content.projects[0].website_url = 'javascript:alert(1)';
        await writeFile(snapshot, JSON.stringify(content));
        await assert.rejects(build({snapshot}), /Invalid project website URL/);

        content.schema_version = 999;
        await writeFile(snapshot, JSON.stringify(content));
        await assert.rejects(build({snapshot}), /Unsupported portfolio schema version/);
    } finally { await rm(temporary, {recursive:true, force:true}); }
});

test('deployment uses staged CMS uploads when the runtime storage file is absent', async () => {
    const temporary = await mkdtemp(path.join(tmpdir(), 'portfolio-static-upload-'));
    const relative = 'storage/cms/static-test-' + process.pid + '.png';
    const staged = path.join(site, 'resources/data/assets', relative);
    const { mkdir } = await import('node:fs/promises');
    try {
        const content = JSON.parse(await readFile(path.join(site, 'resources/data/portfolio.json'), 'utf8'));
        await mkdir(path.dirname(staged), {recursive:true});
        const image = await readFile(path.join(site, 'public/images/about-photo.png'));
        await writeFile(staged, image);
        content.profiles[0].headshot = relative;
        const snapshot = path.join(temporary, 'content.json');
        await writeFile(snapshot, JSON.stringify(content));
        const output = await build({snapshot});
        assert.deepEqual(await readFile(path.join(output, relative)), image);
    } finally {
        await rm(staged, {force:true});
        await rm(temporary, {recursive:true, force:true});
        await build();
    }
});


test('generated page exposes content immediately and includes accessible navigation and dialogs', async () => {
    const output = await build();
    const html = await readFile(path.join(output, 'index.html'), 'utf8');
    assert.ok(!html.includes('ss-preload'));
    assert.ok(!html.includes('id="preloader"'));
    assert.ok(!html.includes('/js/plugins.js'));
    assert.ok(html.includes('Skip to content'));
    assert.ok(html.includes('aria-controls="main-navigation"'));
    assert.ok(html.includes('aria-expanded="false"'));
    assert.ok(html.includes('<dialog '));
    assert.ok(html.includes('data-close-dialog'));
    assert.ok(html.includes('loading="lazy" decoding="async"'));
});

test('CMS tool logos render names, images, appearance and order for the globe', async () => {
    const temporary = await mkdtemp(path.join(tmpdir(), 'portfolio-tools-'));
    try {
        const content = JSON.parse(await readFile(path.join(site, 'resources/data/portfolio.json'), 'utf8'));
        content.tools = [
            {title: 'Last Tool', image: 'images/tools/docker.svg', appearance: 'original', sort_order: 5},
            {title: 'First & Tool', image: content.profiles[0].photo, appearance: 'light', sort_order: 0},
        ];
        const snapshot = path.join(temporary, 'portfolio.json');
        await writeFile(snapshot, JSON.stringify(content));
        const output = await build({snapshot});
        const html = await readFile(path.join(output, 'index.html'), 'utf8');
        assert.ok(html.includes('First &amp; Tool'));
        assert.ok(html.indexOf('First &amp; Tool') < html.indexOf('Last Tool'));
        assert.ok(html.includes('data-appearance="light"'));
        assert.ok(html.includes('id="globe-logos"'));
        assert.ok(!html.includes('data-tool-marquee'));
        const logoSource = html.match(/<img src="([^"]+)" alt="First &amp; Tool"/)[1];
        await access(path.join(output, decodeURIComponent(logoSource)));
        content.tools = [];
        await writeFile(snapshot, JSON.stringify(content));
        await build({snapshot});
        const emptyHtml = await readFile(path.join(output, 'index.html'), 'utf8');
        assert.ok(!/<template id="globe-logos">[\s\S]*?<img[\s\S]*?<\/template>/.test(emptyHtml));
    } finally {
        await rm(temporary, {recursive: true, force: true});
        await build();
    }
});


test('static Analytics loader uses Vercel endpoints and initializes its queue', () => {
    for (const custom of [false, true]) {
        const config = custom ? JSON.stringify({analytics: {scriptSrc: '/metrics/script.js', viewEndpoint: '/metrics/view', eventEndpoint: '/metrics/event'}}) : '{}';
        const scripts = [], window = {};
        vm.runInNewContext(analyticsScript(config), {window, document: {
            createElement: () => ({dataset: {}}), head: {appendChild: script => scripts.push(script)},
        }});
        assert.equal(scripts.length, 1);
        assert.equal(scripts[0].src, custom ? '/metrics/script.js' : '/_vercel/insights/script.js');
        if (custom) {
            assert.equal(scripts[0].dataset.viewEndpoint, '/metrics/view');
            assert.equal(scripts[0].dataset.eventEndpoint, '/metrics/event');
        }
        window.va('event', 'example');
        assert.equal(window.vaq[0][0], 'event');
    }
    assert.throws(() => analyticsScript('{"analytics":{"scriptSrc":"//external.example/script.js"}}'), /same-origin/);
});


test('Analytics normalizes Vercel paths without a leading slash', () => {
    const scripts = [];
    const source = analyticsScript(JSON.stringify({analytics: {
        scriptSrc: 'abc123/script.js', viewEndpoint: 'abc123/view', eventEndpoint: '/abc123/event',
    }}));
    vm.runInNewContext(source, {window: {}, document: {
        createElement: () => ({dataset: {}}), head: {appendChild: script => scripts.push(script)},
    }});
    assert.equal(scripts[0].src, '/abc123/script.js');
    assert.equal(scripts[0].dataset.viewEndpoint, '/abc123/view');
    assert.equal(scripts[0].dataset.eventEndpoint, '/abc123/event');
    for (const scriptSrc of ['https://external.example/script.js', '//external.example/script.js', 'javascript:alert(1)', '/\\external.example/script.js']) {
        assert.throws(() => analyticsScript(JSON.stringify({analytics: {scriptSrc}})), /same-origin/);
    }
});
