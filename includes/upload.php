<?php
declare(strict_types=1);

/**
 * Shared media processing for the admin dashboard. Mirrors the manual
 * PIL/ffmpeg workflow used throughout this project by hand: images are
 * auto-oriented, capped to a sane max dimension, and re-encoded to JPEG;
 * videos are transcoded to H.264 and get an auto-generated poster frame.
 * Every function returns a path relative to assets/images or
 * assets/videos, matching how every array in index.php already stores
 * media paths — nothing downstream needs to change.
 */

class UploadException extends RuntimeException {}

const UPLOAD_MAX_IMAGE_BYTES = 15 * 1024 * 1024;
const UPLOAD_MAX_VIDEO_BYTES = 500 * 1024 * 1024;

/**
 * @param array $file One entry from $_FILES (already confirmed non-empty by the caller)
 * @param string $subfolder e.g. 'gallery', 'faculty', 'labs', 'blog'
 * @return string relative path (e.g. "gallery/abc123.jpg") to store in the DB
 */
function handle_image_upload(array $file, string $subfolder): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new UploadException('Upload failed (error code ' . ($file['error'] ?? -1) . ').');
    }
    if (($file['size'] ?? 0) > UPLOAD_MAX_IMAGE_BYTES) {
        throw new UploadException('Image is too large (max 15MB).');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png'  => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    ];
    if (!isset($allowed[$mime])) {
        throw new UploadException('Unsupported image type. Use JPEG, PNG, or WebP.');
    }

    $src = $allowed[$mime]($file['tmp_name']);
    if ($src === false) {
        throw new UploadException('Could not read the uploaded image (it may be corrupt).');
    }

    // Auto-orient using EXIF, same as ImageOps.exif_transpose() used
    // manually all session — phone photos are frequently rotated.
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $orientation = $exif['Orientation'] ?? 1;
        $src = apply_exif_orientation($src, $orientation);
    }

    $width  = imagesx($src);
    $height = imagesy($src);
    $maxDim = 2000;
    if (max($width, $height) > $maxDim) {
        $scale     = $maxDim / max($width, $height);
        $newWidth  = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);
        $resized   = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($src);
        $src = $resized;
    }

    $destDir = dirname(__DIR__) . '/assets/images/' . trim($subfolder, '/');
    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        throw new UploadException('Could not create the destination folder.');
    }

    $filename = bin2hex(random_bytes(8)) . '.jpg';
    $destPath = $destDir . '/' . $filename;

    imagejpeg($src, $destPath, 82);
    imagedestroy($src);
    chmod($destPath, 0644);

    return trim($subfolder, '/') . '/' . $filename;
}

function apply_exif_orientation(\GdImage $image, int $orientation): \GdImage
{
    switch ($orientation) {
        case 3:
            return imagerotate($image, 180, 0);
        case 6:
            return imagerotate($image, -90, 0);
        case 8:
            return imagerotate($image, 90, 0);
        default:
            return $image;
    }
}

/**
 * Locates the ffmpeg binary, checking common install paths since it's
 * not guaranteed to be on $PATH under a web server process (it was found
 * at /opt/homebrew/bin/ffmpeg locally via Homebrew, but production hosts
 * vary or may not have it at all).
 */
function find_ffmpeg_binary(): ?string
{
    static $resolved = null;
    static $checked = false;
    if ($checked) {
        return $resolved;
    }
    $checked = true;

    $candidates = ['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/homebrew/bin/ffmpeg'];
    $which = @shell_exec('command -v ffmpeg 2>/dev/null');
    if (is_string($which) && trim($which) !== '') {
        array_unshift($candidates, trim($which));
    }
    foreach ($candidates as $path) {
        if (is_executable($path)) {
            $resolved = $path;
            return $resolved;
        }
    }
    return null;
}

/**
 * @return array{path: string, poster: ?string, optimized: bool}
 */
