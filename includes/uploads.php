<?php
declare(strict_types=1);

function upload_error(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return 'Elegí un archivo.';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'No se pudo subir el archivo.';
    }

    return null;
}

function upload_mime(string $tmp): string
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    return is_string($mime) ? $mime : '';
}

function store_upload(array $file, string $folder, string $extension): string
{
    $root = dirname(__DIR__) . '/uploads/' . $folder;
    if (!is_dir($root) && !mkdir($root, 0755, true)) {
        throw new RuntimeException('No se pudo crear la carpeta de subida.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $extension;
    $dest = $root . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('No se pudo guardar el archivo.');
    }

    return 'uploads/' . $folder . '/' . $name;
}

function remove_stored_file(?string $path): bool
{
    if ($path === null || !str_starts_with(str_replace('\\', '/', $path), 'uploads/')) {
        return true;
    }
    $full = dirname(__DIR__) . '/' . $path;
    if (!is_file($full)) {
        return true;
    }

    return unlink($full);
}

/**
 * @return array{width:int,height:int,path:string}|string
 */
function take_image(array $file, string $folder, int $maxBytes, int $targetW, int $targetH, bool $allowRatio = false, bool $allowWebp = true, array $also = []): array|string
{
    $problem = upload_error($file);
    if ($problem !== null) {
        return $problem;
    }
    if (($file['size'] ?? 0) > $maxBytes) {
        return 'La imagen supera el peso permitido.';
    }

    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    $mime = upload_mime((string) $file['tmp_name']);
    $allowed = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];
    if ($allowWebp) {
        $allowed['webp'] = 'image/webp';
    }
    if (!isset($allowed[$ext]) || $allowed[$ext] !== $mime) {
        return $allowWebp ? 'Tiene que ser JPG o WEBP.' : 'Tiene que ser JPG.';
    }

    $info = getimagesize((string) $file['tmp_name']);
    if ($info === false) {
        return 'No se pudo leer la imagen.';
    }
    $width = (int) $info[0];
    $height = (int) $info[1];
    $matches = $width === $targetW && $height === $targetH;
    $targets = array_merge([[$targetW, $targetH]], $also);
    if (!$matches && $allowRatio) {
        $ratio = $height > 0 ? $width / $height : 0;
        foreach ($targets as $target) {
            $expectedW = (int) ($target[0] ?? 0);
            $expectedH = (int) ($target[1] ?? 0);
            $expected = $expectedH > 0 ? $expectedW / $expectedH : 0;
            if ($expected > 0 && abs($ratio - $expected) / $expected <= 0.03) {
                $matches = true;
                break;
            }
        }
    }
    if (!$matches) {
        return 'La imagen mide ' . $width . '×' . $height . ' px.';
    }

    $savedExt = $ext === 'jpeg' ? 'jpg' : $ext;

    return [
        'width' => $width,
        'height' => $height,
        'path' => store_upload($file, $folder, $savedExt),
    ];
}

/**
 * @return array{width:int,height:int,has_audio:bool,h264:bool}
 */
function mp4_inspect(string $path): array
{
    $info = ['width' => 0, 'height' => 0, 'has_audio' => false, 'h264' => false];
    $fh = fopen($path, 'rb');
    if ($fh === false) {
        return $info;
    }
    $end = filesize($path);
    if ($end === false) {
        fclose($fh);
        return $info;
    }

    $walk = function (int $start, int $limit) use (&$walk, $fh, &$info): void {
        $pos = $start;
        while ($pos + 8 <= $limit) {
            fseek($fh, $pos);
            $header = fread($fh, 8);
            if (!is_string($header) || strlen($header) < 8) {
                break;
            }
            $boxSize = unpack('N', substr($header, 0, 4))[1];
            $type = substr($header, 4, 4);
            $headerLen = 8;
            if ($boxSize === 1) {
                $large = fread($fh, 8);
                if (!is_string($large) || strlen($large) < 8) {
                    break;
                }
                $hi = unpack('N', substr($large, 0, 4))[1];
                $lo = unpack('N', substr($large, 4, 4))[1];
                $boxSize = ($hi * 4294967296) + $lo;
                $headerLen = 16;
            } elseif ($boxSize === 0) {
                $boxSize = $limit - $pos;
            }
            if ($boxSize < $headerLen) {
                break;
            }
            $boxEnd = $pos + $boxSize;
            if ($boxEnd > $limit) {
                break;
            }
            $body = $pos + $headerLen;
            if (in_array($type, ['moov', 'trak', 'mdia', 'minf', 'stbl'], true)) {
                $walk($body, $boxEnd);
            } elseif ($type === 'hdlr') {
                fseek($fh, $body);
                $chunk = fread($fh, 16);
                if (is_string($chunk) && strlen($chunk) >= 12 && substr($chunk, 8, 4) === 'soun') {
                    $info['has_audio'] = true;
                }
            } elseif ($type === 'tkhd') {
                fseek($fh, $body);
                $chunk = fread($fh, 96);
                if (is_string($chunk) && strlen($chunk) >= 84) {
                    $offset = ord($chunk[0]) === 1 ? 88 : 76;
                    if (strlen($chunk) >= $offset + 8) {
                        $width = (int) round(unpack('N', substr($chunk, $offset, 4))[1] / 65536);
                        $height = (int) round(unpack('N', substr($chunk, $offset + 4, 4))[1] / 65536);
                        if ($width > 0 && $height > 0) {
                            $info['width'] = $width;
                            $info['height'] = $height;
                        }
                    }
                }
            } elseif ($type === 'stsd' && ($boxEnd - $body) < 262144) {
                fseek($fh, $body);
                $chunk = fread($fh, $boxEnd - $body);
                if (is_string($chunk) && str_contains($chunk, 'avc1')) {
                    $info['h264'] = true;
                }
            }
            $pos = $boxEnd;
        }
    };

    $walk(0, $end);
    fclose($fh);

    return $info;
}

/**
 * @return array{width:int,height:int,path:string}|string
 */
function take_video(array $file): array|string
{
    $problem = upload_error($file);
    if ($problem !== null) {
        return $problem;
    }
    if (($file['size'] ?? 0) > 40 * 1024 * 1024) {
        return 'El video supera los 40 MB. Exportalo de nuevo, más liviano.';
    }
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'mp4' || upload_mime((string) $file['tmp_name']) !== 'video/mp4') {
        return 'El video tiene que ser MP4.';
    }

    $meta = mp4_inspect((string) $file['tmp_name']);
    if (!$meta['h264']) {
        return 'El video tiene que ser H.264.';
    }
    if ($meta['has_audio']) {
        return 'El video no puede tener audio. Exportalo sin pista de sonido.';
    }
    if (abs($meta['width'] - 1920) > 2 || abs($meta['height'] - 960) > 2) {
        return 'El video tiene que medir 1920×960 px.';
    }

    return [
        'width' => $meta['width'],
        'height' => $meta['height'],
        'path' => store_upload($file, 'visuals', 'mp4'),
    ];
}
