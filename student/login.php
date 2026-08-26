<?php
require_once __DIR__ . '/../includes/student_auth.php';

if (current_student()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $result = login_student($email, $password);

    if ($result === STUDENT_AUTH_OK) {
        header('Location: dashboard.php');
        exit;
    }

    $error = match ($result) {
        STUDENT_AUTH_WRONG_PASSWORD => 'Incorrect email or password.',
        STUDENT_AUTH_DB_ERROR       => 'A database error occurred. Please try again shortly.',
        STUDENT_AUTH_LOCKED         => 'Too many sign-in attempts. Please try again in a few minutes.',
        default                     => 'Sign-in failed. Please try again.',
    };
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Login: SCOTSA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
</head>
<body class="min-h-screen grid place-items-center p-4" style="font-family:'Plus Jakarta Sans',sans-serif; background:#0A1F44;">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="<?= BASE_URL ?>/index.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-200/60 hover:text-blue-200 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to website
            </a>
        </div>

        <div class="rounded-2xl bg-white shadow-2xl overflow-hidden">
            <div class="h-1.5 w-full" style="background:linear-gradient(to right,#0A1F44,#D4AF37,#0A1F44);"></div>

            <div class="p-8 sm:p-10">
                <div class="flex items-center gap-3 mb-7">
                    <img src="<?= IMAGES_URL ?>/logo/logo-dark.png" alt="SCOTSA logo"
                         class="h-11 w-11 object-contain flex-shrink-0"
                         onerror="this.src='<?= IMAGES_URL ?>/placeholders/logo-mark.svg'; this.onerror=null;">
                    <div>
                        <span class="block font-heading font-black text-scotsaBlue text-lg leading-none">SCOTSA</span>
                        <span class="block text-[10px] font-semibold text-slate-400 mt-0.5">Student Portal</span>
                    </div>
                </div>

                <h1 class="font-heading font-black text-2xl text-ink mb-1">Sign In</h1>
                <p class="text-sm text-slate-400 mb-7">Use your <?= e(STUDENT_EMAIL_DOMAIN) ?> email to access resources.</p>

                <?php if ($error): ?>
                <div class="mb-5 flex items-center gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <?= e($error) ?>
                </div>
                <?php endif; ?>

                <form method="post" class="grid gap-5">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="email">Email address</label>
                        <input id="email" class="form-input" type="email" name="email" placeholder="you@<?= e(STUDENT_EMAIL_DOMAIN) ?>" autocomplete="email" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="password">Password</label>
                        <?= password_field('password', 'password', 'Enter your password') ?>
                    </div>
                    <button class="btn-primary w-full py-3 text-sm" type="submit">Sign In</button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-400">
                    Don't have an account? <a href="register.php" class="font-bold text-scotsaBlue hover:underline">Create one</a>
                </p>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
