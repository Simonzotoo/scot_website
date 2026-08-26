<?php
declare(strict_types=1);

/*
 * Image paths: use these constants directly in templates.
 *
 * To use an image:  <img src="<?= IMAGES_URL ?>/gallery/my-photo.jpg">
 * Fallback:         onerror="this.src='<?= IMAGES_URL ?>/placeholders/default.svg'; this.onerror=null;"
 *
 * Folder map:
 *   logo/         → logo-dark.png (nav), logo-light.png (footer/admin), favicon.png
 *   executives/   → president.jpg, vice-president.jpg, etc.
 *   gallery/      → scotsa-event.jpg, awards-2024.jpg, etc.
 *   announcements/→ event-banner.jpg, etc.
 *   hero/         → hero-bg.jpg, etc.
 *   events/       → event-specific assets
 *   placeholders/ → default.svg, avatar.svg, logo.svg, logo-footer.svg
 */
define('IMAGES_URL',  BASE_URL . '/assets/images');
define('IMAGES_ROOT', dirname(__DIR__) . '/assets/images');
