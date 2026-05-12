<?php
/**
 * Payment Controller - Handle payment processing
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Payment_Controller {
    
    private $gateway;
    
    public function __construct() {
        $this->gateway = new LOPAS_VNPay_Gateway();
        
        // Register AJAX handlers
        add_action('wp_ajax_lopas_create_payment', array($this, 'create_payment'));
        add_action('wp_ajax_nopriv_lopas_create_payment', array($this, 'create_payment'));
        
        add_action('wp_ajax_lopas_vnpay_return', array($this, 'handle_vnpay_return'));
        add_action('wp_ajax_nopriv_lopas_vnpay_return', array($this, 'handle_vnpay_return'));
        
        add_action('wp_ajax_lopas_vnpay_ipn', array($this, 'handle_vnpay_ipn'));
        add_action('wp_ajax_nopriv_lopas_vnpay_ipn', array($this, 'handle_vnpay_ipn'));
    }
    
    /**
     * Get VNPay URL (Static helper)
     */
    public static function get_vnpay_url($order_id) {
        $gateway = new LOPAS_VNPay_Gateway();
        $order = LOPAS_Order::get($order_id);
        if (!$order) {
            return '';
        }
        
        return $gateway->create_payment_url(
            $order_id,
            $order->total_price,
            $order->order_code
        );
    }
    
    /**
     * Create payment and redirect to VNPay
     */
    public function create_payment() {
        check_ajax_referer('lopas_nonce', 'nonce');
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : 'cod';
        
        if (empty($order_id)) {
            wp_send_json_error(array('message' => 'Order ID is required'));
        }
        
        // Get order
        $order = LOPAS_Order::get($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => 'Order not found'));
        }
        
        // Check if user owns this order
        if ($order->user_id != get_current_user_id() && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        // Handle COD payment
        if ($payment_method === 'cod') {
            $payment_id = LOPAS_Payment::create(array(
                'order_id' => $order_id,
                'amount' => $order->total_amount,
                'payment_method' => 'cod',
                'status' => 'pending'
            ));
            
            if ($payment_id) {
                wp_send_json_success(array(
                    'message' => 'Payment created successfully',
                    'payment_id' => $payment_id,
                    'redirect' => add_query_arg('payment_id', $payment_id, home_url('/payment-confirmation/'))
                ));
            } else {
                wp_send_json_error(array('message' => 'Failed to create payment'));
            }
        }
        
        // Handle VNPay payment
        if ($payment_method === 'vnpay') {
            if (!$this->gateway->is_enabled()) {
                wp_send_json_error(array('message' => 'VNPay is not configured'));
            }
            
            // Create payment record
            $payment_id = LOPAS_Payment::create(array(
                'order_id' => $order_id,
                'amount' => $order->total_amount,
                'payment_method' => 'vnpay',
                'status' => 'pending'
            ));
            
            if (!$payment_id) {
                wp_send_json_error(array('message' => 'Failed to create payment'));
            }
            
            // Create VNPay payment URL
            $payment_url = $this->gateway->create_payment_url(
                $order_id,
                $order->total_amount,
                $order->order_code
            );
            
            if (!$payment_url) {
                wp_send_json_error(array('message' => 'Failed to create payment URL'));
            }
            
            // Store payment ID in session for verification
            $_SESSION['lopas_payment_id'] = $payment_id;
            $_SESSION['lopas_order_id'] = $order_id;
            
            wp_send_json_success(array(
                'message' => 'Redirecting to VNPay',
                'payment_url' => $payment_url,
                'payment_id' => $payment_id
            ));
        }
        
        wp_send_json_error(array('message' => 'Invalid payment method'));
    }
    
    /**
     * Handle VNPay return (user returns from VNPay)
     */
    public function handle_vnpay_return() {
        // Get VNPay response
        $response = $_GET;
        
        if (empty($response)) {
            wp_redirect(home_url('/payment-failed/'));
            exit;
        }
        
        // Verify response
        if (!$this->gateway->verify_response($response)) {
            wp_redirect(home_url('/payment-failed/?reason=invalid_signature'));
            exit;
        }
        
        // Parse response
        $parsed = $this->gateway->parse_response($response);
        
        // Get order by order code
        $order = LOPAS_Order::get_by_code($parsed['order_code']);
        if (!$order) {
            wp_redirect(home_url('/payment-failed/?reason=order_not_found'));
            exit;
        }
        
        // Get the most recent pending payment for this order
        $payments = LOPAS_Payment::get_by_order($order->id);
        $payment = null;
        foreach ($payments as $p) {
            if ($p->status === 'pending' && $p->payment_method === 'vnpay') {
                $payment = $p;
                break;
            }
        }
        
        if (!$payment) {
            wp_redirect(home_url('/payment-failed/?reason=payment_not_found'));
            exit;
        }

        // Update payment with actual VNPay transaction code
        global $wpdb;
        $wpdb->update(
            LOPAS_Database::get_table('payments'),
            array('transaction_code' => $parsed['transaction_code']),
            array('id' => $payment->id)
        );
        
        // Check if payment is successful
        if ($this->gateway->is_payment_success($parsed['response_code'])) {
            // Mark payment as successful
            LOPAS_Payment::mark_success($payment->id, $parsed);
            
            // Update order status
            LOPAS_Order::update($order->id, array(
                'payment_status' => 'paid',
                'order_status' => 'confirmed'
            ));

            // Update booking status
            if ($order->booking_id) {
                LOPAS_Booking::update($order->booking_id, array(
                    'status' => 'confirmed'
                ));
            }
            
            do_action('lopas_payment_completed', $payment->id, $order->id);
            
            wp_redirect(home_url('/my-bookings/?payment_status=success'));
            exit;
        } else {
            // Mark payment as failed
            LOPAS_Payment::mark_failed($payment->id, $parsed);
            
            wp_redirect(home_url('/payment-failed/?reason=' . $parsed['response_code']));
            exit;
        }
    }
    
    /**
     * Handle VNPay IPN (Instant Payment Notification)
     */
    public function handle_vnpay_ipn() {
        // Get VNPay IPN response
        $response = $_GET;
        
        if (empty($response)) {
            echo json_encode(array('RspCode' => '99', 'Message' => 'Invalid request'));
            exit;
        }
        
        // Verify response
        if (!$this->gateway->verify_response($response)) {
            echo json_encode(array('RspCode' => '97', 'Message' => 'Invalid signature'));
            exit;
        }
        
        // Parse response
        $parsed = $this->gateway->parse_response($response);
        
        // Get order by order code
        $order = LOPAS_Order::get_by_code($parsed['order_code']);
        if (!$order) {
            echo json_encode(array('RspCode' => '01', 'Message' => 'Order not found'));
            exit;
        }
        
        // Get payment
        $payment = LOPAS_Payment::get_by_transaction($parsed['transaction_code']);
        if (!$payment) {
            echo json_encode(array('RspCode' => '02', 'Message' => 'Payment not found'));
            exit;
        }
        
        // Check if payment is successful
        if ($this->gateway->is_payment_success($parsed['response_code'])) {
            // Check if payment is already processed
            if ($payment->status === 'success') {
                echo json_encode(array('RspCode' => '00', 'Message' => 'Confirm Success'));
                exit;
            }
            
            // Mark payment as successful
            LOPAS_Payment::mark_success($payment->id, $parsed);
            
            // Update order status
            LOPAS_Order::update($order->id, array(
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ));
            
            do_action('lopas_payment_completed', $payment->id, $order->id);
            
            echo json_encode(array('RspCode' => '00', 'Message' => 'Confirm Success'));
            exit;
        } else {
            // Mark payment as failed
            LOPAS_Payment::mark_failed($payment->id, $parsed);
            
            echo json_encode(array('RspCode' => '00', 'Message' => 'Confirm Success'));
            exit;
        }
    }
}
