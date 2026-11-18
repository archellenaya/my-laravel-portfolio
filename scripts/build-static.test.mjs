import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile, access, mkdtemp, writeFile, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';
import { caseStudies } from './portfolio-presentation.mjs';
import { analyticsScript, build, render, templatePreferences } from './build-static.mjs';

const site = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

test('escapes content and handles nested lists without executing expressions', () => {
    assert.equal(render('{{#rows}}{{name}}{{#tags}}[{{text}}]{{/tags}}{{/rows}}', {
        rows: [{name: '<script>"&', tags: [{text:'<tag>'}]}],
    }), '&lt;script&gt;&quot;&amp;[&lt;tag&gt;]');
    assert.throws(() => render('{{#rows}}{{/tags}}', {}), /Unmatched section/);
    assert.throws(() => render('{{missing}}', {}), /Missing template value/);
});

test('registry selects an enabled configured default or the first enabled template', () => {
    const settings = {
        default_template: 'terminal',
        default_theme: 'system',
        templates: [
            {id: 'modern', enabled: true, supports_theme: true},
            {id: 'minimal', enabled: true, supports_theme: false},
            {id: 'terminal', enabled: false, supports_theme: false},
        ],
    };
    const fallback = templatePreferences(settings);
    assert.equal(fallback.default_template, 'modern');
    assert.equal(fallback.initial_theme, 'system');
    assert.equal(fallback.enabled_templates, 'modern minimal');
    assert.deepEqual(fallback.options.map(template => template.selected.length), [1, 0]);

    const configured = templatePreferences({...settings, default_template: 'minimal'});
    assert.equal(configured.default_template, 'minimal');
    assert.equal(configured.initial_theme, '');
    assert.deepEqual(configured.options.map(template => template.selected.length), [0, 1]);
});

test('browser preferences apply early, persist selection and handle stale or blocked storage', async () => {
    const source = await readFile(path.join(site, 'public/js/portfolio-preferences.js'), 'utf8');
    const run = (saved, blocked = false) => {
        const dataset = {template: 'modern', defaultTheme: 'system', enabledTemplates: 'modern creative', themedTemplates: 'modern'};
        const container = {hidden: true};
        let ready;
        let change;
        const select = {
            closest: () => container,
            addEventListener: (name, callback) => { assert.equal(name, 'change'); change = callback; },
        };
        const themeField = {hidden: true};
        let changeTheme;
        const themeSelect = {
            addEventListener: (name, callback) => { assert.equal(name, 'change'); changeTheme = callback; },
        };
        const controls = {
            'portfolio-template': select,
            'portfolio-theme': themeSelect,
            'portfolio-theme-field': themeField,
        };
        const document = {
            documentElement: {dataset},
            getElementById: id => controls[id],
            addEventListener: (name, callback) => { assert.equal(name, 'DOMContentLoaded'); ready = callback; },
            activeElement: select,
        };
        const window = {
            get localStorage() {
                if (blocked) throw new Error('Storage blocked');
                return {
                    getItem: key => saved.get(key) ?? null,
                    setItem: (key, value) => saved.set(key, value),
                };
            },
        };
        vm.runInNewContext(source, {document, window});
        const early = {...dataset};
        assert.ok(container.hidden);
        ready();
        assert.ok(!container.hidden);
        return {
            early, dataset, select, themeField, themeSelect,
            chooseTheme: value => {
                themeSelect.value = value;
                changeTheme();
            },
            choose: value => {
                select.value = value;
                change();
                assert.equal(document.activeElement, select);
            },
        };
    };

    const saved = new Map([['portfolio.template', 'creative'], ['portfolio.theme', 'dark']]);
    const current = run(saved);
    assert.equal(current.early.template, 'creative');
    assert.equal(current.early.theme, '');
    assert.equal(current.select.value, 'creative');
    current.choose('modern');
    assert.equal(current.dataset.theme, 'dark');
    assert.equal(run(saved).early.template, 'modern');
    assert.equal(run(saved).early.theme, 'dark');
    assert.equal(current.themeField.hidden, false);
    current.chooseTheme('light');
    assert.equal(current.dataset.theme, 'light');
    assert.equal(run(saved).themeSelect.value, 'light');
    current.choose('creative');
    assert.equal(current.themeField.hidden, true);
    assert.equal(current.dataset.theme, '');
    current.choose('modern');
    assert.equal(current.dataset.theme, 'light');

    for (const unavailable of ['terminal', 'invalid']) {
        saved.set('portfolio.template', unavailable);
        assert.equal(run(saved).early.template, 'modern');
    }
    const blocked = run(saved, true);
    assert.equal(blocked.early.theme, 'system');
    blocked.chooseTheme('dark');
    blocked.choose('creative');
    assert.equal(blocked.dataset.template, 'creative');
    blocked.choose('modern');
    assert.equal(blocked.dataset.theme, 'dark');
    assert.equal(run(saved, true).early.template, 'modern');
});

