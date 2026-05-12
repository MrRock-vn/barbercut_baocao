<?php
/**
 * LOPAS Payment Model
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Payment {
    
    /**
     * Get payment by ID
     * 
     * @param int $payment_id Payment ID
     * @return object|null Payment object or null
     */
    public static function get($payment_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $payment_id));
    }
    
    /**
     * Get payment by transaction code
     * 
     * @param string $transaction_code Transaction code
     * @return object|null Payment object or null
     */
    public static function get_by_transaction($transaction_code) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE transaction_code = %s", $transaction_code));
    }
    
    /**
     * Get order payments
     * 
     * @param int $order_id Order ID
     * @return array Array of payment objects
     */
    public static function get_by_order($order_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE order_id = %d ORDER BY created_at DESC",
            $order_id
        ));
    }
    
    /**
     * Create new payment
     * 
     * @param array $data Payment data
     * @return int|false Payment ID or false on error
     */
    public static function create($data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        
        $defaults = array(
            'order_id' => 0,
            'transaction_code' => '',
            'amount' => 0,
            'payment_method' => 'cod',
            'status' => 'pending',
            'response_data' => ''
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['order_id']) || empty($data['amount'])) {
            return false;
        }
        
        // Generate transaction code if not provided
        if (empty($data['transaction_code'])) {
            $data['transaction_code'] = lopas_generate_code('TXN');
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'order_id' => intval($data['order_id']),
                'transaction_code' => sanitize_text_field($data['transaction_code']),
                'amount' => floatval($data['amount']),
                'payment_method' => sanitize_text_field($data['payment_method']),
                'status' => sanitize_text_field($data['status']),
                'response_data' => wp_json_encode($data['response_data'])
            ),
            array('%d', '%s', '%f', '%s', '%s', '%s')
        );
        
        if ($result) {
            do_action('lopas_payment_created', $wpdb->insert_id, $data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update payment
     * 
     * @param int $payment_id Payment ID
     * @param array $data Payment data to update
     * @return bool True on success, false on error
     */
    public static function update($payment_id, $data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            $update_format[] = '%s';
        }
        if (isset($data['response_data'])) {
            $update_data['response_data'] = wp_json_encode($data['response_data']);
            $update_format[] = '%s';
        }
        if (isset($data['refund_id'])) {
            $update_data['refund_id'] = intval($data['refund_id']);
            $update_format[] = '%d';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table,
            $update_data,
            array('id' => intval($payment_id)),
            $update_format,
            array('%d')
        );
        
        if ($result !== false) {
            do_action('lopas_payment_updated', $payment_id, $data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Mark payment as successful
     * 
     * @param int $payment_id Payment ID
     * @param array $response_data Payment response data
     * @return bool True on success, false on error
     */
    public static function mark_success($payment_id, $response_data = array()) {
        $payment = self::get($payment_id);
        if (!$payment) {
            return false;
        }
        
        // Update payment status
        $result = self::update($payment_id, array(
            'status' => 'success',
            'response_data' => $response_data
        ));
        
        if ($result) {
            // Update order payment status
            $order = LOPAS_Order::get($payment->order_id);
            if ($order) {
                LOPAS_Order::update($payment->order_id, array(
                    'payment_status' => 'paid'
                ));
            }
            
            do_action('lopas_payment_success', $payment_id, $payment->order_id);
        }
        
        return $result;
    }
    
    /**
     * Mark payment as failed
     * 
     * @param int $payment_id Payment ID
     * @param array $response_data Payment response data
     * @return bool True on success, false on error
     */
    public static function mark_failed($payment_id, $response_data = array()) {
        return self::update($payment_id, array(
            'status' => 'failed',
            'response_data' => $response_data
        ));
    }
    
    /**
     * Create refund
     * 
     * @param int $payment_id Payment ID
     * @param float $amount Refund amount
     * @param string $reason Refund reason
     * @return int|false Refund ID or false on error
     */
    public static function create_refund($payment_id, $amount, $reason = '') {
        global $wpdb;
        $table = LOPAS_Database::get_table('refunds');
        
        $payment = self::get($payment_id);
        if (!$payment) {
            return false;
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'payment_id' => intval($payment_id),
                'amount' => floatval($amount),
                'reason' => sanitize_text_field($reason),
                'status' => 'pending'
            ),
            array('%d', '%f', '%s', '%s')
        );
        
        if ($result) {
            do_action('lopas_refund_created', $wpdb->insert_id, $payment_id);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get refund
     * 
     * @param int $refund_id Refund ID
     * @return object|null Refund object or null
     */
    public static function get_refund($refund_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('refunds');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $refund_id));
    }
    
    /**
     * Update refund status
     * 
     * @param int $refund_id Refund ID
     * @param string $status Refund status
     * @return bool True on success, false on error
     */
    public static function update_refund_status($refund_id, $status) {
        global $wpdb;
        $table = LOPAS_Database::get_table('refunds');
        
        $result = $wpdb->update(
            $table,
            array('status' => sanitize_text_field($status)),
            array('id' => intval($refund_id)),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            do_action('lopas_refund_status_updated', $refund_id, $status);
            return true;
        }
        
        return false;
    }
    
    /**
     * Count payments by order
     * 
     * @param int $order_id Order ID
     * @param string $status Payment status filter
     * @return int Total count
     */
    public static function count($order_id, $status = null) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        
        $query = $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE order_id = %d", $order_id);
        
        if ($status) {
            $query .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        return intval($wpdb->get_var($query));
    }
}
