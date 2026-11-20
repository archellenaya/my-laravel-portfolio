# Portfolio deployment checklist

For the current verification results and open release issues, see [Progress](PROGRESS.md). The commands below prepare or verify a release. Obtain the owner's approval before committing, pushing, making a deployment, promoting a preview, or changing production settings.

## Content and template ownership

| Source | Owner and workflow |
| --- | --- |
| CMS database | The owner edits the profile, resume, skills, experience, education, tools, projects and case studies. Approve only cleared case studies and client contributions. The public database-mode page reflects saved changes. |
| `resources/data/portfolio.json` | The CMS export is the public snapshot for Laravel with `ISSTATIC=TRUE` and for every Node build. Export from the database after editorial review. |
| `resources/data/assets/` and `public/` | Stage referenced CMS uploads with the snapshot. Built-in static assets live in `public/`. Laravel serves uploads through `public/storage`, linked to `storage/app/public`. |
| `config/portfolio-contract.json` | Maintainers manage the public origin, social image, template registry, availability, and configured service content. Confirm availability and public content changes with the owner. |

Register a template under `template_preferences.templates` using a stable `id`, display `name`, `enabled` flag, and `supports_theme` flag. Set `default_template` to an enabled ID and keep an enabled template available for fallback. Use the root `data-template` and `data-theme` selectors in `public/css/portfolio-templates.css`. Keep the Blade and static partials aligned when changing shared markup. Verify keyboard access, responsive layout, both explicit themes where supported, saved preference fallback, and unchanged canonical URLs.

## Prepare the release

- [ ] Review the public claims, gallery membership, contact links, availability, screenshots and resume with the owner. Confirm the intended public origin and social image.
- [ ] Export from the authenticated CMS using **Publish Static**, or run the commands below from the Compose workspace. Review the snapshot diff and staged upload list together. Keep private configuration, authentication data and generated `dist/` out of Git.
- [ ] From an isolated copy, run PHPUnit with GD and SQLite, `npm run test:static` and `npm run build`. Review the [Phase 7 results](PROGRESS.md#phase-7--release-readiness).
- [ ] Record the candidate revision and the last known-good Vercel deployment and Laravel release. Back up the Laravel database and CMS uploads before a Laravel release that changes data or schema.
- [ ] Confirm which delivery path is being released. For Vercel, use the Git repository root, `npm run build`, `dist` and the checked-in `vercel.json`. For Laravel, confirm HTTPS, the public document root, `APP_URL`, secure sessions, `APP_DEBUG=false`, the storage link, and the intended `ISSTATIC` mode. Keep the CMS database and SMTP settings on the Laravel host.
- [ ] Review Laravel lifecycle risk before approving an internet-facing Laravel release. Laravel 10 security support ended on 4 February 2025. Plan the framework upgrade separately from the portfolio rollout. [Laravel release policy](https://laravel.com/docs/10.x/releases#support-policy)

```sh
docker compose exec app php artisan portfolio:export
cd site
npm run static:stage
npm run test:static
npm run build
```

`portfolio:export` reads the database in either Laravel rendering mode. The staging command copies referenced CMS assets to the deployment asset directory and builds the static site. See the [README](../README.md) for local CMS and environment setup.

## Verify the approved preview and production release

- [ ] Check HTTP success and content types for `/`, `/robots.txt`, `/sitemap.xml` and `/download-cv`. Check that the resume is the approved PDF, downloads as an attachment, and opens. Confirm Laravel redirects guests to CMS login and static `/admin` returns 404.
- [ ] Verify the canonical URL, sitemap and robots reference agree. Inspect the deployed CSP, `X-Content-Type-Options`, frame protection and referrer policy. Check for browser resource or CSP errors.
- [ ] Check the primary contact actions, sampled external project links, gallery images, mobile menu, template and theme persistence and dialog focus at mobile and desktop widths.
- [ ] If Vercel Web Analytics is enabled, confirm the script, a single pageview and dashboard ingestion. Set `PORTFOLIO_ANALYTICS_EVENTS=1` only if custom events are supported and approved, then verify the expected events and their payloads. Review Speed Insights separately where enabled.
- [ ] Record the verification result and request owner approval before production promotion. Repeat the smoke checks at the canonical origin after the approved release.

## Rollback

- Vercel can restore the last known-good production deployment through **Instant Rollback** when the deployment is eligible. Verify the canonical origin, resume, redirects, headers and gallery after rollback. Reconcile source and configuration before the next promotion. [Vercel Instant Rollback](https://vercel.com/docs/instant-rollback)
- Laravel can restore the previous application release, configuration and matching public assets. With `ISSTATIC=TRUE`, restore the matching snapshot and its uploads together. With database-backed content, use the CMS to correct a content-only issue and re-export if needed. For a schema or data incompatibility, use the matching verified backup and account for intervening edits before authorizing a database restore. Repeat the public and administrator smoke checks.
