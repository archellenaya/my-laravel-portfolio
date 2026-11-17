import { readFile, writeFile, mkdir, readdir, realpath, copyFile, rm } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

// Keep generated files writable by the shared group used by local Docker builds.
process.umask(0o002);

const site = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

// Vercel supplies version 2 endpoint paths through this build-time configuration.
export function analyticsScript(rawConfig = process.env.VERCEL_OBSERVABILITY_CLIENT_CONFIG) {
    const options = rawConfig ? JSON.parse(rawConfig).analytics ?? {} : {};
    const config = {
        scriptSrc: options.scriptSrc || '/_vercel/insights/script.js',
        ...Object.fromEntries(['eventEndpoint', 'viewEndpoint'].filter(key => options[key]).map(key => [key, options[key]])),
    };
    for (const [key, value] of Object.entries(config)) {
        // Vercel can supply deployment paths without a leading slash.
        if (typeof value !== 'string' || !value || /^[a-z][a-z0-9+.-]*:/i.test(value) || /[\\\s]/.test(value) || value.startsWith('//')) {
            throw new Error('Analytics endpoints must use same-origin paths');
        }
        config[key] = '/' + value.replace(/^\/+/, '');
    }
    return `(() => {
    const config = ${JSON.stringify(config)};
    window.va = window.va || function () { (window.vaq = window.vaq || []).push(arguments); };
    const script = document.createElement('script');
    script.defer = true;
    script.src = config.scriptSrc;
    if (config.eventEndpoint) script.dataset.eventEndpoint = config.eventEndpoint;
    if (config.viewEndpoint) script.dataset.viewEndpoint = config.viewEndpoint;
    document.head.appendChild(script);
})();\n`;
}

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

function normalizeContent(content, contract) {
    if (!content || typeof content !== 'object' || Array.isArray(content)) {
        throw new Error('Invalid portfolio snapshot');
    }
    if (Object.hasOwn(content, 'schema_version') && content.schema_version !== contract.version) {
        throw new Error('Unsupported portfolio schema version');
    }
    const normalized = { schema_version: contract.version };
    for (const [section, rules] of Object.entries(contract.sections)) {
        const rows = content[section] ?? (rules.optional ? [] : null);
        if (!Array.isArray(rows)) throw new Error(`Invalid portfolio section: ${section}`);
        normalized[section] = rows.map(row => {
            if (!row || typeof row !== 'object' || Array.isArray(row)) {
                throw new Error(`Invalid portfolio record: ${section}`);
            }
            const record = { ...row };
            for (const field of rules.fields) {
                record[field] ??= rules.defaults?.[field];
                if (typeof record[field] !== 'string') throw new Error(`Invalid portfolio field: ${section}.${field}`);
            }
            return record;
        });
    }
    return normalized;
}

export function templatePreferences(settings) {
    const templates = settings.templates.filter(template => template.enabled === true);
    if (!templates.length) throw new Error('Enable at least one portfolio template.');
    const ids = templates.map(template => template.id);
    const defaultTemplate = ids.includes(settings.default_template) ? settings.default_template : ids[0];
    const themed = templates.filter(template => template.supports_theme === true).map(template => template.id);

    return {
        options: templates.map(template => ({
            ...template,
            selected: template.id === defaultTemplate ? [{}] : [],
        })),
        default_template: defaultTemplate,
        default_theme: settings.default_theme,
        enabled_templates: ids.join(' '),
        themed_templates: themed.join(' '),
        initial_theme: themed.includes(defaultTemplate) ? settings.default_theme : '',
    };
}

async function readTemplate(file, parents = []) {
    if (parents.includes(file)) throw new Error(`Circular static template include: ${file}`);
    const source = await readFile(path.join(site, 'resources/views/static', file), 'utf8');
    const include = /{{>\s*([a-z][a-z0-9-]*)\s*}}/g;
    const includes = [...source.matchAll(include)];
    const parts = await Promise.all(includes.map(([, name]) => readTemplate(`sections/${name}.html`, [...parents, file])));
    let index = 0;
    return source.replace(include, () => parts[index++]);
}

function projectWebsite(website) {
    if (!website) return '';
    if (!/^https?:\/\/[^/\s?#]+/i.test(website) || /[\\\s\x00-\x1f\x7f]/.test(website) || !URL.canParse(website)) {
        throw new Error('Invalid project website URL');
    }
    return website;
}

export async function build({ output = path.join(site, 'dist'), snapshot = path.join(site, 'resources/data/portfolio.json'), stageAssets = false } = {}) {
    const analytics = analyticsScript();
    const contract = JSON.parse(await readFile(path.join(site, 'config/portfolio-contract.json'), 'utf8'));
    const content = normalizeContent(JSON.parse(await readFile(snapshot, 'utf8')), contract);
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
    const images = ['.svg', '.png', '.jpg', '.jpeg', '.webp', '.gif', '.avif'];
    async function optimizedImage(relative) {
        const optimized = `images/optimized/${path.parse(relative).name}.webp`;
        try {
            await realpath(path.join(site, 'public', optimized));
            return optimized;
        } catch (error) {
            if (error.code !== 'ENOENT') throw error;
            return relative;
        }
    }
    profile.headshot_url = await asset(await optimizedImage(profile.headshot), images);
    profile.photo_url = await asset(await optimizedImage(profile.photo), images);
    profile.photo_retina_url = await asset(await optimizedImage(profile.photo_retina), images);
    await asset(profile.resume, ['.pdf']);
    const sorted = rows => [...rows].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || (a.id ?? 0) - (b.id ?? 0));
    const context = {
        ...content, profile, anchors: contract.anchors, resume_path: contract.resume_path,
        year: new Date().getFullYear(),
        template_preferences: templatePreferences(contract.template_preferences),
        canonical_url: new URL('/', contract.public_origin).href,
    };
    for (const section of ['expertise', 'skills', 'experiences', 'education']) context[section] = sorted(content[section]);
    for (const section of ['experiences', 'education']) context[section] = context[section].map(row => ({...row, bullets: (row.description ?? '').split('\n').filter(line => line.trim()).map(text => ({text}))}));
    const tools = await Promise.all(sorted(content.tools ?? []).map(async tool => {
        if (!['original', 'light'].includes(tool.appearance)) throw new Error('Invalid tool logo appearance');
        return {...tool, image_url: await asset(await optimizedImage(tool.image), images), image_class: tool.appearance === 'light' ? 'tool-logo--light' : ''};
    }));
    context.tools = tools;
    context.projects = await Promise.all(sorted(content.projects).map(async (row, index) => {
        const website = projectWebsite(row.website_url);
        return {...row, image_url: await asset(row.image, images), modal_id: `modal-project-${index + 1}`,
            website_links: website ? [{url: website, title: row.title}] : [],
            tags: (row.tags ?? '').split(',').map(text => ({text: text.trim()})).filter(row => row.text)};
    }));
    const html = render(await readTemplate('index.html'), context);
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
    await writeFile(path.join(output, 'js/vercel-analytics.js'), analytics);
    await writeFile(path.join(output, 'index.html'), html);
    await writeFile(path.join(output, '404.html'), '<!doctype html><html lang="en"><meta charset="utf-8"><title>Page not found</title><h1>Page not found</h1><a href="/">Return to portfolio</a></html>');
    console.log(`Built static portfolio: ${output}`);
    return output;
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
    const index = process.argv.indexOf('--out');
    await build({ ...(index < 0 ? {} : { output: process.argv[index + 1] }), stageAssets: process.argv.includes('--stage-assets') });
}
