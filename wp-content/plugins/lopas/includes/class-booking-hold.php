<?php
/**
 * LOPAS Booking Hold Model
 * Handles temporary slot holding during booking process
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Booking_Hold {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'lopas_booking_holds';
    }
    
    /**
     * Hold a time slot temporarily
     * 
     * @param array $data Hold data
     * @return int|false Hold ID or false on failure
     */
    public function hold_slot($data) {
        global $wpdb;
        
        // Validate required fields
        $required = ['session_id', 'staff_id', 'service_date', 'start_time', 'end_time'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        
        // Clean expired holds first
        $this->clean_expired_holds();
        
        // Check if slot is already held by another session
        if ($this->is_slot_held($data['staff_id'], $data['service_date'], $data['start_time'], $data['end_time'], $data['session_id'])) {
            return false;
        }
        
        // Check if slot is already booked
        if ($this->is_slot_booked($data['staff_id'], $data['service_date'], $data['start_time'], $data['end_time'])) {
            return false;
        }
        
        // Release any existing holds for this session
        $this->release_session_holds($data['session_id']);
        
        // Set expiration time (10 minutes from now)
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $insert_data = array(
            'user_id' => !empty($data['user_id']) ? absint($data['user_id']) : null,
            'session_id' => sanitize_text_field($data['session_id']),
            'staff_id' => absint($data['staff_id']),
            'service_date' => sanitize_text_field($data['service_date']),
            'start_time' => sanitize_text_field($data['start_time']),
            'end_time' => sanitize_text_field($data['end_time']),
            'expires_at' => $expires_at,
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            do_action('lopas_slot_held', $wpdb->insert_id, $insert_data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Check if a slot is held by another session
     * 
     * @param int $staff_id Staff ID
     * @param string $service_date Service date
     * @param string $start_time Start time
     * @param string $end_time End time
     * @param string $exclude_session Session to exclude from check
     * @return bool
     */
    public function is_slot_held($staff_id, $service_date, $start_time, $end_time, $exclude_session = '') {
        global $wpdb;
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name}
                WHERE staff_id = %d
                AND service_date = %s
                AND expires_at > NOW()
                AND (
                    (start_time < %s AND end_time > %s)
                    OR (start_time < %s AND end_time > %s)
                    OR (start_time >= %s AND end_time <= %s)
                )";
        
        $params = array(
            $staff_id,
            $service_date,
            $end_time, $start_time,
            $end_time, $start_time,
            $start_time, $end_time
        );
        
        if (!empty($exclude_session)) {
            $sql .= " AND session_id != %s";
            $params[] = $exclude_session;
        }
        
        $count = $wpdb->get_var($wpdb->prepare($sql, $params));
        
        return $count > 0;
    }
    
    /**
     * Check if a slot is already booked
     * 
     * @param int $staff_id Staff ID
     * @param string $service_date Service date
     * @param string $start_time Start time
     * @param string $end_time End time
     * @return bool
     */
    public function is_slot_booked($staff_id, $service_date, $start_time, $end_time) {
        global $wpdb;
        
        $bookings_table = $wpdb->prefix . 'lopas_bookings';
        
        $sql = "SELECT COUNT(*) FROM {$bookings_table}
                WHERE staff_id = %d
                AND booking_date = %s
                AND status NOT IN ('cancelled')
                AND (
                    (booking_time < %s AND DATE_ADD(booking_time, INTERVAL duration MINUTE) > %s)
                    OR (booking_time < %s AND DATE_ADD(booking_time, INTERVAL duration MINUTE) > %s)
                    OR (booking_time >= %s AND DATE_ADD(booking_time, INTERVAL duration MINUTE) <= %s)
                )";
        
        $count = $wpdb->get_var($wpdb->prepare($sql, array(
            $staff_id,
            $service_date,
            $end_time, $start_time,
            $end_time, $start_time,
            $start_time, $end_time
        )));
        
        return $count > 0;
    }
    
    /**
     * Release all holds for a session
     * 
     * @param string $session_id Session ID
     * @return bool
     */
    public function release_session_holds($session_id) {
        global $wpdb;
        
        $result = $wpdb->delete($this->table_name, array(
            'session_id' => sanitize_text_field($session_id)
        ));
        
        if ($result !== false) {
            do_action('lopas_session_holds_released', $session_id);
        }
        
        return $result !== false;
    }
    
    /**
     * Clean expired holds
     * 
     * @return int Number of holds cleaned
     */
    public function clean_expired_holds() {
        global $wpdb;
        
        $result = $wpdb->query("DELETE FROM {$this->table_name} WHERE expires_at <= NOW()");
        
        if ($result > 0) {
            do_action('lopas_expired_holds_cleaned', $result);
        }
        
        return $result;
    }
    
    /**
     * Get available time slots for a staff member on a specific date
     * 
     * @param int $staff_id Staff ID
     * @param string $date Date (Y-m-d)
     * @param int $duration Duration in minutes
     * @return array Available slots
     */
    public function get_available_slots($staff_id, $date, $duration = 60) {
        global $wpdb;
        
        // Get staff working hours (default 8:00 - 20:00)
        $start_hour = 8;
        $end_hour = 20;
        $slot_interval = 30; // 30 minutes interval
        
        // Generate all possible slots
        $slots = array();
        $current_time = strtotime($date . ' ' . $start_hour . ':00:00');
        $end_time = strtotime($date . ' ' . $end_hour . ':00:00');
        
        while ($current_time < $end_time) {
            $slot_start = date('H:i:s', $current_time);
            $slot_end = date('H:i:s', strtotime('+' . $duration . ' minutes', $current_time));
            
            // Check if slot end time is within working hours
            if (strtotime($date . ' ' . $slot_end) <= $end_time) {
                // Check if slot is available
                if (!$this->is_slot_held($staff_id, $date, $slot_start, $slot_end) &&
                    !$this->is_slot_booked($staff_id, $date, $slot_start, $slot_end)) {
                    $slots[] = $slot_start;
                }
            }
            
            $current_time = strtotime('+' . $slot_interval . ' minutes', $current_time);
        }
        
        return $slots;
    }
    
    /**
     * Extend hold expiration time
     * 
     * @param int $hold_id Hold ID
     * @param int $minutes Minutes to extend
     * @return bool
     */
    public function extend_hold($hold_id, $minutes = 10) {
        global $wpdb;
        
        $new_expires_at = date('Y-m-d H:i:s', strtotime('+' . $minutes . ' minutes'));
        
        $result = $wpdb->update(
            $this->table_name,
            array('expires_at' => $new_expires_at),
            array('id' => absint($hold_id))
        );
        
        return $result !== false;
    }
    
    /**
     * Get hold by session and slot
     * 
     * @param string $session_id Session ID
     * @param int $staff_id Staff ID
     * @param string $service_date Service date
     * @param string $start_time Start time
     * @return object|null
     */
    public function get_hold($session_id, $staff_id, $service_date, $start_time) {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_name}
                WHERE session_id = %s
                AND staff_id = %d
                AND service_date = %s
                AND start_time = %s
                AND expires_at > NOW()
                LIMIT 1";
        
        return $wpdb->get_row($wpdb->prepare($sql, array(
            $session_id,
            $staff_id,
            $service_date,
            $start_time
        )));
    }
}

