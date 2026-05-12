<?php
/**
 * LOPAS Homepage Setup
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Homepage_Setup {
    
    /**
     * Setup homepage
     */
    public static function setup() {
        // Create homepage
        $homepage_id = self::create_homepage();
        
        if ($homepage_id) {
            // Set as homepage
            update_option('page_on_front', $homepage_id);
            update_option('show_on_front', 'page');
        }
    }
    
    /**
     * Create homepage
     */
    private static function create_homepage() {
        global $wpdb;
        
        // Check if homepage already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'page'",
            'home'
        ));
        
        if ($existing) {
            return $existing->ID;
        }
        
        // Create homepage
        $homepage = array(
            'post_title' => 'Trang chủ',
            'post_name' => 'home',
            'post_content' => '[lopas_homepage]',
            'post_type' => 'page',
            'post_status' => 'publish'
        );
        
        $homepage_id = wp_insert_post($homepage);
        
        return $homepage_id;
    }
}
