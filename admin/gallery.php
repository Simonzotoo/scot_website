<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
$admin = require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_photo') {
        $id = (int) ($_POST['id'] ?? 0);
        $caption = clean_text($_POST['caption'] ?? '');
        $category = clean_text($_POST['category'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'archived'], true) ? $_POST['status'] : 'active';

        try {
            $photoPath = null;
            if (!empty($_FILES['photo']['name'])) {
                $photoPath = handle_image_upload($_FILES['photo'], 'gallery');
            }

            if ($id > 0) {
                if ($photoPath !== null) {
                    $old = db()->prepare('SELECT photo_path FROM gallery_items WHERE id = ?');
                    $old->execute([$id]);
                    if ($oldPath = $old->fetchColumn()) {
                        delete_media_file($oldPath);
                    }
                    db()->prepare('UPDATE gallery_items SET photo_path=?, caption=?, category=?, sort_order=?, status=? WHERE id=?')
                        ->execute([$photoPath, $caption, $category, $sortOrder, $status, $id]);
                } else {
                    db()->prepare('UPDATE gallery_items SET caption=?, category=?, sort_order=?, status=? WHERE id=?')
                        ->execute([$caption, $category, $sortOrder, $status, $id]);
                }
                flash('success', 'Photo updated.');
            } else {
                if ($photoPath === null) {
                    flash('error', 'A photo is required.');
                    header('Location: ' . BASE_URL . '/admin/gallery.php');
                    exit;
                }
                db()->prepare('INSERT INTO gallery_items (photo_path, caption, category, sort_order, status) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$photoPath, $caption, $category, $sortOrder, $status]);
                flash('success', 'Photo added.');
            }
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    } elseif ($action === 'delete_photo') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = db()->prepare('SELECT photo_path FROM gallery_items WHERE id = ?');
        $old->execute([$id]);
        if ($oldPath = $old->fetchColumn()) {
            delete_media_file($oldPath);
        }
        db()->prepare('DELETE FROM gallery_items WHERE id = ?')->execute([$id]);
        flash('success', 'Photo deleted.');
    } elseif ($action === 'add_category') {
        $name = clean_text($_POST['name'] ?? '');
        if ($name !== '') {
            $maxSort = (int) db()->query('SELECT COALESCE(MAX(sort_order),-1) FROM gallery_categories')->fetchColumn();
            db()->prepare('INSERT IGNORE INTO gallery_categories (name, sort_order) VALUES (?, ?)')->execute([$name, $maxSort + 1]);
            flash('success', 'Category added.');
        }
    } elseif ($action === 'delete_category') {
        $id = (int) ($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM gallery_categories WHERE id = ?')->execute([$id]);
        flash('success', 'Category removed (existing photos keep their category text).');
    }

    header('Location: ' . BASE_URL . '/admin/gallery.php');
    exit;
}

$categories = db()->query('SELECT * FROM gallery_categories ORDER BY sort_order')->fetchAll();
$photos = db()->query('SELECT * FROM gallery_items ORDER BY sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
foreach ($photos as $ph) {
    if ((int) $ph['id'] === $editId) {
        $editing = $ph;
        break;
    }
}

$pageTitle = 'Gallery';
$activeNav = 'gallery';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-card">
    <h2>Categories (filter tab order)</h2>
    <div style="display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1rem;">
        <?php foreach ($categories as $c): ?>
        <form method="post" style="display:flex; align-items:center; gap:.35rem; background:#f1f5f9; border-radius:999px; padding:.3rem .3rem .3rem .8rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <span style="font-size:.8rem; font-weight:700;"><?= e($c['name']) ?></span>
            <button type="submit" class="admin-repeater-remove" style="width:1.6rem; height:1.6rem;" onclick="return confirm('Remove this category tab?');">&times;</button>
        </form>
        <?php endforeach; ?>
    </div>
    <form method="post" style="display:flex; gap:.5rem;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_category">
        <input class="admin-input" type="text" name="name" placeholder="New category name" style="max-width:260px;">
        <button type="submit" class="admin-btn admin-btn-secondary">+ Add Category</button>
    </form>
</div>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Photo' : 'Add New Photo' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_photo">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Photo <?= $editing ? '(leave blank to keep current)' : '' ?></label>
            <input class="admin-input" type="file" name="photo" accept="image/jpeg,image/png,image/webp" <?= $editing ? '' : 'required' ?>>
            <?php if ($editing): ?>
            <img style="margin-top:.5rem; width:120px; height:90px; object-fit:cover; border-radius:.5rem;" src="<?= IMAGES_URL ?>/<?= e($editing['photo_path']) ?>" alt="">
            <?php endif; ?>
        </div>
        <div class="admin-form-row">
            <label>Caption</label>
            <input class="admin-input" type="text" name="caption" value="<?= e($editing['caption'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row">
            <label>Category</label>
            <select class="admin-select" name="category">
                <?php foreach ($categories as $c): ?>
                <option value="<?= e($c['name']) ?>" <?= ($editing['category'] ?? '') === $c['name'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="admin-form-row">
            <label>Sort Order</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? count($photos)) ?>">
        </div>
        <div class="admin-form-row">
            <label>Status</label>
            <select class="admin-select" name="status">
                <option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="archived" <?= ($editing['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Photo' ?></button>
        <?php if ($editing): ?>
        <a href="<?= BASE_URL ?>/admin/gallery.php" class="admin-btn admin-btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Photos (<?= count($photos) ?>)</h2>
    <table class="admin-table">
        <thead><tr><th></th><th>Caption</th><th>Category</th><th>Order</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($photos as $ph): ?>
        <tr>
            <td><img class="thumb" src="<?= IMAGES_URL ?>/<?= e($ph['photo_path']) ?>" alt=""></td>
            <td><?= e($ph['caption']) ?></td>
            <td><?= e($ph['category']) ?></td>
            <td><?= (int) $ph['sort_order'] ?></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $ph['id'] ?>">Edit</a>
                <form method="post" onsubmit="return confirm('Delete this photo?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_photo">
                    <input type="hidden" name="id" value="<?= $ph['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
