<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/content.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$row = is_int($id) ? find_row('clients', $id) : null;
if ($id !== null && $id !== false && $row === null) {
    header('Location: clients.php');
    exit;
}

$error = '';
$values = [
    'name' => (string) ($row['name'] ?? ''),
    'handle' => (string) ($row['handle'] ?? ''),
    'url' => (string) ($row['url'] ?? ''),
    'sort_order' => (string) ($row['sort_order'] ?? '0'),
    'is_published' => (int) ($row['is_published'] ?? 0),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $values['name'] = clean_text((string) ($_POST['name'] ?? ''), 120);
    $handle = clean_text((string) ($_POST['handle'] ?? ''), 80);
    if ($handle !== '' && !str_starts_with($handle, '@')) {
        $handle = '@' . $handle;
    }
    $values['handle'] = $handle;
    $values['url'] = trim((string) ($_POST['url'] ?? ''));
    $values['sort_order'] = (string) posted_int('sort_order', 0, 9999);
    $values['is_published'] = posted_flag('is_published');
    $link = https_url($values['url']);
    $photo = (string) ($row['photo_path'] ?? '');
    $file = $_FILES['photo'] ?? null;

    if ($values['name'] === '' || $values['handle'] === '@') {
        $error = 'El nombre y el @ son obligatorios.';
    } elseif ($link === null) {
        $error = 'El link tiene que empezar con https://.';
    } elseif (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $taken = take_image($file, 'clients', 200 * 1024, 400, 400, false, false);
        if (is_string($taken)) {
            $error = $taken . ' JPG cuadrado de 400×400, hasta 200 KB.';
        } else {
            remove_stored_file($photo !== '' ? $photo : null);
            $photo = $taken['path'];
        }
    } elseif ($row === null) {
        $error = 'Subí la foto. JPG de 400×400, hasta 200 KB.';
    }

    if ($error === '') {
        client_save([
            'name' => $values['name'],
            'handle' => $values['handle'],
            'photo_path' => $photo,
            'url' => $link,
            'sort_order' => (int) $values['sort_order'],
            'is_published' => $values['is_published'],
        ], $row === null ? null : (int) $row['id']);
        flash('Cliente guardado.');
        header('Location: clients.php');
        exit;
    }
}

admin_head($row === null ? 'Nuevo cliente' : 'Editar cliente');
?>
<?php if ($error !== ''): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form class="stack" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Nombre <input type="text" name="name" required value="<?= e($values['name']) ?>"></label>
    <label>Usuario <input type="text" name="handle" required value="<?= e($values['handle']) ?>" placeholder="@usuario"></label>
    <label>Foto <span class="hint">JPG de 400×400, hasta 200 KB.</span>
        <input type="file" name="photo" accept=".jpg,.jpeg,image/jpeg">
    </label>
    <label>Link de Instagram <input type="url" name="url" required value="<?= e($values['url']) ?>"></label>
    <label>Orden <input type="number" name="sort_order" min="0" max="9999" value="<?= e($values['sort_order']) ?>"></label>
    <label class="check"><input type="checkbox" name="is_published"<?= $values['is_published'] === 1 ? ' checked' : '' ?>> Visible en la home</label>
    <button type="submit">Guardar</button>
</form>
<?php admin_foot();