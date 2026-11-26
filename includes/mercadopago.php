<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/log.php';

function mp_ready(): bool
{
    return (string) (app_config()['mp_access_token'] ?? '') !== '';
}

function mp_call(string $method, string $path, ?array $body = null): array
{
    $token = (string) (app_config()['mp_access_token'] ?? '');
    if ($token === '') {
        return [];
    }
    $ch = curl_init('https://api.mercadopago.com' . $path);
    if ($ch === false) {
        return [];
    }
    $headers = ['Authorization: Bearer ' . $token, 'Accept: application/json'];
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $json = is_string($raw) ? json_decode($raw, true) : null;
    if ($code < 200 || $code >= 300 || !is_array($json)) {
        app_log('mp-error', ['code' => (string) $code, 'path' => $path]);
        return [];
    }

    return $json;
}

function mp_preference(int $orderId, string $title, string $price): string
{
    $back = site_url('cuenta/compras.php');
    $payload = [
        'items' => [[
            'title' => $title,
            'quantity' => 1,
            'currency_id' => 'ARS',
            'unit_price' => (float) $price,
        ]],
        'external_reference' => 'order:' . $orderId,
        'notification_url' => site_url('api/mp-webhook.php'),
        'back_urls' => ['success' => $back, 'failure' => $back, 'pending' => $back],
    ];
    if (str_starts_with($back, 'https://')) {
        $payload['auto_return'] = 'approved';
    }
    $response = mp_call('POST', '/checkout/preferences', $payload);
    $id = (string) ($response['id'] ?? '');
    if ($id !== '') {
        db()->prepare('UPDATE orders SET mp_preference_id = ? WHERE id = ?')->execute([$id, $orderId]);
    }

    return (string) ($response['init_point'] ?? '');
}

function mp_plan_ready(array $plan): string
{
    $existing = (string) ($plan['mp_plan_id'] ?? '');
    if ($existing !== '') {
        return $existing;
    }
    $created = mp_call('POST', '/preapproval_plan', [
        'reason' => (string) $plan['name'],
        'auto_recurring' => [
            'frequency' => 1,
            'frequency_type' => 'months',
            'transaction_amount' => (float) $plan['price'],
            'currency_id' => 'ARS',
        ],
        'back_url' => site_url('cuenta/compras.php'),
    ]);
    $id = (string) ($created['id'] ?? '');
    if ($id === '') {
        return '';
    }
    db()->prepare('UPDATE plans SET mp_plan_id = ? WHERE id = ? AND mp_plan_id IS NULL')->execute([$id, (int) $plan['id']]);

    return $id;
}

function mp_subscribe(int $subscriptionId, array $plan, string $email): string
{
    $planId = mp_plan_ready($plan);
    if ($planId === '' || $email === '') {
        return '';
    }
    $response = mp_call('POST', '/preapproval', [
        'preapproval_plan_id' => $planId,
        'reason' => (string) $plan['name'],
        'external_reference' => 'sub:' . $subscriptionId,
        'payer_email' => $email,
        'back_url' => site_url('cuenta/compras.php'),
        'status' => 'pending',
    ]);
    $mpId = (string) ($response['id'] ?? '');
    if ($mpId !== '') {
        db()->prepare('UPDATE subscriptions SET mp_preapproval_id = ? WHERE id = ?')->execute([$mpId, $subscriptionId]);
    }

    return (string) ($response['init_point'] ?? '');
}

function mp_query_value(string $key): string
{
    $query = (string) ($_SERVER['QUERY_STRING'] ?? '');
    $pattern = '/(?:^|&)' . preg_quote($key, '/') . '=([^&]*)/';
    if (preg_match($pattern, $query, $match) !== 1) {
        return '';
    }

    return rawurldecode($match[1]);
}

function mp_signature_ok(string $dataId): bool
{
    $secret = (string) (app_config()['mp_webhook_secret'] ?? '');
    $header = (string) ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
    if ($secret === '' || $dataId === '' || $header === '') {
        return false;
    }
    $ts = '';
    $hash = '';
    foreach (explode(',', $header) as $part) {
        $pair = explode('=', trim($part), 2);
        if (count($pair) !== 2) {
            continue;
        }
        if ($pair[0] === 'ts') {
            $ts = $pair[1];
        }
        if ($pair[0] === 'v1') {
            $hash = $pair[1];
        }
    }
    if ($ts === '' || $hash === '') {
        return false;
    }
    $requestId = (string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? '');
    $manifest = 'id:' . strtolower($dataId) . ';request-id:' . $requestId . ';ts:' . $ts . ';';

    return hash_equals(hash_hmac('sha256', $manifest, $secret), $hash);
}
