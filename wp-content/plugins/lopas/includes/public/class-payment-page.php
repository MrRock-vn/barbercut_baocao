<?php
/**
 * Payment Page Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Payment_Page {
    
    public function __construct() {
        add_shortcode('lopas_payment_form', array($this, 'render_payment_form'));
        add_shortcode('lopas_payment_success', array($this, 'render_payment_success'));
        add_shortcode('lopas_payment_failed', array($this, 'render_payment_failed'));
    }
    
    /**
     * Render payment form
     */
    public function render_payment_form($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to make a payment.</p>';
        }
        
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        
        if (empty($order_id)) {
            return '<p>Order not found.</p>';
        }
        
        $order = LOPAS_Order::get($order_id);
        if (!$order) {
            return '<p>Order not found.</p>';
        }
        
        // Check if user owns this order
        if ($order->user_id != get_current_user_id() && !current_user_can('manage_options')) {
            return '<p>Unauthorized.</p>';
        }
        
        // Check if order is already paid
        if ($order->payment_status === 'paid') {
            return '<p>This order has already been paid.</p>';
        }
        
        $output = LOPAS_Public::get_global_header();
        ob_start();
        ?>
        <div class="lopas-payment-form">
            <h2>Payment for Order <?php echo esc_html($order->order_code); ?></h2>
            
            <div class="payment-details">
                <table>
                    <tr>
                        <th>Order Code:</th>
                        <td><?php echo esc_html($order->order_code); ?></td>
                    </tr>
                    <tr>
                        <th>Total Amount:</th>
                        <td><?php echo number_format($order->total_amount, 0, ',', '.'); ?> VND</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><?php echo esc_html(ucfirst($order->status)); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="payment-methods">
                <h3>Select Payment Method</h3>
                
                <form id="payment-form" method="post">
                    <?php wp_nonce_field('lopas_nonce', 'nonce'); ?>
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                    
                    <div class="payment-method-option">
                        <input type="radio" id="payment_cod" name="payment_method" value="cod" checked>
                        <label for="payment_cod">
                            <strong>Cash on Delivery (COD)</strong>
                            <p>Pay when the service is completed</p>
                        </label>
                    </div>
                    
                    <?php if ($this->is_vnpay_enabled()): ?>
                    <div class="payment-method-option">
                        <input type="radio" id="payment_vnpay" name="payment_method" value="vnpay">
                        <label for="payment_vnpay">
                            <strong>VNPay</strong>
                            <p>Pay online using VNPay gateway</p>
                        </label>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="button button-primary">Proceed to Payment</button>
                </form>
            </div>
            
            <style>
                .lopas-payment-form {
                    max-width: 600px;
                    margin: 20px 0;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                
                .payment-details {
                    margin: 20px 0;
                    padding: 15px;
                    background: #f9f9f9;
                    border-radius: 5px;
                }
                
                .payment-details table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                .payment-details th,
                .payment-details td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                
                .payment-details th {
                    font-weight: bold;
                    width: 150px;
                }
                
                .payment-methods {
                    margin: 20px 0;
                }
                
                .payment-method-option {
                    margin: 15px 0;
                    padding: 15px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    cursor: pointer;
                }
                
                .payment-method-option input[type="radio"] {
                    margin-right: 10px;
                }
                
                .payment-method-option label {
                    cursor: pointer;
                    display: inline-block;
                    width: calc(100% - 30px);
                }
                
                .payment-method-option label strong {
                    display: block;
                    margin-bottom: 5px;
                }
                
                .payment-method-option label p {
                    margin: 0;
                    color: #666;
                    font-size: 14px;
                }
                
                .button {
                    margin-top: 20px;
                }
            </style>
            
            <script>
                document.getElementById('payment-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    formData.append('action', 'lopas_create_payment');
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.data.payment_url) {
                                // Redirect to VNPay
                                window.location.href = data.data.payment_url;
                            } else {
                                // Redirect to confirmation page
                                window.location.href = data.data.redirect;
                            }
                        } else {
                            alert('Error: ' + data.data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error);
                    });
                });
            </script>
        </div>
        <?php
        $output .= ob_get_clean();
        $output .= LOPAS_Public::get_global_footer();
        return $output;
    }
    
    /**
     * Render payment success page
     */
    public function render_payment_success($atts) {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
        
        if (empty($order_id) || empty($payment_id)) {
            return '<p>Invalid payment information.</p>';
        }
        
        $order = LOPAS_Order::get($order_id);
        $payment = LOPAS_Payment::get($payment_id);
        
        if (!$order || !$payment) {
            return '<p>Order or payment not found.</p>';
        }
        
        $output = LOPAS_Public::get_global_header();
        ob_start();
        ?>
        <div class="lopas-payment-success">
            <div class="success-message">
                <h2>✓ Payment Successful</h2>
                <p>Your payment has been processed successfully.</p>
            </div>
            
            <div class="payment-details">
                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <th>Order Code:</th>
                        <td><?php echo esc_html($order->order_code); ?></td>
                    </tr>
                    <tr>
                        <th>Transaction Code:</th>
                        <td><?php echo esc_html($payment->transaction_code); ?></td>
                    </tr>
                    <tr>
                        <th>Amount:</th>
                        <td><?php echo number_format($payment->amount, 0, ',', '.'); ?> VND</td>
                    </tr>
                    <tr>
                        <th>Payment Method:</th>
                        <td><?php echo esc_html(strtoupper($payment->payment_method)); ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><span class="status-badge status-success"><?php echo esc_html(ucfirst($payment->status)); ?></span></td>
                    </tr>
                </table>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <p>Your booking has been confirmed. You will receive a confirmation email shortly.</p>
                <a href="<?php echo home_url('/my-bookings/'); ?>" class="button button-primary">View My Bookings</a>
            </div>
            
            <style>
                .lopas-payment-success {
                    max-width: 600px;
                    margin: 20px 0;
                    padding: 20px;
                }
                
                .success-message {
                    padding: 20px;
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    border-radius: 5px;
                    color: #155724;
                    margin-bottom: 20px;
                }
                
                .success-message h2 {
                    margin-top: 0;
                    color: #155724;
                }
                
                .payment-details {
                    margin: 20px 0;
                    padding: 15px;
                    background: #f9f9f9;
                    border-radius: 5px;
                }
                
                .payment-details table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                .payment-details th,
                .payment-details td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                
                .payment-details th {
                    font-weight: bold;
                    width: 150px;
                }
                
                .status-badge {
                    padding: 5px 10px;
                    border-radius: 3px;
                    font-weight: bold;
                }
                
                .status-success {
                    background: #d4edda;
                    color: #155724;
                }
                
                .next-steps {
                    margin-top: 20px;
                    padding: 15px;
                    background: #e7f3ff;
                    border-radius: 5px;
                }
            </style>
        </div>
        <?php
        $output .= ob_get_clean();
        $output .= LOPAS_Public::get_global_footer();
        return $output;
    }
    
    /**
     * Render payment failed page
     */
    public function render_payment_failed($atts) {
        $reason = isset($_GET['reason']) ? sanitize_text_field($_GET['reason']) : 'unknown';
        
        $error_messages = array(
            'invalid_signature' => 'Invalid payment signature. Please try again.',
            'order_not_found' => 'Order not found.',
            'payment_not_found' => 'Payment not found.',
            '01' => 'Transaction rejected.',
            '02' => 'Transaction failed.',
            '99' => 'Unknown error occurred.',
        );
        
        $error_message = isset($error_messages[$reason]) ? $error_messages[$reason] : 'Payment failed. Please try again.';
        
        $output = LOPAS_Public::get_global_header();
        ob_start();
        ?>
        <div class="lopas-payment-failed">
            <div class="error-message">
                <h2>✗ Payment Failed</h2>
                <p><?php echo esc_html($error_message); ?></p>
            </div>
            
            <div class="next-steps">
                <h3>What to do?</h3>
                <ul>
                    <li>Check your payment details and try again</li>
                    <li>Contact our support team if the problem persists</li>
                    <li>Try a different payment method</li>
                </ul>
                <a href="<?php echo home_url('/my-bookings/'); ?>" class="button button-primary">Back to My Bookings</a>
            </div>
            
            <style>
                .lopas-payment-failed {
                    max-width: 600px;
                    margin: 20px 0;
                    padding: 20px;
                }
                
                .error-message {
                    padding: 20px;
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    border-radius: 5px;
                    color: #721c24;
                    margin-bottom: 20px;
                }
                
                .error-message h2 {
                    margin-top: 0;
                    color: #721c24;
                }
                
                .next-steps {
                    margin-top: 20px;
                    padding: 15px;
                    background: #fff3cd;
                    border-radius: 5px;
                }
                
                .next-steps ul {
                    margin: 10px 0;
                    padding-left: 20px;
                }
                
                .next-steps li {
                    margin: 5px 0;
                }
            </style>
        </div>
        <?php
        $output .= ob_get_clean();
        $output .= LOPAS_Public::get_global_footer();
        return $output;
    }
    
    /**
     * Check if VNPay is enabled
     */
    private function is_vnpay_enabled() {
        return get_option('lopas_vnpay_enabled', false) && 
               !empty(get_option('lopas_vnpay_tmn_code', '')) &&
               !empty(get_option('lopas_vnpay_hash_secret', ''));
    }
}
