# Portfolio master specification

The portfolio serves international recruiters hiring remote backend developers and businesses seeking freelance web development. Its public copy should lead with 8+ years of experience, PHP and Laravel, REST APIs and backend integrations, Drupal, WordPress and WooCommerce, and work on international projects. Remote and freelance availability are independently configurable and need owner confirmation before publication.

## Current architecture and feature inventory

| Area | Current implementation |
| --- | --- |
| Repository | `site/` is the Git checkout and Vercel project root. The parent workspace contains Docker Compose and supporting configuration. |
| Application | Laravel 10, PHP ^8.1, Blade, Composer, and PHPUnit. Nginx and PHP-FPM serve the Docker application with MySQL, Redis, and Mailpit. Compose binds its exposed ports to loopback. |
| Laravel public site | `HomeController` serves `/` and `/download-cv`. `PortfolioContent` loads ordered database records or the exported JSON snapshot via `ISSTATIC`. Missing or malformed snapshots fail with HTTP 503. The homepage uses `home_page.blade.php` and `layouts/app.blade.php`. |
| Static public site | `scripts/build-static.mjs` renders `resources/views/static/index.html` using the snapshot and copies assets into `dist/`. Vercel runs the Node build without installing dependencies or requiring Laravel. It rewrites `/download-cv` to the exported PDF. |
| Editing and publishing | The existing CMS manages profiles, projects, experience, education, skills, expertise, tool logos and uploads. Admin routes support authorization, email-code verification, password reset, and a locked export/build action. CMS changes reach the static site through export, asset staging and deployment. |
| Public content | Portrait, summary, experience and education timelines, skill lists, 35 tool logos, 19 client projects with dialogs and stored website URLs, social/contact links and resume download. |
| Frontend | Shared files in `public/css` and `public/js` provide navigation, dialogs, motion and the particle presentation. Vite provides a separate Laravel asset build. A second Blade file, `Home.blade.php`, is a candidate for a reference check before cleanup. |
| Accessibility and performance baseline | Skip navigation, native project dialogs, a labeled mobile menu, reduced-motion CSS, lazy-loading cues and optimized WebP lookup already exist. Optimized hero and About images are roughly 53 KiB and 40 KiB. Verify selected assets in both renderers and cover new CMS uploads that lack variants. |
| SEO and analytics baseline | Title and description exist in both renderers. The repository has robots.txt and a static Vercel Analytics loader with same-origin endpoint validation. Canonical, social cards, sitemap and structured data were not found in the public templates. The Vite analytics entry exists separately and the active Blade layout loads public scripts directly. Confirm production tracking and Speed Insights before expanding instrumentation. |

## Public requirements

- Hero with configurable name, specialty, summary, portrait, location and experience, availability labels, resume, projects and contact CTAs, and configured LinkedIn/GitHub links.
- Featured technical case studies with title, description, problem, responsibilities, technologies, architecture, challenges, solutions, outcomes, screenshots, optional public live and repository URLs, and confidentiality status. Keep missing details in editorial review. Publish verified and approved facts only.
- Preserve the client gallery with responsive previews, category and technology labels where supported, external links, and approved contribution summaries. Filtering is optional.
- Group professional skills into backend, CMS and e-commerce, databases, frontend, APIs and integrations, DevOps and tools, and AI-assisted development. Identify technologies being learned without proficiency scores.
- Keep verified employment history and education. Highlight ownership, architecture, integration work, business problems and international collaboration.
- Publish six services with descriptions, technologies and contact CTAs: custom Laravel applications, REST API development and integration, Drupal and custom modules, WordPress customization, WooCommerce integration, and maintenance and optimization. Icons are optional. Pricing requires explicit configuration.
- Contact provides email, configured social links and resume. A contact form is optional and deferred. Adding one requires a working delivery backend, validation, spam protection and accessible submission feedback.
- Every template shares accessible navigation, semantic headings, keyboard operation and visible focus, reduced motion, responsive layouts and stable canonical metadata.

## Proposed architecture

Keep the database authoritative for existing CMS-managed records and keep the exported JSON as the static build input. Add a versioned public content contract and a small shared configuration file for template defaults, enabled templates, theme preferences, public origin and optional portfolio sections until editing requirements are confirmed. Both renderers consume the same configuration and data shape. Use existing export and asset validation paths. Define ownership for each added field and supply defaults for older rows and snapshots.

Extract equivalent public components within each renderer for navigation, hero, project lists and dialogs, skills, experience, services and contact. Share content, stable section IDs, component contracts and public CSS/JS. Test parity across Blade and the Node builder. Keep the existing Node-only build and CMS flow. If a visual design needs different structural wrappers, keep those in the relevant renderer while retaining a single copy of public content in each page.

Use a registry with stable IDs, displayed names, enabled flag and theme capability. Render the configured default in HTML and apply saved template and theme preferences with a small same-origin script loaded early in the head. Validate preferences against the same registry, fall back to defaults for stale IDs and let the page render normally when storage is unavailable. A labeled native selector exposes the active template and switches the layout by updating root data attributes. Keep selection off URLs and preserve anchor targets, focus, metadata and document reading order.

| Template | Presentation |
| --- | --- |
| Modern Developer | Default. Strong technical hierarchy, featured cards, subtle motion, light and dark themes. |
| Minimal Professional | Spacious corporate layout with prominent experience and readable case studies. |
| Creative Developer | Expressive gradients, responsive project showcase and restrained, optional motion. |
| Terminal Developer | Monospace accents and command-line visual cues with conventional keyboard navigation and readable body copy. |

## Compatibility and risks

- Two independent renderers can drift. Add contract and output parity checks, including old snapshots, optional fields, links, assets and section IDs.
- Browser storage may be blocked or stale. Test corrupt and disabled IDs, no JavaScript and CSS/JS load timing. Avoid CSS reordering that separates visual and keyboard order.
- CSP and deployment policy affect early script loading, JSON-LD, assets and telemetry. Use same-origin scripts, escape content and verify production headers before release.
- Exports and generated assets must preserve CMS uploads and credentials boundaries. Keep publication allowlisted and test failure and retry behavior. Tests should use temporary workspaces and fixtures because the current suite writes assets and build output.
- Both public templates omit project website links even though project data stores URLs. Restore validated external links as part of the component refactor and cover both renderers. The seed catalog contains 22 entries and the checked-in snapshot contains 19. Confirm the intended gallery membership before changing it.
- Client listings contain mostly site descriptions. Confirm which projects can carry specific contributions, screenshots or outcomes before publishing.
- Benchmark motion and mobile rendering before tuning. Historical Lighthouse reports are reference material, and current Core Web Vitals require fresh measurement.
- Review framework and build dependency lifecycle before deployment. Schedule upgrades separately if they are needed so the template rollout remains reviewable.
