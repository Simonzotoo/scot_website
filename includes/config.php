<?php
declare(strict_types=1);

/**
 * Buffer all output for the whole request. Several admin/student pages
 * include their header template (which echoes the page chrome) before
 * deciding whether to `header('Location: ...')` redirect after a POST.
 * That only "worked" before by accident, up to whatever byte limit
 * php.ini's `output_buffering` happens to set — and that setting itself is
 * a *chunked* buffer (e.g. 4096 bytes) that auto-flushes once full, which
 * is exactly what broke it. Nesting our own unlimited buffer inside it
 * (ob_start() with no chunk size) holds everything until the script ends
 * regardless of what the ini buffer beneath it would have done, making
 * every header() call safe no matter how much HTML already ran.
 */
if (PHP_SAPI !== 'cli') {
    ob_start();
}

require_once __DIR__ . '/env.php';

define('APP_ENV', env('APP_ENV', 'development'));
define('APP_NAME', 'SCOTSA Platform');
define('TRUST_PROXY_HEADERS', env('TRUST_PROXY_HEADERS', '0') === '1');

if (APP_ENV === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

/**
 * Base URL is auto-detected from where this project sits relative to the
 * web server's document root, so the app works unmodified whether it's
 * served from a domain root, a subfolder (any name), or `php -S`. Set
 * APP_BASE_URL in .env only for edge cases (CLI scripts, reverse-proxy
 * path rewrites) where there's no request to inspect.
 */
function detect_base_url(): string
{
    if (empty($_SERVER['DOCUMENT_ROOT'])) {
        return '';
    }
    $projectRoot = realpath(dirname(__DIR__));
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
    if ($projectRoot === false || $docRoot === false || !str_starts_with($projectRoot, $docRoot)) {
        return '';
    }
    $path = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
    return rtrim($path, '/');
}

define('BASE_URL', env('APP_BASE_URL') ?? detect_base_url());

define('UPLOAD_ROOT', dirname(__DIR__) . '/uploads');
define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024);

define('STUDENT_EMAIL_DOMAIN', env('STUDENT_EMAIL_DOMAIN', 'wiuc-ghana.edu.gh'));

define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_NAME', env('DB_NAME', 'scotsa_platform'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

require_once __DIR__ . '/security_headers.php';
apply_security_headers();

require_once __DIR__ . '/storage.php';
storage_bootstrap();

require_once __DIR__ . '/images.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/avatars.php';
