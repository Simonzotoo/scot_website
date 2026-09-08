<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
$admin = require_admin_login();

function save_repeater_list(PDO $pdo, string $table, int $memberId, array $values): void
{
    $pdo->prepare("DELETE FROM `$table` WHERE member_id = ?")->execute([$memberId]);
    $stmt = $pdo->prepare("INSERT INTO `$table` (member_id, text_value, sort_order) VALUES (?, ?, ?)");
    $i = 0;
    foreach ($values as $v) {
        $v = trim((string) $v);
        if ($v === '') {
            continue;
        }
        $stmt->execute([$memberId, $v, $i++]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $roster = in_array($_POST['roster'] ?? '', ['leadership', 'faculty', 'officer'], true) ? $_POST['roster'] : 'faculty';
        $role = clean_text($_POST['role'] ?? '');
        $name = clean_text($_POST['name'] ?? '');
        $email = clean_text($_POST['email'] ?? '') ?: null;
        $portfolio = clean_text($_POST['portfolio'] ?? '') ?: null;
        $bio = trim((string) ($_POST['bio'] ?? '')) ?: null;
        $researchInterest = trim((string) ($_POST['research_interest'] ?? '')) ?: null;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        try {
            $photoPath = null;
            if (!empty($_FILES['photo']['name'])) {
                $photoPath = handle_image_upload($_FILES['photo'], 'faculty');
            }

            if ($id > 0) {
                if ($photoPath !== null) {
                    $old = db()->prepare('SELECT photo_path FROM team_members WHERE id = ?');
                    $old->execute([$id]);
                    if ($oldPath = $old->fetchColumn()) {
                        delete_media_file($oldPath);
                    }
                    db()->prepare('UPDATE team_members SET roster=?, role=?, name=?, email=?, portfolio=?, bio=?, research_interest=?, photo_path=?, sort_order=? WHERE id=?')
                        ->execute([$roster, $role, $name, $email, $portfolio, $bio, $researchInterest, $photoPath, $sortOrder, $id]);
                } else {
                    db()->prepare('UPDATE team_members SET roster=?, role=?, name=?, email=?, portfolio=?, bio=?, research_interest=?, sort_order=? WHERE id=?')
                        ->execute([$roster, $role, $name, $email, $portfolio, $bio, $researchInterest, $sortOrder, $id]);
                }
                $memberId = $id;
                flash('success', 'Faculty profile updated.');
            } else {
                db()->prepare('INSERT INTO team_members (roster, role, name, email, portfolio, bio, research_interest, photo_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$roster, $role, $name, $email, $portfolio, $bio, $researchInterest, $photoPath, $sortOrder]);
                $memberId = (int) db()->lastInsertId();
                flash('success', 'Faculty member added.');
            }

            save_repeater_list(db(), 'faculty_courses_taught', $memberId, $_POST['courses_taught'] ?? []);
            save_repeater_list(db(), 'faculty_publications', $memberId, $_POST['publications'] ?? []);
            save_repeater_list(db(), 'faculty_education', $memberId, $_POST['education'] ?? []);
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $old = db()->prepare('SELECT photo_path FROM team_members WHERE id = ?');
        $old->execute([$id]);
        if ($oldPath = $old->fetchColumn()) {
            delete_media_file($oldPath);
        }
        db()->prepare('DELETE FROM team_members WHERE id = ?')->execute([$id]);
        flash('success', 'Faculty member deleted.');
    }

    header('Location: ' . BASE_URL . '/admin/faculty.php');
    exit;
}

$members = db()->query('SELECT * FROM team_members ORDER BY roster DESC, sort_order')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
$editCourses = $editPubs = $editEdu = [];
foreach ($members as $m) {
    if ((int) $m['id'] === $editId) {
        $editing = $m;
        $editCourses = db()->prepare('SELECT text_value FROM faculty_courses_taught WHERE member_id=? ORDER BY sort_order');
        $editCourses->execute([$editId]);
        $editCourses = $editCourses->fetchAll(PDO::FETCH_COLUMN);
        $editPubs = db()->prepare('SELECT text_value FROM faculty_publications WHERE member_id=? ORDER BY sort_order');
        $editPubs->execute([$editId]);
        $editPubs = $editPubs->fetchAll(PDO::FETCH_COLUMN);
        $editEdu = db()->prepare('SELECT text_value FROM faculty_education WHERE member_id=? ORDER BY sort_order');
        $editEdu->execute([$editId]);
        $editEdu = $editEdu->fetchAll(PDO::FETCH_COLUMN);
        break;
    }
}

$pageTitle = 'Faculty';
$activeNav = 'faculty';
require __DIR__ . '/includes/admin_header.php';

function repeater_field(string $name, string $label, array $values): void
{
    ?>
    <div class="admin-form-row" data-repeater="<?= e($name) ?>">
        <label><?= e($label) ?></label>
        <div class="admin-repeater-list">
            <?php foreach ($values ?: [''] as $v): ?>
            <div class="admin-repeater-row">
                <input class="admin-input" type="text" name="<?= e($name) ?>[]" value="<?= e($v) ?>">
                <button type="button" class="admin-repeater-remove" data-remove-row>&times;</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" data-add-repeater="<?= e($name) ?>">+ Add Line</button>
    </div>
    <?php
}
?>

<div class="admin-card">
    <h2><?= $editing ? 'Edit Faculty Member' : 'Add New Faculty Member' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">

        <div class="admin-form-row">
            <label>Grid</label>
            <select class="admin-select" name="roster">
                <option value="leadership" <?= ($editing['roster'] ?? '') === 'leadership' ? 'selected' : '' ?>>Dean & Heads of Department</option>
                <option value="faculty" <?= ($editing['roster'] ?? 'faculty') === 'faculty' ? 'selected' : '' ?>>Lecturers</option>
                <option value="officer" <?= ($editing['roster'] ?? '') === 'officer' ? 'selected' : '' ?>>Faculty Office</option>
            </select>
        </div>
        <div class="admin-form-row">
            <label>Name</label>
            <input class="admin-input" type="text" name="name" value="<?= e($editing['name'] ?? '') ?>">
            <p class="admin-hint">Leave blank to show as "Awaiting Appointment".</p>
        </div>
        <div class="admin-form-row">
            <label>Role / Title</label>
            <input class="admin-input" type="text" name="role" value="<?= e($editing['role'] ?? '') ?>" required>
        </div>
        <div class="admin-form-row">
            <label>Portfolio line (short credential/specialisation)</label>
            <input class="admin-input" type="text" name="portfolio" value="<?= e($editing['portfolio'] ?? '') ?>">
        </div>
        <div class="admin-form-row">
            <label>Email</label>
            <input class="admin-input" type="email" name="email" value="<?= e($editing['email'] ?? '') ?>">
        </div>
        <div class="admin-form-row">
            <label>Photo <?= $editing ? '(leave blank to keep current)' : '' ?></label>
            <input class="admin-input" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($editing['photo_path'])): ?>
            <img style="margin-top:.5rem; width:90px; height:110px; object-fit:cover; border-radius:.5rem;" src="<?= IMAGES_URL ?>/<?= e($editing['photo_path']) ?>" alt="">
            <?php endif; ?>
        </div>
        <div class="admin-form-row">
            <label>Overview / Bio</label>
            <textarea class="admin-textarea" name="bio" style="min-height:10rem;"><?= e($editing['bio'] ?? '') ?></textarea>
            <p class="admin-hint">Blank paragraphs (double line breaks) are preserved. Leave blank to show "Biography coming soon."</p>
        </div>
        <div class="admin-form-row">
            <label>Research Interest</label>
            <textarea class="admin-textarea" name="research_interest"><?= e($editing['research_interest'] ?? '') ?></textarea>
        </div>

        <?php
        repeater_field('courses_taught', 'Courses Taught', $editCourses);
        repeater_field('publications', 'Publications', $editPubs);
        repeater_field('education', 'Education', $editEdu);
        ?>

        <div class="admin-form-row">
            <label>Sort Order (within its grid)</label>
            <input class="admin-input" type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? 0) ?>">
        </div>

        <button type="submit" class="admin-btn"><?= $editing ? 'Save Changes' : 'Add Faculty Member' ?></button>
        <?php if ($editing): ?>
        <a href="<?= BASE_URL ?>/admin/faculty.php" class="admin-btn admin-btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Faculty</h2>
    <table class="admin-table">
        <thead><tr><th></th><th>Name</th><th>Role</th><th>Grid</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($members as $m): ?>
        <tr>
            <td><?php if ($m['photo_path']): ?><img class="thumb" src="<?= IMAGES_URL ?>/<?= e($m['photo_path']) ?>" alt=""><?php endif; ?></td>
            <td><?= e($m['name'] ?: 'Awaiting Appointment') ?></td>
            <td><?= e($m['role']) ?></td>
            <td><?= ['leadership' => 'Dean & Heads', 'officer' => 'Faculty Office'][$m['roster']] ?? 'Lecturers' ?></td>
            <td class="admin-table-actions">
                <a class="admin-btn admin-btn-sm admin-btn-secondary" href="?edit=<?= $m['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this faculty member?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
