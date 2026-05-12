<?php
/**
 * LOPAS Activator class
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Activator {
    /**
     * Activation hook callback
     */
    public static function activate() {
        // Create custom database tables
        LOPAS_Database::create_tables();
        
        // Create frontend pages
        require_once LOPAS_PATH . 'includes/class-page-creator.php';
        LOPAS_Page_Creator::create_pages();
        
        // Setup homepage
        require_once LOPAS_PATH . 'includes/class-homepage-setup.php';
        LOPAS_Homepage_Setup::setup();
        
        // Set plugin version
        update_option('lopas_db_version', LOPAS_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        do_action('lopas_activated');
    }
}
