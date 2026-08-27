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

/**
 * Resource types open for new uploads/browsing right now. The DB column is
 * an ENUM covering all four (see database/schema.sql) so no migration is
 * needed to bring midsem/end_sem back later — just add them back here.
 */
define('ACTIVE_RESOURCE_TYPES', [
    'past_question' => 'Past Question',
    'lecture_note'  => 'Lecture Note',
]);

// Every type the schema supports, used where existing/legacy data of a
// currently-inactive type still needs a sensible label (e.g. admin lists).
define('ALL_RESOURCE_TYPES', [
    'past_question' => 'Past Question',
    'midsem'        => 'Midsem Paper',
    'end_sem'       => 'End-of-Sem',
    'lecture_note'  => 'Lecture Note',
]);

/**
 * File types accepted for resource uploads, keyed by the MIME type detected
 * server-side (via finfo, not the client-supplied filename) so the stored
 * extension and Content-Type can never be spoofed by a renamed file.
 */
define('ALLOWED_RESOURCE_MIME_TYPES', [
    'application/pdf'                                                          => 'pdf',
    'application/msword'                                                       => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'  => 'docx',
    'application/vnd.ms-powerpoint'                                            => 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'=> 'pptx',
    'image/jpeg'                                                               => 'jpg',
    'image/png'                                                                => 'png',
]);

define('EXAM_MONTHS', [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
]);

define('STUDENT_EMAIL_DOMAIN', env('STUDENT_EMAIL_DOMAIN', 'wiuc-ghana.edu.gh'));

define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_NAME', env('DB_NAME', 'scotsa_platform'));
// No fallback default here on purpose: silently connecting as root with a
// blank password if .env is ever missing/misread is worse than failing
// loudly and telling whoever's deploying exactly what to fix.
define('DB_USER', env('DB_USER') ?? throw new RuntimeException('DB_USER is not set — check .env'));
define('DB_PASS', env('DB_PASS') ?? throw new RuntimeException('DB_PASS is not set — check .env'));

require_once __DIR__ . '/security_headers.php';
apply_security_headers();

require_once __DIR__ . '/storage.php';
storage_bootstrap();

require_once __DIR__ . '/images.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/avatars.php';
require_once __DIR__ . '/media.php';
require_once __DIR__ . '/resource_filters.php';
