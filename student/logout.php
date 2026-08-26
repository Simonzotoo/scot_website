<?php
require_once __DIR__ . '/../includes/student_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();

logout_student();
header('Location: login.php');
exit;
