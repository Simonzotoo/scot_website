<?php
declare(strict_types=1);

define('MEDIA_MAX_BYTES', 8 * 1024 * 1024);

/**
 * Validate and store an uploaded photo under assets/images/{subdir}/,
 * preserving its aspect ratio (unlike handle_avatar_upload(), which force-
 * crops to a square). Downscales anything wider/taller than $maxDimension
 * so a single oversized phone photo can't blow past MEDIA_MAX_BYTES or, once
 * displayed full-bleed (e.g. a hero banner), look pixelated when the browser
 * has to upscale it back up on a large screen.
 *
 * Returns ['ok' => bool, 'path' => string|null (relative, for the DB),
 * 'error' => string|null (user-facing message on failure)].
 */
function handle_media_upload(array $file, string $subdir, string $prefix, int $maxDimension = 1800, int $quality = 88): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'error' => 'Upload failed. Please choose an image and try again.'];
    }
    if ($file['size'] > MEDIA_MAX_BYTES) {
        return ['ok' => false, 'path' => null, 'error' => 'Image must not exceed ' . (MEDIA_MAX_BYTES / 1048576) . ' MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        return ['ok' => false, 'path' => null, 'error' => 'Only JPEG, PNG, or WebP images are accepted.'];
    }

    $data = file_get_contents($file['tmp_name']);
    $src = $data !== false ? @imagecreatefromstring($data) : false;
    if (!$src) {
        return ['ok' => false, 'path' => null, 'error' => 'Could not read that image. Please try a different file.'];
    }

    $width = imagesx($src);
    $height = imagesy($src);
    $longest = max($width, $height);

    if ($longest > $maxDimension) {
        $scale = $maxDimension / $longest;
        $newWidth = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($src);
        $src = $resized;
    }

    $dir = IMAGES_ROOT . '/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        imagedestroy($src);
        return ['ok' => false, 'path' => null, 'error' => 'Could not create the image folder. Contact your system administrator.'];
    }

    $filename = $prefix . '-' . bin2hex(random_bytes(6)) . '.jpg';
    $relativePath = $subdir . '/' . $filename;
    $absolutePath = IMAGES_ROOT . '/' . $relativePath;

    $saved = imagejpeg($src, $absolutePath, $quality);
    imagedestroy($src);

    if (!$saved) {
        return ['ok' => false, 'path' => null, 'error' => 'Could not save the image. Please try again.'];
    }

    return ['ok' => true, 'path' => $relativePath, 'error' => null];
}

/**
 * Delete a previously-uploaded media file, if it's actually inside
 * assets/images/ (defense against a stray absolute/traversal path ever
 * ending up in the DB).
 */
function delete_media(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }
    $full = realpath(IMAGES_ROOT . '/' . $relativePath);
    $root = realpath(IMAGES_ROOT);
    if ($full && $root && strncmp($full, $root, strlen($root)) === 0 && is_file($full)) {
        @unlink($full);
    }
}
