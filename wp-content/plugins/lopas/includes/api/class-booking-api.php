<?php
/**
 * LOPAS Booking API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Booking_API extends LOPAS_API_Base {
    
    public function register_routes() {
        // Get user bookings
        register_rest_route($this->namespace, '/bookings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_bookings'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Get single booking
        register_rest_route($this->namespace, '/bookings/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_booking'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Create booking
        register_rest_route($this->namespace, '/bookings', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_booking'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Update booking
        register_rest_route($this->namespace, '/bookings/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_booking'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Cancel booking
        register_rest_route($this->namespace, '/bookings/(?P<id>\d+)/cancel', array(
            'methods' => 'POST',
            'callback' => array($this, 'cancel_booking'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Get available slots
        register_rest_route($this->namespace, '/bookings/available-slots', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_available_slots'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Get user bookings
     */
    public function get_bookings($request) {
        $user = wp_get_current_user();
        $page = $request->get_param('page') ?? 1;
        $per_page = $request->get_param('per_page') ?? 20;
        $status = $request->get_param('status');
        
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        $query = $wpdb->prepare("SELECT * FROM {$table} WHERE user_id = %d", $user->ID);
        
        if ($status) {
            $query .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        $query .= " ORDER BY booking_date DESC";
        
        $bookings = $wpdb->get_results($query);
        
        $paginated = $this->paginate_results($bookings, $page, $per_page);
        
        return $this->success_response($paginated);
    }
    
    /**
     * Get single booking
     */
    public function get_booking($request) {
        $user = wp_get_current_user();
        $booking_id = $request->get_param('id');
        
        $booking = LOPAS_Booking::get($booking_id);
        
        if (!$booking) {
            return $this->error_response('Booking not found', 'not_found', 404);
        }
        
        // Check ownership
        if ($booking->user_id != $user->ID && !current_user_can('manage_options')) {
            return $this->error_response('Unauthorized', 'unauthorized', 403);
        }
        
        return $this->success_response($booking);
    }
    
    /**
     * Create booking
     */
    public function create_booking($request) {
        $user = wp_get_current_user();
        $params = $request->get_json_params();
        
        $booking_id = LOPAS_Booking::create(array(
            'user_id' => $user->ID,
            'salon_id' => $params['salon_id'] ?? 0,
            'service_id' => $params['service_id'] ?? 0,
            'booking_date' => $params['booking_date'] ?? '',
            'booking_time' => $params['booking_time'] ?? '',
            'notes' => $params['notes'] ?? '',
            'status' => 'pending'
        ));
        
        if (!$booking_id) {
            return $this->error_response('Failed to create booking', 'creation_failed', 400);
        }
        
        $booking = LOPAS_Booking::get($booking_id);
        
        return $this->success_response($booking, 201);
    }
    
    /**
     * Update booking
     */
    public function update_booking($request) {
        $user = wp_get_current_user();
        $booking_id = $request->get_param('id');
        $params = $request->get_json_params();
        
        $booking = LOPAS_Booking::get($booking_id);
        
        if (!$booking) {
            return $this->error_response('Booking not found', 'not_found', 404);
        }
        
        // Check ownership
        if ($booking->user_id != $user->ID && !current_user_can('manage_options')) {
            return $this->error_response('Unauthorized', 'unauthorized', 403);
        }
        
        $result = LOPAS_Booking::update($booking_id, $params);
        
        if (!$result) {
            return $this->error_response('Failed to update booking', 'update_failed', 400);
        }
        
        $updated_booking = LOPAS_Booking::get($booking_id);
        
        return $this->success_response($updated_booking);
    }
    
    /**
     * Cancel booking
     */
    public function cancel_booking($request) {
        $user = wp_get_current_user();
        $booking_id = $request->get_param('id');
        
        $booking = LOPAS_Booking::get($booking_id);
        
        if (!$booking) {
            return $this->error_response('Booking not found', 'not_found', 404);
        }
        
        // Check ownership
        if ($booking->user_id != $user->ID && !current_user_can('manage_options')) {
            return $this->error_response('Unauthorized', 'unauthorized', 403);
        }
        
        $result = LOPAS_Booking::update($booking_id, array('status' => 'cancelled'));
        
        if (!$result) {
            return $this->error_response('Failed to cancel booking', 'cancel_failed', 400);
        }
        
        do_action('lopas_booking_cancelled', $booking_id);
        
        return $this->success_response(array('message' => 'Booking cancelled successfully'));
    }
    
    /**
     * Get available slots
     */
    public function get_available_slots($request) {
        $salon_id = $request->get_param('salon_id');
        $booking_date = $request->get_param('date');
        
        if (!$salon_id || !$booking_date) {
            return $this->error_response('Salon ID and date are required', 'missing_params', 400);
        }
        
        $salon = LOPAS_Salon::get($salon_id);
        
        if (!$salon) {
            return $this->error_response('Salon not found', 'not_found', 404);
        }
        
        // Generate time slots
        $slots = $this->generate_time_slots($salon->opening_time, $salon->closing_time);
        
        // Get booked slots
        global $wpdb;
        $bookings_table = LOPAS_Database::get_table('bookings');
        
        $booked = $wpdb->get_col($wpdb->prepare(
            "SELECT booking_time FROM {$bookings_table} 
            WHERE salon_id = %d AND booking_date = %s AND status IN ('pending', 'confirmed')",
            $salon_id,
            $booking_date
        ));
        
        // Filter available slots
        $available = array_filter($slots, function($slot) use ($booked) {
            return !in_array($slot, $booked);
        });
        
        return $this->success_response(array(
            'salon_id' => $salon_id,
            'date' => $booking_date,
            'available_slots' => array_values($available)
        ));
    }
    
    /**
     * Generate time slots
     */
    private function generate_time_slots($opening, $closing, $interval = 30) {
        $slots = array();
        $current = strtotime($opening);
        $end = strtotime($closing);
        
        while ($current < $end) {
            $slots[] = date('H:i:s', $current);
            $current += ($interval * 60);
        }
        
        return $slots;
    }
    
    /**
     * Check user permission
     */
    public function check_user_permission() {
        return is_user_logged_in();
    }
}
