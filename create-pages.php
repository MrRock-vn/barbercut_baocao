<?php
define('WP_USE_THEMES', false);
require('wp-load.php');

// Create pages
$pages = array(
    array(
        'post_title' => 'Trang chủ',
        'post_name' => 'home',
        'post_content' => '[lopas_homepage]',
        'post_type' => 'page',
        'post_status' => 'publish'
    ),
    array(
        'post_title' => 'Đặt lịch',
        'post_name' => 'booking',
        'post_content' => '[lopas_booking_form]',
        'post_type' => 'page',
        'post_status' => 'publish'
    ),
    array(
        'post_title' => 'Danh sách salon',
        'post_name' => 'salons',
        'post_content' => '[lopas_salon_list limit="12"]',
        'post_type' => 'page',
        'post_status' => 'publish'
    ),
    array(
        'post_title' => 'Lịch sử đặt lịch',
        'post_name' => 'my-bookings',
        'post_content' => '[lopas_my_bookings]',
        'post_type' => 'page',
        'post_status' => 'publish'
    ),
    array(
        'post_title' => 'Dashboard',
        'post_name' => 'dashboard',
        'post_content' => '[lopas_customer_dashboard]',
        'post_type' => 'page',
        'post_status' => 'publish'
    ),
    array(
        'post_title' => 'Thanh toán',
        'post_name' => 'payment',
        'post_content' => '[lopas_payment_form]',
        'post_type' => 'page',
        'post_status' => 'publish'
    ),
    array(
        'post_title' => 'Thanh toán thành công',
        'post_name' => 'payment-success',
        'post_content' => '[lopas_payment_success]',
        'post_type' => 'page',
        'post_status' => 'publish'
    ),
    array(
        'post_title' => 'Thanh toán thất bại',
        'post_name' => 'payment-failed',
        'post_content' => '[lopas_payment_failed]',
        'post_type' => 'page',
        'post_status' => 'publish'
    )
);

echo "Creating pages...\n";
$homepage_id = null;

foreach ($pages as $page) {
    $existing = get_page_by_path($page['post_name']);
    
    if (!$existing) {
        $page_id = wp_insert_post($page);
        echo "✓ Created: " . $page['post_title'] . " (ID: $page_id)\n";
        
        if ($page['post_name'] === 'home') {
            $homepage_id = $page_id;
        }
    } else {
        echo "- Already exists: " . $page['post_title'] . "\n";
        if ($page['post_name'] === 'home') {
            $homepage_id = $existing->ID;
        }
    }
}

// Set homepage
if ($homepage_id) {
    update_option('page_on_front', $homepage_id);
    update_option('show_on_front', 'page');
    echo "\n✓ Homepage set to: " . get_permalink($homepage_id) . "\n";
}

// Flush rewrite rules
flush_rewrite_rules();
echo "✓ Rewrite rules flushed\n";

echo "\n=== PAGES CREATED ===\n";
$all_pages = get_pages();
foreach ($all_pages as $page) {
    echo "- " . $page->post_title . " (" . get_permalink($page->ID) . ")\n";
}
?>
