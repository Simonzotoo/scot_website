<?php
$pageTitle = 'Gallery';
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id        = (int) ($_POST['id'] ?? 0);
        $category  = strtolower(substr(clean_text($_POST['category'] ?? ''), 0, 60));
        $category  = preg_replace('/[^a-z0-9-]+/', '-', str_replace(' ', '-', $category)) ?: '';
        $caption   = substr(clean_text($_POST['caption'] ?? ''), 0, 180);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if (!$category || !$caption) {
            flash('error', 'Category and caption are required.');
            header('Location: gallery.php'); exit;
        }

        $photoPath = null;
        if ($id) {
            $stmt = db()->prepare('SELECT photo_path FROM gallery_items WHERE id = ?');
            $stmt->execute([$id]);
            $photoPath = $stmt->fetchColumn() ?: null;
        }

        if (!empty($_FILES['photo']['name'])) {
            $result = handle_media_upload($_FILES['photo'], 'gallery', $category);
            if (!$result['ok']) {
                flash('error', $result['error']);
                header('Location: gallery.php'); exit;
            }
            if ($id) {
                delete_media($photoPath);
            }
            $photoPath = $result['path'];
        } elseif (!$id) {
            flash('error', 'Please choose a photo to upload.');
            header('Location: gallery.php'); exit;
        }

        try {
            if ($id) {
                db()->prepare('UPDATE gallery_items SET category=?, caption=?, photo_path=?, sort_order=? WHERE id=?')
                    ->execute([$category, $caption, $photoPath, $sortOrder, $id]);
                audit_log('update', 'gallery_item', $id, $caption);
                flash('success', 'Gallery photo saved.');
            } else {
                db()->prepare('INSERT INTO gallery_items (category, caption, photo_path, sort_order) VALUES (?,?,?,?)')
                    ->execute([$category, $caption, $photoPath, $sortOrder]);
                audit_log('create', 'gallery_item', (int) db()->lastInsertId(), $caption);
                flash('success', 'Photo added to gallery.');
            }
        } catch (Throwable $e) {
            error_log('[SCOTSA Gallery] DB error: ' . $e->getMessage());
            flash('error', 'A database error occurred. Please try again.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT photo_path FROM gallery_items WHERE id = ?');
        $stmt->execute([$id]);
        $photo = $stmt->fetchColumn();
        db()->prepare('DELETE FROM gallery_items WHERE id = ?')->execute([$id]);
        delete_media($photo ?: null);
        audit_log('delete', 'gallery_item', $id);
        flash('success', 'Photo removed from gallery.');
    }

    header('Location: gallery.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM gallery_items WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}

$existingCategories = db()->query(
    "SELECT DISTINCT category FROM gallery_items ORDER BY category"
)->fetchAll(PDO::FETCH_COLUMN);

$totalItems = (int) db()->query('SELECT COUNT(*) FROM gallery_items')->fetchColumn();
$pg = paginate($totalItems, 24);
$items = db()->query(
    "SELECT * FROM gallery_items ORDER BY category, sort_order, id LIMIT {$pg['perPage']} OFFSET {$pg['offset']}"
)->fetchAll();
?>

<div class="mb-6">
    <h1 class="font-heading font-black text-2xl text-ink">Gallery</h1>
    <p class="mt-1 text-sm text-slate-500">
        Manages the public Gallery page. Add, recategorise, or remove photos without touching code.
    </p>
</div>

<div class="grid gap-6 xl:grid-cols-[420px_1fr]">
    <form method="post" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-6 h-fit">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">

        <h2 class="text-lg font-black"><?= $edit ? 'Edit Photo' : 'Add Photo' ?></h2>

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Category</label>
        <input class="form-input" name="category" list="gallery-categories" value="<?= e($edit['category'] ?? '') ?>" placeholder="e.g. events, seminars, tech" required>
        <datalist id="gallery-categories">
            <?php foreach ($existingCategories as $c): ?>
            <option value="<?= e($c) ?>">
            <?php endforeach; ?>
        </datalist>
        <p class="mt-1 text-xs text-slate-400">Pick an existing category from the list, or type a new one to create it.</p>

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Caption</label>
        <input class="form-input" name="caption" value="<?= e($edit['caption'] ?? '') ?>" placeholder="e.g. SCOTSA Annual General Assembly" required>

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Sort order</label>
        <input class="form-input" type="number" name="sort_order" value="<?= e((string) ($edit['sort_order'] ?? 0)) ?>">

        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Photo</label>
        <?php if (!empty($edit['photo_path'])): ?>
        <img src="<?= IMAGES_URL ?>/<?= e($edit['photo_path']) ?>" class="mb-2 h-24 w-24 rounded-lg object-cover border border-slate-200">
        <?php endif; ?>
        <input type="file" class="form-input" name="photo" accept="image/jpeg,image/png,image/webp" <?= $edit ? '' : 'required' ?>>
        <p class="mt-1 text-xs text-slate-400">JPEG, PNG, or WebP, up to 8 MB. <?= $edit ? 'Leave blank to keep the current photo.' : '' ?></p>

        <div class="mt-5 flex gap-2">
            <button class="btn-primary flex-1" type="submit">Save</button>
            <?php if ($edit): ?>
            <a href="gallery.php" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-black">All Photos <span class="text-sm font-medium text-slate-400">(<?= $totalItems ?>)</span></h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($items as $item): ?>
            <div class="rounded-lg border border-slate-200 overflow-hidden">
                <img src="<?= IMAGES_URL ?>/<?= e($item['photo_path']) ?>" class="h-28 w-full object-cover">
                <div class="p-2.5">
                    <p class="text-xs font-bold text-slate-700 truncate" title="<?= e($item['caption']) ?>"><?= e($item['caption']) ?></p>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wide mt-0.5"><?= e($item['category']) ?></p>
                    <div class="mt-2 flex gap-1.5">
                        <a href="?edit=<?= (int) $item['id'] ?>" class="flex-1 text-center rounded border px-2 py-1 text-[11px] font-bold">Edit</a>
                        <form method="post" class="flex-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button data-confirm="Remove this photo from the gallery?" class="w-full rounded bg-red-600 px-2 py-1 text-[11px] font-bold text-white">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (!$items): ?>
            <p class="sm:col-span-3 xl:col-span-4 p-6 text-center text-slate-400">No photos yet. Add one using the form.</p>
            <?php endif; ?>
        </div>
        <?= pagination_links($pg) ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
