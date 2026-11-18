# Portfolio implementation plan

Advance one phase at a time with owner direction. Keep the Laravel and Vercel paths deployable after each phase. Record files, tests, open issues and a Conventional Commit suggestion in [progress](PROGRESS.md).

## Phase 1 — Repository audit and planning

Inventory public and CMS features, rendering and data ownership, deployment, local baseline checks and known risks. Deliver the [master specification](PORTFOLIO_MASTER_SPEC.md), this plan and the progress log. Leave runtime behavior untouched.

## Phase 2 — Data and component refactoring (completed)

- [x] Map database fields, snapshot keys, seeders, and renderer-specific transformations. Add shared configuration and define a backward-compatible contract for public data and optional sections.
- [x] Extract matching Blade and Node partials. Keep shared frontend behavior, section IDs and URL conventions, and use prepared project image URLs.
- [x] Restore validated project website links. Verify legacy snapshots, CMS export, staged uploads, dialog targets, navigation and resume parity. Confirm that the legacy `Home.blade.php` has no application route and retain it for this phase.
- [x] Run the full PHP regression suite with GD, including CMS image uploads and tool-logo editing.

Gate: existing routes, rendered content, static build and CMS behavior pass parity and regression checks.

## Phase 3 — Template switching foundation (completed)

- [x] Add the registry, configurable default and enabled list. Expose the available choices in both renderers.
- [x] Render a labeled selector and apply localStorage preferences via a same-origin early script. Keep theme separate and scoped to templates that support it.
- [x] Test refresh, blocked storage, invalid and disabled IDs, no JavaScript, focus retention, shared content and stable canonical URL.

Gate: instant selection and reliable fallback on both delivery paths.

## Phase 4 — Initial templates (completed)

- [x] Implement Modern Developer and its light and dark variants.
- [x] Implement Minimal Professional, Creative Developer and Terminal Developer using the shared rendered sections and documented per-template styling.
- [x] Check all designs at representative widths of 375, 768, 1024 and 1440 CSS pixels, including navigation, project interactions, text resizing, focus, contrast and reduced motion.

Gate: all four templates present the same content and preserve reading order and usable navigation.

## Phase 5 — Content improvements (awaiting owner review)

- [x] Update the shared hero, organize CMS skills by category and learning status, and clarify experience using existing responsibilities.
- [x] Preserve the client gallery, add six configured services and contact CTAs, and keep optional links conditional.
- [x] Add approval-gated rendering for technical case studies and client gallery contributions.
- [x] Confirm and enable both remote employment and freelance project availability badges.
- [x] Add case-study drafts and gallery approval controls to the existing authenticated CMS.
- [ ] Review the property synchronization, commerce integration, and headless Drupal drafts in the CMS. Approve only the studies and gallery details cleared for public use, or leave them as drafts.

Gate: owner-reviewed claims and confidentiality, working resume and contact actions, and preserved client listings.

## Phase 6 — SEO, accessibility and performance

1. Publish a single configured canonical origin, title, description, Open Graph and X cards, JSON-LD, sitemap and updated robots references in both delivery paths. Verify rendered markup and CSP.
2. Audit keyboard and screen reader flows, contrast, menu and dialog focus, responsive layout and reduced motion.
3. Measure the default and alternate templates on representative mobile and desktop profiles. Verify WebP fallbacks and responsive candidates, right-size gallery assets, review fonts and particle cost, and record Lighthouse and available field metrics.
4. Verify the existing analytics path and project telemetry. Add only needed events for resume, project views, contact CTAs, socials and template choice using stable non-personal identifiers. Check duplicate events, privacy requirements, and whether Speed Insights is available and appropriate.

Gate: metadata parity, accessibility checks and recorded performance comparisons with analytics verified where enabled.

## Phase 7 — Testing and deployment readiness

1. Run PHPUnit and Node tests in isolated workspaces, plus a production static build and Laravel smoke checks. Verify internal links, sampled external links, asset existence, resume downloads, CMS export, URLs and headers.
2. Run the browser matrix for both public delivery paths. Check template selection persistence, light and dark themes, keyboard navigation, responsive overflow and visual regressions.
3. Document content ownership, template registration, configuration, export and staging, verification and rollback. Prepare a deployment checklist for owner review.

Gate: results and open issues are documented. Deployment, commits and pushes require an explicit request.
