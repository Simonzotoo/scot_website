<?php
// Facilities now lives on the Watch page (after the videos), so the nav
// doesn't need a separate item for it. This file stays only to redirect
// anyone with an old link/bookmark rather than 404ing them.
require_once __DIR__ . '/includes/config.php';
header('Location: ' . BASE_URL . '/watch.php#facilities', true, 301);
exit;
