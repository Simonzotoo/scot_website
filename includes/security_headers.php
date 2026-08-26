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

    if (APP_ENV === 'production' && !$secure && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '') {
            header('Location: https://' . $host . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
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
}
