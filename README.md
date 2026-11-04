# Archell Enaya Portfolio & CMS

Laravel portfolio with a database-backed public site and a session-authenticated CMS.

## Local setup

The local site now uses HTTPS and is bound to loopback. MySQL and Redis are available only inside the Docker network. Database passwords are generated secrets in the repository-root `.env`; the existing database accounts have been rotated to match. Do not replace that file with the example on an existing installation.

For a fresh checkout, copy the root `.env.example` to `.env` and set two different strong random database passwords. Generate local TLS files before starting Nginx:

```sh
mkdir -p docker/nginx/certs
openssl req -x509 -newkey rsa:3072 -sha256 -nodes -days 365 -keyout docker/nginx/certs/localhost.key -out docker/nginx/certs/localhost.crt -subj '/CN=localhost' -addext 'subjectAltName=DNS:localhost,IP:127.0.0.1'
chmod 600 docker/nginx/certs/localhost.key
```

Trust `docker/nginx/certs/localhost.crt` in your local browser/OS certificate store. It is a self-signed development certificate, not a public CA certificate. HTTP redirects to HTTPS. The certificate expires after one year; regenerate it before expiry and trust the replacement. For public deployment, use the real domain and a CA-issued certificate, adjust the Nginx hostname/redirect/certificate settings and host bindings, and update `APP_URL`. Keep secure cookies and debug protection enabled.

From the repository root:

```sh
docker compose up -d --build
docker compose exec app php artisan storage:link
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --class=PortfolioSeeder --force
docker compose exec app php artisan cms:admin your-email@example.com
```

The administrator command asks for a password of at least 12 characters. It never modifies an existing account. There is no public registration. Only users explicitly marked `is_admin` can access the CMS.

- Public site: https://localhost
- CMS: https://localhost/admin
- Local email inbox: http://localhost:8025

Sign-in requires your password followed by a six-digit email code. Codes expire after 10 minutes, work once, and allow five guesses per challenge. Resends require a 60-second wait, with at most three sends per administrator in 10 minutes. Codes are stored as hashes and tied to the browser session and current password. Existing sessions must sign in again to complete verification. Remember-me login is disabled so returning sessions cannot bypass the code.

Local sign-in codes arrive in Mailpit at http://localhost:8025. For delivery to a real email inbox, configure production SMTP instead of Mailpit.

Use **Forgot password** on the sign-in screen. Local reset emails arrive in Mailpit, not an external mailbox. Tokens expire after 60 minutes and can only be used once. Login and reset requests are rate limited. Configure a real SMTP provider and correct `APP_URL` for production, and replace the development mail settings in Compose. HTTPS, secure cookies and `APP_DEBUG=false` are already enabled in Compose.

## Content structure

| Table | Content |
| --- | --- |
| profiles | Name, role, biography, contact/social details, SEO text, image and CV paths, section introductions |
| expertise | Ordered expertise labels |
| skills | Ordered skill categories and descriptions |
| experiences | Role, employer, period and responsibility bullets |
| education | Qualification, institution, period and study bullets |
| projects | Title, category, description, tags and image |
| users | Administrator credentials and access flag |
| login_codes | Hashed, session-bound sign-in codes, expiration and attempt counts |
| password_reset_tokens | Hashed, expiring password reset tokens |

The CMS supports creating, editing, ordering and deleting list content. The single profile can be edited but cannot be deleted. Text is escaped when rendered. Use one line per experience/education bullet and commas between project tags. Upload profile photos, high-resolution profile photos and project images directly from their edit forms (JPG, PNG, WebP or GIF; up to 8 MB each). Upload the resume as a PDF up to 10 MB. Leave file fields empty to retain existing files. Uploads use randomized filenames on the public storage disk; database records store their relative paths. Previous files are retained when replaced to avoid breaking shared references. Saved content is immediately reflected on the public site.

The portfolio seeder imports the resume-based content only into empty tables, leaving existing populated sections untouched. Do not run it to restore deliberately emptied sections unless that is intended. Migrations add tables without deleting existing data; the user migration adds an admin flag that defaults to false.

## Tests

```sh
docker compose exec app php artisan test
```

