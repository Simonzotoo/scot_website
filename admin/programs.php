<?php
$pageTitle = 'Programs';
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save') {
            $name = substr(clean_text($_POST['name'] ?? ''), 0, 120);
            $description = clean_text($_POST['description'] ?? '');
            $slug = substr(strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')), 0, 140);
            if (!$name) {
                flash('error', 'Program name is required.');
            } elseif (!empty($_POST['id'])) {
                $id = (int) $_POST['id'];
                db()->prepare('UPDATE programs SET name = ?, slug = ?, description = ? WHERE id = ?')->execute([$name, $slug, $description, $id]);
                audit_log('update', 'program', $id, $name);
                flash('success', 'Program saved.');
            } else {
                db()->prepare('INSERT INTO programs (name, slug, description) VALUES (?, ?, ?)')->execute([$name, $slug, $description]);
                audit_log('create', 'program', (int) db()->lastInsertId(), $name);
                flash('success', 'Program saved.');
            }
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            db()->prepare('DELETE FROM programs WHERE id = ?')->execute([$id]);
            audit_log('delete', 'program', $id);
            flash('success', 'Program deleted.');
        }
    } catch (Throwable $e) {
        error_log('[SCOTSA Programs] DB error: ' . $e->getMessage());
        flash('error', 'A database error occurred. Please check your input and try again.');
    }
    header('Location: programs.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM programs WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$totalPrograms = (int) db()->query('SELECT COUNT(*) FROM programs')->fetchColumn();
$pg = paginate($totalPrograms, 20);
$programs = db()->query("SELECT * FROM programs ORDER BY name LIMIT {$pg['perPage']} OFFSET {$pg['offset']}")->fetchAll();
?>
<div class="grid gap-6 lg:grid-cols-[380px_1fr]">
    <form method="post" class="rounded-lg border border-slate-200 bg-white p-6">
        <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
        <h2 class="text-lg font-black"><?= $edit ? 'Edit Program' : 'Add Program' ?></h2>
        <label class="mt-4 block text-sm font-bold">Name<input class="form-input mt-2" name="name" value="<?= e($edit['name'] ?? '') ?>" required></label>
        <label class="mt-4 block text-sm font-bold">Description<textarea class="form-input mt-2" name="description"><?= e($edit['description'] ?? '') ?></textarea></label>
        <button class="btn-primary mt-5 w-full" type="submit">Save Program</button>
    </form>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-black">All Programs <span class="text-sm font-medium text-slate-400">(<?= $totalPrograms ?>)</span></h2>
        <div class="mt-4 grid gap-3">
            <?php foreach ($programs as $program): ?>
                <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-100 p-4">
                    <div><p class="font-black"><?= e($program['name']) ?></p><p class="text-sm text-slate-500"><?= e($program['description']) ?></p></div>
                    <div class="flex gap-2">
                        <a class="rounded border px-3 py-2 text-sm font-bold" href="?edit=<?= (int) $program['id'] ?>">Edit</a>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $program['id'] ?>"><button data-confirm="Delete this program and its courses?" class="rounded bg-red-600 px-3 py-2 text-sm font-bold text-white">Delete</button></form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?= pagination_links($pg) ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

