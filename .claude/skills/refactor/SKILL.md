---
name: refactor
description: Modernize and clean up legacy PHP code safely without breaking working features - incremental strategy, extraction patterns, security upgrades. Use when improving existing project code.
---

# Refactor — Modernize Without Breaking

Many projects in htdocs are live client sites. "Working and ugly" beats "clean and broken".
Refactor in small, verifiable steps.

## Golden rules

1. Never refactor and change behavior in the same step. First make it safe/clean with
   identical behavior, verify, THEN add the feature.
2. One refactor type per commit ("extraer conexión a includes/db.php", "escapar salidas en catalogo.php").
3. Before touching a legacy file: ensure Git is initialized and the current state is committed.
   No Git => `git init && git add -A && git commit -m "estado inicial"` first.
4. After each step: run the smoke tests for the affected pages (see `testing` skill).

## Priority order for legacy PHP

Refactor in this order — security first, aesthetics last:

1. **SQLi**: replace concatenated queries with prepared statements (mechanical, low risk, huge win).
2. **XSS**: add `e()` helper and escape outputs page by page.
3. **Secrets**: move credentials to `config.local.php` + example file + .gitignore.
4. **Duplication**: extract repeated header/footer/DB code into `includes/`.
5. **Dead code**: delete commented-out blocks and unused files (they're in Git history now).
6. **Structure**: move AJAX handlers into `api/`, admin pages behind `admin/_auth.php`.
7. Style/naming — only when the above is done.

## Extraction pattern (duplicated page code)

When 3+ pages repeat the same block (DB connect, session start, helpers), extract to
`includes/init.php` and replace with a single `require`. Verify each page still renders
after replacing it — one page at a time, not all at once.

## Password hash migration (MD5/SHA1 found)

Don't reset all users. Transparent migration on login:

```php
if (password_verify($input, $user['password'])) {
    // already modern
} elseif (md5($input) === $user['password']) {
    $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([password_hash($input, PASSWORD_DEFAULT), $user['id']]);
} else {
    // fail
}
```

## When NOT to refactor

- Marzipano exported bundles (`app*.js`, viewer internals): treat as vendor code — extend via
  separate patch files (the `mars2-*.js` pattern), never edit the bundle.
- Anything you cannot test before delivery.
- Live client code the user didn't ask you to touch — propose it, don't do it silently.
