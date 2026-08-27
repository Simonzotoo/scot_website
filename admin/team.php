<?php
$pageTitle = 'Team & Leadership';
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id        = (int) ($_POST['id'] ?? 0);
        $roster    = ($_POST['roster'] ?? '') === 'executive' ? 'executive' : 'leadership';
        $role      = substr(clean_text($_POST['role'] ?? ''), 0, 120);
        $name      = trim(clean_text($_POST['name'] ?? ''));
        $portfolio = substr(clean_text($_POST['portfolio'] ?? ''), 0, 180);
        $bio       = clean_text($_POST['bio'] ?? '');
        $initials  = substr(strtoupper(clean_text($_POST['initials'] ?? '')), 0, 4);
        $gradient  = substr(clean_text($_POST['gradient'] ?? ''), 0, 120);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if (!$role) {
            flash('error', 'Role is required.');
            header('Location: team.php'); exit;
        }

        $photoPath = null;
        $oldPhoto  = null;
        if ($id) {
            $stmt = db()->prepare('SELECT photo_path FROM team_members WHERE id = ?');
            $stmt->execute([$id]);
            $oldPhoto = $stmt->fetchColumn() ?: null;
            $photoPath = $oldPhoto;
        }

        if (!empty($_POST['remove_photo'])) {
            delete_media($oldPhoto);
            $photoPath = null;
        }

        if (!empty($_FILES['photo']['name'])) {
            $result = handle_media_upload($_FILES['photo'], 'executives', $roster . '-' . preg_replace('/[^a-z0-9]+/i', '-', $role));
            if (!$result['ok']) {
                flash('error', $result['error']);
                header('Location: team.php'); exit;
            }
            delete_media($oldPhoto);
            $photoPath = $result['path'];
        }

        try {
            if ($id) {
                db()->prepare(
                    'UPDATE team_members SET roster=?, role=?, name=?, portfolio=?, bio=?, initials=?, gradient=?, photo_path=?, sort_order=? WHERE id=?'
                )->execute([$roster, $role, $name ?: null, $portfolio ?: null, $bio ?: null, $initials ?: null, $gradient ?: null, $photoPath, $sortOrder, $id]);
                audit_log('update', 'team_member', $id, $role);
                flash('success', 'Team member saved.');
            } else {
                db()->prepare(
                    'INSERT INTO team_members (roster, role, name, portfolio, bio, initials, gradient, photo_path, sort_order) VALUES (?,?,?,?,?,?,?,?,?)'
                )->execute([$roster, $role, $name ?: null, $portfolio ?: null, $bio ?: null, $initials ?: null, $gradient ?: null, $photoPath, $sortOrder]);
                audit_log('create', 'team_member', (int) db()->lastInsertId(), $role);
                flash('success', 'Team member added.');
            }
        } catch (Throwable $e) {
            error_log('[SCOTSA Team] DB error: ' . $e->getMessage());
            flash('error', 'A database error occurred. Please try again.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT photo_path FROM team_members WHERE id = ?');
        $stmt->execute([$id]);
        $photo = $stmt->fetchColumn();
        db()->prepare('DELETE FROM team_members WHERE id = ?')->execute([$id]);
        delete_media($photo ?: null);
        audit_log('delete', 'team_member', $id);
        flash('success', 'Team member removed.');
    }

    header('Location: team.php' . (!empty($_POST['roster']) ? '?roster=' . urlencode($_POST['roster']) : ''));
    exit;
}

$roster = ($_GET['roster'] ?? '') === 'executive' ? 'executive' : 'leadership';

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM team_members WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
    if ($edit) {
        $roster = $edit['roster'];
    }
}

$stmt = db()->prepare('SELECT * FROM team_members WHERE roster = ? ORDER BY sort_order, id');
$stmt->execute([$roster]);
$members = $stmt->fetchAll();
?>

<div class="mb-6">
    <h1 class="font-heading font-black text-2xl text-ink">Team &amp; Leadership</h1>
    <p class="mt-1 text-sm text-slate-500">
        Manages the "School Leadership" section on the homepage and the "Executive Team" page. No code changes needed for name, photo, or bio updates.
    </p>
</div>

<!-- Roster tabs -->
<div class="mb-6 flex gap-2">
    <a href="team.php?roster=leadership" class="rounded-lg px-4 py-2 text-sm font-bold <?= $roster === 'leadership' ? 'bg-scotsaBlue text-white' : 'border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
        School Leadership
    </a>
    <a href="team.php?roster=executive" class="rounded-lg px-4 py-2 text-sm font-bold <?= $roster === 'executive' ? 'bg-scotsaBlue text-white' : 'border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
        Executive Team
    </a>
