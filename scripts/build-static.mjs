import { readFile, writeFile, mkdir, readdir, realpath, copyFile, rm } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

// Keep generated files writable by the shared group used by local Docker builds.
process.umask(0o002);

const site = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

// A deliberately small, non-executable template format: escaped values and list sections.
export function render(template, context) {
    const root = [], stack = [{ name: null, children: root }];
    let cursor = 0;
    for (const match of template.matchAll(/{{\s*([#/]?)([\w.]+)\s*}}/g)) {
        stack.at(-1).children.push(template.slice(cursor, match.index));
        const [, type, name] = match;
        if (type === '#') {
            const section = { name, children: [] };
            stack.at(-1).children.push(section);
            stack.push(section);
        } else if (type === '/') {
            if (stack.length === 1 || stack.at(-1).name !== name) throw new Error(`Unmatched section: ${name}`);
            stack.pop();
        } else stack.at(-1).children.push({ value: name });
        cursor = match.index + match[0].length;
    }
    if (stack.length !== 1) throw new Error('Unclosed template section');
    root.push(template.slice(cursor));
    const lookup = (scope, name) => name.split('.').reduce((value, key) => value?.[key], scope);
    const walk = (nodes, scope) => nodes.map(node => {
        if (typeof node === 'string') return node;
        if (node.value) {
            const value = lookup(scope, node.value);
            if (value === undefined) throw new Error(`Missing template value: ${node.value}`);
            return escape(value);
        }
        const rows = lookup(scope, node.name);
        if (!Array.isArray(rows)) throw new Error(`Expected list: ${node.name}`);
        return rows.map(row => walk(node.children, { ...scope, ...row })).join('');
    }).join('');
    return walk(root, context);
}

export async function build({ output = path.join(site, 'dist'), snapshot = path.join(site, 'resources/data/portfolio.json'), stageAssets = false } = {}) {
    const content = JSON.parse(await readFile(snapshot, 'utf8'));
    for (const section of ['profiles', 'expertise', 'skills', 'experiences', 'education', 'projects']) {
        if (!Array.isArray(content[section])) throw new Error(`Missing content section: ${section}`);
    }
    const profile = { ...content.profiles[0] };
    if (!profile.fullname) throw new Error('A profile is required');
    for (const field of ['linkedin_url', 'facebook_url', 'instagram_url', 'github_url']) {
        if (!['https:', 'http:'].includes(new URL(profile[field]).protocol)) throw new Error(`Invalid ${field}`);
    }
    if (!/^\d{7,15}$/.test(profile.whatsapp) || /[\r\n]/.test(profile.email)) throw new Error('Invalid contact details');

    const assets = new Map();
    async function asset(relative, extensions) {
        if (typeof relative !== 'string' || !/^[\w./@ -]+$/.test(relative) || relative.split('/').includes('..') || relative.startsWith('/')) throw new Error(`Unsafe asset path: ${relative}`);
        if (!extensions.includes(path.extname(relative).toLowerCase())) throw new Error(`Unsupported asset: ${relative}`);
        const uploaded = relative.startsWith('storage/cms/');
        const roots = uploaded
            ? [[path.join(site, 'storage/app/public'), relative.slice(8)], [path.join(site, 'resources/data/assets'), relative]]
            : [[path.join(site, 'public'), relative]];
        let source;
        for (const [directory, file] of roots) {
            let base, candidate;
            try { base = await realpath(directory); candidate = await realpath(path.join(base, file)); }
            catch { continue; }
            if (!candidate.startsWith(base + path.sep)) throw new Error(`Asset escapes its directory: ${relative}`);
            source = candidate;
            break;
        }
        if (!source) throw new Error(`Missing asset ${relative}. Run npm run static:stage locally and commit referenced assets before deploying.`);
        assets.set(relative, source);
        return '/' + relative.split('/').map(encodeURIComponent).join('/');
    }
    const images = ['.png', '.jpg', '.jpeg', '.webp', '.gif', '.avif'];
    let headshot = profile.headshot ?? 'images/formal-photo.jpg';
    const optimizedHeadshot = `images/optimized/${path.parse(headshot).name}.webp`;
    try {
        await realpath(path.join(site, 'public', optimizedHeadshot));
        headshot = optimizedHeadshot;
    } catch (error) {
        if (error.code !== 'ENOENT') throw error;
    }
    profile.headshot_url = await asset(headshot, images);
    profile.photo_url = await asset(profile.photo, images);
    profile.photo_retina_url = await asset(profile.photo_retina, images);
    await asset(profile.resume, ['.pdf']);
    const sorted = rows => [...rows].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || (a.id ?? 0) - (b.id ?? 0));
    const context = { ...content, profile, year: new Date().getFullYear() };
    for (const section of ['expertise', 'skills', 'experiences', 'education']) context[section] = sorted(content[section]);
    for (const section of ['experiences', 'education']) context[section] = context[section].map(row => ({...row, bullets: (row.description ?? '').split('\n').filter(line => line.trim()).map(text => ({text}))}));
    context.projects = await Promise.all(sorted(content.projects).map(async (row, index) => ({...row, image_url: await asset(row.image, images), modal_id: `modal-project-${index + 1}`, tags: (row.tags ?? '').split(',').map(text => ({text: text.trim()})).filter(row => row.text)})));
    const html = render(await readFile(path.join(site, 'resources/views/static/index.html'), 'utf8'), context);
    if (/{{|@foreach|@extends/.test(html)) throw new Error('Unrendered template syntax');

    if (stageAssets) {
        for (const [relative, source] of assets) {
            if (!relative.startsWith('storage/cms/')) continue;
            const destination = path.join(site, 'resources/data/assets', relative);
            await mkdir(path.dirname(destination), { recursive: true });
            if (path.resolve(source) !== path.resolve(destination)) await copyFile(source, destination);
        }
        console.log('Referenced CMS uploads staged in resources/data/assets. Include these files in your deployment commit.');
    }

    // Only this dedicated build directory may be replaced.
    output = path.resolve(output);
    if (output !== path.join(site, 'dist')) throw new Error('Output must be the project dist directory');
    await mkdir(output, { recursive: true });
    // Preserve the directory itself: it may be a Docker bind mount or have a read-only parent.
    for (const entry of await readdir(output)) {
        await rm(path.join(output, entry), { recursive: true, force: true });
    }
    async function copy(source, relative) {
        const destination = path.join(output, relative);
        await mkdir(path.dirname(destination), { recursive: true });
        await copyFile(source, destination);
    }
    const allowed = ['.css', '.js', '.png', '.jpg', '.jpeg', '.webp', '.gif', '.svg', '.avif', '.ico', '.woff', '.woff2', '.ttf', '.eot'];
    async function directory(relative) {
        for (const entry of await readdir(path.join(site, 'public', relative), { withFileTypes: true })) {
            const file = path.join(relative, entry.name);
            if (entry.isDirectory()) await directory(file);
            else if (entry.isFile() && allowed.includes(path.extname(file).toLowerCase())) await copy(path.join(site, 'public', file), file);
        }
    }
    for (const dir of ['css', 'js', 'images']) await directory(dir);
    for (const name of ['favicon.ico', 'favicon-16x16.png', 'favicon-32x32.png', 'apple-touch-icon.png', 'android-chrome-192x192.png', 'android-chrome-512x512.png', 'robots.txt', 'site.webmanifest']) await copy(path.join(site, 'public', name), name);
    for (const [relative, source] of assets) await copy(source, relative);
    await copy(assets.get(profile.resume), 'resume.pdf');
    await writeFile(path.join(output, 'index.html'), html);
    await writeFile(path.join(output, '404.html'), '<!doctype html><html lang="en"><meta charset="utf-8"><title>Page not found</title><h1>Page not found</h1><a href="/">Return to portfolio</a></html>');
    console.log(`Built static portfolio: ${output}`);
    return output;
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
    const index = process.argv.indexOf('--out');
    await build({ ...(index < 0 ? {} : { output: process.argv[index + 1] }), stageAssets: process.argv.includes('--stage-assets') });
}
