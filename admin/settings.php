<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/content.php';

$row = settings_row();
$error = '';
$values = [
    'brand_name' => (string) $row['brand_name'],
    'tagline' => (string) $row['tagline'],
    'whatsapp_e164' => (string) ($row['whatsapp_e164'] ?? ''),
    'whatsapp_message' => (string) ($row['whatsapp_message'] ?? ''),
    'instagram_url' => (string) ($row['instagram_url'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $values['brand_name'] = clean_text((string) ($_POST['brand_name'] ?? ''), 120);
    $values['tagline'] = clean_text((string) ($_POST['tagline'] ?? ''), 240);
    $digits = preg_replace('/\D+/', '', (string) ($_POST['whatsapp_e164'] ?? '')) ?? '';
    $values['whatsapp_e164'] = substr($digits, 0, 20);
    $values['whatsapp_message'] = clean_text((string) ($_POST['whatsapp_message'] ?? ''), 240);
    $values['instagram_url'] = trim((string) ($_POST['instagram_url'] ?? ''));
    $instagram = $values['instagram_url'] === '' ? '' : https_url($values['instagram_url']);

    if ($values['brand_name'] === '') {
        $error = 'El nombre de la marca es obligatorio.';
    } elseif ($values['instagram_url'] !== '' && $instagram === null) {
        $error = 'El Instagram tiene que ser un link https://.';
    } else {
        settings_save([
            'brand_name' => $values['brand_name'],
            'tagline' => $values['tagline'],
            'whatsapp_e164' => $values['whatsapp_e164'] !== '' ? $values['whatsapp_e164'] : null,
            'whatsapp_message' => $values['whatsapp_message'] !== '' ? $values['whatsapp_message'] : null,
            'instagram_url' => $instagram !== '' ? $instagram : null,
        ]);
        flash('Se guardó el contacto.');
        header('Location: settings.php');
        exit;
    }
}

admin_head('Contacto');
?>
<?php if ($error !== ''): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form class="stack" method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Marca <input type="text" name="brand_name" required value="<?= e($values['brand_name']) ?>"></label>
    <label>Frase <input type="text" name="tagline" value="<?= e($values['tagline']) ?>"></label>
    <label>WhatsApp <span class="hint">Solo números, con código de país. Ejemplo 5492346571207.</span>
        <input type="text" name="whatsapp_e164" value="<?= e($values['whatsapp_e164']) ?>">
    </label>
    <label>Mensaje de WhatsApp <textarea name="whatsapp_message"><?= e($values['whatsapp_message']) ?></textarea></label>
    <label>Instagram <input type="url" name="instagram_url" value="<?= e($values['instagram_url']) ?>"></label>
    <button type="submit">Guardar</button>
</form>
<?php admin_foot();