</div>

<div class="grid gap-6 xl:grid-cols-[420px_1fr]">
    <form method="post" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-6 h-fit">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
        <input type="hidden" name="roster" value="<?= e($roster) ?>">

        <h2 class="text-lg font-black"><?= $edit ? 'Edit' : 'Add' ?> <?= $roster === 'executive' ? 'Executive' : 'Leadership' ?> Entry</h2>

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Role / Title</label>
        <input class="form-input" name="role" value="<?= e($edit['role'] ?? '') ?>" placeholder="e.g. Head of Department, Cyber Security" required>

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Name</label>
        <input class="form-input" name="name" value="<?= e($edit['name'] ?? '') ?>" placeholder="Leave blank if the seat is vacant">
        <p class="mt-1 text-xs text-slate-400">Leaving this blank displays the seat as vacant (e.g. "Awaiting Election").</p>

        <?php if ($roster === 'executive'): ?>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Portfolio (short tagline)</label>
        <input class="form-input" name="portfolio" value="<?= e($edit['portfolio'] ?? '') ?>" placeholder="e.g. Finance, Budgets & Accountability">

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Bio</label>
        <textarea class="form-input" name="bio" rows="4" placeholder="Full responsibilities paragraph shown on the Executives page"><?= e($edit['bio'] ?? '') ?></textarea>

        <div class="grid gap-4 sm:grid-cols-2 mt-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Initials (badge fallback)</label>
                <input class="form-input" name="initials" maxlength="4" value="<?= e($edit['initials'] ?? '') ?>" placeholder="e.g. TR">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Card gradient (CSS)</label>
                <input class="form-input" name="gradient" value="<?= e($edit['gradient'] ?? '') ?>" placeholder="135deg, #0A1F44 0%, #0d2a5c 100%">
            </div>
        </div>
        <?php endif; ?>

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Sort order</label>
        <input class="form-input" type="number" name="sort_order" value="<?= e((string) ($edit['sort_order'] ?? 0)) ?>">

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Photo</label>
        <?php if (!empty($edit['photo_path'])): ?>
        <div class="mb-2 flex items-center gap-3">
            <img src="<?= IMAGES_URL ?>/<?= e($edit['photo_path']) ?>" class="h-16 w-16 rounded-lg object-cover border border-slate-200">
            <label class="flex items-center gap-1.5 text-xs text-red-500 font-semibold">
                <input type="checkbox" name="remove_photo" value="1"> Remove current photo
            </label>
        </div>
        <?php endif; ?>
        <input type="file" class="form-input" name="photo" accept="image/jpeg,image/png,image/webp">
        <p class="mt-1 text-xs text-slate-400">Leave blank to keep the current photo (or show initials if none is set). JPEG, PNG, or WebP, up to 8 MB.</p>

        <div class="mt-5 flex gap-2">
            <button class="btn-primary flex-1" type="submit">Save</button>
            <?php if ($edit): ?>
            <a href="team.php?roster=<?= e($roster) ?>" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-black"><?= $roster === 'executive' ? 'Executive Team' : 'School Leadership' ?> <span class="text-sm font-medium text-slate-400">(<?= count($members) ?>)</span></h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr><th class="p-3">Photo</th><th class="p-3">Role</th><th class="p-3">Name</th><th class="p-3">Order</th><th class="p-3">Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($members as $m): ?>
                    <tr class="border-t">
                        <td class="p-3">
                            <img src="<?= e($m['photo_path'] ? IMAGES_URL . '/' . $m['photo_path'] : avatar_url(null, $m['name'] ?: $m['role'])) ?>"
                                 class="h-10 w-10 rounded-lg object-cover border border-slate-200">
                        </td>
                        <td class="p-3 font-semibold"><?= e($m['role']) ?></td>
                        <td class="p-3 <?= $m['name'] ? '' : 'text-slate-400 italic' ?>"><?= e($m['name'] ?: 'Vacant') ?></td>
                        <td class="p-3 text-slate-400"><?= (int) $m['sort_order'] ?></td>
                        <td class="flex gap-2 p-3">
                            <a class="rounded border px-3 py-2 font-bold" href="?edit=<?= (int) $m['id'] ?>">Edit</a>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                <input type="hidden" name="roster" value="<?= e($roster) ?>">
                                <button data-confirm="Remove &quot;<?= e($m['role']) ?>&quot;? This cannot be undone." class="rounded bg-red-600 px-3 py-2 font-bold text-white">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$members): ?>
                    <tr><td colspan="5" class="p-6 text-center text-slate-400">No entries yet. Add one using the form.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
