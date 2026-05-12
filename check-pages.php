<?php
define('WP_USE_THEMES', false);
require('wp-load.php');

// Get homepage
$homepage_id = get_option('page_on_front');
$homepage = get_post($homepage_id);

echo "=== HOMEPAGE ===\n";
if ($homepage) {
    echo "Homepage ID: " . $homepage_id . "\n";
    echo "Homepage Title: " . $homepage->post_title . "\n";
    echo "Homepage URL: " . get_permalink($homepage_id) . "\n";
} else {
    echo "Homepage not found\n";
}

// Get all pages
$pages = get_pages();
echo "\n=== PAGES CREATED ===\n";
foreach ($pages as $page) {
    echo "- " . $page->post_title . " (" . get_permalink($page->ID) . ")\n";
}

echo "\n=== PLUGIN STATUS ===\n";
if (is_plugin_active('lopas/lopas.php')) {
    echo "✓ LOPAS Plugin is ACTIVE\n";
} else {
    echo "✗ LOPAS Plugin is NOT active\n";
}
?>
