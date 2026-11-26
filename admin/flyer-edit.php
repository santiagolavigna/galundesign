<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/catalog.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$row = is_int($id) ? find_flyer($id) : null;
if ($id !== null && $id !== false && $row === null) {
    header('Location: flyers.php');
    exit;
}

$error = '';
$values = [
    'title' => (string) ($row['title'] ?? ''),
    'price' => (string) ($row['price'] ?? ''),
    'sort_order' => (string) ($row['sort_order'] ?? '0'),
    'is_published' => (int) ($row['is_published'] ?? 0),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $values['title'] = clean_text((string) ($_POST['title'] ?? ''), 160);
    $values['sort_order'] = (string) posted_int('sort_order', 0, 9999);
    $values['is_published'] = posted_flag('is_published');
    $price = money_parse((string) ($_POST['price'] ?? ''));
    $values['price'] = $price ?? (string) ($_POST['price'] ?? '');
    $preview = (string) ($row['preview_path'] ?? '');
    $source = (string) ($row['source_path'] ?? '');
    $newPreview = '';
    $newSource = '';
    $image = $_FILES['preview'] ?? null;
    $psd = $_FILES['source'] ?? null;
    $hasImage = is_array($image) && ($image['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    $hasPsd = is_array($psd) && ($psd['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if ($values['title'] === '') {
        $error = 'El título es obligatorio.';
    } elseif ($price === null) {
        $error = 'El precio tiene que ser mayor a cero, en pesos.';
    } elseif ($hasImage) {
        $problem = upload_error($image);
        if ($problem !== null) {
            $error = $problem;
        } elseif ((int) $image['size'] > FLYER_PREVIEW_MAX) {
            $error = 'La muestra supera los 8 MB.';
        } else {
            $saved = watermark_preview((string) $image['tmp_name']);
            if ($saved === null) {
                $error = 'La muestra tiene que ser JPG o WEBP.';
            } else {
                $newPreview = $saved;
            }
        }
    } elseif ($row === null) {
        $error = 'Subí la imagen de muestra. El sitio le pone la marca de agua.';
    }

    if ($error === '' && $hasPsd) {
        $problem = accept_psd($psd);
        if ($problem !== null) {
            $error = $problem;
        } else {
            try {
                $newSource = store_upload($psd, 'flyers/sources', 'psd');
            } catch (RuntimeException) {
                $error = 'No se pudo guardar el PSD.';
            }
        }
    } elseif ($error === '' && $row === null) {
        $error = 'Subí el PSD.';
    }

    $finalPreview = $newPreview !== '' ? $newPreview : $preview;
    $finalSource = $newSource !== '' ? $newSource : $source;
    if ($error === '' && $values['is_published'] === 1 && ($finalPreview === '' || $finalSource === '')) {
        $error = 'Para publicarlo hacen falta la muestra y el PSD.';
    }

    if ($error !== '') {
        if ($newPreview !== '') {
            remove_stored_file($newPreview);
        }
        if ($newSource !== '') {
            remove_stored_file($newSource);
        }
    } else {
        if ($row === null) {
            $stmt = db()->prepare(
                'INSERT INTO flyers (title, preview_path, source_path, price, currency, sort_order, is_published)
                 VALUES (?, ?, ?, ?, \'ARS\', ?, ?)'
            );
            $stmt->execute([$values['title'], $finalPreview, $finalSource, $price, (int) $values['sort_order'], $values['is_published']]);
        } else {
            $stmt = db()->prepare(
                'UPDATE flyers
                 SET title = ?, preview_path = ?, source_path = ?, price = ?, sort_order = ?, is_published = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $values['title'], $finalPreview, $finalSource, $price, (int) $values['sort_order'], $values['is_published'], (int) $row['id'],
            ]);
            if ($newPreview !== '' && $preview !== '') {
                remove_stored_file($preview);
            }
            if ($newSource !== '' && $source !== '') {
                remove_stored_file($source);
            }
        }
        flash('Se guardó «' . $values['title'] . '».');
        header('Location: flyers.php');
        exit;
    }
}

admin_head($row === null ? 'Nuevo flyer' : 'Editar flyer');
?>
<?php if ($error !== ''): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form class="stack" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Título <input type="text" name="title" required value="<?= e($values['title']) ?>"></label>
    <label>Precio en pesos <input type="text" name="price" inputmode="decimal" required value="<?= e($values['price']) ?>"></label>
    <label>Muestra <span class="hint">JPG o WEBP, hasta 8 MB. Se publica con marca de agua. El original no queda en el sitio.</span>
        <input type="file" name="preview" accept=".jpg,.jpeg,.webp,image/jpeg,image/webp">
    </label>
    <label>PSD <span class="hint">Hasta 40 MB. No se puede abrir por URL.</span>
        <input type="file" name="source" accept=".psd,application/octet-stream">
    </label>
    <label>Orden <input type="number" name="sort_order" min="0" max="9999" value="<?= e($values['sort_order']) ?>"></label>
    <label class="check"><input type="checkbox" name="is_published"<?= $values['is_published'] === 1 ? ' checked' : '' ?>> Visible en el catálogo</label>
    <button type="submit">Guardar</button>
</form>
<?php admin_foot();
