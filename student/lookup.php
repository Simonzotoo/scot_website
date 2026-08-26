<?php
/**
 * Lets the registration form prefill name/program/level when an admin has
 * already pre-added the student to the roster (admin/users.php) under the
 * same email, so they don't have to retype details we already have.
 *
 * There's no real integration with WIUC's student records here — this only
 * ever returns data an admin at SCOTSA typed in themselves.
 */
require_once __DIR__ . '/../includes/student_auth.php';

header('Content-Type: application/json');

if (rate_limit_hit('student-lookup:' . client_ip(), 20, 3600)) {
    http_response_code(429);
    echo json_encode(['found' => false]);
    exit;
}

$email = valid_school_email((string) ($_GET['email'] ?? ''));
if (!$email) {
    echo json_encode(['found' => false]);
    exit;
}

try {
    $stmt = db()->prepare('SELECT full_name, program_id, level_id FROM users WHERE email = ? AND password_hash IS NULL');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
} catch (Throwable $e) {
    error_log('[SCOTSA StudentLookup] error: ' . $e->getMessage());
    echo json_encode(['found' => false]);
    exit;
}

if (!$row) {
    echo json_encode(['found' => false]);
    exit;
}

echo json_encode([
    'found'      => true,
    'full_name'  => $row['full_name'],
    'program_id' => $row['program_id'],
    'level_id'   => $row['level_id'],
]);
