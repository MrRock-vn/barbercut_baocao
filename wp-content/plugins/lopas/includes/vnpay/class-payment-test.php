<?php
/**
 * Payment Testing Utilities
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Payment_Test {
    
    /**
     * Create test order and payment
     */
    public static function create_test_order() {
        // Get current user
        $user_id = get_current_user_id();
        if (!$user_id) {
            return array('error' => 'User not logged in');
        }
        
        // Get first salon
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        $salon = $wpdb->get_row("SELECT * FROM {$table} WHERE status = 'active' LIMIT 1");
        
        if (!$salon) {
            return array('error' => 'No active salon found');
        }
        
        // Get first service
        $table = LOPAS_Database::get_table('services');
        $service = $wpdb->get_row("SELECT * FROM {$table} WHERE status = 'active' LIMIT 1");
        
        if (!$service) {
            return array('error' => 'No active service found');
        }
        
        // Create booking
        $booking_id = LOPAS_Booking::create(array(
            'user_id' => $user_id,
            'salon_id' => $salon->id,
            'service_id' => $service->id,
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'booking_time' => '10:00:00',
            'status' => 'pending'
        ));
        
        if (!$booking_id) {
            return array('error' => 'Failed to create booking');
        }
        
        // Create order
        $order_id = LOPAS_Order::create(array(
            'user_id' => $user_id,
            'total_amount' => $service->price,
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ));
        
        if (!$order_id) {
            return array('error' => 'Failed to create order');
        }
        
        return array(
            'success' => true,
            'booking_id' => $booking_id,
            'order_id' => $order_id,
            'salon' => $salon,
            'service' => $service
        );
    }
    
    /**
     * Simulate VNPay payment success
     */
    public static function simulate_payment_success($order_id) {
        $order = LOPAS_Order::get($order_id);
        if (!$order) {
            return array('error' => 'Order not found');
        }
        
        // Create payment
        $payment_id = LOPAS_Payment::create(array(
            'order_id' => $order_id,
            'amount' => $order->total_amount,
            'payment_method' => 'vnpay',
            'status' => 'pending'
        ));
        
        if (!$payment_id) {
            return array('error' => 'Failed to create payment');
        }
        
        // Mark as successful
        LOPAS_Payment::mark_success($payment_id, array(
            'response_code' => '00',
            'transaction_code' => 'TEST' . time(),
            'bank_code' => 'TESTBANK'
        ));
        
        // Update order
        LOPAS_Order::update($order_id, array(
            'payment_status' => 'paid',
            'status' => 'confirmed'
        ));
        
        return array(
            'success' => true,
            'payment_id' => $payment_id,
            'message' => 'Payment simulated successfully'
        );
    }
    
    /**
     * Simulate VNPay payment failure
     */
    public static function simulate_payment_failure($order_id) {
        $order = LOPAS_Order::get($order_id);
        if (!$order) {
            return array('error' => 'Order not found');
        }
        
        // Create payment
        $payment_id = LOPAS_Payment::create(array(
            'order_id' => $order_id,
            'amount' => $order->total_amount,
            'payment_method' => 'vnpay',
            'status' => 'pending'
        ));
        
        if (!$payment_id) {
            return array('error' => 'Failed to create payment');
        }
        
        // Mark as failed
        LOPAS_Payment::mark_failed($payment_id, array(
            'response_code' => '01',
            'transaction_code' => 'TEST' . time(),
            'message' => 'Transaction rejected'
        ));
        
        return array(
            'success' => true,
            'payment_id' => $payment_id,
            'message' => 'Payment failure simulated'
        );
    }
    
    /**
     * Get test data
     */
    public static function get_test_data() {
        global $wpdb;
        
        $salons_table = LOPAS_Database::get_table('salons');
        $services_table = LOPAS_Database::get_table('services');
        $bookings_table = LOPAS_Database::get_table('bookings');
        $orders_table = LOPAS_Database::get_table('orders');
        $payments_table = LOPAS_Database::get_table('payments');
        
        return array(
            'salons' => $wpdb->get_results("SELECT * FROM {$salons_table} LIMIT 5"),
            'services' => $wpdb->get_results("SELECT * FROM {$services_table} LIMIT 5"),
            'bookings' => $wpdb->get_results("SELECT * FROM {$bookings_table} ORDER BY id DESC LIMIT 5"),
            'orders' => $wpdb->get_results("SELECT * FROM {$orders_table} ORDER BY id DESC LIMIT 5"),
            'payments' => $wpdb->get_results("SELECT * FROM {$payments_table} ORDER BY id DESC LIMIT 5")
        );
    }
}
