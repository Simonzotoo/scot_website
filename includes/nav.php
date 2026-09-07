<?php
declare(strict_types=1);

/**
 * Single source of truth for the public site's page list — used by both
 * header.php (top nav + mobile menu) and footer.php (Explore links), so
 * they can't drift apart. Mirrors the exact pattern already used for
 * ADMIN_NAV_ITEMS in admin/includes/admin_header.php.
 */
const NAV_ITEMS = [
    'home'       => ['index.php', 'Home'],
    'programmes' => ['programmes.php', 'Programmes'],
    'watch'      => ['watch.php', 'Watch'],
    'faculty'    => ['faculty.php', 'Faculty'],
    'projects'   => ['projects.php', 'Projects'],
    'blog'       => ['blog.php', 'Blog'],
    'gallery'    => ['gallery.php', 'Gallery'],
    'downloads'  => ['downloads.php', 'Downloads'],
    'contact'    => ['contact.php', 'Contact'],
];
