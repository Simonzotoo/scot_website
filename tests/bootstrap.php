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
require_once dirname(__DIR__) . '/includes/security.php';

// Starts the session here, before PHPUnit has printed a single character of
// progress-dot output. Any test file that pulls in includes/session.php
// later would otherwise hit "cannot start session: headers already sent" —
// CLI has no real HTTP headers, but PHP enforces the same rule once *any*
// output has occurred.
require_once dirname(__DIR__) . '/includes/session.php';
