<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/student_auth.php';

$student = current_student();

if (rate_limit_hit('download:' . client_ip(), 30, 60)) {
    http_response_code(429);
    header('Retry-After: 60');
    exit('Too many requests. Please slow down and try again shortly.');
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT id, title, file_path, original_filename FROM past_questions WHERE id = ? AND status = "active" LIMIT 1');
$stmt->execute([$id]);
$resource = $stmt->fetch();

if (!$resource) {
    http_response_code(404);
    exit('Resource not found.');
}

$file = realpath(UPLOAD_ROOT . '/' . $resource['file_path']);
$uploadRoot = realpath(UPLOAD_ROOT);

if (!$file || !$uploadRoot || strncmp($file, $uploadRoot, strlen($uploadRoot)) !== 0 || !is_file($file)) {
    http_response_code(404);
    exit('File not found.');
}

$isPreview = isset($_GET['preview']) && $_GET['preview'] === '1';

if (!$isPreview) {
    db()->prepare('UPDATE past_questions SET download_count = download_count + 1 WHERE id = ?')->execute([$id]);
    db()->prepare('INSERT INTO downloads (past_question_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)')
        ->execute([$id, $student['id'] ?? null, $_SERVER['REMOTE_ADDR'] ?? null, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
}

$safeFilename = preg_replace('/[^\w\-. ]/u', '_', basename($resource['original_filename']));
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: ' . ($isPreview ? 'inline' : 'attachment') . '; filename="' . $safeFilename . '"');
header('X-Content-Type-Options: nosniff');
if ($isPreview) {
    header('Cache-Control: private, max-age=300');
}
// Stream the file directly instead of holding it in the request-wide output
// buffer (see includes/config.php) — matters for large PDFs.
while (ob_get_level() > 0) {
    ob_end_clean();
}
readfile($file);
exit;

