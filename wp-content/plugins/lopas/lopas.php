<?php
/**
 * Plugin Name: LOPAS - Barber & Spa Booking System
 * Plugin URI: https://github.com/yourusername/lopas
 * Description: Professional booking system for barber shops and spa services with VNPay payment integration
 * Version: 1.0.0
 * Author: Development Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: lopas
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

// Define constants
define('LOPAS_VERSION', '1.0.0');
define('LOPAS_PATH', plugin_dir_path(__FILE__));
define('LOPAS_URL', plugin_dir_url(__FILE__));
define('LOPAS_BASENAME', plugin_basename(__FILE__));
define('LOPAS_TEXTDOMAIN', 'lopas');

// Require core files
require_once LOPAS_PATH . 'includes/class-activator.php';
require_once LOPAS_PATH . 'includes/class-deactivator.php';
require_once LOPAS_PATH . 'includes/class-database.php';
require_once LOPAS_PATH . 'includes/class-mailer.php';
require_once LOPAS_PATH . 'includes/helpers.php';

// Register activation & deactivation hooks
register_activation_hook(__FILE__, array('LOPAS_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('LOPAS_Deactivator', 'deactivate'));

/**
 * Plugin initialization
 */
add_action('plugins_loaded', function() {
    // Load text domain
    load_plugin_textdomain(LOPAS_TEXTDOMAIN, false, dirname(LOPAS_BASENAME) . '/languages');
    
    // Load additional classes
    require_once LOPAS_PATH . 'includes/class-booking.php';
    require_once LOPAS_PATH . 'includes/class-service.php';
    require_once LOPAS_PATH . 'includes/class-salon.php';
    require_once LOPAS_PATH . 'includes/class-order.php';
    require_once LOPAS_PATH . 'includes/class-payment.php';
    require_once LOPAS_PATH . 'includes/class-rest-api.php';
    require_once LOPAS_PATH . 'includes/class-email-hooks.php';
    require_once LOPAS_PATH . 'includes/class-voucher.php';
    require_once LOPAS_PATH . 'includes/class-reporting.php';
    require_once LOPAS_PATH . 'includes/class-booking-reminder.php';
    
    // Phase 8: Booking Improvements
    require_once LOPAS_PATH . 'includes/class-booking-hold.php';
    require_once LOPAS_PATH . 'includes/api/class-slot-api.php';
    require_once LOPAS_PATH . 'includes/public/class-booking-wizard.php';
    
    // Initialize email hooks
    new LOPAS_Email_Hooks();
    
    // Initialize booking reminders
    new LOPAS_Booking_Reminder();
    
    // Load VNPay payment gateway
    require_once LOPAS_PATH . 'includes/vnpay/class-vnpay-gateway.php';
    require_once LOPAS_PATH . 'includes/vnpay/class-payment-controller.php';
    
    // Initialize payment controller
    new LOPAS_Payment_Controller();
    
    // Load REST API classes
    require_once LOPAS_PATH . 'includes/api/class-api-base.php';
    require_once LOPAS_PATH . 'includes/api/class-api-auth.php';
    require_once LOPAS_PATH . 'includes/api/class-salon-api.php';
    require_once LOPAS_PATH . 'includes/api/class-service-api.php';
    require_once LOPAS_PATH . 'includes/api/class-booking-api.php';
    require_once LOPAS_PATH . 'includes/api/class-payment-api.php';
    
    // Initialize API classes
    new LOPAS_API_Auth();
    new LOPAS_Salon_API();
    new LOPAS_Service_API();
    new LOPAS_Booking_API();
    new LOPAS_Payment_API();
    
    // Admin & Public
    // Admin
    if (is_admin()) {
        require_once LOPAS_PATH . 'includes/admin/class-admin.php';
        new LOPAS_Admin();
    }
    
    // Public (AJAX hooks need to be registered even in admin-ajax.php)
    require_once LOPAS_PATH . 'includes/public/class-public.php';
    $lopas_public = new LOPAS_Public();
    
    add_action('init', function() use ($lopas_public) {
        // Explicit AJAX registration
        add_action('wp_ajax_lopas_get_services', array('LOPAS_Booking_Form', 'ajax_get_services'));
        add_action('wp_ajax_nopriv_lopas_get_services', array('LOPAS_Booking_Form', 'ajax_get_services'));
        add_action('wp_ajax_lopas_fetch_time_slots', array('LOPAS_Booking_Form', 'ajax_get_available_slots'));
        add_action('wp_ajax_nopriv_lopas_fetch_time_slots', array('LOPAS_Booking_Form', 'ajax_get_available_slots'));
        add_action('wp_ajax_lopas_get_salon_details', array('LOPAS_Booking_Form', 'ajax_get_salon_details'));
        add_action('wp_ajax_nopriv_lopas_get_salon_details', array('LOPAS_Booking_Form', 'ajax_get_salon_details'));
        
        add_action('wp_ajax_lopas_create_booking', array($lopas_public, 'ajax_create_booking'));
        add_action('wp_ajax_nopriv_lopas_create_booking', array($lopas_public, 'ajax_create_booking'));
        add_action('wp_ajax_lopas_search_salons', array($lopas_public, 'ajax_search_salons'));
        add_action('wp_ajax_nopriv_lopas_search_salons', array($lopas_public, 'ajax_search_salons'));
        add_action('wp_ajax_lopas_submit_review', array($lopas_public, 'ajax_submit_review'));
        add_action('wp_ajax_lopas_cancel_booking', array($lopas_public, 'ajax_cancel_booking'));
    });
});
