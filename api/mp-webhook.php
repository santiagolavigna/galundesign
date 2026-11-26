<?php
declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/catalog.php';
require __DIR__ . '/../includes/mercadopago.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$dataId = mp_query_value('data.id');
if ($dataId === '') {
    $dataId = mp_query_value('id');
}
if (!mp_signature_ok($dataId)) {
    app_log('mp-webhook', ['result' => 'firma']);
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

$type = mp_query_value('type');
if ($type === '') {
    $type = mp_query_value('topic');
}

try {
    if ($type === 'payment') {
        $payment = mp_call('GET', '/v1/payments/' . rawurlencode($dataId));
        if ($payment === []) {
            http_response_code(500);
            echo json_encode(['ok' => false]);
            exit;
        }
        [$kind, $target] = reference_target((string) ($payment['external_reference'] ?? ''));
        apply_mp_payment(
            (string) ($payment['id'] ?? $dataId),
            (string) ($payment['status'] ?? ''),
            (string) ($payment['transaction_amount'] ?? '0'),
            (string) ($payment['currency_id'] ?? 'ARS'),
            $kind === 'order' ? $target : null,
            $kind === 'sub' ? $target : null
        );
    } elseif ($type === 'subscription_preapproval' || $type === 'preapproval') {
        $pre = mp_call('GET', '/preapproval/' . rawurlencode($dataId));
        $status = (string) ($pre['status'] ?? '');
        if (in_array($status, ['cancelled', 'paused'], true)) {
            db()->prepare(
                'UPDATE subscriptions SET status = ? WHERE mp_preapproval_id = ? AND status = \'active\''
            )->execute([$status, $dataId]);
        }
    }
} catch (Throwable $e) {
    app_log('mp-webhook', ['result' => 'error', 'message' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(['ok' => false]);
    exit;
}

echo json_encode(['ok' => true]);
