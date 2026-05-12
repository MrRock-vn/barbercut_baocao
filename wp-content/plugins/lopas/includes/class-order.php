<?php
/**
 * LOPAS Order Model
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Order {
    
    /**
     * Get order by ID
     * 
     * @param int $order_id Order ID
     * @return object|null Order object or null
     */
    public static function get($order_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $order_id));
    }
    
    /**
     * Get order by code
     * 
     * @param string $order_code Order code
     * @return object|null Order object or null
     */
    public static function get_by_code($order_code) {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE order_code = %s", $order_code));
    }
    
    /**
     * Get user orders
     * 
     * @param int $user_id User ID
     * @param array $args Query arguments
     * @return array Array of order objects
     */
    public static function get_by_user($user_id, $args = array()) {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        
        $defaults = array(
            'status' => null,
            'payment_status' => null,
            'limit' => -1,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query = $wpdb->prepare("SELECT * FROM {$table} WHERE user_id = %d", $user_id);
        
        if ($args['status']) {
            $query .= $wpdb->prepare(" AND order_status = %s", $args['status']);
        }
        
        if ($args['payment_status']) {
            $query .= $wpdb->prepare(" AND payment_status = %s", $args['payment_status']);
        }
        
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Create new order
     * 
     * @param array $data Order data
     * @return int|false Order ID or false on error
     */
    public static function create($data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        
        $defaults = array(
            'user_id' => get_current_user_id(),
            'total_price' => 0,
            'voucher_id' => null,
            'discount_amount' => 0,
            'final_price' => 0,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'notes' => ''
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['user_id']) || empty($data['total_price'])) {
            return false;
        }
        
        // Generate order code
        $order_code = lopas_generate_short_code('BSO');
        
        // Calculate final price
        $final_price = floatval($data['total_price']) - floatval($data['discount_amount']);
        
        $result = $wpdb->insert(
            $table,
            array(
                'order_code' => $order_code,
                'booking_id' => intval($data['booking_id']),
                'user_id' => intval($data['user_id']),
                'total_price' => floatval($data['total_price']),
                'voucher_id' => intval($data['voucher_id']),
                'discount_amount' => floatval($data['discount_amount']),
                'final_price' => $final_price,
                'payment_method' => sanitize_text_field($data['payment_method']),
                'payment_status' => sanitize_text_field($data['payment_status']),
                'order_status' => sanitize_text_field($data['order_status']),
                'notes' => wp_kses_post($data['notes'])
            ),
            array('%s', '%d', '%d', '%f', '%d', '%f', '%f', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            do_action('lopas_order_created', $wpdb->insert_id, $data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update order
     * 
     * @param int $order_id Order ID
     * @param array $data Order data to update
     * @return bool True on success, false on error
     */
    public static function update($order_id, $data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['payment_status'])) {
            $update_data['payment_status'] = sanitize_text_field($data['payment_status']);
            $update_format[] = '%s';
        }
        if (isset($data['order_status'])) {
            $update_data['order_status'] = sanitize_text_field($data['order_status']);
            $update_format[] = '%s';
        }
        if (isset($data['notes'])) {
            $update_data['notes'] = wp_kses_post($data['notes']);
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table,
            $update_data,
            array('id' => intval($order_id)),
            $update_format,
            array('%d')
        );
        
        if ($result !== false) {
            do_action('lopas_order_updated', $order_id, $data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Add item to order
     * 
     * @param int $order_id Order ID
     * @param int $service_id Service ID
     * @param float $price Item price
     * @param int $booking_id Booking ID (optional)
     * @param int $quantity Item quantity
     * @return int|false Order item ID or false on error
     */
    public static function add_item($order_id, $service_id, $price, $booking_id = null, $quantity = 1) {
        global $wpdb;
        $table = LOPAS_Database::get_table('order_items');
        
        $subtotal = floatval($price) * intval($quantity);
        
        $result = $wpdb->insert(
            $table,
            array(
                'order_id' => intval($order_id),
                'booking_id' => intval($booking_id),
                'service_id' => intval($service_id),
                'price' => floatval($price),
                'quantity' => intval($quantity),
                'subtotal' => $subtotal
            ),
            array('%d', '%d', '%d', '%f', '%d', '%f')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get order items
     * 
     * @param int $order_id Order ID
     * @return array Array of order item objects
     */
    public static function get_items($order_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('order_items');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE order_id = %d",
            $order_id
        ));
    }
    
    /**
     * Count orders by user
     * 
     * @param int $user_id User ID
     * @param string $status Order status filter
     * @return int Total count
     */
    public static function count($user_id, $status = null) {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        
        $query = $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id);
        
        if ($status) {
            $query .= $wpdb->prepare(" AND order_status = %s", $status);
        }
        
        return intval($wpdb->get_var($query));
    }
}
