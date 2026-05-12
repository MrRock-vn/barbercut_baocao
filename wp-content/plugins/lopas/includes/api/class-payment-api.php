<?php
/**
 * LOPAS Payment API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Payment_API extends LOPAS_API_Base {
    
    public function register_routes() {
        // Get user payments
        register_rest_route($this->namespace, '/payments', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_payments'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Get single payment
        register_rest_route($this->namespace, '/payments/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_payment'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Create payment
        register_rest_route($this->namespace, '/payments', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_payment'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // Get payment methods
        register_rest_route($this->namespace, '/payment-methods', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_payment_methods'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Get user payments
     */
    public function get_payments($request) {
        $user = wp_get_current_user();
        $page = $request->get_param('page') ?? 1;
        $per_page = $request->get_param('per_page') ?? 20;
        $status = $request->get_param('status');
        
        global $wpdb;
        $payments_table = LOPAS_Database::get_table('payments');
        $orders_table = LOPAS_Database::get_table('orders');
        
        $query = $wpdb->prepare(
            "SELECT p.* FROM {$payments_table} p
            JOIN {$orders_table} o ON p.order_id = o.id
            WHERE o.user_id = %d",
            $user->ID
        );
        
        if ($status) {
            $query .= $wpdb->prepare(" AND p.status = %s", $status);
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        $payments = $wpdb->get_results($query);
        
        $paginated = $this->paginate_results($payments, $page, $per_page);
        
        return $this->success_response($paginated);
    }
    
    /**
     * Get single payment
     */
    public function get_payment($request) {
        $user = wp_get_current_user();
        $payment_id = $request->get_param('id');
        
        $payment = LOPAS_Payment::get($payment_id);
        
        if (!$payment) {
            return $this->error_response('Payment not found', 'not_found', 404);
        }
        
        // Check ownership
        $order = LOPAS_Order::get($payment->order_id);
        if ($order->user_id != $user->ID && !current_user_can('manage_options')) {
            return $this->error_response('Unauthorized', 'unauthorized', 403);
        }
        
        return $this->success_response($payment);
    }
    
    /**
     * Create payment
     */
    public function create_payment($request) {
        $user = wp_get_current_user();
        $params = $request->get_json_params();
        
        $order_id = $params['order_id'] ?? 0;
        $payment_method = $params['payment_method'] ?? 'cod';
        
        $order = LOPAS_Order::get($order_id);
        
        if (!$order) {
            return $this->error_response('Order not found', 'not_found', 404);
        }
        
        // Check ownership
        if ($order->user_id != $user->ID && !current_user_can('manage_options')) {
            return $this->error_response('Unauthorized', 'unauthorized', 403);
        }
        
        // Create payment
        $payment_id = LOPAS_Payment::create(array(
            'order_id' => $order_id,
            'amount' => $order->total_amount,
            'payment_method' => $payment_method,
            'status' => 'pending'
        ));
        
        if (!$payment_id) {
            return $this->error_response('Failed to create payment', 'creation_failed', 400);
        }
        
        $payment = LOPAS_Payment::get($payment_id);
        
        // If VNPay, generate payment URL
        if ($payment_method === 'vnpay') {
            require_once LOPAS_PATH . 'includes/vnpay/class-vnpay-gateway.php';
            $gateway = new LOPAS_VNPay_Gateway();
            
            if ($gateway->is_enabled()) {
                $payment_url = $gateway->create_payment_url(
                    $order_id,
                    $order->total_amount,
                    $order->order_code
                );
                
                return $this->success_response(array(
                    'payment' => $payment,
                    'payment_url' => $payment_url
                ), 201);
            }
        }
        
        return $this->success_response($payment, 201);
    }
    
    /**
     * Get payment methods
     */
    public function get_payment_methods($request) {
        $methods = array(
            array(
                'id' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay when the service is completed',
                'enabled' => true
            )
        );
        
        // Add VNPay if enabled
        if (get_option('lopas_vnpay_enabled', false)) {
            $methods[] = array(
                'id' => 'vnpay',
                'name' => 'VNPay',
                'description' => 'Pay online using VNPay gateway',
                'enabled' => true
            );
        }
        
        return $this->success_response($methods);
    }
    
    /**
     * Check user permission
     */
    public function check_user_permission() {
        return is_user_logged_in();
    }
}
