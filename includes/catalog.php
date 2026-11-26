<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/uploads.php';

const FLYER_PREVIEW_MAX = 8388608;
const FLYER_SOURCE_MAX = 41943040;

function money_parse(string $raw, bool $allowZero = false): ?string
{
    $raw = str_replace(',', '.', trim($raw));
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $raw)) {
        return null;
    }
    if (str_contains($raw, '.')) {
        [$whole, $cents] = explode('.', $raw, 2);
        $raw = $whole . '.' . str_pad($cents, 2, '0');
    } else {
        $raw .= '.00';
    }
    if (!$allowZero && $raw === '0.00') {
        return null;
    }

    return $raw;
}

function money_label(string $amount): string
{
    $negative = str_starts_with($amount, '-');
    $amount = ltrim($amount, '-');
    [$whole, $cents] = array_pad(explode('.', $amount, 2), 2, '00');
    $whole = number_format((int) $whole, 0, ',', '.');
    $text = $cents === '00' ? $whole : $whole . ',' . substr(str_pad($cents, 2, '0'), 0, 2);

    return ($negative ? '-' : '') . '$ ' . $text;
}

function site_url(string $path = ''): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $base = rtrim((string) (app_config()['base_path'] ?? ''), '/');

    return ($https ? 'https' : 'http') . '://' . $host . $base . '/' . ltrim($path, '/');
}

function brand_name(): string
{
    try {
        $name = db()->query('SELECT brand_name FROM settings WHERE id = 1')->fetchColumn();
    } catch (Throwable) {
        return 'Galun Design';
    }

    return is_string($name) && $name !== '' ? $name : 'Galun Design';
}

function published_flyers(): array
{
    $stmt = db()->query(
        'SELECT id, title, preview_path, price, currency, sort_order
         FROM flyers WHERE is_published = 1 ORDER BY sort_order ASC, id DESC'
    );

    return $stmt->fetchAll();
}

function find_flyer(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM flyers WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

function all_flyers(): array
{
    return db()->query('SELECT * FROM flyers ORDER BY sort_order ASC, id DESC')->fetchAll();
}

function monthly_plan(): array
{
    $stmt = db()->prepare('SELECT * FROM plans WHERE code = ?');
    $stmt->execute(['monthly']);
    $row = $stmt->fetch();
    if ($row === false) {
        throw new RuntimeException('Falta el plan monthly.');
    }

    return $row;
}

function save_plan_price(string $price): void
{
    $current = monthly_plan();
    if ((string) $current['price'] === $price) {
        return;
    }
    $stmt = db()->prepare('UPDATE plans SET price = ?, mp_plan_id = NULL WHERE id = ?');
    $stmt->execute([$price, (int) $current['id']]);
}

function watermark_preview(string $tmp): ?string
{
    $info = getimagesize($tmp);
    if ($info === false) {
        return null;
    }
    $mime = (string) ($info['mime'] ?? '');
    $img = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($tmp),
        'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($tmp) : false,
        default => false,
    };
    if ($img === false) {
        return null;
    }

    $width = imagesx($img);
    $height = imagesy($img);
    $bar = imagecolorallocatealpha($img, 7, 7, 8, 60);
    imagefilledrectangle($img, 0, (int) ($height * 0.38), $width, (int) ($height * 0.62), $bar);
    $stamp = imagecreatetruecolor(96, 18);
    $stampBg = imagecolorallocate($stamp, 7, 7, 8);
    $stampFg = imagecolorallocate($stamp, 243, 239, 230);
    imagefilledrectangle($stamp, 0, 0, 96, 18, $stampBg);
    imagestring($stamp, 5, 12, 2, 'MUESTRA', $stampFg);
    $stampW = max(96, (int) ($width * 0.55));
    $stampH = max(18, (int) ($stampW * 18 / 96));
    imagecopyresampled(
        $img,
        $stamp,
        (int) (($width - $stampW) / 2),
        (int) (($height - $stampH) / 2),
        0,
        0,
        $stampW,
        $stampH,
        96,
        18
    );
    imagedestroy($stamp);

    $dir = dirname(__DIR__) . '/uploads/flyers/previews';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        imagedestroy($img);
        return null;
    }
    $name = bin2hex(random_bytes(16)) . '.jpg';
    $ok = imagejpeg($img, $dir . '/' . $name, 82);
    imagedestroy($img);

    return $ok ? 'uploads/flyers/previews/' . $name : null;
}

