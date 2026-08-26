<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

/* ── Login failure codes (internal use only) ─────────────────
   Never expose these directly to end users.
──────────────────────────────────────────────────────────── */
define('AUTH_OK',               0);
define('AUTH_FAIL_NOT_FOUND',   1);
define('AUTH_FAIL_PASSWORD',    2);
define('AUTH_FAIL_DB',          3);
define('AUTH_FAIL_SESSION',     4);
define('AUTH_FAIL_LOCKED',      5);

/* ── Login throttling ─────────────────────────────────────────
   Keyed by IP rather than email, so it can't be used to enumerate
   valid admin addresses and so one attacker can't lock out a real
   admin's account by deliberately failing their login. */
define('LOGIN_MAX_ATTEMPTS',    5);
define('LOGIN_WINDOW_MINUTES',  15);

/* ── Admin session expiry ─────────────────────────────────────── */
define('ADMIN_SESSION_IDLE_SECONDS',     30 * 60);
define('ADMIN_SESSION_ABSOLUTE_SECONDS', 8 * 60 * 60);

function login_is_rate_limited(string $ip): bool
{
    try {
        $stmt = db()->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE identifier = ? AND success = 0
               AND attempted_at >= (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->execute([$ip, LOGIN_WINDOW_MINUTES]);
        return (int) $stmt->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
    } catch (Throwable $e) {
        error_log('[SCOTSA Auth] rate-limit check failed: ' . $e->getMessage());
        return false; // fail open on infra error rather than lock everyone out
    }
}

function record_login_attempt(string $ip, bool $success): void
{
    try {
        db()->prepare('INSERT INTO login_attempts (identifier, success) VALUES (?, ?)')
            ->execute([$ip, $success ? 1 : 0]);
        // Opportunistic cleanup so the table doesn't grow forever.
        if (random_int(1, 100) === 1) {
            db()->exec('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
        }
    } catch (Throwable $e) {
        error_log('[SCOTSA Auth] could not record login attempt: ' . $e->getMessage());
    }
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $now = time();
    if (
        (($now - (int) ($_SESSION['admin_last_active'] ?? $now)) > ADMIN_SESSION_IDLE_SECONDS) ||
        (($now - (int) ($_SESSION['admin_login_at'] ?? $now)) > ADMIN_SESSION_ABSOLUTE_SECONDS)
    ) {
        logout_admin();
        return null;
    }

    try {
        $stmt = db()->prepare('SELECT id, name, email, role, avatar_path FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('[SCOTSA Auth] current_admin DB error: ' . $e->getMessage());
        unset($_SESSION['admin_id']);
        return null;
    }

    if (!$admin) {
        unset($_SESSION['admin_id']);
        return null;
    }

    $_SESSION['admin_last_active'] = $now;
    return $admin;
}

function require_admin(): void
{
    if (!current_admin()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function require_super_admin(): void
{
    $admin = current_admin();
    if (!$admin) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
    if ($admin['role'] !== 'super_admin') {
        http_response_code(403);
        exit('Forbidden: this area is restricted to super admins.');
    }
}

/**
 * Attempt to authenticate an admin.
 *
 * Returns an AUTH_* constant so callers can surface specific error messages
 * without exposing internal details to the HTTP layer.
 */
function login_admin(string $email, string $password): int
{
    $ip = client_ip();

    if (login_is_rate_limited($ip)) {
        error_log('[SCOTSA Auth] login_admin: rate-limited IP ' . $ip);
        return AUTH_FAIL_LOCKED;
    }

    // ── 1. Fetch admin record ───────────────────────────────
    try {
        $stmt = db()->prepare('SELECT id, email, password_hash FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('[SCOTSA Auth] login_admin DB query failed for <' . $email . '>: ' . $e->getMessage());
        return AUTH_FAIL_DB;
    }

    if (!$admin) {
        record_login_attempt($ip, false);
        error_log('[SCOTSA Auth] login_admin: no account found for <' . $email . '>');
        return AUTH_FAIL_NOT_FOUND;
    }

    // ── 2. Verify password ──────────────────────────────────
    if (!password_verify($password, (string) $admin['password_hash'])) {
        record_login_attempt($ip, false);
        error_log('[SCOTSA Auth] login_admin: wrong password for <' . $email . '>');
        return AUTH_FAIL_PASSWORD;
    }

    // ── 3. Establish authenticated session ──────────────────
    try {
        record_login_attempt($ip, true);
        session_regenerate_id(true);
        $_SESSION['admin_id']          = (int) $admin['id'];
        $_SESSION['admin_login_at']    = time();
        $_SESSION['admin_last_active'] = time();
        session_write_close();      // flush to disk before redirect
        session_start();            // reopen so subsequent code can still read it
    } catch (Throwable $e) {
        error_log('[SCOTSA Auth] login_admin session error for <' . $email . '>: ' . $e->getMessage());
        return AUTH_FAIL_SESSION;
    }

    return AUTH_OK;
}

function logout_admin(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_login_at'], $_SESSION['admin_last_active']);
    session_regenerate_id(true);
}
