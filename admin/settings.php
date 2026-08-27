<?php
$pageTitle = 'Site Settings';
require_once __DIR__ . '/../includes/admin_header.php';

$textFields = ['contact_email', 'whatsapp_url', 'social_facebook', 'social_instagram', 'social_tiktok', 'social_twitter'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'save_settings';

    try {
        if ($action === 'save_settings') {
            $stmt = db()->prepare(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            foreach ($textFields as $key) {
                $stmt->execute([$key, clean_text($_POST[$key] ?? '')]);
            }

            if (!empty($_FILES['hero_image']['name'])) {
                $result = handle_media_upload($_FILES['hero_image'], 'hero', 'hero', 2400);
                if (!$result['ok']) {
                    flash('error', $result['error']);
                    header('Location: settings.php'); exit;
                }
                $oldStmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
                $oldStmt->execute(['hero_image']);
                delete_media($oldStmt->fetchColumn() ?: null);
                $stmt->execute(['hero_image', $result['path']]);
            }

            audit_log('update', 'site_settings', 0, 'Site settings updated');
            flash('success', 'Settings saved.');

        } elseif ($action === 'add_hero_photo') {
            if (empty($_FILES['page_hero_photo']['name'])) {
                flash('error', 'Please choose a photo to upload.');
                header('Location: settings.php'); exit;
            }
            $result = handle_media_upload($_FILES['page_hero_photo'], 'hero', 'page-hero', 2400);
            if (!$result['ok']) {
                flash('error', $result['error']);
                header('Location: settings.php'); exit;
            }
            $nextOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM page_hero_photos')->fetchColumn();
            db()->prepare('INSERT INTO page_hero_photos (photo_path, sort_order) VALUES (?, ?)')
                ->execute([$result['path'], $nextOrder]);
            audit_log('create', 'page_hero_photo', (int) db()->lastInsertId());
            flash('success', 'Photo added to the banner rotation.');

        } elseif ($action === 'delete_hero_photo') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = db()->prepare('SELECT photo_path FROM page_hero_photos WHERE id = ?');
            $stmt->execute([$id]);
            $photo = $stmt->fetchColumn();
            db()->prepare('DELETE FROM page_hero_photos WHERE id = ?')->execute([$id]);
            delete_media($photo ?: null);
            audit_log('delete', 'page_hero_photo', $id);
            flash('success', 'Photo removed from the banner rotation.');
        }
    } catch (Throwable $e) {
        error_log('[SCOTSA Settings] DB error: ' . $e->getMessage());
        flash('error', 'A database error occurred. Please try again.');
    }

    header('Location: settings.php');
    exit;
}

$rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
$heroPhotos = db()->query('SELECT * FROM page_hero_photos ORDER BY sort_order, id')->fetchAll();
?>

<div class="mb-6">
    <h1 class="font-heading font-black text-2xl text-ink">Site Settings</h1>
    <p class="mt-1 text-sm text-slate-500">
        Controls the homepage hero photo, contact email, WhatsApp link, and social media links shown across the site.
    </p>
</div>

<form method="post" enctype="multipart/form-data" class="max-w-2xl rounded-lg border border-slate-200 bg-white p-6">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_settings">

    <h2 class="text-sm font-black uppercase tracking-wide text-slate-500">Homepage Hero Photo</h2>
    <?php if (!empty($rows['hero_image'])): ?>
    <img src="<?= IMAGES_URL ?>/<?= e($rows['hero_image']) ?>" class="mt-3 h-32 w-full rounded-lg object-cover border border-slate-200">
    <?php endif; ?>
    <input type="file" class="form-input mt-3" name="hero_image" accept="image/jpeg,image/png,image/webp">
    <p class="mt-1 text-xs text-slate-400">Shown behind the homepage headline. Leave blank to keep the current photo. For best sharpness, use a photo at least 2000px wide.</p>

    <div class="my-6 border-t border-slate-100"></div>

    <h2 class="text-sm font-black uppercase tracking-wide text-slate-500">Contact</h2>
    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">Email</label>
    <input class="form-input" type="email" name="contact_email" value="<?= e($rows['contact_email'] ?? '') ?>" placeholder="scotsawiuc@gmail.com">

    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mt-4 mb-1.5">WhatsApp community link</label>
    <input class="form-input" type="url" name="whatsapp_url" value="<?= e($rows['whatsapp_url'] ?? '') ?>" placeholder="https://chat.whatsapp.com/...">

    <div class="my-6 border-t border-slate-100"></div>

    <h2 class="text-sm font-black uppercase tracking-wide text-slate-500">Social Media</h2>
    <div class="grid gap-4 mt-4">
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Facebook</label>
            <input class="form-input" type="url" name="social_facebook" value="<?= e($rows['social_facebook'] ?? '') ?>" placeholder="https://www.facebook.com/...">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Instagram</label>
            <input class="form-input" type="url" name="social_instagram" value="<?= e($rows['social_instagram'] ?? '') ?>" placeholder="https://www.instagram.com/...">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">TikTok</label>
            <input class="form-input" type="url" name="social_tiktok" value="<?= e($rows['social_tiktok'] ?? '') ?>" placeholder="https://www.tiktok.com/@...">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">X (Twitter)</label>
            <input class="form-input" type="url" name="social_twitter" value="<?= e($rows['social_twitter'] ?? '') ?>" placeholder="https://x.com/...">
        </div>
    </div>

    <button class="btn-primary mt-6 w-full" type="submit">Save Settings</button>
</form>

<div class="max-w-2xl mt-6 rounded-lg border border-slate-200 bg-white p-6">
    <h2 class="text-sm font-black uppercase tracking-wide text-slate-500">Inner Page Banner Photos</h2>
    <p class="mt-1 text-xs text-slate-400">
        Shown behind the title banner on Executives, Resources, Gallery, Contact, About, and Announcements.
        Each page is assigned a different photo from this pool automatically. Add more photos for more variety.
    </p>

    <div class="mt-4 grid grid-cols-3 sm:grid-cols-4 gap-3">
        <?php foreach ($heroPhotos as $photo): ?>
        <div class="relative group">
            <img src="<?= IMAGES_URL ?>/<?= e($photo['photo_path']) ?>" class="h-24 w-full rounded-lg object-cover border border-slate-200">
            <form method="post" class="absolute top-1 right-1">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_hero_photo">
                <input type="hidden" name="id" value="<?= (int) $photo['id'] ?>">
                <button data-confirm="Remove this photo from the banner rotation?"
                        class="grid h-6 w-6 place-items-center rounded-full bg-red-600 text-white opacity-0 group-hover:opacity-100 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$heroPhotos): ?>
    <p class="mt-3 text-sm text-slate-400">No banner photos yet. Pages will show the plain navy background until you add one.</p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mt-4 flex items-end gap-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_hero_photo">
        <div class="flex-1">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Add a photo</label>
            <input type="file" class="form-input" name="page_hero_photo" accept="image/jpeg,image/png,image/webp" required>
        </div>
        <button class="btn-primary" type="submit">Add</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
