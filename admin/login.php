<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = clean_text($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    try {
        if ($email !== '' && $password !== '' && attempt_admin_login($email, $password)) {
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        }
        $error = 'Incorrect email or password.';
    } catch (TooManyLoginAttemptsException $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — SCOT Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@700;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-login-shell">
    <div class="admin-login-logo">
        <img src="<?= IMAGES_URL ?>/logo/scot-logo-dark.png" alt="SCOT — School of Computing and Technology">
    </div>
    <div class="admin-login-card">
        <h1>SCOT Admin</h1>
        <p class="sub">Sign in to manage the department site.</p>
        <?php if ($error): ?>
        <div class="admin-flash admin-flash-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="admin-form-row">
                <label for="email">Email</label>
                <input class="admin-input" type="email" id="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="admin-form-row">
                <label for="password">Password</label>
                <input class="admin-input" type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="admin-btn" style="width:100%; justify-content:center;">Sign In</button>
        </form>
    </div>
</div>
</body>
</html>