function accept_psd(array $file): ?string
{
    $problem = upload_error($file);
    if ($problem !== null) {
        return $problem;
    }
    if ((int) ($file['size'] ?? 0) > FLYER_SOURCE_MAX) {
        return 'El PSD supera los 40 MB.';
    }
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'psd') {
        return 'El archivo tiene que ser PSD.';
    }
    $head = file_get_contents((string) $file['tmp_name'], false, null, 0, 4);
    if ($head !== '8BPS') {
        return 'El archivo no es un PSD de Photoshop.';
    }

    return null;
}

function buyer_id(): int
{
    return (int) ($_SESSION['buyer_id'] ?? 0);
}

function current_buyer(): ?array
{
    $id = buyer_id();
    if ($id < 1) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, email, name FROM buyers WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

function login_is_blocked(string $ip): bool
{
    $count = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > (NOW() - INTERVAL 15 MINUTE)'
    );
    $count->execute([$ip]);

    return (int) $count->fetchColumn() >= 5;
}

function register_buyer(string $name, string $email, string $password): string
{
    if ($name === '' || mb_strlen($name) > 120) {
        return 'El nombre es obligatorio.';
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 190) {
        return 'El email no es válido.';
    }
    if (strlen($password) < 8) {
        return 'La clave necesita al menos 8 caracteres.';
    }
    $stmt = db()->prepare('SELECT id FROM buyers WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch() !== false) {
        return 'Ese email ya tiene cuenta.';
    }
    $insert = db()->prepare('INSERT INTO buyers (email, password_hash, name) VALUES (?, ?, ?)');
    $insert->execute([$email, password_hash($password, PASSWORD_DEFAULT), $name]);
    session_regenerate_id(true);
    $_SESSION['buyer_id'] = (int) db()->lastInsertId();

    return '';
}

function login_buyer(string $email, string $password): string
{
    $ip = client_ip();
    if (login_is_blocked($ip)) {
        return 'Demasiados intentos. Probá de nuevo en unos minutos.';
    }
    $stmt = db()->prepare('SELECT id, password_hash FROM buyers WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row === false || !password_verify($password, (string) $row['password_hash'])) {
        db()->prepare('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, NOW())')->execute([$ip]);
        return 'Email o clave incorrectos.';
    }
    db()->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
    session_regenerate_id(true);
    $_SESSION['buyer_id'] = (int) $row['id'];

    return '';
}

function membership_open(int $buyerId): bool
{
    $stmt = db()->prepare(
        'SELECT id FROM subscriptions
         WHERE buyer_id = ? AND current_period_end > NOW()
           AND status IN (\'active\', \'cancelled\', \'paused\')
         LIMIT 1'
    );
    $stmt->execute([$buyerId]);

    return $stmt->fetch() !== false;
}

function buyer_can_download(int $buyerId, array $flyer): bool
{
    $paid = db()->prepare(
        'SELECT oi.id FROM order_items oi
         INNER JOIN orders o ON o.id = oi.order_id
         WHERE o.buyer_id = ? AND oi.flyer_id = ? AND o.status = \'paid\'
         LIMIT 1'
    );
    $paid->execute([$buyerId, (int) $flyer['id']]);
    if ($paid->fetch() !== false) {
        return true;
    }

    return (int) $flyer['is_published'] === 1 && membership_open($buyerId);
}

function create_flyer_order(int $buyerId, int $flyerId): array|string
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'SELECT id, title, price, currency, is_published FROM flyers WHERE id = ? FOR UPDATE'
        );
        $stmt->execute([$flyerId]);
        $flyer = $stmt->fetch();
        if ($flyer === false || (int) $flyer['is_published'] !== 1) {
            $pdo->rollBack();
            return 'Ese flyer no está disponible.';
        }
        if ((string) $flyer['price'] === '0.00') {
            $pdo->rollBack();
            return 'Ese flyer todavía no tiene precio.';
        }
        $order = $pdo->prepare(
            'INSERT INTO orders (buyer_id, status, total, currency) VALUES (?, \'pending\', ?, ?)'
        );
        $order->execute([$buyerId, $flyer['price'], $flyer['currency']]);
        $orderId = (int) $pdo->lastInsertId();
        $item = $pdo->prepare(
            'INSERT INTO order_items (order_id, flyer_id, title, unit_price) VALUES (?, ?, ?, ?)'
        );
        $item->execute([$orderId, (int) $flyer['id'], $flyer['title'], $flyer['price']]);
        $pdo->commit();

        return ['id' => $orderId, 'title' => (string) $flyer['title'], 'price' => (string) $flyer['price']];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        app_log('order-exception', ['message' => $e->getMessage()]);
        return 'No se pudo iniciar la compra.';
    }
}

