<?php
require_once __DIR__ . '/../includes/student_auth.php';

if (current_student()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$old = ['full_name' => '', 'email' => '', 'program_id' => '', 'level_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old['full_name']  = (string) ($_POST['full_name'] ?? '');
    $old['email']      = (string) ($_POST['email'] ?? '');
    $old['program_id'] = (string) ($_POST['program_id'] ?? '');
    $old['level_id']   = (string) ($_POST['level_id'] ?? '');

    $password  = (string) ($_POST['password'] ?? '');
    $confirm   = (string) ($_POST['confirm_password'] ?? '');
    $programId = $old['program_id'] !== '' ? (int) $old['program_id'] : null;
    $levelId   = $old['level_id'] !== '' ? (int) $old['level_id'] : null;

    if ($password !== $confirm) {
        $error = 'Password and confirmation do not match.';
    } else {
        $result = register_student($old['full_name'], $old['email'], $password, $programId, $levelId);

        if ($result === STUDENT_AUTH_OK) {
            header('Location: dashboard.php');
            exit;
        }

        $error = match ($result) {
            STUDENT_AUTH_MISSING_NAME  => 'Please enter your full name.',
            STUDENT_AUTH_INVALID_EMAIL => 'Please use a valid @' . STUDENT_EMAIL_DOMAIN . ' email address.',
            STUDENT_AUTH_EMAIL_TAKEN   => 'An account with that email already exists. Try signing in instead.',
            STUDENT_AUTH_WEAK_PASSWORD => 'Password must be at least 8 characters long.',
            STUDENT_AUTH_DB_ERROR      => 'A database error occurred. Please try again shortly.',
            STUDENT_AUTH_LOCKED        => 'Too many attempts. Please try again in a few minutes.',
            default                    => 'Registration failed. Please try again.',
        };
    }
}

$programs = db()->query('SELECT id, name FROM programs ORDER BY name')->fetchAll();
$levels   = db()->query('SELECT id, name FROM levels ORDER BY sort_order')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration: SCOTSA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
</head>
<body class="min-h-screen grid place-items-center p-4 py-10" style="font-family:'Plus Jakarta Sans',sans-serif; background:#0A1F44;">

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

                <h1 class="font-heading font-black text-2xl text-ink mb-1">Create Your Account</h1>
                <p class="text-sm text-slate-400 mb-7">Only @<?= e(STUDENT_EMAIL_DOMAIN) ?> addresses can register.</p>

                <?php if ($error): ?>
                <div class="mb-5 flex items-center gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <?= e($error) ?>
                </div>
                <?php endif; ?>

                <form method="post" class="grid gap-4">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="email">Email address</label>
                        <input id="email" class="form-input" type="email" name="email" value="<?= e($old['email']) ?>" placeholder="you@<?= e(STUDENT_EMAIL_DOMAIN) ?>" autocomplete="email" required>
                        <p id="prefill-note" class="hidden mt-1.5 text-xs font-semibold text-emerald-600">Found your details, filled them in below.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="full_name">Full name</label>
                        <input id="full_name" class="form-input" name="full_name" value="<?= e($old['full_name']) ?>" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="program_id">Program</label>
                            <select id="program_id" class="form-input" name="program_id">
                                <option value="">Select a program</option>
                                <?php foreach ($programs as $p): ?>
                                <option value="<?= (int) $p['id'] ?>" <?= (string) $p['id'] === $old['program_id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="level_id">Level</label>
                            <select id="level_id" class="form-input" name="level_id">
                                <option value="">Select a level</option>
                                <?php foreach ($levels as $l): ?>
                                <option value="<?= (int) $l['id'] ?>" <?= (string) $l['id'] === $old['level_id'] ? 'selected' : '' ?>><?= e($l['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="password">Password</label>
                        <?= password_field('password', 'password', '', 'new-password', true, 8) ?>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="confirm_password">Confirm password</label>
                        <?= password_field('confirm_password', 'confirm_password', '', 'new-password', true, 8) ?>
                    </div>
                    <button class="btn-primary w-full py-3 text-sm mt-1" type="submit">Create Account</button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-400">
                    Already have an account? <a href="login.php" class="font-bold text-scotsaBlue hover:underline">Sign in</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const emailEl  = document.getElementById('email');
            const nameEl   = document.getElementById('full_name');
            const progEl   = document.getElementById('program_id');
            const levelEl  = document.getElementById('level_id');
            const note     = document.getElementById('prefill-note');
            if (!emailEl) return;

            let lastChecked = '';
            emailEl.addEventListener('blur', async () => {
                const email = emailEl.value.trim();
                if (!email || email === lastChecked) return;
                lastChecked = email;

                try {
                    const res  = await fetch('lookup.php?email=' + encodeURIComponent(email));
                    const data = await res.json();
                    if (!data.found) { note.classList.add('hidden'); return; }

                    if (!nameEl.value) nameEl.value = data.full_name || '';
                    if (data.program_id && progEl && !progEl.value) progEl.value = data.program_id;
                    if (data.level_id && levelEl && !levelEl.value) levelEl.value = data.level_id;
                    note.classList.remove('hidden');
                } catch (e) {
                    /* lookup is a nicety, not required for registration to work */
                }
            });
        })();
    </script>
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
