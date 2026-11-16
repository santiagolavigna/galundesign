<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/content.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$row = is_int($id) ? find_row('works', $id) : null;
if ($id !== null && $id !== false && $row === null) {
    header('Location: works.php');
    exit;
}

$error = '';
$values = [
    'title' => (string) ($row['title'] ?? ''),
    'frame' => (string) ($row['frame'] ?? 'story'),
    'group_label' => (string) ($row['group_label'] ?? ''),
    'link_url' => (string) ($row['link_url'] ?? ''),
    'sort_order' => (string) ($row['sort_order'] ?? '0'),
    'is_featured' => (int) ($row['is_featured'] ?? 0),
    'is_published' => (int) ($row['is_published'] ?? 0),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploaded = $_FILES['image'] ?? null;
    app_log('work-post', [
        'bytes' => (string) ($_SERVER['CONTENT_LENGTH'] ?? '0'),
        'frame' => (string) ($_POST['frame'] ?? ''),
        'post' => $_POST === [] ? 'vacio' : 'ok',
        'file_error' => is_array($uploaded) ? (string) ($uploaded['error'] ?? '') : 'ninguno',
        'file_bytes' => is_array($uploaded) ? (string) ($uploaded['size'] ?? '0') : '0',
        'ext' => is_array($uploaded) ? strtolower(pathinfo((string) ($uploaded['name'] ?? ''), PATHINFO_EXTENSION)) : '',
    ]);
    try {
    csrf_require();
    $values['title'] = clean_text((string) ($_POST['title'] ?? ''), 160);
    $values['frame'] = ($_POST['frame'] ?? '') === 'feed' ? 'feed' : 'story';
    $values['group_label'] = clean_text((string) ($_POST['group_label'] ?? ''), 80);
    $values['link_url'] = trim((string) ($_POST['link_url'] ?? ''));
    $values['sort_order'] = (string) posted_int('sort_order', 0, 9999);
    $values['is_featured'] = posted_flag('is_featured');
    $values['is_published'] = posted_flag('is_published');
    $link = https_url($values['link_url']);
    $targetW = $values['frame'] === 'feed' ? 1080 : 1080;
    $targetH = $values['frame'] === 'feed' ? 1350 : 1920;
    $filePath = (string) ($row['file_path'] ?? '');
    $width = (int) ($row['width'] ?? 0);
    $height = (int) ($row['height'] ?? 0);
    $uploaded = $_FILES['image'] ?? null;
    $hasNewFile = is_array($uploaded) && ($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if (!$hasNewFile && $values['frame'] === 'feed' && $row !== null && (string) $row['frame'] === 'square') {
        $values['frame'] = 'square';
    }

    if ($values['title'] === '' || $values['group_label'] === '') {
        $error = 'El título y el grupo son obligatorios.';
    } elseif ($link === null) {
        $error = 'El link tiene que empezar con https://.';
    } elseif (is_array($uploaded) && ($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $maxBytes = 8 * 1024 * 1024;
        $also = $values['frame'] === 'feed' ? [[1, 1]] : [];
        $taken = take_image($uploaded, 'works', $maxBytes, $targetW, $targetH, true, true, $also);
        if (is_string($taken)) {
            $error = $taken . ($values['frame'] === 'feed'
                ? ' El post tiene que ser 4:5 (1080×1350) o cuadrado (1080×1080), JPG o WEBP, hasta 8 MB.'
                : ' La historia tiene que ser 9:16 (1080×1920), JPG o WEBP, hasta 8 MB.');
        } else {
            remove_stored_file($filePath !== '' ? $filePath : null);
            $filePath = $taken['path'];
            $width = $taken['width'];
            $height = $taken['height'];
            if ($values['frame'] === 'feed' && $height > 0 && abs(($width / $height) - 1) <= 0.03) {
                $values['frame'] = 'square';
            }
        }
        } elseif ($row === null) {
        $error = 'Subí la imagen. Post: 1080×1350 o 1080×1080. Historia: 1080×1920. JPG o WEBP, hasta 8 MB.';
    } elseif ((string) $row['frame'] !== $values['frame']) {
        $error = 'Si cambiás el formato, subí la imagen de nuevo.';
    }

    if ($error === '') {
        work_save([
            'title' => $values['title'],
            'frame' => $values['frame'],
            'file_path' => $filePath,
            'link_url' => $link,
            'group_label' => $values['group_label'],
            'is_featured' => $values['is_featured'],
            'sort_order' => (int) $values['sort_order'],
            'is_published' => $values['is_published'],
            'width' => $width,
            'height' => $height,
        ], $row === null ? null : (int) $row['id']);
        flash('Se guardó «' . $values['title'] . '».');
        header('Location: works.php');
        exit;
    }
    if ($error !== '') {
        app_log('work-rejected', ['reason' => $error]);
    }
    } catch (Throwable $e) {
        app_log('work-exception', ['type' => $e::class, 'message' => $e->getMessage()]);
        $error = 'No se pudo guardar la imagen.';
    }
}

admin_head($row === null ? 'Nuevo trabajo' : 'Editar trabajo');
?>
<?php if ($error !== ''): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form class="stack" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Título <input type="text" name="title" required value="<?= e($values['title']) ?>"></label>
    <label>Grupo <span class="hint">Por ejemplo Eliastom Kick, Eventos o Historias.</span>
        <input type="text" name="group_label" required value="<?= e($values['group_label']) ?>">
    </label>
    <label>Formato
        <select name="frame">
            <option value="story"<?= $values['frame'] === 'story' ? ' selected' : '' ?>>Historia 9:16</option>
            <option value="feed"<?= $values['frame'] === 'feed' || $values['frame'] === 'square' ? ' selected' : '' ?>>Post de Instagram</option>
        </select>
    </label>
    <label>Imagen <span class="hint">JPG o WEBP, hasta 8 MB. Historia 1080×1920. Post 1080×1350 o cuadrado 1080×1080. El sitio no la comprime.</span>
        <input type="file" name="image" accept=".jpg,.jpeg,.webp,image/jpeg,image/webp">
    </label>
    <label>Link de Instagram <input type="url" name="link_url" required value="<?= e($values['link_url']) ?>"></label>
    <label>Orden <input type="number" name="sort_order" min="0" max="9999" value="<?= e($values['sort_order']) ?>"></label>
    <label class="check"><input type="checkbox" name="is_featured"<?= $values['is_featured'] === 1 ? ' checked' : '' ?>> Destacado (solo puede haber uno)</label>
    <label class="check"><input type="checkbox" name="is_published"<?= $values['is_published'] === 1 ? ' checked' : '' ?>> Visible en la home</label>
    <button type="submit">Guardar</button>
</form>
<?php admin_foot();