test('only approved case studies resolve assets and expose optional details', async () => {
    const approved = {
        approved: true, title: 'Approved example', description: 'Example description',
        problem: 'Example problem', technologies: 'PHP',
        screenshot_path: 'images/example.png', screenshot_alt: 'Approved preview',
        repository_url: 'https://example.test/repository',
    };
    const contract = {
        case_study_fields: {problem: 'Business problem', outcomes: 'Results'},
    };
    const studies = await caseStudies([{title: 'Private draft', screenshot_path: '../draft.png'}, approved], contract, url => url, async file => '/' + file);
    assert.equal(studies.length, 1);
    assert.equal(studies[0].title, approved.title);
    assert.deepEqual(studies[0].details, [{label: 'Business problem', text: approved.problem}]);
    assert.deepEqual(studies[0].links, [{label: 'Public repository', url: approved.repository_url}]);
    assert.deepEqual(studies[0].screenshots, [{alt: 'Approved preview', url: '/images/example.png'}]);
});

test('build produces HTML, linked assets and resume without PHP or secret files', async () => {
    const output = await build();
    const html = await readFile(path.join(output, 'index.html'), 'utf8');
    const data = JSON.parse(await readFile(path.join(site, 'resources/data/portfolio.json'), 'utf8'));
    assert.ok(html.includes(data.profiles[0].fullname));
    for (const text of [
        '8+ years of professional experience', 'Download Resume', 'View Projects',
        'Technical Skills', 'Learning &amp; Academic Background',
        'Client Website Gallery', 'Freelance Services',
    ]) assert.ok(html.includes(text));
    assert.equal((html.match(/aria-label="Discuss /g) ?? []).length, 6);
    assert.ok(html.includes('Web: JavaScript, HTML, CSS</li>'));
    assert.ok(html.includes('Server-side Languages: PHP</li>'));
    const hasApprovedStudies = (data.case_studies ?? []).some(study => study.approved === true);
    assert.equal(html.includes('Featured Technical Case Studies'), hasApprovedStudies);
    assert.ok(html.includes('Available for remote employment'));
    assert.ok(html.includes('Available for freelance projects'));
    assert.ok(html.includes('src="/js/vercel-analytics.js"'));
    assert.ok(html.includes('data-template="modern"'));
    assert.ok(html.includes('data-theme="system"'));
    assert.ok(html.includes('<link rel="canonical" href="https://www.archellenaya.com/">'));
    assert.ok(html.indexOf('<script src="/js/portfolio-preferences.js"></script>') < html.indexOf('<link rel="stylesheet"'));
    assert.ok(html.includes('class="row portfolio-preferences" hidden'));
    assert.ok(html.includes('<label for="portfolio-template">Portfolio template</label>'));
    assert.ok(html.includes('<label for="portfolio-theme">Color theme</label>'));
    assert.ok(html.includes('id="portfolio-theme-field" hidden'));
    assert.ok(html.includes('href="/css/portfolio-templates.css"'));
    assert.ok(html.includes('<option value="modern" selected>Modern Developer</option>'));
    assert.ok(html.includes('<option value="terminal">Terminal Developer</option>'));
    assert.ok((await readFile(path.join(output, 'js/vercel-analytics.js'), 'utf8')).includes('window.va'));
    assert.ok(!html.includes('https://localhost'));
    assert.ok(!/{{|@foreach|@extends|\$counter/.test(html));
    assert.equal((html.match(/class="folio-list__item column"/g) ?? []).length, data.projects.length);
    for (const id of ['intro', 'about', 'works', 'services', 'contact']) {
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
    const resumeBytes = resume.startsWith('storage/cms/')
        ? await readFile(path.join(site, 'storage/app/public', resume.slice(8))).catch(error => {
            if (error.code !== 'ENOENT') throw error;
            return readFile(path.join(site, 'resources/data/assets', resume));
        })
        : await readFile(path.join(site, 'public', resume));
    assert.deepEqual(await readFile(path.join(output, 'resume.pdf')), resumeBytes);
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
        content.case_studies = [
            {title: 'Draft must stay private', approved: false},
            {title: 'Approved site example', description: 'Approved study description.', technologies: 'PHP, Laravel', approved: true},
        ];
        content.projects[0].contribution = 'Approved gallery contribution.';
        content.projects[0].technologies = 'Laravel';
        delete content.profiles[0].github_url;
        delete content.projects[0].website_url;
        content.projects[1].website_url = 'https://example.test/?a=1&b=2';
        await writeFile(snapshot, JSON.stringify(content));
        const output = await build({snapshot});
        const html = await readFile(path.join(output, 'index.html'), 'utf8');
        assert.ok(html.includes('/images/formal-photo.jpg'));
        assert.ok(html.includes('href="https://example.test/?a=1&amp;b=2"'));
        assert.ok(!html.includes('href="https://franksalt.com.mt/"'));
        assert.ok(!html.includes('>GitHub</a>'));
        assert.ok(html.includes('Approved site example'));
        assert.ok(html.includes('Approved gallery contribution.'));
        assert.ok(!html.includes('Draft must stay private'));

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
