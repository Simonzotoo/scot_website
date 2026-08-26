<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/admin_header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_avatar') {
            $result = handle_avatar_upload($_FILES['avatar'] ?? [], 'admin', $admin['id'], $admin['avatar_path']);
            if ($result['ok']) {
                db()->prepare('UPDATE admins SET avatar_path = ? WHERE id = ?')->execute([$result['path'], $admin['id']]);
                audit_log('update', 'admin_profile', $admin['id'], 'avatar changed');
                flash('success', 'Profile photo updated.');
            } else {
                flash('error', $result['error']);
            }
        } elseif ($action === 'remove_avatar') {
            if ($admin['avatar_path']) {
                $old = realpath(IMAGES_ROOT . '/' . $admin['avatar_path']);
                $root = realpath(IMAGES_ROOT . '/avatars');
                if ($old && $root && strncmp($old, $root, strlen($root)) === 0 && is_file($old)) {
                    @unlink($old);
                }
            }
            db()->prepare('UPDATE admins SET avatar_path = NULL WHERE id = ?')->execute([$admin['id']]);
            flash('success', 'Profile photo removed.');
        } elseif ($action === 'update_profile') {
            $name = substr(clean_text($_POST['name'] ?? ''), 0, 120);
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

            if (!$name || !$email) {
                flash('error', 'Provide a valid name and email address.');
            } else {
                $stmt = db()->prepare('SELECT id FROM admins WHERE email = ? AND id != ?');
                $stmt->execute([$email, $admin['id']]);
                if ($stmt->fetch()) {
                    flash('error', 'That email address is already used by another admin account.');
                } else {
                    db()->prepare('UPDATE admins SET name = ?, email = ? WHERE id = ?')
                        ->execute([$name, $email, $admin['id']]);
                    audit_log('update', 'admin_profile', $admin['id'], $email);
                    flash('success', 'Profile updated.');
                }
            }
        } elseif ($action === 'change_password') {
            $current = (string) ($_POST['current_password'] ?? '');
            $new     = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');

            $stmt = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
            $stmt->execute([$admin['id']]);
            $hash = (string) $stmt->fetchColumn();

            if (!password_verify($current, $hash)) {
                flash('error', 'Your current password is incorrect.');
            } elseif (strlen($new) < 10) {
                flash('error', 'New password must be at least 10 characters long.');
            } elseif ($new !== $confirm) {
                flash('error', 'New password and confirmation do not match.');
            } else {
                db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
                audit_log('change_password', 'admin_profile', $admin['id']);
                flash('success', 'Password changed. Use it next time you sign in.');
            }
        }
    } catch (Throwable $e) {
        error_log('[SCOTSA Profile] DB error: ' . $e->getMessage());
        flash('error', 'A database error occurred. Please try again.');
    }

    header('Location: profile.php');
    exit;
}
?>
<div class="mx-auto max-w-2xl space-y-6">

    <div class="rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 p-6 shadow-sm">
        <h2 class="font-heading font-black text-lg text-ink dark:text-white">Profile Photo</h2>
        <div class="mt-4 flex items-center gap-5">
            <img src="<?= avatar_url($admin['avatar_path'], $admin['name']) ?>" alt="" class="h-20 w-20 rounded-full object-cover border border-slate-200 dark:border-white/10 flex-shrink-0">
            <div class="flex flex-wrap gap-2">
                <form method="post" enctype="multipart/form-data" class="flex items-center gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_avatar">
                    <label class="btn-primary text-sm cursor-pointer">
                        Upload photo
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only" onchange="this.form.submit()">
                    </label>
                </form>
                <?php if ($admin['avatar_path']): ?>
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

    <div class="rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 p-6 shadow-sm">
        <h2 class="font-heading font-black text-lg text-ink dark:text-white">Profile Details</h2>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Signed in as <strong class="text-slate-600 dark:text-slate-300"><?= e($admin['role'] === 'super_admin' ? 'Super Admin' : 'Admin') ?></strong></p>

        <form method="post" class="mt-5 grid gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_profile">
            <label class="block text-sm font-bold text-slate-600 dark:text-slate-300">
                Full name
                <input class="form-input mt-1.5" name="name" value="<?= e($admin['name']) ?>" required>
            </label>
            <label class="block text-sm font-bold text-slate-600 dark:text-slate-300">
                Email address
                <input class="form-input mt-1.5" type="email" name="email" value="<?= e($admin['email']) ?>" required>
            </label>
            <button class="btn-primary mt-1 w-full sm:w-auto" type="submit">Save changes</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 p-6 shadow-sm">
        <h2 class="font-heading font-black text-lg text-ink dark:text-white">Change Password</h2>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Use at least 10 characters. Change the seeded default password immediately if you haven't already.</p>

        <form method="post" class="mt-5 grid gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_password">
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-slate-300 mb-1.5" for="current_password">Current password</label>
                <?= password_field('current_password', 'current_password') ?>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-slate-300 mb-1.5" for="new_password">New password</label>
                <?= password_field('new_password', 'new_password', '', 'new-password', true, 10) ?>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-slate-300 mb-1.5" for="confirm_password">Confirm new password</label>
                <?= password_field('confirm_password', 'confirm_password', '', 'new-password', true, 10) ?>
            </div>
            <button class="btn-primary mt-1 w-full sm:w-auto" type="submit">Change password</button>
        </form>
    </div>

</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
