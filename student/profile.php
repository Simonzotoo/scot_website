<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/student_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_avatar') {
            $result = handle_avatar_upload($_FILES['avatar'] ?? [], 'student', $student['id'], $student['avatar_path']);
            if ($result['ok']) {
                db()->prepare('UPDATE users SET avatar_path = ? WHERE id = ?')->execute([$result['path'], $student['id']]);
                flash('success', 'Profile photo updated.');
            } else {
                flash('error', $result['error']);
            }
        } elseif ($action === 'remove_avatar') {
            if ($student['avatar_path']) {
                $old = realpath(IMAGES_ROOT . '/' . $student['avatar_path']);
                $root = realpath(IMAGES_ROOT . '/avatars');
                if ($old && $root && strncmp($old, $root, strlen($root)) === 0 && is_file($old)) {
                    @unlink($old);
                }
            }
            db()->prepare('UPDATE users SET avatar_path = NULL WHERE id = ?')->execute([$student['id']]);
            flash('success', 'Profile photo removed.');
        } elseif ($action === 'update_profile') {
            $fullName = substr(clean_text($_POST['full_name'] ?? ''), 0, 140);
            $programId = $_POST['program_id'] !== '' ? (int) $_POST['program_id'] : null;
            $levelId = $_POST['level_id'] !== '' ? (int) $_POST['level_id'] : null;

            if (!$fullName) {
                flash('error', 'Please enter your full name.');
            } else {
                db()->prepare('UPDATE users SET full_name = ?, program_id = ?, level_id = ? WHERE id = ?')
                    ->execute([$fullName, $programId, $levelId, $student['id']]);
                flash('success', 'Profile updated.');
            }
        } elseif ($action === 'change_password') {
            $current = (string) ($_POST['current_password'] ?? '');
            $new     = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');

            $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$student['id']]);
            $hash = (string) $stmt->fetchColumn();

            if (!password_verify($current, $hash)) {
                flash('error', 'Your current password is incorrect.');
            } elseif (strlen($new) < 8) {
                flash('error', 'New password must be at least 8 characters long.');
            } elseif ($new !== $confirm) {
                flash('error', 'New password and confirmation do not match.');
            } else {
                db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($new, PASSWORD_DEFAULT), $student['id']]);
                flash('success', 'Password changed.');
            }
        }
    } catch (Throwable $e) {
        error_log('[SCOTSA StudentProfile] DB error: ' . $e->getMessage());
        flash('error', 'A database error occurred. Please try again.');
    }

    header('Location: profile.php');
    exit;
}

$programs = db()->query('SELECT id, name FROM programs ORDER BY name')->fetchAll();
$levels   = db()->query('SELECT id, name FROM levels ORDER BY sort_order')->fetchAll();
?>
<div class="mx-auto max-w-2xl space-y-6">

    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-6">
        <h2 class="font-heading font-black text-lg text-ink dark:text-white">Profile Photo</h2>
        <div class="mt-4 flex items-center gap-5">
            <img src="<?= avatar_url($student['avatar_path'], $student['full_name']) ?>" alt="" class="h-20 w-20 rounded-full object-cover border border-slate-200 dark:border-white/10 flex-shrink-0">
            <div class="flex flex-wrap gap-2">
                <form method="post" enctype="multipart/form-data" class="flex items-center gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_avatar">
                    <label class="btn-primary text-sm cursor-pointer">
                        Upload photo
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only" onchange="this.form.submit()">
                    </label>
                </form>
                <?php if ($student['avatar_path']): ?>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="remove_avatar">
                    <button data-confirm="Remove your profile photo?" class="rounded-lg border border-slate-200 dark:border-white/10 px-4 py-2 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5" type="submit">Remove</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-400">JPEG, PNG, or WebP. Max 3 MB, cropped to a square.</p>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-6">
        <h2 class="font-heading font-black text-lg text-ink dark:text-white">Profile Details</h2>
        <p class="text-sm text-slate-400 mt-1"><?= e($student['email']) ?></p>

        <form method="post" class="mt-5 grid gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_profile">
            <label class="block text-sm font-bold text-slate-600 dark:text-slate-300">
                Full name
                <input class="form-input mt-1.5" name="full_name" value="<?= e($student['full_name']) ?>" required>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="block text-sm font-bold text-slate-600 dark:text-slate-300">
                    Program
                    <select class="form-input mt-1.5" name="program_id">
                        <option value="">None</option>
                        <?php foreach ($programs as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (int) $p['id'] === (int) $student['program_id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block text-sm font-bold text-slate-600 dark:text-slate-300">
                    Level
                    <select class="form-input mt-1.5" name="level_id">
                        <option value="">None</option>
                        <?php foreach ($levels as $l): ?>
                        <option value="<?= (int) $l['id'] ?>" <?= (int) $l['id'] === (int) $student['level_id'] ? 'selected' : '' ?>><?= e($l['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <button class="btn-primary mt-1 w-full sm:w-auto" type="submit">Save changes</button>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-6">
        <h2 class="font-heading font-black text-lg text-ink dark:text-white">Change Password</h2>
        <form method="post" class="mt-5 grid gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_password">
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-slate-300 mb-1.5" for="current_password">Current password</label>
                <?= password_field('current_password', 'current_password') ?>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-slate-300 mb-1.5" for="new_password">New password</label>
                <?= password_field('new_password', 'new_password', '', 'new-password', true, 8) ?>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-slate-300 mb-1.5" for="confirm_password">Confirm new password</label>
                <?= password_field('confirm_password', 'confirm_password', '', 'new-password', true, 8) ?>
            </div>
            <button class="btn-primary mt-1 w-full sm:w-auto" type="submit">Change password</button>
        </form>
    </div>

</div>
<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
