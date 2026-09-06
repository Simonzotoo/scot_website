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
        $postDate = clean_text($_POST['post_date'] ?? '') ?: date('Y-m-d');
        $excerpt = trim((string) ($_POST['excerpt'] ?? '')) ?: null;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'published';
        $notice = null;

        try {
            $videoPath = null;
            $posterPath = null;
            if (!empty($_FILES['video']['name'])) {
                $result = handle_video_upload($_FILES['video'], 'blog');
                $videoPath = $result['path'];
                $posterPath = $result['poster'];
                if (!$result['optimized']) {
                    $notice = 'Video saved but could not be auto-optimized (ffmpeg unavailable).';
                }
            }

            if ($id > 0) {
                $old = db()->prepare('SELECT video_path FROM blog_posts WHERE id = ?');
                $old->execute([$id]);
                $oldVideo = $old->fetchColumn();
                $finalVideo = $videoPath ?? $oldVideo;

                if ($videoPath !== null && $oldVideo) {
                    delete_media_file($oldVideo, 'videos');
                }
                $stmt = db()->prepare('UPDATE blog_posts SET title=?, post_date=?, excerpt=?, sort_order=?, status=?' . ($videoPath !== null ? ', video_path=?, poster_path=?' : '') . ' WHERE id=?');
                $params = [$title, $postDate, $excerpt, $sortOrder, $status];
                if ($videoPath !== null) {
                    $params[] = $videoPath;
                    $params[] = $posterPath;
                }
                $params[] = $id;
                $stmt->execute($params);
                $postId = $id;
                flash('success', 'Blog post updated.' . ($notice ? ' ' . $notice : ''));
            } else {
                db()->prepare('INSERT INTO blog_posts (title, post_date, excerpt, video_path, poster_path, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$title, $postDate, $excerpt, $videoPath, $posterPath, $sortOrder, $status]);
                $postId = (int) db()->lastInsertId();
                flash('success', 'Blog post added.' . ($notice ? ' ' . $notice : ''));
            }

            // Existing photos: update captions/featured/order, handle deletes.
            $existingIds = $_POST['photo_id'] ?? [];
            $captions = $_POST['photo_caption'] ?? [];
            $featuredId = (int) ($_POST['photo_featured'] ?? 0);
            $deleteIds = array_map('intval', $_POST['photo_delete'] ?? []);

            foreach ($existingIds as $i => $photoId) {
                $photoId = (int) $photoId;
                if (in_array($photoId, $deleteIds, true)) {
                    $row = db()->prepare('SELECT photo_path FROM blog_post_photos WHERE id=?');
                    $row->execute([$photoId]);
                    if ($path = $row->fetchColumn()) {
                        delete_media_file($path);
                    }
                    db()->prepare('DELETE FROM blog_post_photos WHERE id=?')->execute([$photoId]);
                    continue;
                }
                db()->prepare('UPDATE blog_post_photos SET caption=?, featured=?, sort_order=? WHERE id=?')
                    ->execute([clean_text($captions[$i] ?? ''), $photoId === $featuredId ? 1 : 0, $i, $photoId]);
            }

            // New photo uploads (multi-file input "new_photos[]").
            if (!empty($_FILES['new_photos']['name'][0])) {
                $count = count($_FILES['new_photos']['name']);
                $newCaptions = $_POST['new_photo_caption'] ?? [];
                $maxSort = (int) db()->query('SELECT COALESCE(MAX(sort_order),-1) FROM blog_post_photos WHERE post_id=' . (int) $postId)->fetchColumn();
                $insertStmt = db()->prepare('INSERT INTO blog_post_photos (post_id, photo_path, caption, featured, sort_order) VALUES (?, ?, ?, 0, ?)');
                for ($i = 0; $i < $count; $i++) {
                    if (($_FILES['new_photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    $file = [
                        'name' => $_FILES['new_photos']['name'][$i],
                        'type' => $_FILES['new_photos']['type'][$i],
                        'tmp_name' => $_FILES['new_photos']['tmp_name'][$i],
                        'error' => $_FILES['new_photos']['error'][$i],
                        'size' => $_FILES['new_photos']['size'][$i],
                    ];
                    $path = handle_image_upload($file, 'blog');
                    $insertStmt->execute([$postId, $path, clean_text($newCaptions[$i] ?? ''), ++$maxSort]);
                }
            }
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $rows = db()->prepare('SELECT photo_path FROM blog_post_photos WHERE post_id=?');
        $rows->execute([$id]);
        foreach ($rows->fetchAll(PDO::FETCH_COLUMN) as $path) {
            delete_media_file($path);
        }
        $old = db()->prepare('SELECT video_path FROM blog_posts WHERE id = ?');
        $old->execute([$id]);
        if ($v = $old->fetchColumn()) {
            delete_media_file($v, 'videos');
        }
        db()->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$id]);
        flash('success', 'Blog post deleted.');
    }

    header('Location: ' . BASE_URL . '/admin/blog.php');
    exit;
}

