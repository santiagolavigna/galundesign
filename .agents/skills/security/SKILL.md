---
name: security
description: OWASP-based security for PHP/MySQL apps - SQL injection, XSS, CSRF, session hardening, file uploads, headers, audit procedure with concrete grep patterns. Use when auditing security or handling auth, uploads, payments or any sensitive data.
---

# Security — PHP/MySQL

Client projects handle real money, member data and admin panels exposed to the internet.
These checks are mandatory, not optional.

## 1. SQL Injection

- Prepared statements everywhere (PDO/MySQLi bind, or Eloquent). Dynamic column/direction
  names get whitelisted, never interpolated.
- Audit greps (run over the project, review every hit):

```
query\s*\(\s*["'].*\$          <- variables inside query strings
->exec\s*\(\s*["'].*\$
DB::raw|whereRaw|selectRaw     <- Laravel raw fragments: verify bindings are used
mysqli_query.*\$_(GET|POST|REQUEST)
```

## 2. XSS

- Escape ALL dynamic output: `e()` helper / `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`;
  Blade `{{ }}` is safe, `{!! !!}` requires justification.
- Data into inline JS: `json_encode($v, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)`.
- Audit greps:

```
echo\s+\$_(GET|POST|REQUEST|COOKIE|SERVER)
<\?=\s*\$(?!.*\be\()             <- short echo without e()
\{!!                             <- Blade unescaped
innerHTML\s*=                    <- JS sinks; prefer textContent
```

## 3. CSRF

- Every state-changing request (POST/PUT/DELETE, including fetch/AJAX) validates a session
  token with `hash_equals()`. Laravel: keep `VerifyCsrfToken` on; exemptions only for
  token-authenticated machine endpoints, documented in project.md.
- Session cookie: `SameSite=Lax` minimum (`session.cookie_samesite`).

## 4. Auth & sessions

- `password_hash()` / `password_verify()` only. Migrate any MD5/SHA1 hash on next login.
- After successful login: `session_regenerate_id(true)`.
- Rate limit logins: 5 failures => lockout/delay (per user and per IP). Log attempts.
- Cookies: `httponly = 1`, `secure = 1` in production, `samesite`.
- Admin guard at the top of EVERY admin file (`admin/_auth.php` pattern) — direct URL access
  to `admin/whatever.php` must redirect to login. Test it.
- Generic login error ("Usuario o contraseña incorrectos") — never reveal which one failed.

## 5. File uploads

- Whitelist extensions AND check real mime (`finfo_file`). Images: re-validate with
  `getimagesize()` or re-encode (GD/Imagick) to strip payloads.
- Random generated filename (`bin2hex(random_bytes(16)) . '.webp'`); never trust `$_FILES['name']`.
- Store outside docroot when possible; otherwise the upload dir gets
  `php_flag engine off` / "no PHP execution" via .htaccess or server config.
- Limit size in PHP (`upload_max_filesize`) and validate server-side.

## 6. Config & secrets

- Credentials only in `.env` / `config.local.php` (gitignored, with committed `*.example`).
- Never commit SQL dumps with client data; `sql/schema.sql` is structure + neutral seed only.
- Production: `display_errors = Off` (`APP_DEBUG=false`), errors to log. Verify this in every deploy.
- Remove/block test helpers before deploy: `update_db.php`, `phpinfo`, `test-*.php`, `adminer`.

## 7. Headers & transport (production)

```apache
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
# HTTPS forced (Hostinger/VPS): redirect 80 -> 443, then:
Header always set Strict-Transport-Security "max-age=31536000"
```

Also in .htaccess: deny access to `config.local.php`, `.env`, `sql/`, `project.md`, `.git`.

## 8. Business-logic checks (custom systems)

- Prices/totals ALWAYS recalculated server-side from DB values — never trust amounts posted
  from the client (cart totals, discounts).
- IDs from the client: verify ownership/permission before acting
  (`WHERE id = ? AND user_id = ?`), otherwise any user edits any record (IDOR).
- Stock/cash: transactions + row locks (see backend skills).

## Audit procedure (agent: security)

1. Run all greps from sections 1-2 plus: `password`, `md5(`, `sha1(`, `api_key`, hardcoded
   credentials in tracked files.
2. Walk every entry point: public pages, `api/*`, `admin/*` — check auth guard, CSRF,
   validation, escaping per endpoint.
3. Check `.gitignore` covers `.env`, `config.local.php`, dumps, `uploads/`.
4. Report findings by severity (crítico / alto / medio / bajo) with file:line and the fix,
   in Spanish. Fix criticals immediately; never leave a known SQLi/XSS "for later".
