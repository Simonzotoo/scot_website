<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
$admin = require_admin_login();

const SETTINGS_FIELDS = [
    'Site' => [
        ['site_meta_title', 'Page Title', 'text'],
    ],
    'Admissions' => [
        ['apply_now_url', 'Apply Now Button Link', 'text'],
    ],
    'About Section' => [
        ['about_paragraph_1', 'Paragraph 1', 'textarea'],
        ['about_paragraph_2', 'Paragraph 2', 'textarea'],
        ['about_feature_1_title', 'Feature 1 Title', 'text'],
        ['about_feature_1_body', 'Feature 1 Body', 'textarea'],
        ['about_feature_2_title', 'Feature 2 Title', 'text'],
        ['about_feature_2_body', 'Feature 2 Body', 'textarea'],
        ['about_feature_3_title', 'Feature 3 Title', 'text'],
        ['about_feature_3_body', 'Feature 3 Body', 'textarea'],
    ],
    'Dean\'s Message' => [
        ['dean_message', 'Quote', 'textarea'],
    ],
    'Contact' => [
        ['contact_email', 'Email', 'text'],
        ['contact_phone', 'Phone', 'text'],
        ['contact_address', 'Campus Address', 'text'],
    ],
    'Faculty Office' => [
        ['faculty_office_email', 'Email', 'text'],
        ['faculty_office_phone_1', 'Phone 1', 'text'],
        ['faculty_office_phone_2', 'Phone 2', 'text'],
    ],
    'Footer & Social' => [
        ['footer_tagline', 'Footer Tagline', 'textarea'],
        ['social_facebook', 'Facebook URL', 'text'],
        ['social_instagram', 'Instagram URL', 'text'],
        ['social_x', 'X (Twitter) URL', 'text'],
        ['social_linkedin', 'LinkedIn URL', 'text'],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach (SETTINGS_FIELDS as $fields) {
        foreach ($fields as [$key, $label, $type]) {
            $value = trim((string) ($_POST[$key] ?? ''));
            $stmt->execute([$key, $value]);
        }
    }

    if (!empty($_FILES['dean_photo']['name'])) {
        try {
            $path = handle_image_upload($_FILES['dean_photo'], 'faculty');
            $stmt->execute(['dean_photo', $path]);
        } catch (UploadException $e) {
            flash('error', $e->getMessage());
        }
    }

    flash('success', 'Settings saved.');
    header('Location: ' . BASE_URL . '/admin/settings.php');
    exit;
}

$rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Site Settings';
$activeNav = 'settings';
require __DIR__ . '/includes/admin_header.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php foreach (SETTINGS_FIELDS as $section => $fields): ?>
    <div class="admin-card">
        <h2><?= e($section) ?></h2>
        <?php foreach ($fields as [$key, $label, $type]): ?>
        <div class="admin-form-row">
            <label><?= e($label) ?></label>
            <?php if ($type === 'textarea'): ?>
            <textarea class="admin-textarea" name="<?= e($key) ?>"><?= e($rows[$key] ?? '') ?></textarea>
            <?php else: ?>
            <input class="admin-input" type="text" name="<?= e($key) ?>" value="<?= e($rows[$key] ?? '') ?>">
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div class="admin-card">
        <h2>Dean's Photo</h2>
        <div class="admin-form-row">
            <label>Photo (leave blank to keep current)</label>
            <input class="admin-input" type="file" name="dean_photo" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($rows['dean_photo'])): ?>
            <img style="margin-top:.5rem; width:90px; height:110px; object-fit:cover; border-radius:.5rem;" src="<?= IMAGES_URL ?>/<?= e($rows['dean_photo']) ?>" alt="">
            <?php endif; ?>
            <p class="admin-hint">The Dean's name/role/bio itself is managed on the Faculty screen — this is just the "Message from the Dean" photo.</p>
        </div>
    </div>

    <button type="submit" class="admin-btn">Save All Settings</button>
</form>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
