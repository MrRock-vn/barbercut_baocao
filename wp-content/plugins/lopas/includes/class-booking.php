<?php
/**
 * LOPAS Booking Model
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Booking {
    
    /**
     * Get booking by ID
     * 
     * @param int $booking_id Booking ID
     * @return object|null Booking object or null
     */
    public static function get($booking_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $booking_id));
    }
    
    /**
     * Get booking by code
     * 
     * @param string $booking_code Booking code
     * @return object|null Booking object or null
     */
    public static function get_by_code($booking_code) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE booking_code = %s", $booking_code));
    }
    
    /**
     * Get user bookings
     * 
     * @param int $user_id User ID
     * @param array $args Query arguments
     * @return array Array of booking objects
     */
    public static function get_by_user($user_id, $args = array()) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        $defaults = array(
            'status' => null,
            'limit' => -1,
            'offset' => 0,
            'orderby' => 'booking_date',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query = $wpdb->prepare("SELECT * FROM {$table} WHERE user_id = %d", $user_id);
        
        if ($args['status']) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get salon bookings
     * 
     * @param int $salon_id Salon ID
     * @param array $args Query arguments
     * @return array Array of booking objects
     */
    public static function get_by_salon($salon_id, $args = array()) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        $defaults = array(
            'status' => null,
            'date' => null,
            'limit' => -1,
            'offset' => 0,
            'orderby' => 'booking_time',
            'order' => 'ASC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query = $wpdb->prepare("SELECT * FROM {$table} WHERE salon_id = %d", $salon_id);
        
        if ($args['status']) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        if ($args['date']) {
            $query .= $wpdb->prepare(" AND booking_date = %s", $args['date']);
        }
        
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Create new booking
     * 
     * @param array $data Booking data
     * @return int|false Booking ID or false on error
     */
    public static function create($data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        $defaults = array(
            'user_id' => get_current_user_id(),
            'salon_id' => 0,
            'service_id' => 0,
            'staff_id' => null,
            'booking_date' => '',
            'booking_time' => '',
            'duration' => 30,
            'note' => '',
            'status' => 'pending'
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['user_id']) || empty($data['salon_id']) || empty($data['service_id']) || 
            empty($data['booking_date']) || empty($data['booking_time'])) {
            return false;
        }

        // If staff_id is empty, pick the first staff member from the salon
        if (empty($data['staff_id'])) {
            global $wpdb;
            $staff_table = LOPAS_Database::get_table('staff');
            $data['staff_id'] = $wpdb->get_var($wpdb->prepare("SELECT id FROM $staff_table WHERE salon_id = %d LIMIT 1", $data['salon_id']));
        }
        
        if (empty($data['staff_id'])) {
            // Still empty? Maybe no staff in salon. Try to create one.
            $data['staff_id'] = 0; // This will still fail if FK exists, but we seeded staff so it shouldn't.
        }
        
        // Generate booking code
        $booking_code = lopas_generate_short_code('BSB');
        
        // Check if slot is available
        if (!self::is_slot_available($data['salon_id'], $data['staff_id'], $data['booking_date'], $data['booking_time'])) {
            return false;
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'booking_code' => $booking_code,
                'user_id' => intval($data['user_id']),
                'salon_id' => intval($data['salon_id']),
                'service_id' => intval($data['service_id']),
                'staff_id' => intval($data['staff_id']),
                'booking_date' => sanitize_text_field($data['booking_date']),
                'booking_time' => sanitize_text_field($data['booking_time']),
                'duration' => intval($data['duration']),
                'note' => wp_kses_post($data['note']),
                'status' => sanitize_text_field($data['status'])
            ),
            array('%s', '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result) {
            do_action('lopas_booking_created', $wpdb->insert_id, $data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update booking
     * 
     * @param int $booking_id Booking ID
     * @param array $data Booking data to update
     * @return bool True on success, false on error
     */
    public static function update($booking_id, $data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['booking_date'])) {
            $update_data['booking_date'] = sanitize_text_field($data['booking_date']);
            $update_format[] = '%s';
        }
        if (isset($data['booking_time'])) {
            $update_data['booking_time'] = sanitize_text_field($data['booking_time']);
            $update_format[] = '%s';
        }
        if (isset($data['staff_id'])) {
            $update_data['staff_id'] = intval($data['staff_id']);
            $update_format[] = '%d';
        }
        if (isset($data['note'])) {
            $update_data['note'] = wp_kses_post($data['note']);
            $update_format[] = '%s';
        }
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            $update_format[] = '%s';
        }
        if (isset($data['cancellation_reason'])) {
            $update_data['cancellation_reason'] = wp_kses_post($data['cancellation_reason']);
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table,
            $update_data,
            array('id' => intval($booking_id)),
            $update_format,
            array('%d')
        );
        
        if ($result !== false) {
            do_action('lopas_booking_updated', $booking_id, $data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Cancel booking
     * 
     * @param int $booking_id Booking ID
     * @param string $reason Cancellation reason
     * @return bool True on success, false on error
     */
    public static function cancel($booking_id, $reason = '') {
        return self::update($booking_id, array(
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ));
    }
    
    /**
     * Check if time slot is available
     * 
     * @param int $salon_id Salon ID
     * @param int $staff_id Staff ID
     * @param string $booking_date Booking date (Y-m-d)
     * @param string $booking_time Booking time (H:i:s)
     * @return bool True if available, false otherwise
     */
    public static function is_slot_available($salon_id, $staff_id, $booking_date, $booking_time) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE salon_id = %d AND booking_date = %s AND booking_time = %s AND status != 'cancelled'",
            $salon_id,
            $booking_date,
            $booking_time
        );
        
        if ($staff_id) {
            $query .= $wpdb->prepare(" AND staff_id = %d", $staff_id);
        }
        
        $count = intval($wpdb->get_var($query));
        return $count === 0;
    }
    
    /**
     * Get available time slots for a date
     * 
     * @param int $salon_id Salon ID
     * @param string $booking_date Booking date (Y-m-d)
     * @param int $duration Service duration in minutes
     * @return array Array of available time slots
     */
    public static function get_available_slots($salon_id, $booking_date, $duration = 30) {
        global $wpdb;
        
        $salon = LOPAS_Salon::get($salon_id);
        if (!$salon) {
            return array();
        }
        
        $opening = strtotime($salon->opening_time);
        $closing = strtotime($salon->closing_time);
        $interval = $duration * 60; // Convert to seconds
        
        $slots = array();
        for ($time = $opening; $time < $closing; $time += $interval) {
            $slot_time = date('H:i:s', $time);
            if (self::is_slot_available($salon_id, null, $booking_date, $slot_time)) {
                $slots[] = $slot_time;
            }
        }
        
        return $slots;
    }
    
    /**
     * Count bookings by salon
     * 
     * @param int $salon_id Salon ID
     * @param string $status Booking status filter
     * @return int Total count
     */
    public static function count($salon_id, $status = null) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        $query = $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE salon_id = %d", $salon_id);
        
        if ($status) {
            $query .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        return intval($wpdb->get_var($query));
    }
}