$posts = db()->query('SELECT * FROM blog_posts ORDER BY sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
$editPhotos = [];
foreach ($posts as $p) {
    if ((int) $p['id'] === $editId) {
        $editing = $p;
        $photoRows = db()->prepare('SELECT * FROM blog_post_photos WHERE post_id=? ORDER BY sort_order');
        $photoRows->execute([$editId]);
        $editPhotos = $photoRows->fetchAll();
        break;
    }
}

$pageTitle = 'Blog';
$activeNav = 'blog';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Post' : 'Add New Post' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Title</label>
            <input class="admin-input" type="text" name="title" value="<?= e($editing['title'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row">
            <label>Date</label>
            <input class="admin-input" type="date" name="post_date" value="<?= e($editing['post_date'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="admin-form-row">
            <label>Excerpt</label>
            <textarea class="admin-textarea" name="excerpt" style="min-height:8rem;"><?= e($editing['excerpt'] ?? '') ?></textarea>
            <p class="admin-hint">Keep this a public-facing summary — no internal process details.</p>
        </div>
        <div class="admin-form-row">
            <label>Video <?= $editing ? '(leave blank to keep current)' : '' ?></label>
            <input class="admin-input" type="file" name="video" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska">
        </div>

        <?php if ($editing && $editPhotos): ?>
        <div class="admin-form-row">
            <label>Existing Photos</label>
            <?php foreach ($editPhotos as $ph): ?>
            <div class="admin-repeater-row" style="align-items:center;">
                <img src="<?= IMAGES_URL ?>/<?= e($ph['photo_path']) ?>" style="width:70px; height:52px; object-fit:cover; border-radius:.4rem; flex-shrink:0;" alt="">
                <input type="hidden" name="photo_id[]" value="<?= $ph['id'] ?>">
                <input class="admin-input" type="text" name="photo_caption[]" value="<?= e($ph['caption']) ?>" placeholder="Caption">
                <label style="display:flex; align-items:center; gap:.3rem; font-size:.75rem; white-space:nowrap;">
                    <input type="radio" name="photo_featured" value="<?= $ph['id'] ?>" <?= $ph['featured'] ? 'checked' : '' ?>> Featured
                </label>
                <label style="display:flex; align-items:center; gap:.3rem; font-size:.75rem; white-space:nowrap; color:#b91c1c;">
                    <input type="checkbox" name="photo_delete[]" value="<?= $ph['id'] ?>"> Delete
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="admin-form-row">
            <label>Add Photos</label>
            <input class="admin-input" type="file" name="new_photos[]" accept="image/jpeg,image/png,image/webp" multiple>
            <p class="admin-hint">Uploaded photos get an editable caption after you save once (add captions on the next edit).</p>
        </div>

        <div class="admin-form-row">
            <label>Sort Order</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? count($posts)) ?>">
        </div>
        <div class="admin-form-row">
            <label>Status</label>
            <select class="admin-select" name="status">
                <option value="published" <?= ($editing['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($editing['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
        </div>

        <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Post' ?></button>
        <?php if ($editing): ?>
        <a href="<?= BASE_URL ?>/admin/blog.php" class="admin-btn admin-btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Posts</h2>
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Date</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($posts as $p): ?>
        <tr>
            <td><?= e($p['title']) ?></td>
            <td><?= e($p['post_date']) ?></td>
            <td><span class="admin-badge admin-badge-<?= $p['status'] === 'published' ? 'active' : 'archived' ?>"><?= e($p['status']) ?></span></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $p['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this post?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
