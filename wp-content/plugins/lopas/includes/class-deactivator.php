<?php
/**
 * LOPAS Deactivator class
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Deactivator {
    /**
     * Deactivation hook callback
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        do_action('lopas_deactivated');
    }
}
