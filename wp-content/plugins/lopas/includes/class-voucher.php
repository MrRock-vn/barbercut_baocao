<?php
/**
 * LOPAS Voucher Model
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Voucher {
    
    /**
     * Get voucher by ID
     */
    public static function get($voucher_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('vouchers');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $voucher_id));
    }
    
    /**
     * Get voucher by code
     */
    public static function get_by_code($code) {
        global $wpdb;
        $table = LOPAS_Database::get_table('vouchers');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE code = %s", $code));
    }
    
    /**
     * Get all vouchers
     */
    public static function get_all($limit = 50, $offset = 0) {
        global $wpdb;
        $table = LOPAS_Database::get_table('vouchers');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
    }
    
    /**
     * Create voucher
     */
    public static function create($data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('vouchers');
        
        $defaults = array(
            'code' => '',
            'discount_type' => 'percentage', // percentage or fixed
            'discount_value' => 0,
            'max_uses' => 0,
            'current_uses' => 0,
            'valid_from' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'active'
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate
        if (empty($data['code']) || empty($data['discount_value'])) {
            return false;
        }
        
        // Check if code already exists
        if (self::get_by_code($data['code'])) {
            return false;
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'code' => sanitize_text_field($data['code']),
                'discount_type' => sanitize_text_field($data['discount_type']),
                'discount_value' => floatval($data['discount_value']),
                'max_uses' => intval($data['max_uses']),
                'current_uses' => 0,
                'valid_from' => sanitize_text_field($data['valid_from']),
                'valid_until' => sanitize_text_field($data['valid_until']),
                'status' => sanitize_text_field($data['status'])
            ),
            array('%s', '%s', '%f', '%d', '%d', '%s', '%s', '%s')
        );
        
        if ($result) {
            do_action('lopas_voucher_created', $wpdb->insert_id, $data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update voucher
     */
    public static function update($voucher_id, $data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('vouchers');
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['code'])) {
            $update_data['code'] = sanitize_text_field($data['code']);
            $update_format[] = '%s';
        }
        if (isset($data['discount_type'])) {
            $update_data['discount_type'] = sanitize_text_field($data['discount_type']);
            $update_format[] = '%s';
        }
        if (isset($data['discount_value'])) {
            $update_data['discount_value'] = floatval($data['discount_value']);
            $update_format[] = '%f';
        }
        if (isset($data['max_uses'])) {
            $update_data['max_uses'] = intval($data['max_uses']);
            $update_format[] = '%d';
        }
        if (isset($data['valid_from'])) {
            $update_data['valid_from'] = sanitize_text_field($data['valid_from']);
            $update_format[] = '%s';
        }
        if (isset($data['valid_until'])) {
            $update_data['valid_until'] = sanitize_text_field($data['valid_until']);
            $update_format[] = '%s';
        }
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table,
            $update_data,
            array('id' => intval($voucher_id)),
            $update_format,
            array('%d')
        );
        
        if ($result !== false) {
            do_action('lopas_voucher_updated', $voucher_id, $data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete voucher
     */
    public static function delete($voucher_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('vouchers');
        
        $result = $wpdb->delete(
            $table,
            array('id' => intval($voucher_id)),
            array('%d')
        );
        
        if ($result) {
            do_action('lopas_voucher_deleted', $voucher_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Validate voucher
     */
    public static function validate($code, $order_total = 0) {
        $voucher = self::get_by_code($code);
        
        if (!$voucher) {
            return array('valid' => false, 'message' => 'Voucher not found');
        }
        
        if ($voucher->status !== 'active') {
            return array('valid' => false, 'message' => 'Voucher is not active');
        }
        
        $today = date('Y-m-d');
        if ($today < $voucher->valid_from || $today > $voucher->valid_until) {
            return array('valid' => false, 'message' => 'Voucher is expired');
        }
        
        if ($voucher->max_uses > 0 && $voucher->current_uses >= $voucher->max_uses) {
            return array('valid' => false, 'message' => 'Voucher usage limit reached');
        }
        
        return array(
            'valid' => true,
            'voucher' => $voucher,
            'discount' => self::calculate_discount($voucher, $order_total)
        );
    }
    
    /**
     * Calculate discount amount
     */
    public static function calculate_discount($voucher, $order_total) {
        if ($voucher->discount_type === 'percentage') {
            return ($order_total * $voucher->discount_value) / 100;
        } else {
            return min($voucher->discount_value, $order_total);
        }
    }
    
    /**
     * Apply voucher to order
     */
    public static function apply_to_order($order_id, $voucher_code) {
        $order = LOPAS_Order::get($order_id);
        if (!$order) {
            return false;
        }
        
        $validation = self::validate($voucher_code, $order->total_amount);
        if (!$validation['valid']) {
            return false;
        }
        
        $voucher = $validation['voucher'];
        $discount = $validation['discount'];
        
        // Update order
        $new_total = $order->total_amount - $discount;
        LOPAS_Order::update($order_id, array(
            'voucher_id' => $voucher->id,
            'discount_amount' => $discount,
            'total_amount' => $new_total
        ));
        
        // Increment voucher usage
        self::update($voucher->id, array(
            'current_uses' => $voucher->current_uses + 1
        ));
        
        do_action('lopas_voucher_applied', $order_id, $voucher->id);
        
        return array(
            'success' => true,
            'discount' => $discount,
            'new_total' => $new_total
        );
    }
    
    /**
     * Count vouchers
     */
    public static function count() {
        global $wpdb;
        $table = LOPAS_Database::get_table('vouchers');
        return intval($wpdb->get_var("SELECT COUNT(*) FROM {$table}"));
    }
}
