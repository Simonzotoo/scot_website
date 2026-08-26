<?php
declare(strict_types=1);

function storage_bootstrap(): void
{
    $dirs = [
        UPLOAD_ROOT . '/resources',
        UPLOAD_ROOT . '/gallery/images',
        UPLOAD_ROOT . '/gallery/videos',
        UPLOAD_ROOT . '/announcements',
        UPLOAD_ROOT . '/temp',
        // Avatars live under assets/images (web-servable) rather than
        // uploads/ (blocked by .htaccess) since they're meant to render
        // inline as <img> tags.
        dirname(__DIR__) . '/assets/images/avatars',
    ];

    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            continue;
        }
        if (!mkdir($dir, 0755, true)) {
            error_log('[SCOTSA Storage] Could not create directory: ' . $dir);
            continue;
        }
        $index = $dir . '/index.html';
        if (!file_exists($index)) {
            file_put_contents($index, '');
        }
    }
}
