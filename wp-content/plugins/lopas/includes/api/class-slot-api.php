<?php
/**
 * LOPAS Slot API
 * AJAX endpoints for slot availability and holding
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Slot_API {
    
    public function __construct() {
        // AJAX endpoints for logged-in users
        add_action('wp_ajax_lopas_get_available_slots', array($this, 'get_available_slots'));
        add_action('wp_ajax_lopas_hold_slot', array($this, 'hold_slot'));
        add_action('wp_ajax_lopas_release_hold', array($this, 'release_hold'));
        
        // AJAX endpoints for non-logged-in users (guests can also book)
        add_action('wp_ajax_nopriv_lopas_get_available_slots', array($this, 'get_available_slots'));
        add_action('wp_ajax_nopriv_lopas_hold_slot', array($this, 'hold_slot'));
        add_action('wp_ajax_nopriv_lopas_release_hold', array($this, 'release_hold'));
    }
    
    /**
     * Get available time slots
     */
    public function get_available_slots() {
        // Verify nonce
        if (!check_ajax_referer('lopas_slot_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token'));
        }
        
        // Get parameters
        $staff_id = isset($_GET['staff_id']) ? absint($_GET['staff_id']) : 0;
        $date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
        $duration = isset($_GET['duration']) ? absint($_GET['duration']) : 60;
        
        // Validate parameters
        if (empty($staff_id) || empty($date)) {
            wp_send_json_error(array('message' => 'Missing required parameters'));
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            wp_send_json_error(array('message' => 'Invalid date format'));
        }
        
        // Check if date is in the past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            wp_send_json_error(array('message' => 'Cannot book in the past'));
        }
        
        // Get available slots
        require_once LOPAS_PATH . 'includes/class-booking-hold.php';
        $hold_model = new LOPAS_Booking_Hold();
        
        $slots = $hold_model->get_available_slots($staff_id, $date, $duration);
        
        wp_send_json_success(array(
            'slots' => $slots,
            'count' => count($slots)
        ));
    }
    
    /**
     * Hold a time slot
     */
    public function hold_slot() {
        // Verify nonce
        if (!check_ajax_referer('lopas_slot_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token'));
        }
        
        // Get parameters
        $staff_id = isset($_POST['staff_id']) ? absint($_POST['staff_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
        $duration = isset($_POST['duration']) ? absint($_POST['duration']) : 60;
        
        // Validate parameters
        if (empty($staff_id) || empty($date) || empty($start_time)) {
            wp_send_json_error(array('message' => 'Missing required parameters'));
        }
        
        // Calculate end time
        $end_time = date('H:i:s', strtotime($start_time . ' +' . $duration . ' minutes'));
        
        // Get session ID
        if (!session_id()) {
            session_start();
        }
        $session_id = session_id();
        
        // Hold the slot
        require_once LOPAS_PATH . 'includes/class-booking-hold.php';
        $hold_model = new LOPAS_Booking_Hold();
        
        $hold_data = array(
            'user_id' => get_current_user_id(),
            'session_id' => $session_id,
            'staff_id' => $staff_id,
            'service_date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time
        );
        
        $hold_id = $hold_model->hold_slot($hold_data);
        
        if ($hold_id) {
            wp_send_json_success(array(
                'message' => 'Slot held successfully',
                'hold_id' => $hold_id,
                'expires_in' => 600 // 10 minutes in seconds
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to hold slot. It may be already taken.'));
        }
    }
    
    /**
     * Release a hold
     */
    public function release_hold() {
        // Verify nonce
        if (!check_ajax_referer('lopas_slot_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token'));
        }
        
        // Get session ID
        if (!session_id()) {
            session_start();
        }
        $session_id = session_id();
        
        // Release holds
        require_once LOPAS_PATH . 'includes/class-booking-hold.php';
        $hold_model = new LOPAS_Booking_Hold();
        
        $result = $hold_model->release_session_holds($session_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Hold released successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to release hold'));
        }
    }
}

// Initialize
new LOPAS_Slot_API();

