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
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'archived'], true) ? $_POST['status'] : 'active';

        try {
            $filePath = null;
            $fileSize = null;
            if (!empty($_FILES['document']['name'])) {
                $result = handle_document_upload($_FILES['document']);
                $filePath = $result['path'];
                $fileSize = $result['size'];
            }

            if ($id > 0) {
                if ($filePath !== null) {
                    $old = db()->prepare('SELECT file_path FROM documents WHERE id = ?');
                    $old->execute([$id]);
                    if ($oldPath = $old->fetchColumn()) {
                        delete_media_file($oldPath, 'documents');
                    }
                    db()->prepare('UPDATE documents SET title=?, file_path=?, file_size=?, sort_order=?, status=? WHERE id=?')
                        ->execute([$title, $filePath, $fileSize, $sortOrder, $status, $id]);
                } else {
                    db()->prepare('UPDATE documents SET title=?, sort_order=?, status=? WHERE id=?')
                        ->execute([$title, $sortOrder, $status, $id]);
                }
                flash('success', 'Document updated.');
            } else {
                if ($filePath === null) {
                    flash('error', 'A file is required for a new document.');
                    header('Location: ' . BASE_URL . '/admin/documents.php');
                    exit;
                }
                db()->prepare('INSERT INTO documents (title, file_path, file_size, sort_order, status) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$title, $filePath, $fileSize, $sortOrder, $status]);
                flash('success', 'Document added.');
            }
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = db()->prepare('SELECT file_path FROM documents WHERE id = ?');
        $old->execute([$id]);
        if ($oldPath = $old->fetchColumn()) {
            delete_media_file($oldPath, 'documents');
        }
        db()->prepare('DELETE FROM documents WHERE id = ?')->execute([$id]);
        flash('success', 'Document deleted.');
    }

    header('Location: ' . BASE_URL . '/admin/documents.php');
    exit;
}

$documents = db()->query('SELECT * FROM documents ORDER BY sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
foreach ($documents as $d) {
    if ((int) $d['id'] === $editId) {
        $editing = $d;
        break;
    }
}

$pageTitle = 'Documents';
$activeNav = 'documents';
require __DIR__ . '/includes/admin_header.php';

$formatBytes = static function (int $bytes): string {
    if ($bytes >= 1024 * 1024) {
        return round($bytes / (1024 * 1024), 1) . ' MB';
    }
    return round($bytes / 1024) . ' KB';
};
?>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Document' : 'Add New Document' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Title</label>
            <input class="admin-input" type="text" name="title" value="<?= e($editing['title'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row">
            <label>File <?= $editing ? '(leave blank to keep current)' : '' ?></label>
            <input class="admin-input" type="file" name="document" accept=".pdf,.doc,.docx" <?= $editing ? '' : 'required' ?>>
            <?php if ($editing): ?>
            <p class="admin-hint">Current file: <?= e(basename($editing['file_path'])) ?> (<?= $formatBytes((int) $editing['file_size']) ?>)</p>
            <?php endif; ?>
            <p class="admin-hint">PDF or Word documents, up to 20MB.</p>
        </div>
        <div class="admin-form-row">
            <label>Sort Order</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? count($documents)) ?>">
        </div>
        <div class="admin-form-row">
            <label>Status</label>
            <select class="admin-select" name="status">
                <option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="archived" <?= ($editing['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Document' ?></button>
        <?php if ($editing): ?>
        <a href="<?= BASE_URL ?>/admin/documents.php" class="admin-btn admin-btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Documents (<?= count($documents) ?>)</h2>
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Size</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($documents as $d): ?>
        <tr>
            <td><?= e($d['title']) ?></td>
            <td><?= $formatBytes((int) $d['file_size']) ?></td>
            <td><?= (int) $d['sort_order'] ?></td>
            <td><span class="admin-badge admin-badge-<?= e($d['status']) ?>"><?= e($d['status']) ?></span></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $d['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this document?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
