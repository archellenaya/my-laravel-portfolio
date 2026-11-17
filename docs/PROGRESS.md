# Portfolio enhancement progress

## Phase 1 — Completed 18 November 2026

Audited the Laravel, CMS and static build paths, public templates, data and assets, Docker and Vercel configuration, and existing test coverage. Documented the target architecture and phase gates. No application changes, deployment, commits or pushes were made.

### Files created

- [PORTFOLIO_MASTER_SPEC.md](PORTFOLIO_MASTER_SPEC.md) inventories current features, establishes requirements and proposes the architecture.
- [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md) defines the seven phases and their exit criteria.
- [PROGRESS.md](PROGRESS.md) records the audit baseline and restart point.

### Decisions

Keep both delivery paths and the current CMS. Use a shared content contract and template registry with renderer-specific partials and shared public scripts and styles. Keep canonical metadata independent of browser preferences. Preserve existing client content and verify new claims and availability with the owner.

### Baseline verification

| Check | Result |
| --- | --- |
| `npm run test:static` | Passed in an isolated copy with the CMS asset fixtures. |
| `npm run build` | Passed in the isolated copy and produced `dist/`. |
| `php vendor/bin/phpunit` | 38 tests executed. 31 passed, six errored because local PHP lacks GD, and one failed because the rendered Blade homepage omits an expected project website link. The Docker PHP image installs GD, but the current session cannot access the Docker socket. |
| Public website | Browser fetch was unavailable and shell DNS resolution failed. Production response, responsive layouts, live links and analytics delivery remain unverified. |

The direct workspace static test run previously hit generated-output permissions. Use a temporary project copy with its own vendor directory for repeatable PHP and Node runs.

### Handoff from Phase 1

Phase 1 identified the Phase 2 starting points as `PortfolioContent`, the profile and project view models, the Blade homepage and the Node static builder. Add parity coverage and restore validated project website links in both layouts. Confirm the intentional differences between the 22-entry seeded catalog and 19-entry snapshot. Run the PHP suite with GD and verify deployed behavior when access is available.

Before the content phase, confirm remote and freelance availability and gather approved contributions and outcomes for featured case studies. Before release, verify analytics and fresh mobile, keyboard and performance baselines.

Suggested commit message

```text
docs: add portfolio audit and enhancement roadmap
```


## Phase 2 — Completed 18 November 2026

Both renderers now use a versioned content contract in `config/portfolio-contract.json`. CMS-managed records still originate in the database, the CMS field map controls what SQL exports, and static builds read the exported snapshot. Exports now write `schema_version: 1`. Older snapshots can omit the version, headshot, tool logos and project website URLs. The shared config also records public origin and defaults for optional sections and availability for later phases.

Extracted matching navigation, hero, about, skills, experience, projects and contact partials. Kept the existing public CSS and JavaScript, route paths, and each renderer's project dialog IDs. Validated HTTP(S) project links now appear inside dialogs. Laravel omits unsafe project URLs and the static builder preserves its existing behavior of rejecting snapshots with unsafe project URLs.

### Files changed

- `config/portfolio-contract.json` and `app/Services/PortfolioContract.php`
- `app/Services/PortfolioContent.php`, `app/Console/Commands/ExportPortfolio.php` and `app/Http/Controllers/HomeController.php`
- `resources/views/home_page.blade.php`, `resources/views/layouts/app.blade.php` and `resources/views/static/index.html`
- `resources/views/portfolio/` and `resources/views/static/sections/`, each with seven new partials
- `scripts/build-static.mjs`, `scripts/build-static.test.mjs` and `tests/Feature/StaticContentTest.php`
- `docs/IMPLEMENTATION_PLAN.md` and `docs/PROGRESS.md`

### Verification

| Check | Result |
| --- | --- |
| Node static tests | 9 passed, including legacy snapshots, escaped links, unsafe URLs, unsupported versions and staged CMS assets. |
| Production static build and asset staging | `npm run build` and `npm run static:stage` passed in a temporary copy. |
| Blade and static parity | Both rendered the same snapshot with matching section anchors, headings, project titles, live links, image paths and resume bytes. Dialog targets resolved within each page. |
| Focused PHPUnit | All 12 static-content, publishing and website project tests passed. The missing Blade project link regression is resolved. |
| Full PHPUnit | 38 passed with 380 assertions after loading GD and its libraries from a temporary directory. The unmodified suite ran in an isolated checkout. The host PHP installation requires GD for future in-place runs. |
| Lint | Laravel Pint passed for all five changed PHP files. Both Node scripts passed syntax checks, and `git diff --check` passed. |

Phase 3 is next and awaits owner instruction. Start with the template registry and browser preferences. The configured gallery membership and live deployment checks remain open as recorded in Phase 1.

Suggested commit message

```text
refactor: share portfolio content contract and public sections
```


## Phase 3 — Completed 18 November 2026

Both public renderers now read `template_preferences` from `config/portfolio-contract.json`. Each template has a stable ID, display name, enabled flag and theme capability. Modern Developer is the default. An unavailable configured default falls back to the first enabled entry. The four registered choices share the existing layout during this phase.

The labeled native selector is in the footer. Its selected value reflects the active template. A same-origin head script reads `portfolio.template` before styles load and applies the selected ID to the root element. The script handles unavailable storage and stale IDs, and the rendered default serves visitors with JavaScript disabled. The independent `portfolio.theme` preference accepts system, light or dark and applies to theme-capable templates. Theme controls and visual variations are scheduled for Phase 4. Both renderers emit the same canonical homepage URL regardless of selection.

### Files changed

- `config/portfolio-contract.json`, `app/Services/PortfolioContract.php` and `app/Http/Controllers/HomeController.php`
- `resources/views/layouts/app.blade.php`, `resources/views/portfolio/preferences.blade.php`, `resources/views/static/index.html` and `resources/views/static/sections/preferences.html`
- `public/js/portfolio-preferences.js`, `public/css/styles.css` and `scripts/build-static.mjs`
- `scripts/build-static.test.mjs` and `tests/Feature/StaticContentTest.php`
- `docs/IMPLEMENTATION_PLAN.md` and `docs/PROGRESS.md`

### Verification

| Check | Result |
| --- | --- |
| Node static tests | 11 passed, including blocked storage, unavailable IDs, persistence and template-scoped theme preferences. |
| PHP tests | 39 passed with 392 assertions in an isolated checkout using temporary GD. |
| Build and asset staging | `npm run build` and `npm run static:stage` passed in the isolated checkout. |
| Chromium smoke test | Static at 375 px and Blade at 1440 px passed keyboard selection, focus retention, reload persistence, JavaScript-disabled fallback, content and canonical URL checks. Requests were served from the generated files through browser interception. |
| Lint | Pint passed for the three changed PHP files. Node syntax checks and `git diff --check` passed. |

Phase 4 awaits owner instruction. Start with Modern Developer and its light and dark styles, then implement the remaining visual layouts using the root template and theme attributes. Production and cross-browser verification remain pending. A separate schema-version-only change to the checked-in portfolio snapshot appeared in the working tree during Phase 3 and is outside the phase changes.

Suggested commit message

```text
feat: add persistent portfolio template preferences
```
