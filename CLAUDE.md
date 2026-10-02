# AI Development Kit — Project Context

You are working on a project by Nuvola Services: custom administrative systems, gym platforms,
virtual tours (Marzipano), e-commerce and marketing websites. Built with PHP/MySQL on XAMPP
(Windows), deployed to Hostinger / VPS. UI language is ALWAYS Spanish; code identifiers in English.

## Project Stack

This is a **vanilla PHP 8.2 + MySQL** project (no framework). Primary skill: `backend-php`. Structure: shared code in `includes/` (db.php with PDO singleton, init.php, csrf.php), AJAX JSON endpoints in `api/`, admin behind `admin/_auth.php` guard. Credentials ONLY in `config.local.php` (gitignored, with committed `config.example.php`). New files start with `declare(strict_types=1)`. Keep `sql/schema.sql` current and document schema changes in `sql/updates/`.

Stack detection (if unsure): `artisan` file => Laravel 12 + Livewire. Marzipano assets
(`data.js` + tiles/, or marzipano in vendor/) => static virtual tour. Otherwise `.php` files
=> vanilla PHP + MySQL.

## Non-Negotiable Rules

Security (top priority — these prevent real incidents):

1. NEVER concatenate variables into SQL. Always prepared statements (PDO `prepare`/`execute`,
   MySQLi `bind_param`) or Eloquent/Query Builder. No exceptions, not even for "safe" values.
2. NEVER echo user input or DB values without escaping: `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')`
   (use the project's `e()` helper if it exists; Blade `{{ }}` already escapes).
3. Every state-changing form and AJAX endpoint requires CSRF protection (session token verified
   server-side; Laravel handles it via `@csrf` / middleware).
4. Passwords: `password_hash()` / `password_verify()` only. Never MD5/SHA1, never plaintext.
5. File uploads: validate extension whitelist AND mime type, rename with random name, store
   outside web root or block PHP execution in the upload dir.
6. Credentials/config live ONLY in `.env` (Laravel) or `config.local.php` (vanilla, gitignored,
   with a committed `config.local.example.php`). Never hardcode credentials in tracked files.
7. Admin areas: session auth check at the top of EVERY admin file/route, rate-limit logins,
   `session_regenerate_id(true)` after login.

Quality:

8. PHP 8.2+: `declare(strict_types=1)` in new vanilla PHP files; typed properties and return types.
9. AJAX endpoints return JSON with correct `Content-Type: application/json` and proper HTTP status
   codes; never mix HTML error output into JSON responses.
10. Multi-step DB writes (sales, stock, cash movements) go inside a transaction
    (`DB::transaction()` or PDO `beginTransaction`), with row locks when money/stock is involved.
11. Every project keeps a `project.md` (source of truth: what it does, modules, DB schema,
    credentials location, deploy notes). Update it when you add or change modules.
12. Mobile-first responsive UI. Test layouts mentally at 375px before desktop.
13. No Tailwind CDN in production for app projects — use a build (Vite) or plain CSS.
    CDN is tolerated only for small static marketing sites.

## Workflow

For any non-trivial feature (touches DB, money, auth, or 3+ files):

0. New client / PDF / specs: slash `/comercial` first (fills `project.md`, no code).
1. PLAN: slash `/arquitecto`. Full audit: `/orquestador`. Public UI look: `/diseno` or
   `/orquestador-visual`. For a single feature, also fill `docs/templates/feature.md`.
2. IMPLEMENT: `/backend` and/or `/frontend` (or `/implementar` for approved P0 items).
3. SELF-REVIEW: `/reviewer` (and `/security` if auth/money/uploads). `/qa` for flows.
4. Close the session: `/cerrar`. Persist work: `/commit` (add `y push` only if you want remote).
   Production: `/deploy` first (asks hosting/credentials location); upload only after you confirm.

Never invent business rules — if a requirement is ambiguous (prices, permissions, stock rules),
ask the user instead of guessing.

## Skills

Consult the matching skill BEFORE working on that area (in `.claude/skills/` / `.agents/skills/`):

| Area | Skill |
|------|-------|
| Kickoff from client PDF / briefing / specs | `comercial` |
| Vanilla PHP backend, AJAX endpoints, sessions | `backend-php` |
| Laravel 12 + Livewire 4 backend | `backend-laravel` |
| UI/UX, modern design, Tailwind, GSAP, responsive | `frontend` |
| Design system before markup (UI UX Pro Max bridge) | `design-intelligence` |
| Vue 3 + TypeScript SPA/PWA (stores, API modules) | `frontend-vue` |
| New project setup, folder structure, stack choice | `architecture` |
| MySQL schema, indexes, migrations, backups | `database` |
| Security audit and hardening | `security` |
| Tests (Pest/PHPUnit) and manual smoke tests | `testing` |
| Modernizing/cleaning legacy code safely | `refactor` |
| Pre-delivery code review checklist | `review` |
| Marzipano virtual tours, hotspots, tiles | `virtual-tours` |
| project.md, README, DEPLOY.md | `documentation` |
| XAMPP to Hostinger/VPS deploys | `deployment` |

## Agents / Roles

Specialized roles in `.claude/agents/` (Claude Code subagents). In Cursor, adopt the role by
reading the file when the task matches:

- `orchestrator` — director: full-project audit (including visual differentiation); no code until you OK.
- `architect` — design before code: stack, structure, DB schema.
- `senior-backend` — PHP/Laravel/MySQL implementation.
- `senior-frontend` — modern, innovative UI with solid UX.
- `qa` — test full flows, edge cases, invalid input.
- `reviewer` — reviews diffs against the skills before sign-off.
- `security` — audits for SQLi/XSS/CSRF/session/upload issues.

## Environment

- Windows + XAMPP (Apache + MySQL at `localhost`, docroot `C:\xampp\htdocs`), PowerShell.
- PHP 8.2 CLI, Composer 2, Node 22, Git available.
- MySQL user `root` with no password in local dev (NEVER in production).
- Test locally at `http://localhost/<PROJECT-FOLDER>/` (vanilla) or `php artisan serve` (Laravel).

## Templates

Under `docs/templates/`: `feature.md` (feature spec), `api.md` (endpoint contract),
`migration.md` (schema change + rollback), `project.md` (project source of truth),
`deploy.md` (deploy checklist). All in Spanish — fill them in Spanish.
