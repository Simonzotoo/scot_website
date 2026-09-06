<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
$admin = require_admin_login();

// levels: 1=100 2=200 3=300 4=400 | semesters: 1=lower(First) 2=upper(Second)
const LEVEL_IDS = ['100' => 1, '200' => 2, '300' => 3, '400' => 4];
const SEM_IDS = ['lower' => 1, 'upper' => 2];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = clean_text($_POST['name'] ?? '');
        $tag = in_array($_POST['tag'] ?? '', ['BSc', 'MSc', 'Diploma', 'Short Courses'], true) ? $_POST['tag'] : 'BSc';
        $desc = clean_text($_POST['description'] ?? '') ?: null;
        $note = clean_text($_POST['note'] ?? '') ?: null;
        $featured = isset($_POST['featured']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $slugBase = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name . '-' . $tag));
        $slug = trim(substr($slugBase, 0, 140), '-');

        if ($id > 0) {
            db()->prepare('UPDATE programs SET name=?, tag=?, description=?, note=?, featured=?, sort_order=? WHERE id=?')
                ->execute([$name, $tag, $desc, $note, $featured, $sortOrder, $id]);
            $programId = $id;
        } else {
            db()->prepare('INSERT INTO programs (name, tag, slug, description, note, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([$name, $tag, $slug, $desc, $note, $featured, $sortOrder]);
            $programId = (int) db()->lastInsertId();
        }

        // Replace all course rows for this programme with what was submitted.
        db()->prepare('DELETE FROM courses WHERE program_id = ?')->execute([$programId]);
        $courseStmt = db()->prepare('INSERT INTO courses (program_id, level_id, semester_id, group_label, sort_order, code, title) VALUES (?, ?, ?, ?, ?, ?, ?)');

        if ($tag === 'MSc') {
            foreach (['core', 'elective'] as $group) {
                $titles = $_POST['msc_courses'][$group] ?? [];
                $i = 0;
                foreach ($titles as $title) {
                    $title = trim((string) $title);
                    if ($title === '') {
                        continue;
                    }
                    $courseStmt->execute([$programId, null, null, $group, $i++, '', $title]);
                }
            }
        } elseif (in_array($tag, ['BSc', 'Diploma'], true)) {
            foreach (LEVEL_IDS as $levelKey => $levelId) {
                foreach (SEM_IDS as $semKey => $semId) {
                    $rows = $_POST['courses'][$levelKey][$semKey] ?? [];
                    $i = 0;
                    foreach ($rows as $row) {
                        $code = trim((string) ($row['code'] ?? ''));
                        $title = trim((string) ($row['title'] ?? ''));
                        if ($code === '' && $title === '') {
                            continue;
                        }
                        $courseStmt->execute([$programId, $levelId, $semId, null, $i++, $code, $title]);
                    }
                }
            }
        }
        // Short Courses: no course rows at all.

        flash('success', 'Programme saved.');
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM programs WHERE id = ?')->execute([$id]); // courses cascade
        flash('success', 'Programme deleted.');
    }

    header('Location: ' . BASE_URL . '/admin/programmes.php');
    exit;
}

