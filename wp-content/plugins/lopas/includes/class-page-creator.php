<?php
/**
 * LOPAS Page Creator - Create frontend pages automatically
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Page_Creator {
    
    /**
     * Create all pages
     */
    public static function create_pages() {
        $pages = array(
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
            ),
            array(
                'post_title' => 'Đăng nhập',
                'post_name' => 'login',
                'post_content' => '[lopas_login]',
                'post_type' => 'page',
                'post_status' => 'publish'
            ),
            array(
                'post_title' => 'Đăng ký',
                'post_name' => 'register',
                'post_content' => '[lopas_register]',
                'post_type' => 'page',
                'post_status' => 'publish'
            ),
            array(
                'post_title' => 'Quản lý Salon',
                'post_name' => 'owner-dashboard',
                'post_content' => '[lopas_owner_dashboard]',
                'post_type' => 'page',
                'post_status' => 'publish'
            )
        );
        
        foreach ($pages as $page) {
            self::create_page($page);
        }
    }
    
    /**
     * Create single page
     */
    private static function create_page($page_data) {
        global $wpdb;
        
        // Check if page already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'page'",
            $page_data['post_name']
        ));
        
        if ($existing) {
            return $existing->ID;
        }
        
        // Create page
        $page_id = wp_insert_post($page_data);
        
        return $page_id;
    }
}
