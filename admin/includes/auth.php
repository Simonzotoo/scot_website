<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/db.php';

/** Redirects to the login screen unless an admin is already signed in. */
function require_admin_login(): array
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }

    return [
        'id'    => (int) $_SESSION['admin_id'],
        'name'  => $_SESSION['admin_name'] ?? 'Admin',
        'email' => $_SESSION['admin_email'] ?? '',
    ];
}

class TooManyLoginAttemptsException extends RuntimeException {}

const LOGIN_ATTEMPT_WINDOW_MINUTES = 15;
const LOGIN_ATTEMPT_MAX = 5;

/**
 * True if this IP has failed to log in LOGIN_ATTEMPT_MAX+ times within the
 * last LOGIN_ATTEMPT_WINDOW_MINUTES. Checked before touching the password
 * at all, so a locked-out attacker can't keep hammering password_verify().
 */
function admin_login_locked_out(string $ip): bool
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE identifier = ? AND success = 0 AND attempted_at > NOW() - INTERVAL ' . LOGIN_ATTEMPT_WINDOW_MINUTES . ' MINUTE'
    );
    $stmt->execute([$ip]);
    return (int) $stmt->fetchColumn() >= LOGIN_ATTEMPT_MAX;
}

/**
 * @return bool true on success
 * @throws TooManyLoginAttemptsException if this IP is currently locked out
 */
function attempt_admin_login(string $email, string $password): bool
{
    $ip = client_ip();
    if (admin_login_locked_out($ip)) {
        throw new TooManyLoginAttemptsException(
            'Too many failed login attempts. Try again in ' . LOGIN_ATTEMPT_WINDOW_MINUTES . ' minutes.'
        );
    }

    $stmt = db()->prepare('SELECT id, name, email, password_hash FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    $success = $admin && password_verify($password, $admin['password_hash']);
    db()->prepare('INSERT INTO login_attempts (identifier, success) VALUES (?, ?)')->execute([$ip, $success ? 1 : 0]);

    if (!$success) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id']    = $admin['id'];
    $_SESSION['admin_name']  = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];

    return true;
}

function admin_logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
