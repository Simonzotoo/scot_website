<?php
$pageTitle = 'Admins';
require_once __DIR__ . '/../includes/admin_header.php';
require_super_admin();

function generate_temp_password(): string
{
    return bin2hex(random_bytes(6)); // 12 hex chars, well above the 10-char minimum
}

$revealPassword = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $name = substr(clean_text($_POST['name'] ?? ''), 0, 120);
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $role = $_POST['role'] === 'super_admin' ? 'super_admin' : 'admin';

            if (!$name || !$email) {
                flash('error', 'Provide a valid name and email address.');
            } else {
                $stmt = db()->prepare('SELECT id FROM admins WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    flash('error', 'An admin with that email already exists.');
                } else {
                    $tempPassword = generate_temp_password();
                    db()->prepare('INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, ?)')
                        ->execute([$name, $email, password_hash($tempPassword, PASSWORD_DEFAULT), $role]);
                    $newId = (int) db()->lastInsertId();
                    audit_log('create', 'admin', $newId, $email);
                    $_SESSION['reveal_password'] = ['email' => $email, 'password' => $tempPassword];
                    flash('success', 'Admin account created. Share the one-time password shown below with them securely.');
                }
            }
        } elseif ($action === 'reset_password') {
            $id = (int) $_POST['id'];
            $tempPassword = generate_temp_password();
            $stmt = db()->prepare('SELECT email FROM admins WHERE id = ?');
            $stmt->execute([$id]);
            $email = $stmt->fetchColumn();
            if ($email) {
                db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($tempPassword, PASSWORD_DEFAULT), $id]);
                audit_log('reset_password', 'admin', $id, $email);
                $_SESSION['reveal_password'] = ['email' => $email, 'password' => $tempPassword];
                flash('success', 'Password reset. Share the new one-time password shown below with them securely.');
            } else {
                flash('error', 'Admin not found.');
            }
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            if ($id === (int) $admin['id']) {
                flash('error', 'You cannot delete your own account.');
            } else {
                $superAdminCount = (int) db()->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'")->fetchColumn();
                $stmt = db()->prepare('SELECT role, email FROM admins WHERE id = ?');
                $stmt->execute([$id]);
                $target = $stmt->fetch();
                if ($target && $target['role'] === 'super_admin' && $superAdminCount <= 1) {
                    flash('error', 'Cannot delete the last remaining super admin.');
                } elseif ($target) {
                    db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);
                    audit_log('delete', 'admin', $id, $target['email']);
                    flash('success', 'Admin account deleted.');
                } else {
                    flash('error', 'Admin not found.');
                }
            }
        }
    } catch (Throwable $e) {
        error_log('[SCOTSA Admins] DB error: ' . $e->getMessage());
        flash('error', 'A database error occurred. Please try again.');
    }

    header('Location: admins.php');
    exit;
}

if (!empty($_SESSION['reveal_password'])) {
    $revealPassword = $_SESSION['reveal_password'];
    unset($_SESSION['reveal_password']);
}

$admins = db()->query('SELECT id, name, email, role, created_at FROM admins ORDER BY created_at')->fetchAll();
?>
<div class="grid gap-6 lg:grid-cols-[380px_1fr]">

    <div>
        <?php if ($revealPassword): ?>
        <div class="mb-5 rounded-lg border-2 border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
            <p class="text-sm font-black text-amber-800 dark:text-amber-300">One-time password: save it now</p>
            <p class="mt-1 text-xs text-amber-700 dark:text-amber-400">This will not be shown again. Share it with <strong><?= e($revealPassword['email']) ?></strong> through a secure channel and have them change it on first sign-in.</p>
            <code class="mt-3 block rounded bg-white dark:bg-slate-900 px-3 py-2 text-sm font-mono font-bold text-ink dark:text-white select-all"><?= e($revealPassword['password']) ?></code>
        </div>
        <?php endif; ?>

        <form method="post" class="rounded-lg border border-slate-200 bg-white p-6">
            <?= csrf_field() ?><input type="hidden" name="action" value="create">
            <h2 class="text-lg font-black">Add Admin</h2>
            <input class="form-input mt-4" name="name" placeholder="Full name" required>
            <input class="form-input mt-4" type="email" name="email" placeholder="Email" required>
            <select class="form-input mt-4" name="role">
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            </select>
            <p class="mt-2 text-xs text-slate-400">A one-time password is generated automatically. You'll see it once after creation.</p>
            <button class="btn-primary mt-5 w-full" type="submit">Create Admin</button>
        </form>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-black">All Admins <span class="text-sm font-medium text-slate-400">(<?= count($admins) ?>)</span></h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[680px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Role</th><th class="p-3">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $row): ?>
                    <tr class="border-t">
                        <td class="p-3 font-black"><?= e($row['name']) ?><?= (int) $row['id'] === (int) $admin['id'] ? ' <span class="text-xs font-semibold text-slate-400">(you)</span>' : '' ?></td>
                        <td class="p-3"><?= e($row['email']) ?></td>
                        <td class="p-3">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold <?= $row['role'] === 'super_admin' ? 'bg-scotsaGold/20 text-amber-700' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $row['role'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button data-confirm="Generate a new one-time password for <?= e($row['email']) ?>?" class="rounded border px-3 py-1.5 text-xs font-bold">Reset password</button>
                                </form>
                                <?php if ((int) $row['id'] !== (int) $admin['id']): ?>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button data-confirm="Delete admin account for <?= e($row['email']) ?>? This cannot be undone." class="rounded bg-red-600 px-3 py-1.5 text-xs font-bold text-white">Delete</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