$programs = db()->query('SELECT * FROM programs ORDER BY FIELD(tag,"BSc","MSc","Diploma","Short Courses"), sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
$editCourses = []; // [level][sem] = [[code,title],...]
$editMsc = ['core' => [], 'elective' => []];
foreach ($programs as $p) {
    if ((int) $p['id'] === $editId) {
        $editing = $p;
        $rows = db()->prepare('SELECT * FROM courses WHERE program_id=? ORDER BY sort_order');
        $rows->execute([$editId]);
        foreach ($rows->fetchAll() as $r) {
            if ($r['group_label']) {
                $editMsc[$r['group_label']][] = $r['title'];
            } else {
                $levelKey = array_search((int) $r['level_id'], LEVEL_IDS, true);
                $semKey = array_search((int) $r['semester_id'], SEM_IDS, true);
                if ($levelKey !== false && $semKey !== false) {
                    $editCourses[$levelKey][$semKey][] = [$r['code'], $r['title']];
                }
            }
        }
        break;
    }
}

$pageTitle = 'Programmes';
$activeNav = 'programmes';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Programme' : 'Add New Programme' ?></h2>
    <form method="post" id="programme-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Type</label>
            <select class="admin-select" name="tag" id="tag-select">
                <?php foreach (['BSc', 'MSc', 'Diploma', 'Short Courses'] as $t): ?>
                <option value="<?= $t ?>" <?= ($editing['tag'] ?? 'BSc') === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="admin-form-row">
            <label>Name</label>
            <input class="admin-input" type="text" name="name" value="<?= e($editing['name'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row">
            <label>Description</label>
            <textarea class="admin-textarea" name="description"><?= e($editing['description'] ?? '') ?></textarea>
        </div>
        <div class="admin-form-row">
            <label>Note (e.g. "Course structure currently published through...")</label>
            <input class="admin-input" type="text" name="note" value="<?= e($editing['note'] ?? '') ?>">
        </div>
        <div class="admin-form-row admin-checkbox-row">
            <input type="checkbox" id="featured" name="featured" <?= !empty($editing['featured']) ? 'checked' : '' ?>>
            <label for="featured" style="margin:0;">Featured bento card (only BSc IT should have this)</label>
        </div>
        <div class="admin-form-row">
            <label>Sort Order</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? 0) ?>">
        </div>

        <div id="section-levels">
            <h3 style="font-size:.9rem; font-weight:800; margin:1.5rem 0 .5rem;">Course Structure (by Level &amp; Semester)</h3>
            <?php foreach (LEVEL_IDS as $levelKey => $levelId): ?>
            <div class="admin-card" style="background:#f8fafc;">
                <h2>Level <?= $levelKey ?></h2>
                <?php foreach (['lower' => 'First Semester', 'upper' => 'Second Semester'] as $semKey => $semLabel): ?>
                <p style="font-size:.75rem; font-weight:800; text-transform:uppercase; color:#64748b; margin:1rem 0 .35rem;"><?= $semLabel ?></p>
                <div data-course-repeater="<?= $levelKey ?>-<?= $semKey ?>">
                    <?php foreach (($editCourses[$levelKey][$semKey] ?? [['', '']]) as $row): ?>
                    <div class="admin-repeater-row">
                        <input class="admin-input" style="max-width:120px;" type="text" placeholder="Code" name="courses[<?= $levelKey ?>][<?= $semKey ?>][][code]" value="<?= e($row[0]) ?>">
                        <input class="admin-input" type="text" placeholder="Course title" name="courses[<?= $levelKey ?>][<?= $semKey ?>][][title]" value="<?= e($row[1]) ?>">
                        <button type="button" class="admin-repeater-remove" data-remove-row>&times;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" data-add-course-row data-level="<?= e((string) $levelKey) ?>" data-sem="<?= e($semKey) ?>">+ Add Course</button>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="section-msc">
            <h3 style="font-size:.9rem; font-weight:800; margin:1.5rem 0 .5rem;">Course Structure (Core / Elective)</h3>
            <?php foreach (['core' => 'Core Courses', 'elective' => 'Elective Courses'] as $group => $label): ?>
            <div class="admin-card" style="background:#f8fafc;">
                <h2><?= $label ?></h2>
                <div data-msc-repeater="<?= $group ?>">
                    <?php foreach (($editMsc[$group] ?: ['']) as $title): ?>
                    <div class="admin-repeater-row">
                        <input class="admin-input" type="text" placeholder="Course title" name="msc_courses[<?= $group ?>][]" value="<?= e($title) ?>">
                        <button type="button" class="admin-repeater-remove" data-remove-row>&times;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" data-add-msc-row data-group="<?= e($group) ?>">+ Add Course</button>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="section-short" class="admin-hint" style="margin-top:1rem;">Short Courses have no course-structure list — just the name above.</div>

        <div style="margin-top:1.5rem;">
            <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Programme' ?></button>
            <?php if ($editing): ?>
            <a href="<?= BASE_URL ?>/admin/programmes.php" class="admin-btn admin-btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-card">
    <h2>All Programmes</h2>
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Type</th><th>Featured</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($programs as $p): ?>
        <tr>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['tag']) ?></td>
            <td><?= $p['featured'] ? 'Yes' : '' ?></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $p['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this programme and its course list?">
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
