<?php
declare(strict_types=1);

define('AVATAR_MAX_BYTES', 3 * 1024 * 1024);
define('AVATAR_SIZE_PX', 480);

/**
 * Validate an uploaded photo, crop it to a square, re-encode as JPEG (so
 * whatever format arrives, we control the output), and store it under
 * assets/images/avatars/. Deletes the entity's previous avatar file, if any.
 *
 * Returns ['ok' => bool, 'path' => string|null (relative, for the DB),
 * 'error' => string|null (user-facing message on failure)].
 */
function handle_avatar_upload(array $file, string $entityType, int $entityId, ?string $oldAvatarPath): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'error' => 'Upload failed. Please choose an image and try again.'];
    }
    if ($file['size'] > AVATAR_MAX_BYTES) {
        return ['ok' => false, 'path' => null, 'error' => 'Image must not exceed 3 MB.'];
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
    $side = min($width, $height);
    $cropX = (int) (($width - $side) / 2);
    $cropY = (int) (($height - $side) / 2);

    $canvas = imagecreatetruecolor(AVATAR_SIZE_PX, AVATAR_SIZE_PX);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white); // flattens transparency (e.g. PNG) onto white before JPEG encode
    imagecopyresampled($canvas, $src, 0, 0, $cropX, $cropY, AVATAR_SIZE_PX, AVATAR_SIZE_PX, $side, $side);
    imagedestroy($src);

    $filename = $entityType . '-' . $entityId . '-' . bin2hex(random_bytes(6)) . '.jpg';
    $relativePath = 'avatars/' . $filename;
    $absolutePath = IMAGES_ROOT . '/' . $relativePath;

    $saved = imagejpeg($canvas, $absolutePath, 88);
    imagedestroy($canvas);

    if (!$saved) {
        return ['ok' => false, 'path' => null, 'error' => 'Could not save the image. Please try again.'];
    }

    if ($oldAvatarPath) {
        $oldFull = realpath(IMAGES_ROOT . '/' . $oldAvatarPath);
        $root = realpath(IMAGES_ROOT . '/avatars');
        if ($oldFull && $root && strncmp($oldFull, $root, strlen($root)) === 0 && is_file($oldFull)) {
            @unlink($oldFull);
        }
    }

    return ['ok' => true, 'path' => $relativePath, 'error' => null];
}

/**
 * Resolve a stored avatar_path to a displayable <img> src, falling back to
 * a generated initials avatar (data: URI, no extra request/file) when unset.
 */
function avatar_url(?string $avatarPath, string $name): string
{
    if ($avatarPath) {
        return IMAGES_URL . '/' . $avatarPath;
    }

    $words = preg_split('/\s+/', trim($name)) ?: [];
    $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[count($words) - 1] ?? '', 0, 1)) ?: '?';

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
        . '<rect width="100" height="100" rx="50" fill="#0A1F44"/>'
        . '<text x="50" y="52" font-family="DM Sans, sans-serif" font-size="38" font-weight="800" '
        . 'fill="#D4AF37" text-anchor="middle" dominant-baseline="middle">' . $initials . '</text>'
        . '</svg>';

    return 'data:image/svg+xml,' . rawurlencode($svg);
}
