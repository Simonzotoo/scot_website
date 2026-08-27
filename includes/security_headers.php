<?php
declare(strict_types=1);

function is_request_secure(): bool
{
    if (isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) === 'on') {
        return true;
    }

    // Only trust a proxy's word for it when this deploy explicitly says it
    // sits behind one (TRUST_PROXY_HEADERS=1); otherwise a client could
    // just send this header itself and fake a secure connection.
    if (TRUST_PROXY_HEADERS && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
        return true;
    }

    return false;
}

/**
 * Redirect to HTTPS and attach baseline security headers. Called once from
 * config.php, i.e. before any output; safe to `header()` here.
 */
function apply_security_headers(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    $secure = is_request_secure();

    if (APP_ENV === 'production' && !$secure) {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '') {
            // 307 (not 301) for non-GET so the method and POST body survive
            // the redirect instead of being dropped — a login form posted
            // over plain HTTP must still land on HTTPS, not just GETs.
            $isGet = in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true);
            header('Location: https://' . $host . ($_SERVER['REQUEST_URI'] ?? '/'), true, $isGet ? 301 : 307);
            exit;
        }
    }

    if (APP_ENV === 'production' && $secure) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

    // Allow-list built from what this app actually loads: SweetAlert2 and
    // PDF.js from their CDNs, Google Fonts, and the Google Maps embed on
    // contact.php. style-src needs 'unsafe-inline' because templates use
    // inline style="..." attributes throughout.
    header("Content-Security-Policy: "
        . "default-src 'self'; "
        . "script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src https://fonts.gstatic.com; "
        . "img-src 'self' data:; "
        . "frame-src https://www.google.com; "
        . "base-uri 'self'; form-action 'self'");
}