Feature tests use an isolated in-memory SQLite database; they do not modify the local MySQL content. Coverage includes admin authorization, login/logout, rate limits, password reset and token reuse, content CRUD/validation, public content rendering, and CV downloads.

## Static JSON content

The current public content is exported to `resources/data/portfolio.json`. It contains profiles, expertise, skills, experience, education and projects, including their display order and asset paths. Users, passwords, login codes, sessions and password-reset tokens are excluded.

Set the following in `site/.env`:

```dotenv
ISSTATIC=TRUE
```

`TRUE` serves the public homepage and CV download from JSON without querying the database. `FALSE` (the default) loads live database content. The CMS, login, email codes and password resets continue to use the database in either mode. Missing or malformed JSON returns HTTP 503 in static mode; it does not silently fall back to the database.

After changing the setting, clear cached configuration:

```sh
docker compose exec app php artisan config:clear
```

Refresh the snapshot after CMS edits with:

```sh
docker compose exec app php artisan portfolio:export
```

The command always exports the current database content, even when `ISSTATIC=TRUE`, and atomically replaces the JSON file. An empty profile table leaves the previous snapshot untouched. Uploaded files are referenced by their paths rather than embedded in JSON; retain the corresponding `public/` and `storage/app/public/` assets when deploying the snapshot. CMS changes appear in static mode after re-exporting.

## Vercel: public static portfolio

The static build renders `resources/data/portfolio.json` into a standalone HTML portfolio and copies its CSS, JavaScript, images and resume into `dist/`. It uses Node built-ins only: PHP, Composer, database access, and npm dependency installation are not required on Vercel. The `ISSTATIC` setting controls Laravel; the Vercel build always uses JSON.

Vercel settings (configuration files are included at both repository and `site/` level):

| Setting | Value |
| --- | --- |
| Root Directory | Repository root, or `site` if that is your existing project setting |
| Framework Preset | Other |
| Build Command | `npm run build` |
| Output Directory | `dist` |
| Install Command | Disabled/empty; no build dependencies are needed |

Redeploy after committing these changes. Do not set the output directory to `public/build`: that contains Laravel's Vite assets rather than the complete static site. The configuration maps `/download-cv` to the exported resume PDF and supplies download/security headers. Unknown routes, including `/admin`, remain 404 on the static deployment. Keep the CMS on the PHP host; no CMS credentials, database configuration, or Laravel application key are needed in Vercel.

After editing content in the CMS:

```sh
# Run from the repository root.
docker compose exec app php artisan portfolio:export
npm run static:stage
npm run build
npm run test:static
```

Commit `site/resources/data/portfolio.json` and any referenced upload copies created in `site/resources/data/assets/`, then redeploy. The staging command copies only referenced CMS photos and resume files into that deployment asset directory. The build fails if any required asset is missing. Existing files in `site/public` remain the source for built-in assets. Generated `dist/` folders are ignored; Vercel regenerates them.

Local verification from the repository root:

```sh
npm run build
npm run test:static
```

`npm --prefix site run build` also builds the site using `site/` as the project root. `npm --prefix site run build:assets` retains the original Vite build for Laravel development.

The static HTML template is `resources/views/static/index.html`. It preserves the current portfolio markup, CSS classes and modal structure; update it alongside the Laravel Blade views when changing the layout. Template substitutions escape HTML and do not execute code. JSON remains the shared source for content in both rendering paths.

Configuration reference: https://vercel.com/docs/project-configuration/vercel-json

### Rebuild from the local CMS

Open `https://localhost/admin` and click **Publish Static** after saving content. The authenticated, email-verified action exports current database content to JSON, stages referenced CMS uploads, and rebuilds both repository-root `dist/` and `site/dist/`. It displays success or an error on the dashboard; builds are rate limited and concurrent builds are blocked.

Docker includes Node and mounts the root output directory at `/var/www/dist`. The entrypoint grants the web-server user write access only to the generated content and output directories needed for this feature. Rebuild the app image after pulling the updated entrypoint (`docker compose up -d --build app`) on another installation.

This button prepares local deployment files. Commit the updated JSON and staged assets and push/redeploy to update Vercel; it does not publish or push commits automatically. The generated dist directories remain ignored by Git because Vercel builds them again from the committed source.
