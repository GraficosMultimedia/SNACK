<?php
declare(strict_types=1);
function money(float|int|string $n): string { return '$'.number_format((float)$n,2,'.',','); }
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function config_value(PDO $pdo,string $key,string $default=''): string {
    $s=$pdo->prepare('SELECT valor FROM configuracion WHERE clave=? LIMIT 1');
    $s->execute([$key]);
    return (string)($s->fetchColumn() ?? $default);
}
function set_config(PDO $pdo,string $key,string $value): void {
    $s=$pdo->prepare('INSERT INTO configuracion(clave,valor) VALUES(?,?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)');
    $s->execute([$key,$value]);
}
function app_url(string $path=''): string {
    return rtrim(APP_ROOT,'/').'/'.ltrim($path,'/');
}

/**
 * Optimiza una foto de producto para TPV/menú.
 * Genera una imagen cuadrada de 600x600 y la guarda en WebP cuando GD lo permite.
 * Si WebP no está disponible, usa JPEG optimizado.
 */
function optimize_product_image(string $tmpPath, string $mime, string $destDir, string $baseName): string {
    if (!extension_loaded('gd')) {
        throw new RuntimeException('El servidor no tiene habilitada la extensión GD para optimizar imágenes.');
    }

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($tmpPath),
        'image/png'  => @imagecreatefrompng($tmpPath),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : false,
        default => false,
    };
    if (!$src) {
        throw new RuntimeException('No se pudo procesar la imagen seleccionada.');
    }

    // Corrige orientación EXIF de fotografías JPEG tomadas con celular.
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($tmpPath);
        $orientation = (int)($exif['Orientation'] ?? 1);
        if ($orientation === 3) $src = imagerotate($src, 180, 0);
        elseif ($orientation === 6) $src = imagerotate($src, -90, 0);
        elseif ($orientation === 8) $src = imagerotate($src, 90, 0);
    }

    $srcW = imagesx($src);
    $srcH = imagesy($src);
    if ($srcW < 1 || $srcH < 1) {
        imagedestroy($src);
        throw new RuntimeException('La imagen no tiene dimensiones válidas.');
    }

    // Recorte centrado a formato cuadrado para que el TPV tenga todas las tarjetas uniformes.
    $side = min($srcW, $srcH);
    $srcX = (int)floor(($srcW - $side) / 2);
    $srcY = (int)floor(($srcH - $side) / 2);
    $size = 600;

    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
    imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);
    imagealphablending($dst, true);
    imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $side, $side);

    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        imagedestroy($src); imagedestroy($dst);
        throw new RuntimeException('No se pudo crear la carpeta de imágenes.');
    }

    $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $baseName) ?: 'producto';
    $safeBase = trim($safeBase, '-_') ?: 'producto';
    $filename = $safeBase.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(4));

    if (function_exists('imagewebp')) {
        $file = $filename.'.webp';
        $ok = imagewebp($dst, $destDir.'/'.$file, 82);
    } else {
        $file = $filename.'.jpg';
        $bg = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);
        imagecopy($bg, $dst, 0, 0, 0, 0, $size, $size);
        $ok = imagejpeg($bg, $destDir.'/'.$file, 82);
        imagedestroy($bg);
    }

    imagedestroy($src);
    imagedestroy($dst);
    if (!$ok) throw new RuntimeException('No se pudo guardar la imagen optimizada.');
    return $file;
}

