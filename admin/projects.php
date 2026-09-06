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
        $student = clean_text($_POST['student_name'] ?? '');
        $programme = clean_text($_POST['programme_label'] ?? '') ?: null;
        $supervisor = clean_text($_POST['supervisor'] ?? '') ?: null;
        $coSupervisor = clean_text($_POST['co_supervisor'] ?? '') ?: null;
        $summary = trim((string) ($_POST['summary'] ?? '')) ?: null;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'archived'], true) ? $_POST['status'] : 'active';
        $notice = null;

        try {
            $videoPath = null;
            $posterPath = null;
            if (!empty($_FILES['video']['name'])) {
                $result = handle_video_upload($_FILES['video'], 'projects');
                $videoPath = $result['path'];
                $posterPath = $result['poster'];
                if (!$result['optimized']) {
                    $notice = 'Video saved but could not be auto-optimized (ffmpeg unavailable).';
                }
            }

            if ($id > 0) {
                $old = db()->prepare('SELECT video_path, poster_path FROM student_projects WHERE id = ?');
                $old->execute([$id]);
                $oldRow = $old->fetch();
                $finalVideo = $videoPath ?? $oldRow['video_path'];
                $finalPoster = $posterPath ?? $oldRow['poster_path'];
                if ($videoPath !== null && $oldRow['video_path']) {
                    delete_media_file($oldRow['video_path'], 'videos');
                }

                db()->prepare('UPDATE student_projects SET title=?, student_name=?, programme_label=?, supervisor=?, co_supervisor=?, video_path=?, poster_path=?, summary=?, sort_order=?, status=? WHERE id=?')
                    ->execute([$title, $student, $programme, $supervisor, $coSupervisor, $finalVideo, $finalPoster, $summary, $sortOrder, $status, $id]);
                $projectId = $id;
                flash('success', 'Project updated.' . ($notice ? ' ' . $notice : ''));
            } else {
                db()->prepare('INSERT INTO student_projects (title, student_name, programme_label, supervisor, co_supervisor, video_path, poster_path, summary, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$title, $student, $programme, $supervisor, $coSupervisor, $videoPath, $posterPath, $summary, $sortOrder, $status]);
                $projectId = (int) db()->lastInsertId();
                flash('success', 'Project added.' . ($notice ? ' ' . $notice : ''));
            }

            db()->prepare('DELETE FROM student_project_stack WHERE project_id = ?')->execute([$projectId]);
            $stackStmt = db()->prepare('INSERT INTO student_project_stack (project_id, tech_text, sort_order) VALUES (?, ?, ?)');
            $i = 0;
            foreach ($_POST['stack'] ?? [] as $tech) {
                $tech = trim((string) $tech);
                if ($tech === '') {
                    continue;
                }
                $stackStmt->execute([$projectId, $tech, $i++]);
            }
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = db()->prepare('SELECT video_path FROM student_projects WHERE id = ?');
        $old->execute([$id]);
        if ($v = $old->fetchColumn()) {
            delete_media_file($v, 'videos');
        }
        db()->prepare('DELETE FROM student_projects WHERE id = ?')->execute([$id]);
        flash('success', 'Project deleted.');
    }

    header('Location: ' . BASE_URL . '/admin/projects.php');
    exit;
}

$projects = db()->query('SELECT * FROM student_projects ORDER BY sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
$editStack = [];
foreach ($projects as $p) {
    if ((int) $p['id'] === $editId) {
        $editing = $p;
        $stackRows = db()->prepare('SELECT tech_text FROM student_project_stack WHERE project_id=? ORDER BY sort_order');
        $stackRows->execute([$editId]);
        $editStack = $stackRows->fetchAll(PDO::FETCH_COLUMN);
        break;
    }
}

$pageTitle = 'Student Projects';
$activeNav = 'projects';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Project' : 'Add New Project' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Title</label>
            <input class="admin-input" type="text" name="title" value="<?= e($editing['title'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row">
            <label>Student Name</label>
            <input class="admin-input" type="text" name="student_name" value="<?= e($editing['student_name'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row">
            <label>Programme label</label>
            <input class="admin-input" type="text" name="programme_label" value="<?= e($editing['programme_label'] ?? '') ?>" placeholder="Final Year Project, BSc Information Technology">
        </div>
        <div class="admin-form-row">
            <label>Supervisor</label>
            <input class="admin-input" type="text" name="supervisor" value="<?= e($editing['supervisor'] ?? '') ?>">
        </div>
        <div class="admin-form-row">
            <label>Co-Supervisor</label>
            <input class="admin-input" type="text" name="co_supervisor" value="<?= e($editing['co_supervisor'] ?? '') ?>">
        </div>
        <div class="admin-form-row">
            <label>Demo Video <?= $editing ? '(leave blank to keep current)' : '' ?></label>
            <input class="admin-input" type="file" name="video" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska">
        </div>
        <div class="admin-form-row">
            <label>Summary</label>
            <textarea class="admin-textarea" name="summary" style="min-height:8rem;"><?= e($editing['summary'] ?? '') ?></textarea>
        </div>
        <div class="admin-form-row" data-repeater="stack">
            <label>Tech Stack</label>
            <div class="admin-repeater-list">
                <?php foreach (($editStack ?: ['']) as $tech): ?>
                <div class="admin-repeater-row">
                    <input class="admin-input" type="text" name="stack[]" value="<?= e($tech) ?>">
                    <button type="button" class="admin-repeater-remove" data-remove-row>&times;</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" data-add-stack-row>+ Add Tag</button>
        </div>
        <div class="admin-form-row">
            <label>Sort Order</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? count($projects)) ?>">
        </div>
        <div class="admin-form-row">
            <label>Status</label>
            <select class="admin-select" name="status">
                <option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="archived" <?= ($editing['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Project' ?></button>
        <?php if ($editing): ?>
        <a href="<?= BASE_URL ?>/admin/projects.php" class="admin-btn admin-btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Projects</h2>
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Student</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
        <tr>
            <td><?= e($p['title']) ?></td>
            <td><?= e($p['student_name']) ?></td>
            <td><span class="admin-badge admin-badge-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $p['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this project?">
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