function create_subscription_row(int $buyerId): array|string
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $open = $pdo->prepare(
            'SELECT id FROM subscriptions
             WHERE buyer_id = ? AND current_period_end > NOW()
               AND status IN (\'active\', \'cancelled\', \'paused\')
             LIMIT 1 FOR UPDATE'
        );
        $open->execute([$buyerId]);
        if ($open->fetch() !== false) {
            $pdo->rollBack();
            return 'La membresía ya está activa.';
        }
        $plan = monthly_plan();
        if ((string) $plan['price'] === '0.00') {
            $pdo->rollBack();
            return 'La membresía todavía no tiene precio.';
        }
        $insert = $pdo->prepare(
            'INSERT INTO subscriptions (buyer_id, plan_id, status, price, currency) VALUES (?, ?, \'pending\', ?, ?)'
        );
        $insert->execute([$buyerId, (int) $plan['id'], $plan['price'], $plan['currency']]);
        $pdo->commit();

        return [
            'id' => (int) db()->lastInsertId(),
            'plan' => $plan,
            'email' => (string) (current_buyer()['email'] ?? ''),
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        app_log('subscription-exception', ['message' => $e->getMessage()]);
        return 'No se pudo iniciar la membresía.';
    }
}

function apply_mp_payment(string $mpId, string $status, string $amount, string $currency, ?int $orderId, ?int $subscriptionId): void
{
    $amount = money_parse($amount, true) ?? '0.00';
    $currency = preg_match('/^[A-Z]{3}$/', $currency) ? $currency : 'ARS';
    $status = substr(preg_replace('/[^a-z_]/', '', strtolower($status)) ?? '', 0, 32);
    if ($status === '') {
        $status = 'unknown';
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $existing = $pdo->prepare('SELECT id, status FROM payments WHERE mp_payment_id = ? FOR UPDATE');
        $existing->execute([$mpId]);
        $row = $existing->fetch();
        if ($row !== false) {
            if ($status === 'refunded' && (string) $row['status'] !== 'refunded') {
                $pdo->prepare('UPDATE payments SET status = \'refunded\' WHERE id = ?')->execute([(int) $row['id']]);
                if ($orderId !== null) {
                    $pdo->prepare('UPDATE orders SET status = \'refunded\' WHERE id = ? AND status = \'paid\'')->execute([$orderId]);
                }
            }
            $pdo->commit();
            return;
        }
        $insert = $pdo->prepare(
            'INSERT INTO payments (order_id, subscription_id, mp_payment_id, status, amount, currency)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([$orderId, $subscriptionId, $mpId, $status, $amount, $currency]);
        if ($status === 'approved' && $orderId !== null) {
            $pdo->prepare('UPDATE orders SET status = \'paid\' WHERE id = ? AND status = \'pending\'')->execute([$orderId]);
        }
        if ($status === 'approved' && $subscriptionId !== null) {
            $pdo->prepare(
                'UPDATE subscriptions
                 SET status = \'active\',
                     current_period_end = DATE_ADD(
                        IF(current_period_end IS NULL OR current_period_end < NOW(), NOW(), current_period_end),
                        INTERVAL 1 MONTH
                     )
                 WHERE id = ?'
            )->execute([$subscriptionId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function reference_target(string $reference): array
{
    if (preg_match('/^order:(\d+)$/', $reference, $match) === 1) {
        return ['order', (int) $match[1]];
    }
    if (preg_match('/^sub:(\d+)$/', $reference, $match) === 1) {
        return ['sub', (int) $match[1]];
    }

    return ['', 0];
}

function safe_next(string $value): string
{
    if ($value === '' || str_contains($value, '://') || str_contains($value, '//') || str_contains($value, '\\')) {
        return 'cuenta/compras';
    }
    if (!preg_match('#^[a-z0-9_./?=&%-]+$#i', $value)) {
        return 'cuenta/compras';
    }

    return $value;
}
