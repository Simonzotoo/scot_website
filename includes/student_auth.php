<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

/* ── Result codes (internal use only) ─────────────────────────
   Never expose these directly to end users. */
define('STUDENT_AUTH_OK',              0);
define('STUDENT_AUTH_INVALID_EMAIL',   1);
define('STUDENT_AUTH_EMAIL_TAKEN',     2);
define('STUDENT_AUTH_WEAK_PASSWORD',   3);
define('STUDENT_AUTH_WRONG_PASSWORD',  4);
define('STUDENT_AUTH_DB_ERROR',        5);
define('STUDENT_AUTH_LOCKED',          6);
define('STUDENT_AUTH_MISSING_NAME',    7);

define('STUDENT_SESSION_IDLE_SECONDS',     4 * 60 * 60);
define('STUDENT_SESSION_ABSOLUTE_SECONDS', 30 * 24 * 60 * 60);

/**
 * Validates format AND that the address belongs to the school domain.
 * Returns the normalized email, or null if either check fails.
 */
function valid_school_email(string $email): ?string
{
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        return null;
    }
    $suffix = '@' . strtolower(STUDENT_EMAIL_DOMAIN);
    return str_ends_with(strtolower($email), $suffix) ? $email : null;
}

function register_student(string $fullName, string $email, string $password, ?int $programId, ?int $levelId): int
{
    if (rate_limit_hit('student-register:' . client_ip(), 10, 3600)) {
        return STUDENT_AUTH_LOCKED;
    }

    $fullName = substr(clean_text($fullName), 0, 140);
    $email = valid_school_email($email);

    if (!$fullName) {
        return STUDENT_AUTH_MISSING_NAME;
    }
    if (!$email) {
        return STUDENT_AUTH_INVALID_EMAIL;
    }
    if (strlen($password) < 8) {
        return STUDENT_AUTH_WEAK_PASSWORD;
    }

    try {
        $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($existing) {
            if ($existing['password_hash'] !== null) {
                return STUDENT_AUTH_EMAIL_TAKEN;
            }
            // Claim a roster row an admin pre-added but that was never activated.
            db()->prepare('UPDATE users SET full_name = ?, password_hash = ?, program_id = ?, level_id = ? WHERE id = ?')
                ->execute([$fullName, $hash, $programId, $levelId, $existing['id']]);
            $studentId = (int) $existing['id'];
        } else {
            db()->prepare('INSERT INTO users (full_name, email, password_hash, program_id, level_id) VALUES (?, ?, ?, ?, ?)')
                ->execute([$fullName, $email, $hash, $programId, $levelId]);
            $studentId = (int) db()->lastInsertId();
        }
    } catch (Throwable $e) {
        error_log('[SCOTSA StudentAuth] register error: ' . $e->getMessage());
        return STUDENT_AUTH_DB_ERROR;
    }

    start_student_session($studentId);
    return STUDENT_AUTH_OK;
}

function login_student(string $email, string $password): int
{
    if (rate_limit_hit('student-login:' . client_ip(), 8, 900)) {
        return STUDENT_AUTH_LOCKED;
    }

    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        return STUDENT_AUTH_WRONG_PASSWORD;
    }

    try {
        $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('[SCOTSA StudentAuth] login DB error: ' . $e->getMessage());
        return STUDENT_AUTH_DB_ERROR;
    }

    if (!$user || $user['password_hash'] === null || !password_verify($password, $user['password_hash'])) {
        return STUDENT_AUTH_WRONG_PASSWORD;
    }

    start_student_session((int) $user['id']);
    return STUDENT_AUTH_OK;
}

function start_student_session(int $studentId): void
{
    session_regenerate_id(true);
    $_SESSION['student_id']          = $studentId;
    $_SESSION['student_login_at']    = time();
    $_SESSION['student_last_active'] = time();
}

function current_student(): ?array
{
    if (empty($_SESSION['student_id'])) {
        return null;
    }

    $now = time();
    if (
        (($now - (int) ($_SESSION['student_last_active'] ?? $now)) > STUDENT_SESSION_IDLE_SECONDS) ||
        (($now - (int) ($_SESSION['student_login_at'] ?? $now)) > STUDENT_SESSION_ABSOLUTE_SECONDS)
    ) {
        logout_student();
        return null;
    }

    try {
        $stmt = db()->prepare(
            'SELECT u.id, u.full_name, u.email, u.program_id, u.level_id, u.avatar_path,
                    p.name AS program_name, l.name AS level_name
             FROM users u
             LEFT JOIN programs p ON p.id = u.program_id
             LEFT JOIN levels l ON l.id = u.level_id
             WHERE u.id = ? AND u.password_hash IS NOT NULL
             LIMIT 1'
        );
        $stmt->execute([$_SESSION['student_id']]);
        $student = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('[SCOTSA StudentAuth] current_student error: ' . $e->getMessage());
        unset($_SESSION['student_id']);
        return null;
    }

    if (!$student) {
        unset($_SESSION['student_id']);
        return null;
    }

    $_SESSION['student_last_active'] = $now;
    return $student;
}

function require_student(): void
{
    if (!current_student()) {
        header('Location: ' . BASE_URL . '/student/login.php');
        exit;
    }
}

function logout_student(): void
{
    unset($_SESSION['student_id'], $_SESSION['student_login_at'], $_SESSION['student_last_active']);
    session_regenerate_id(true);
}
