<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
$admin = require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $caption = clean_text($_POST['caption'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'archived'], true) ? $_POST['status'] : 'active';

        try {
            $photoPath = null;
            if (!empty($_FILES['photo']['name'])) {
                $photoPath = handle_image_upload($_FILES['photo'], 'labs');
            }

            if ($id > 0) {
                if ($photoPath !== null) {
                    $old = db()->prepare('SELECT photo_path FROM facilities WHERE id = ?');
                    $old->execute([$id]);
                    if ($oldPath = $old->fetchColumn()) {
                        delete_media_file($oldPath);
                    }
                    db()->prepare('UPDATE facilities SET photo_path=?, caption=?, featured=?, sort_order=?, status=? WHERE id=?')
                        ->execute([$photoPath, $caption, $featured, $sortOrder, $status, $id]);
                } else {
                    db()->prepare('UPDATE facilities SET caption=?, featured=?, sort_order=?, status=? WHERE id=?')
                        ->execute([$caption, $featured, $sortOrder, $status, $id]);
                }
                flash('success', 'Facility tile updated.');
            } else {
                if ($photoPath === null) {
                    flash('error', 'A photo is required.');
                    header('Location: ' . BASE_URL . '/admin/facilities.php');
                    exit;
                }
                db()->prepare('INSERT INTO facilities (photo_path, caption, featured, sort_order, status) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$photoPath, $caption, $featured, $sortOrder, $status]);
                flash('success', 'Facility tile added.');
            }
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = db()->prepare('SELECT photo_path FROM facilities WHERE id = ?');
        $old->execute([$id]);
        if ($oldPath = $old->fetchColumn()) {
            delete_media_file($oldPath);
        }
        db()->prepare('DELETE FROM facilities WHERE id = ?')->execute([$id]);
        flash('success', 'Facility tile deleted.');
    }

    header('Location: ' . BASE_URL . '/admin/facilities.php');
    exit;
}

$tiles = db()->query('SELECT * FROM facilities ORDER BY sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
foreach ($tiles as $t) {
    if ((int) $t['id'] === $editId) {
        $editing = $t;
        break;
    }
}

$pageTitle = 'Facilities';
$activeNav = 'facilities';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Tile' : 'Add New Tile' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Photo <?= $editing ? '(leave blank to keep current)' : '' ?></label>
            <input class="admin-input" type="file" name="photo" accept="image/jpeg,image/png,image/webp" <?= $editing ? '' : 'required' ?>>
            <?php if ($editing): ?>
            <img style="margin-top:.5rem; width:120px; height:80px; object-fit:cover; border-radius:.5rem;" src="<?= IMAGES_URL ?>/<?= e($editing['photo_path']) ?>" alt="">
            <?php endif; ?>
        </div>
        <div class="admin-form-row">
            <label>Caption</label>
            <input class="admin-input" type="text" name="caption" value="<?= e($editing['caption'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row admin-checkbox-row">
            <input type="checkbox" id="featured" name="featured" <?= !empty($editing['featured']) ? 'checked' : '' ?>>
            <label for="featured" style="margin:0;">Featured (large bento tile — only one should be featured)</label>
        </div>
        <div class="admin-form-row">
            <label>Sort Order</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? count($tiles)) ?>">
        </div>
        <div class="admin-form-row">
            <label>Status</label>
            <select class="admin-select" name="status">
                <option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="archived" <?= ($editing['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Tile' ?></button>
        <?php if ($editing): ?>
        <a href="<?= BASE_URL ?>/admin/facilities.php" class="admin-btn admin-btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Tiles</h2>
    <table class="admin-table">
        <thead><tr><th></th><th>Caption</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tiles as $t): ?>
        <tr>
            <td><img class="thumb" src="<?= IMAGES_URL ?>/<?= e($t['photo_path']) ?>" alt=""></td>
            <td><?= e($t['caption']) ?> <?php if ($t['featured']): ?><span class="admin-badge admin-badge-featured">Featured</span><?php endif; ?></td>
            <td><?= (int) $t['sort_order'] ?></td>
            <td><span class="admin-badge admin-badge-<?= e($t['status']) ?>"><?= e($t['status']) ?></span></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $t['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this tile?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
