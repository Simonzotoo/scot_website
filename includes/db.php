<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Single lazily-created PDO connection for the whole request. Throws on
 * connection failure rather than leaking DB credentials/paths in a stack
 * trace to the browser — callers in admin/ catch this and show a flash
 * message instead.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST', '127.0.0.1');
    $name = env('DB_NAME', 'scotsa_platform');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        throw new RuntimeException('Database connection failed. Check .env DB_* settings.', 0, $e);
    }

    return $pdo;
}
