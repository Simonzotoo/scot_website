<?php
declare(strict_types=1);

// Same reasoning as the unconditional ob_start() in includes/config.php:
// login/logout call session_regenerate_id(), which needs to send a fresh
// Set-Cookie header and refuses once anything has been output. In a real
// request that's handled by config.php's own buffer; under PHPUnit, the
// runner's own progress dots ("...") count as output by the time later
// tests run, so buffer here too rather than let that leak into "headers
// already sent" failures that have nothing to do with the code under test.
ob_start();

require_once dirname(__DIR__) . '/vendor/autoload.php';

// DB_NAME etc. are already overridden via <env> in phpunit.xml (read through
// getenv() by includes/env.php) before this file even runs, so requiring the
// app's normal bootstrap here connects to the test database, never the real
// scotsa_platform one.
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/security.php';

// Starts the session here, before PHPUnit has printed a single character of
// progress-dot output. Any test file that pulls in includes/session.php
// later (directly or via student_auth.php / auth.php) would otherwise hit
// "cannot start session: headers already sent" — CLI has no real HTTP
// headers, but PHP enforces the same rule once *any* output has occurred.
require_once dirname(__DIR__) . '/includes/session.php';

/**
 * Wipes every table that tests might touch, in FK-safe order. Call this in
 * setUp()/tearDown() for any test that writes to the database, so tests
 * never depend on — or leak into — another test's data.
 */
function test_reset_database(): void
{
    // DELETE rather than TRUNCATE: the app's DB user only ever has SELECT/
    // INSERT/UPDATE/DELETE in production (no DROP), and TRUNCATE requires
    // DROP under the hood — using DELETE here keeps the test user's grants
    // identical to production instead of quietly needing more.
    $pdo = db();
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach ([
        'downloads', 'past_questions', 'courses', 'announcements', 'audit_log',
        'login_attempts', 'rate_limit_hits', 'gallery_items', 'team_members',
        'site_settings', 'page_hero_photos', 'users', 'admins',
    ] as $table) {
        $pdo->exec("DELETE FROM `$table`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}
