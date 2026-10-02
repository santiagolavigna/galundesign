---
name: backend-php
description: Vanilla PHP 8.2 + MySQL backend patterns - PDO connections, AJAX JSON endpoints, sessions, CSRF, admin auth, project structure. Use when writing or modifying PHP code in non-Laravel projects.
---

# Backend PHP Vanilla (8.2 + MySQL)

Reference patterns proven in TRACKSUITARG, VISAHUNTER and INVID. Follow them exactly in
non-Laravel projects.

## Project structure

```
PROJECT/
├── index.php, catalogo.php, ...      <- public pages
├── includes/
│   ├── db.php                        <- PDO connection (singleton)
│   ├── init.php                      <- session_start, helpers (e(), csrf), config load
│   └── csrf.php                      <- token generation/validation
├── api/                              <- AJAX JSON endpoints
├── admin/
│   ├── _auth.php                     <- session guard, required at top of every admin page
│   ├── login.php, dashboard.php, ...
├── assets/  (css/, js/, img/)
├── sql/schema.sql                    <- schema without client data
├── config.example.php                <- committed template
├── config.local.php                  <- real credentials, GITIGNORED
└── project.md
```

## Database connection (includes/db.php)

```php
<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $config = require __DIR__ . '/../config.local.php';
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}
```

## Queries — prepared statements ALWAYS

```php
// CORRECT
$stmt = db()->prepare('SELECT * FROM products WHERE brand_id = ? AND active = 1 ORDER BY name LIMIT ?');
$stmt->execute([$brandId, $limit]);
$products = $stmt->fetchAll();

// WRONG — never do this, even with "safe" values
$rows = db()->query("SELECT * FROM products WHERE brand_id = $brandId");
```

Dynamic ORDER BY / column names cannot be bound — whitelist them:

```php
$allowed = ['name', 'price', 'created_at'];
$orderBy = in_array($_GET['sort'] ?? '', $allowed, true) ? $_GET['sort'] : 'name';
```

## AJAX JSON endpoint pattern (api/*.php)

```php
<?php
declare(strict_types=1);
require __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
        exit;
    }
    csrf_validate();                       // dies with 403 JSON if invalid

    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $qty  = filter_var($data['qty'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($qty === false || $qty === null) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Cantidad inválida']);
        exit;
    }

    // ... business logic with prepared statements ...

    echo json_encode(['ok' => true, 'data' => $result]);
} catch (Throwable $e) {
    error_log($e->getMessage());           // log detail server-side
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno']);  // generic to client
}
```

Rules: always JSON in AND out, correct HTTP status codes (422 validation, 401 auth, 403 CSRF,
405 method, 500 server), user-facing error strings in Spanish, never leak exception messages.

## CSRF (includes/csrf.php)

```php
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_validate(): void
{
    $sent = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Sesión expirada, recargá la página']);
        exit;
    }
}
```

Forms: `<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">`.
Fetch: send header `X-CSRF-Token`.

## Output escaping

Define once in init.php and use everywhere:

```php
function e(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
```

`<?= e($product['name']) ?>` — no exceptions. For JSON embedded in HTML:
`<script>const data = <?= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>`.

## Admin auth (admin/_auth.php)

```php
<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
```

Login: `password_verify()` against `password_hash()` stored hash, then
`session_regenerate_id(true)`. Track failed attempts (per IP or username) and delay/block
after 5 failures. Logout destroys the session AND the cookie.

## Money and stock

- Store money as `DECIMAL(12,2)`, never FLOAT. In PHP compare with `bccomp` or integer cents.
- Stock/cash multi-step writes: `$pdo->beginTransaction()` + `SELECT ... FOR UPDATE`, commit/rollback in try/catch.
