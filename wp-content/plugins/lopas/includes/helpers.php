<?php
/**
 * Helper functions for LOPAS plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get database prefix
 */
function lopas_get_prefix() {
    global $wpdb;
    return $wpdb->prefix . 'lopas_';
}

/**
 * Generate unique code
 * 
 * @param string $prefix Code prefix (VD: 'BSB' for booking, 'BSO' for order)
 * @return string Unique code
 */
function lopas_generate_code($prefix = 'LOPAS') {
    $date = date('YmdHis');
    $random = strtoupper(substr(md5(time() . rand()), 0, 6));
    return $prefix . $date . $random;
}

/**
 * Generate short unique code
 * 
 * @param string $prefix Code prefix
 * @return string Short code (VD: BSB20260509001)
 */
function lopas_generate_short_code($prefix = 'LOPAS') {
    $today = date('Ymd');
    $sequence = mt_rand(1000, 9999);
    return $prefix . $today . $sequence;
}

/**
 * Format Vietnamese currency
 * 
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function lopas_format_currency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

/**
 * Format Vietnamese date
 * 
 * @param string $date Date string (Y-m-d format)
 * @return string Formatted date
 */
function lopas_format_date($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Format date Vietnamese style
 * 
 * @param string $date Date string
 * @return string Formatted (VD: "Thứ hai, 09/05/2026")
 */
function lopas_format_date_vi($date) {
    $timestamp = strtotime($date);
    $days = array('Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy');
    $day_name = $days[date('w', $timestamp)];
    return $day_name . ', ' . date('d/m/Y', $timestamp);
}

/**
 * Get salon by ID
 * 
 * @param int $salon_id Salon ID
 * @return object|null Salon object or null
 */
function lopas_get_salon($salon_id) {
    global $wpdb;
    $table = lopas_get_prefix() . 'salons';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $salon_id));
}

/**
 * Get service by ID
 * 
 * @param int $service_id Service ID
 * @return object|null Service object or null
 */
function lopas_get_service($service_id) {
    global $wpdb;
    $table = lopas_get_prefix() . 'services';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $service_id));
}

/**
 * Get booking by ID
 * 
 * @param int $booking_id Booking ID
 * @return object|null Booking object or null
 */
function lopas_get_booking($booking_id) {
    global $wpdb;
    $table = lopas_get_prefix() . 'bookings';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $booking_id));
}

/**
 * Check if user is salon owner
 * 
 * @param int $user_id User ID
 * @param int $salon_id Salon ID
 * @return bool
 */
function lopas_is_salon_owner($user_id, $salon_id) {
    $salon = lopas_get_salon($salon_id);
    return $salon && $salon->user_id == $user_id;
}

/**
 * Send email notification
 * 
 * @param string $email Email address
 * @param string $subject Email subject
 * @param string $message Email message
 * @param array $headers Email headers
 * @return bool
 */
function lopas_send_email($email, $subject, $message, $headers = array()) {
    $default_headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
    );
    
    $headers = array_merge($default_headers, (array) $headers);
    
    return wp_mail($email, $subject, $message, $headers);
}

/**
 * API Response helper
 * 
 * @param bool $success Success status
 * @param mixed $data Data to return
 * @param string $message Message
 * @param int $code HTTP status code
 * @return array Response array
 */
function lopas_api_response($success, $data = null, $message = '', $code = 200) {
    return array(
        'success' => $success,
        'code' => $code,
        'message' => $message,
        'data' => $data
    );
}

/**
 * Get user's full name
 * 
 * @param int $user_id User ID
 * @return string User full name
 */
function lopas_get_user_fullname($user_id) {
    $user = get_userdata($user_id);
    if (!$user) {
        return '';
    }
    
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    
    if ($first_name || $last_name) {
        return trim($first_name . ' ' . $last_name);
    }
    
    return $user->display_name;
}

/**
 * Check if string is valid email
 * 
 * @param string $email Email string
 * @return bool
 */
function lopas_is_valid_email($email) {
    return is_email($email);
}

/**
 * Check if string is valid phone number (Vietnam)
 * 
 * @param string $phone Phone number
 * @return bool
 */
function lopas_is_valid_phone($phone) {
    // Vietnam phone: 10 digits starting with 0
    return preg_match('/^0\d{9}$/', str_replace(array(' ', '-', '.'), '', $phone));
}

/**
 * Get plugin setting
 * 
 * @param string $key Setting key
 * @param mixed $default Default value
 * @return mixed Setting value
 */
function lopas_get_setting($key, $default = null) {
    return get_option('lopas_' . $key, $default);
}

/**
 * Update plugin setting
 * 
 * @param string $key Setting key
 * @param mixed $value Setting value
 * @return bool
 */
function lopas_update_setting($key, $value) {
    return update_option('lopas_' . $key, $value);
}
