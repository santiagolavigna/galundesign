<?php
declare(strict_types=1);

function app_log(string $event, array $fields = []): void
{
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) {
        return;
    }

    $parts = [gmdate('c'), $event];
    foreach ($fields as $key => $value) {
        $text = preg_replace('/\s+/', ' ', (string) $value) ?? '';
        $text = str_replace(['"', '\\'], '', $text);
        $parts[] = preg_replace('/[^a-z0-9_]/', '', (string) $key) . '=' . substr($text, 0, 180);
    }

    file_put_contents($dir . '/app.log', implode(' ', $parts) . "\n", FILE_APPEND | LOCK_EX);
}