function handle_video_upload(array $file, string $subfolder, string $posterSubfolder = 'video-posters'): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new UploadException('Upload failed (error code ' . ($file['error'] ?? -1) . ').');
    }
    if (($file['size'] ?? 0) > UPLOAD_MAX_VIDEO_BYTES) {
        throw new UploadException('Video is too large (max 500MB).');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowedMimes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'];
    if (!in_array($mime, $allowedMimes, true)) {
        throw new UploadException('Unsupported video type. Use MP4, MOV, AVI, or MKV.');
    }

    $destDir = dirname(__DIR__) . '/assets/videos/' . trim($subfolder, '/');
    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        throw new UploadException('Could not create the destination folder.');
    }
    $posterDir = dirname(__DIR__) . '/assets/images/' . trim($posterSubfolder, '/');
    if (!is_dir($posterDir) && !mkdir($posterDir, 0755, true) && !is_dir($posterDir)) {
        throw new UploadException('Could not create the poster folder.');
    }

    $basename = bin2hex(random_bytes(8));
    $destPath = $destDir . '/' . $basename . '.mp4';
    $posterPath = $posterDir . '/' . $basename . '.jpg';

    $ffmpeg = find_ffmpeg_binary();

    if ($ffmpeg === null) {
        // Fallback: store as-uploaded rather than fail the whole save.
        // The admin screen flags this so staff know to compress by hand.
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new UploadException('Could not save the uploaded video.');
        }
        chmod($destPath, 0644);
        return [
            'path'      => trim($subfolder, '/') . '/' . $basename . '.mp4',
            'poster'    => null,
            'optimized' => false,
        ];
    }

    $tmpIn = $file['tmp_name'];
    $cmd = sprintf(
        '%s -y -i %s -vf %s -c:v libx264 -crf 24 -preset fast -c:a aac -b:a 128k -movflags +faststart %s 2>&1',
        escapeshellarg($ffmpeg),
        escapeshellarg($tmpIn),
        escapeshellarg("scale='min(1280,iw)':-2"),
        escapeshellarg($destPath)
    );
    exec($cmd, $out, $exitCode);

    if ($exitCode !== 0 || !is_file($destPath)) {
        // Transcode failed (e.g. corrupt input) — fall back to raw store
        // rather than lose the upload entirely.
        if (!move_uploaded_file($tmpIn, $destPath)) {
            throw new UploadException('Video processing failed and the raw upload could not be saved either.');
        }
        chmod($destPath, 0644);
        return [
            'path'      => trim($subfolder, '/') . '/' . basename($destPath),
            'poster'    => null,
            'optimized' => false,
        ];
    }
    chmod($destPath, 0644);

    // Poster frame at 1s in (or 0s for very short clips).
    $posterCmd = sprintf(
        '%s -y -ss 1 -i %s -vframes 1 -q:v 3 %s 2>&1',
        escapeshellarg($ffmpeg),
        escapeshellarg($destPath),
        escapeshellarg($posterPath)
    );
    exec($posterCmd, $posterOut, $posterExit);
    $posterRelative = null;
    if ($posterExit === 0 && is_file($posterPath)) {
        chmod($posterPath, 0644);
        $posterRelative = trim($posterSubfolder, '/') . '/' . $basename . '.jpg';
    }

    return [
        'path'      => trim($subfolder, '/') . '/' . $basename . '.mp4',
        'poster'    => $posterRelative,
        'optimized' => true,
    ];
}

/**
 * Deletes a previously-stored media file (image or video) given its
 * relative path, as saved in the DB. Used when replacing or removing a
 * record's media. Silently no-ops if the file is already gone.
 */
function delete_media_file(?string $relativePath, string $baseDir = 'images'): void
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    $root = $baseDir === 'videos'
        ? dirname(__DIR__) . '/assets/videos/'
        : dirname(__DIR__) . '/assets/images/';
    $full = $root . ltrim($relativePath, '/');
    if (is_file($full)) {
        @unlink($full);
    }
}
