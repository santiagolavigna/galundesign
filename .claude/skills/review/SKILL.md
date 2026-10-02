---
name: review
description: Pre-delivery code review checklist covering security, correctness, UX and documentation. Use before declaring any feature or project done, and when acting as the reviewer agent.
---

# Review — Pre-Delivery Checklist

Run this checklist over the diff (or whole project for final review) BEFORE telling the user
something is finished. Report findings in Spanish, ordered by severity, with file:line.

## 1. Security (blockers — any hit fails the review)

- [ ] No SQL with interpolated variables (grep patterns in `security` skill, section 1).
- [ ] All dynamic output escaped (`e()` / Blade `{{ }}`); no `echo $_GET/$_POST`.
- [ ] State-changing forms/endpoints validate CSRF.
- [ ] No credentials/API keys in tracked files; `.gitignore` covers `.env` / `config.local.php`.
- [ ] Admin pages/endpoints have the auth guard (test direct URL mentally).
- [ ] Client-posted IDs verified for ownership; totals recalculated server-side.

## 2. Correctness

- [ ] Money is DECIMAL end-to-end; no float arithmetic on prices.
- [ ] Multi-table writes are transactional; stock/cash reads use locks.
- [ ] Validation server-side (client-side JS validation is UX, not security).
- [ ] Error paths handled: DB down, empty result sets, invalid input => controlled Spanish
      message, correct HTTP status, no stack trace to the user.
- [ ] No PHP warnings/notices in the log while exercising the feature.

## 3. Frontend / UX

- [ ] Works at 375px width; touch targets adequate; no horizontal overflow.
- [ ] Loading, empty and error states exist (no blank screens, no frozen buttons).
- [ ] All user-facing text in correct Spanish (including validation and JS messages).
- [ ] Interactive elements have hover/focus states; destructive actions ask confirmation.
- [ ] Images optimized (WebP where possible, lazy below fold, width/height set).

## 4. Code quality

- [ ] New PHP files: `declare(strict_types=1)`, typed signatures.
- [ ] No copy-pasted blocks that should be a function/include/component (3+ repetitions).
- [ ] No dead code, commented-out blocks, or leftover `var_dump`/`console.log`/`dd()`.
- [ ] Names in English, consistent with the existing project conventions.
- [ ] Laravel: Pint + PHPStan + tests pass if configured.

## 5. Documentation & delivery

- [ ] `project.md` updated (new module/table/config documented).
- [ ] Schema changes have a migration file or `sql/updates/` script with rollback.
- [ ] New config keys added to `.env.example` / `config.example.php`.
- [ ] Smoke-test checklist updated and executed (`testing` skill).

## Output format

```
## Revisión — <feature/proyecto>
### Crítico   (bloquea entrega)
- archivo.php:42 — SQL concatenado con $_GET['id']. Fix: prepared statement.
### Alto / Medio / Bajo
- ...
### OK
- Resumen de lo que está bien.
Veredicto: APROBADO / APROBADO CON OBSERVACIONES / RECHAZADO
```
