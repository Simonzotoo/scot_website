<?php
declare(strict_types=1);

/*
 * Image paths: use these constants directly in templates.
 *
 * To use an image:  <img src="<?= IMAGES_URL ?>/gallery/my-photo.jpg">
 * Fallback:         data-fallback="<?= IMAGES_URL ?>/placeholders/default.svg"
 *                   (handled by the delegated 'error' listener in assets/js/main.js —
 *                   not inline onerror="...", which this site's CSP blocks)
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
