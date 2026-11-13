<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/uploads.php';

function published_rows(string $table): array
{
    $allowed = ['works' => true, 'visuals' => true, 'clients' => true];
    if (!isset($allowed[$table])) {
        return [];
    }
    $sql = 'SELECT * FROM ' . $table . ' WHERE is_published = 1 ORDER BY sort_order ASC, id DESC';

    return db()->query($sql)->fetchAll();
}

function find_row(string $table, int $id): ?array
{
    $allowed = ['works' => true, 'visuals' => true, 'clients' => true];
    if (!isset($allowed[$table])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM ' . $table . ' WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

function all_rows(string $table): array
{
    $allowed = ['works' => true, 'visuals' => true, 'clients' => true];
    if (!isset($allowed[$table])) {
        return [];
    }

    return db()->query('SELECT * FROM ' . $table . ' ORDER BY sort_order ASC, id DESC')->fetchAll();
}

function settings_row(): array
{
    $row = db()->query('SELECT * FROM settings WHERE id = 1')->fetch();
    if ($row === false) {
        return [
            'brand_name' => 'Galun Design',
            'tagline' => '',
            'whatsapp_e164' => '',
            'whatsapp_message' => '',
            'instagram_url' => '',
        ];
    }

    return $row;
}

function settings_save(array $data): void
{
    $stmt = db()->prepare(
        'UPDATE settings
         SET brand_name = ?, tagline = ?, whatsapp_e164 = ?, whatsapp_message = ?, instagram_url = ?
         WHERE id = 1'
    );
    $stmt->execute([
        $data['brand_name'],
        $data['tagline'],
        $data['whatsapp_e164'],
        $data['whatsapp_message'],
        $data['instagram_url'],
    ]);
}

function work_save(array $data, ?int $id): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        if ($data['is_featured'] === 1) {
            $pdo->exec('UPDATE works SET is_featured = 0');
        }
        if ($id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO works
                (title, frame, file_path, link_url, group_label, is_featured, sort_order, is_published, width, height)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $data['title'], $data['frame'], $data['file_path'], $data['link_url'], $data['group_label'],
                $data['is_featured'], $data['sort_order'], $data['is_published'], $data['width'], $data['height'],
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE works
                 SET title = ?, frame = ?, file_path = ?, link_url = ?, group_label = ?,
                     is_featured = ?, sort_order = ?, is_published = ?, width = ?, height = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $data['title'], $data['frame'], $data['file_path'], $data['link_url'], $data['group_label'],
                $data['is_featured'], $data['sort_order'], $data['is_published'], $data['width'], $data['height'],
                $id,
            ]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function visual_save(array $data, ?int $id): void
{
    if ($id === null) {
        $stmt = db()->prepare(
            'INSERT INTO visuals (title, file_path, poster_path, link_url, sort_order, is_published, width, height)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'], $data['file_path'], $data['poster_path'], $data['link_url'],
            $data['sort_order'], $data['is_published'], $data['width'], $data['height'],
        ]);
        return;
    }
    $stmt = db()->prepare(
        'UPDATE visuals
         SET title = ?, file_path = ?, poster_path = ?, link_url = ?, sort_order = ?, is_published = ?, width = ?, height = ?
         WHERE id = ?'
    );
    $stmt->execute([
        $data['title'], $data['file_path'], $data['poster_path'], $data['link_url'],
        $data['sort_order'], $data['is_published'], $data['width'], $data['height'], $id,
    ]);
}

function client_save(array $data, ?int $id): void
{
    if ($id === null) {
        $stmt = db()->prepare(
            'INSERT INTO clients (name, handle, photo_path, url, sort_order, is_published)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'], $data['handle'], $data['photo_path'], $data['url'],
            $data['sort_order'], $data['is_published'],
        ]);
        return;
    }
    $stmt = db()->prepare(
        'UPDATE clients SET name = ?, handle = ?, photo_path = ?, url = ?, sort_order = ?, is_published = ? WHERE id = ?'
    );
    $stmt->execute([
        $data['name'], $data['handle'], $data['photo_path'], $data['url'],
        $data['sort_order'], $data['is_published'], $id,
    ]);
}

function delete_row(string $table, int $id): bool
{
    $row = find_row($table, $id);
    if ($row === null) {
        return false;
    }
    $paths = [];
    if (isset($row['file_path'])) {
        $paths[] = $row['file_path'];
    }
    if (isset($row['poster_path'])) {
        $paths[] = $row['poster_path'];
    }
    if (isset($row['photo_path'])) {
        $paths[] = $row['photo_path'];
    }
    foreach ($paths as $path) {
        if (!remove_stored_file($path)) {
            return false;
        }
    }
    $allowed = ['works' => true, 'visuals' => true, 'clients' => true];
    if (!isset($allowed[$table])) {
        return false;
    }
    $stmt = db()->prepare('DELETE FROM ' . $table . ' WHERE id = ?');
    $stmt->execute([$id]);

    return true;
}
