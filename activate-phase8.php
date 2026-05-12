<?php
/**
 * Phase 8 Activation Script
 * Run this file once to create the booking_holds table
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

// Load LOPAS database class
require_once __DIR__ . '/wp-content/plugins/lopas/includes/class-database.php';

echo "=== LOPAS Phase 8 Activation ===\n\n";

// Create tables
echo "Creating booking_holds table...\n";
LOPAS_Database::create_tables();

echo "✅ Table created successfully!\n\n";

// Verify table exists
global $wpdb;
$table_name = $wpdb->prefix . 'lopas_booking_holds';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;

if ($table_exists) {
    echo "✅ Verification: booking_holds table exists\n";
    
    // Show table structure
    $columns = $wpdb->get_results("DESCRIBE {$table_name}");
    echo "\nTable Structure:\n";
    echo "----------------\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
} else {
    echo "❌ Error: Table was not created\n";
}

echo "\n=== Activation Complete ===\n";
echo "\nNext steps:\n";
echo "1. Create a new page with shortcode: [lopas_booking_wizard]\n";
echo "2. Visit the page with ?salon_id=1 parameter\n";
echo "3. Test the 4-step booking wizard\n";
echo "\nEnjoy Phase 8! 🎉\n";

