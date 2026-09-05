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
        $title = clean_text($_POST['title'] ?? '');
        $caption = clean_text($_POST['caption'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'archived'], true) ? $_POST['status'] : 'active';
        $notice = null;

        try {
            $filePath = null;
            $posterPath = null;
            if (!empty($_FILES['video']['name'])) {
                $result = handle_video_upload($_FILES['video'], 'watch');
                $filePath = $result['path'];
                $posterPath = $result['poster'];
                if (!$result['optimized']) {
                    $notice = 'Video saved but could not be auto-optimized (ffmpeg unavailable) — consider compressing it manually.';
                }
            }
            if (!empty($_FILES['poster']['name'])) {
                $posterPath = handle_image_upload($_FILES['poster'], 'video-posters');
            }

            if ($id > 0) {
                $old = db()->prepare('SELECT file_path, poster_path FROM videos WHERE id = ?');
                $old->execute([$id]);
                $oldRow = $old->fetch();

                $finalFile = $filePath ?? $oldRow['file_path'];
                $finalPoster = $posterPath ?? $oldRow['poster_path'];

                if ($filePath !== null && $oldRow['file_path']) {
                    delete_media_file($oldRow['file_path'], 'videos');
                }
                if ($posterPath !== null && $oldRow['poster_path']) {
                    delete_media_file($oldRow['poster_path']);
                }

                db()->prepare('UPDATE videos SET file_path=?, poster_path=?, title=?, caption=?, sort_order=?, status=? WHERE id=?')
                    ->execute([$finalFile, $finalPoster, $title, $caption, $sortOrder, $status, $id]);
                flash('success', 'Video updated.' . ($notice ? ' ' . $notice : ''));
            } else {
                if ($filePath === null) {
                    flash('error', 'A video file is required.');
                    header('Location: ' . BASE_URL . '/admin/videos.php');
                    exit;
                }
                db()->prepare('INSERT INTO videos (file_path, poster_path, title, caption, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([$filePath, $posterPath, $title, $caption, $sortOrder, $status]);
                flash('success', 'Video added.' . ($notice ? ' ' . $notice : ''));
            }
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = db()->prepare('SELECT file_path, poster_path FROM videos WHERE id = ?');
        $old->execute([$id]);
        if ($row = $old->fetch()) {
            delete_media_file($row['file_path'], 'videos');
            delete_media_file($row['poster_path']);
        }
        db()->prepare('DELETE FROM videos WHERE id = ?')->execute([$id]);
        flash('success', 'Video deleted.');
    }

    header('Location: ' . BASE_URL . '/admin/videos.php');
    exit;
}

$videos = db()->query('SELECT * FROM videos ORDER BY sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
foreach ($videos as $v) {
    if ((int) $v['id'] === $editId) {
        $editing = $v;
        break;
    }
}

$pageTitle = 'Watch Videos';
$activeNav = 'videos';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Video' : 'Add New Video' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Video file <?= $editing ? '(leave blank to keep current)' : '' ?></label>
            <input class="admin-input" type="file" name="video" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska" <?= $editing ? '' : 'required' ?>>
            <p class="admin-hint">Automatically transcoded to a web-friendly size, with a poster frame generated at the 1-second mark.</p>
        </div>
        <div class="admin-form-row">
            <label>Poster image override (optional)</label>
            <input class="admin-input" type="file" name="poster" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($editing['poster_path'])): ?>
            <img style="margin-top:.5rem; width:160px; height:90px; object-fit:cover; border-radius:.5rem;" src="<?= IMAGES_URL ?>/<?= e($editing['poster_path']) ?>" alt="">
            <?php endif; ?>
        </div>
        <div class="admin-form-row">
            <label>Title</label>
            <input class="admin-input" type="text" name="title" value="<?= e($editing['title'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row">
            <label>Caption</label>
            <textarea class="admin-textarea" name="caption"><?= e($editing['caption'] ?? '') ?></textarea>
        </div>
        <div class="admin-form-row">
            <label>Sort Order</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? count($videos)) ?>">
        </div>
        <div class="admin-form-row">
            <label>Status</label>
            <select class="admin-select" name="status">
                <option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="archived" <?= ($editing['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Video' ?></button>
        <?php if ($editing): ?>
        <a href="<?= BASE_URL ?>/admin/videos.php" class="admin-btn admin-btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Videos</h2>
    <table class="admin-table">
        <thead><tr><th></th><th>Title</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($videos as $v): ?>
        <tr>
            <td><?php if ($v['poster_path']): ?><img class="thumb" src="<?= IMAGES_URL ?>/<?= e($v['poster_path']) ?>" alt=""><?php endif; ?></td>
            <td><?= e($v['title']) ?></td>
            <td><?= (int) $v['sort_order'] ?></td>
            <td><span class="admin-badge admin-badge-<?= e($v['status']) ?>"><?= e($v['status']) ?></span></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $v['id'] ?>">Edit</a>
                <form method="post" onsubmit="return confirm('Delete this video?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
