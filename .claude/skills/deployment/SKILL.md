---
name: deployment
description: Deploying from XAMPP/Windows to Hostinger shared hosting or VPS - checklists, config differences, DB migration, post-deploy verification. Use when preparing or executing a deploy.
---

# Deployment — XAMPP to Hostinger / VPS

## Credentials inventory (before any upload)

Never put production passwords, FTP keys or API secrets in `project.md`, git, or chat logs
if you can avoid it. Document **where they live**, not the values.

In `project.md` under Credenciales / Deploys, keep only:

- Hosting type: Hostinger shared / VPS (Hostinger, DigitalOcean, AWS Lightsail, etc.) / other
- How files get there: FTP, hPanel, `git pull` over SSH, rsync, GitHub Actions
- How the DB is reached: phpMyAdmin, SSH + mysql
- Where secrets are stored: password manager, hosting panel, `~/.ssh/`, `.env` **on the server**

If any of that is missing, STOP and ask the user (Spanish):

1. ¿Dónde está (o va a estar) producción? Hostinger, VPS, otro cloud — cuál.
2. ¿Cómo se suben archivos? FTP / git / panel.
3. ¿Dónde guardás usuario/clave/SSH? (gestor, panel — no las pegues acá si no hace falta).
4. If they need a new provider: recommend the simplest that fits (shared Hostinger for
   vanilla PHP/sites; VPS with SSH for Laravel). Do not lock the kit to one vendor.

Only execute upload/SSH/migrate when the user explicitly confirms **this** deploy
("sí, subí", "deploy ahora") AND backup of production DB is done or explicitly skipped
by the user with a reason.

## Pre-deploy checklist (any stack)

- [ ] Smoke tests / test suite pass locally (see `testing` skill).
- [ ] `review` skill checklist executed — no critical findings.
- [ ] Production config ready: `.env.production` values / `config.local.php` for the server
      (real DB credentials, `display_errors Off`, `APP_DEBUG=false`, `APP_ENV=production`).
- [ ] Frontend built: `npm run build` (Vite) — deploy `public/build`, never rely on CDN dev mode.
- [ ] Composer: `composer install --no-dev --optimize-autoloader` (on server or upload vendor).
- [ ] Backup current production: files + `mysqldump` of the DB. NO deploy without backup.
- [ ] Excluded from upload: `.git`, `node_modules`, tests, local dumps, `.env` local,
      helper scripts (`update_db.php`, `test-*.php`, `generate_hash.php`).

## Hostinger shared hosting (typical for vanilla PHP sites)

1. Files via hPanel File Manager or FTP into `public_html/` (or subdomain folder).
2. Create DB + user in hPanel MySQL; import `sql/schema.sql` (+ incremental
   `sql/updates/*.sql` in order) via phpMyAdmin.
3. Create `config.local.php` on the server with production credentials (never upload your local one).
4. PHP version in hPanel >= 8.2 to match local.
5. Force HTTPS + security headers via `.htaccess` (see `security` skill section 7); deny
   access to `config.local.php`, `sql/`, `project.md`.

## VPS (Laravel systems — pattern from SISTEMA-GYM/GESTION Docker setup)

Follow the project's own `DEPLOY.md` (these projects ship one). Standard sequence:

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force        # after DB backup
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart          # if queues are used
```

## Local vs production differences that bite

| Local (XAMPP) | Production |
|---|---|
| `root` sin contraseña | dedicated DB user, strong password, least privilege |
| `http://localhost/PROYECTO/` subfolder paths | domain root — use relative paths or a BASE_URL config, never hardcode `/PROYECTO/` |
| Windows case-insensitive filenames | Linux case-SENSITIVE — `img/Logo.png` != `img/logo.png`; verify include/asset casing |
| `display_errors=On` | Off + error log |
| `mail()` doesn't send | configure SMTP of the hosting |

## Post-deploy verification (in production, always)

- [ ] Home and 2-3 key pages load without errors (view source: no warnings/paths leaked).
- [ ] One full critical flow: login admin, create test record, delete it. E-commerce: add to
      cart => WhatsApp message correct.
- [ ] Forms send (test a real lead/contact submission).
- [ ] HTTPS forced; `http://` redirects.
- [ ] `config.local.php` / `.env` NOT downloadable via browser.
- [ ] Mobile check of home + main flow on a real phone.
- [ ] Update DEPLOY.md with anything new learned; note the deploy date in project.md.
