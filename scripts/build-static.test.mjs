import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile, access, mkdtemp, writeFile, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { build, render } from './build-static.mjs';

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
    assert.ok(!html.includes('https://localhost'));
    assert.ok(!/{{|@foreach|@extends|\$counter/.test(html));
    assert.equal((html.match(/class="folio-list__item column"/g) ?? []).length, data.projects.length);
    const ids = new Set([...html.matchAll(/\bid="([^"]+)"/g)].map(match => match[1]));
    for (const match of html.matchAll(/href="#(modal-[^"]+)"/g)) assert.ok(ids.has(match[1]));
    for (const match of html.matchAll(/(?:src|href)="(\/[^"#]*)"/g)) {
        if (match[1] === '/' || match[1] === '/download-cv') continue;
        await access(path.join(output, decodeURIComponent(match[1])));
    }
    assert.deepEqual(await readFile(path.join(output, 'resume.pdf')), await readFile(path.join(site, 'public', data.profiles[0].resume)));
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
        content.profiles[0].photo = relative;
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
