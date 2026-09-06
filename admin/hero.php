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
        $headline = clean_text($_POST['headline'] ?? '');
        $subtext  = clean_text($_POST['subtext'] ?? '');
        $position = clean_text($_POST['photo_position'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'archived'], true) ? $_POST['status'] : 'active';

        try {
            $photoPath = null;
            if (!empty($_FILES['photo']['name'])) {
                $photoPath = handle_image_upload($_FILES['photo'], 'hero');
            }

            if ($id > 0) {
                if ($photoPath !== null) {
                    $old = db()->prepare('SELECT photo_path FROM hero_slides WHERE id = ?');
                    $old->execute([$id]);
                    if ($oldPath = $old->fetchColumn()) {
                        delete_media_file($oldPath);
                    }
                    $stmt = db()->prepare('UPDATE hero_slides SET photo_path=?, photo_position=?, headline=?, subtext=?, sort_order=?, status=? WHERE id=?');
                    $stmt->execute([$photoPath, $position ?: null, $headline, $subtext, $sortOrder, $status, $id]);
                } else {
                    $stmt = db()->prepare('UPDATE hero_slides SET photo_position=?, headline=?, subtext=?, sort_order=?, status=? WHERE id=?');
                    $stmt->execute([$position ?: null, $headline, $subtext, $sortOrder, $status, $id]);
                }
                flash('success', 'Hero slide updated.');
            } else {
                if ($photoPath === null) {
                    flash('error', 'A photo is required for a new slide.');
                    header('Location: ' . BASE_URL . '/admin/hero.php');
                    exit;
                }
                $stmt = db()->prepare('INSERT INTO hero_slides (photo_path, photo_position, headline, subtext, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$photoPath, $position ?: null, $headline, $subtext, $sortOrder, $status]);
                flash('success', 'Hero slide added.');
            }
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = db()->prepare('SELECT photo_path FROM hero_slides WHERE id = ?');
        $old->execute([$id]);
        if ($oldPath = $old->fetchColumn()) {
            delete_media_file($oldPath);
        }
        db()->prepare('DELETE FROM hero_slides WHERE id = ?')->execute([$id]);
        flash('success', 'Hero slide deleted.');
    }

    header('Location: ' . BASE_URL . '/admin/hero.php');
    exit;
}

$slides = db()->query('SELECT * FROM hero_slides ORDER BY sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
foreach ($slides as $s) {
    if ((int) $s['id'] === $editId) {
        $editing = $s;
        break;
    }
}

$pageTitle = 'Hero Carousel';
$activeNav = 'hero';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Slide' : 'Add New Slide' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Photo <?= $editing ? '(leave blank to keep current)' : '' ?></label>
            <input class="admin-input" type="file" name="photo" accept="image/jpeg,image/png,image/webp" <?= $editing ? '' : 'required' ?>>
            <?php if ($editing): ?>
            <img class="thumb" style="margin-top:.5rem; width:120px; height:80px; object-fit:cover; border-radius:.5rem;" src="<?= IMAGES_URL ?>/<?= e($editing['photo_path']) ?>" alt="">
            <?php endif; ?>
        </div>
        <div class="admin-form-row">
            <label>Headline</label>
            <input class="admin-input" type="text" name="headline" value="<?= e($editing['headline'] ?? '') ?>">
        </div>
        <div class="admin-form-row">
            <label>Subtext</label>
            <textarea class="admin-textarea" name="subtext"><?= e($editing['subtext'] ?? '') ?></textarea>
        </div>
        <div class="admin-form-row">
            <label>Custom crop position (optional, e.g. "center 70%")</label>
            <input class="admin-input" type="text" name="photo_position" value="<?= e($editing['photo_position'] ?? '') ?>">
            <p class="admin-hint">Use this if the photo's main subject isn't centred — CSS background-position value.</p>
        </div>
        <div class="admin-form-row">
            <label>Sort Order</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? count($slides)) ?>">
        </div>
        <div class="admin-form-row">
            <label>Status</label>
            <select class="admin-select" name="status">
                <option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="archived" <?= ($editing['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Slide' ?></button>
        <?php if ($editing): ?>
        <a href="<?= BASE_URL ?>/admin/hero.php" class="admin-btn admin-btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Slides</h2>
    <table class="admin-table">
        <thead><tr><th></th><th>Headline</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($slides as $s): ?>
        <tr>
            <td><img class="thumb" src="<?= IMAGES_URL ?>/<?= e($s['photo_path']) ?>" alt=""></td>
            <td><?= e($s['headline'] ?: '(no headline)') ?></td>
            <td><?= (int) $s['sort_order'] ?></td>
            <td><span class="admin-badge admin-badge-<?= e($s['status']) ?>"><?= e($s['status']) ?></span></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $s['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this slide?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
