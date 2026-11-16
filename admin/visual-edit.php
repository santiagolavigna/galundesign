<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/content.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$row = is_int($id) ? find_row('visuals', $id) : null;
if ($id !== null && $id !== false && $row === null) {
    header('Location: visuals.php');
    exit;
}

$error = '';
$values = [
    'title' => (string) ($row['title'] ?? ''),
    'link_url' => (string) ($row['link_url'] ?? ''),
    'sort_order' => (string) ($row['sort_order'] ?? '0'),
    'is_published' => (int) ($row['is_published'] ?? 0),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $values['title'] = clean_text((string) ($_POST['title'] ?? ''), 160);
    $values['link_url'] = trim((string) ($_POST['link_url'] ?? ''));
    $values['sort_order'] = (string) posted_int('sort_order', 0, 9999);
    $values['is_published'] = posted_flag('is_published');
    $link = https_url($values['link_url']);
    $filePath = (string) ($row['file_path'] ?? '');
    $posterPath = (string) ($row['poster_path'] ?? '');
    $width = (int) ($row['width'] ?? 1920);
    $height = (int) ($row['height'] ?? 960);
    $video = $_FILES['video'] ?? null;
    $poster = $_FILES['poster'] ?? null;

    if ($values['title'] === '') {
        $error = 'El título es obligatorio.';
    } elseif ($link === null) {
        $error = 'El link tiene que empezar con https://.';
    } else {
        if (is_array($video) && ($video['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $taken = take_video($video);
            if (is_string($taken)) {
                $error = $taken . ' MP4 H.264, 1920×960, sin audio, hasta 40 MB.';
            } else {
                remove_stored_file($filePath !== '' ? $filePath : null);
                $filePath = $taken['path'];
                $width = $taken['width'];
                $height = $taken['height'];
            }
        } elseif ($row === null) {
            $error = 'Subí el video. MP4 H.264, 1920×960, sin audio, hasta 40 MB.';
        }
        if ($error === '' && is_array($poster) && ($poster['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $takenPoster = take_image($poster, 'visuals', 400 * 1024, 1920, 960, false, false);
            if (is_string($takenPoster)) {
                $error = $takenPoster . ' La portada es un JPG de 1920×960, hasta 400 KB.';
            } else {
                remove_stored_file($posterPath !== '' ? $posterPath : null);
                $posterPath = $takenPoster['path'];
            }
        } elseif ($error === '' && $row === null) {
            $error = 'Subí la portada. JPG de 1920×960, hasta 400 KB.';
        }
    }

    if ($error === '') {
        visual_save([
            'title' => $values['title'],
            'file_path' => $filePath,
            'poster_path' => $posterPath,
            'link_url' => $link,
            'sort_order' => (int) $values['sort_order'],
            'is_published' => $values['is_published'],
            'width' => $width,
            'height' => $height,
        ], $row === null ? null : (int) $row['id']);
        flash('Se guardó «' . $values['title'] . '».');
        header('Location: visuals.php');
        exit;
    }
}

admin_head($row === null ? 'Nuevo visual' : 'Editar visual');
?>
<?php if ($error !== ''): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form class="stack" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Título <input type="text" name="title" required value="<?= e($values['title']) ?>"></label>
    <label>Video <span class="hint">MP4 H.264, 1920×960, sin audio, hasta 40 MB. El sitio no lo comprime.</span>
        <input type="file" name="video" accept="video/mp4,.mp4">
    </label>
    <label>Portada <span class="hint">JPG 1920×960, hasta 400 KB.</span>
        <input type="file" name="poster" accept=".jpg,.jpeg,image/jpeg">
    </label>
    <label>Link de Instagram <input type="url" name="link_url" required value="<?= e($values['link_url']) ?>"></label>
    <label>Orden <input type="number" name="sort_order" min="0" max="9999" value="<?= e($values['sort_order']) ?>"></label>
    <label class="check"><input type="checkbox" name="is_published"<?= $values['is_published'] === 1 ? ' checked' : '' ?>> Visible en la home</label>
    <button type="submit">Guardar</button>
</form>
<?php admin_foot();