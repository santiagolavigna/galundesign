<?php
declare(strict_types=1);

function app_config(): array
{
    static $config = null;
    if (is_array($config)) {
        return $config;
    }

    $configPath = dirname(__DIR__) . '/config.local.php';
    if (!is_file($configPath)) {
        throw new RuntimeException('Falta config.local.php');
    }

    $loaded = require $configPath;
    $config = is_array($loaded) ? $loaded : [];

    return $config;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config();
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $config['db_host'],
            $config['db_name']
        ),
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}
