<?php
$pageTitle = 'Users';
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save') {
            $fullName = substr(clean_text($_POST['full_name'] ?? ''), 0, 140);
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $programId = $_POST['program_id'] !== '' ? (int) $_POST['program_id'] : null;
            $levelId = $_POST['level_id'] !== '' ? (int) $_POST['level_id'] : null;
            if ($fullName && $email) {
                db()->prepare('INSERT INTO users (full_name, email, program_id, level_id) VALUES (?, ?, ?, ?)')->execute([$fullName, $email, $programId, $levelId]);
                audit_log('create', 'user', (int) db()->lastInsertId(), $email);
                flash('success', 'User added.');
            } else {
                flash('error', 'Provide a valid name and email.');
            }
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
            audit_log('delete', 'user', $id);
            flash('success', 'User deleted.');
        } elseif ($action === 'reset_password') {
            $id = (int) $_POST['id'];
            db()->prepare('UPDATE users SET password_hash = NULL WHERE id = ?')->execute([$id]);
            audit_log('reset_password', 'user', $id);
            flash('success', 'Password cleared. The student can reclaim their account by registering again with the same email.');
        }
    } catch (Throwable $e) {
        error_log('[SCOTSA Users] DB error: ' . $e->getMessage());
        flash('error', 'A database error occurred. That email may already be registered.');
    }
    header('Location: users.php');
    exit;
}

$programs = db()->query('SELECT id, name FROM programs ORDER BY name')->fetchAll();
$levels = db()->query('SELECT id, name FROM levels ORDER BY sort_order')->fetchAll();
$totalUsers = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$pg = paginate($totalUsers, 20);
$users = db()->query("SELECT u.*, p.name program_name, l.name level_name FROM users u LEFT JOIN programs p ON p.id=u.program_id LEFT JOIN levels l ON l.id=u.level_id ORDER BY u.created_at DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}")->fetchAll();
?>
<div class="grid gap-6 lg:grid-cols-[380px_1fr]">
    <form method="post" class="rounded-lg border border-slate-200 bg-white p-6">
        <?= csrf_field() ?><input type="hidden" name="action" value="save">
        <h2 class="text-lg font-black">Pre-add Student</h2>
        <p class="mt-1 text-xs text-slate-400">Creates a roster entry the student can claim by registering at <span class="font-mono">/student/register.php</span> with the same email. No password is set here.</p>
        <input class="form-input mt-4" name="full_name" placeholder="Full name" required>
        <input class="form-input mt-4" type="email" name="email" placeholder="Email" required>
        <select class="form-input mt-4" name="program_id"><option value="">Program</option><?php foreach ($programs as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?></select>
        <select class="form-input mt-4" name="level_id"><option value="">Level</option><?php foreach ($levels as $l): ?><option value="<?= (int) $l['id'] ?>"><?= e($l['name']) ?></option><?php endforeach; ?></select>
        <button class="btn-primary mt-5 w-full" type="submit">Add to Roster</button>
    </form>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-black">Students <span class="text-sm font-medium text-slate-400">(<?= $totalUsers ?>)</span></h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Program</th><th class="p-3">Status</th><th class="p-3">Action</th></tr></thead><tbody>
                <?php foreach ($users as $user): ?><tr class="border-t">
                    <td class="p-3 font-black"><?= e($user['full_name']) ?></td>
                    <td class="p-3"><?= e($user['email']) ?></td>
                    <td class="p-3"><?= e(($user['program_name'] ?? 'N/A') . ' ' . ($user['level_name'] ?? '')) ?></td>
                    <td class="p-3">
                        <?php if ($user['password_hash']): ?>
                        <span class="rounded-full bg-emerald-50 text-emerald-700 px-2 py-1 text-[11px] font-bold">Active</span>
                        <?php else: ?>
                        <span class="rounded-full bg-slate-100 text-slate-500 px-2 py-1 text-[11px] font-bold">Not activated</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3">
                        <div class="flex flex-wrap gap-2">
                            <?php if ($user['password_hash']): ?>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"><button data-confirm="Clear this student's password? They'll need to register again to regain access." class="rounded border px-3 py-2 text-xs font-bold">Reset password</button></form>
                            <?php endif; ?>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"><button data-confirm="Delete this student?" class="rounded bg-red-600 px-3 py-2 text-xs font-bold text-white">Delete</button></form>
                        </div>
                    </td>
                </tr><?php endforeach; ?>
            </tbody></table>
        </div>
        <?= pagination_links($pg) ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

