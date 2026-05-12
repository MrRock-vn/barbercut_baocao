<?php
/**
 * Payment Testing Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Payment_Test_Page {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_test_page'));
        add_action('wp_ajax_lopas_test_create_order', array($this, 'ajax_create_test_order'));
        add_action('wp_ajax_lopas_test_payment_success', array($this, 'ajax_test_payment_success'));
        add_action('wp_ajax_lopas_test_payment_failure', array($this, 'ajax_test_payment_failure'));
    }
    
    /**
     * Add test page to admin menu
     */
    public function add_test_page() {
        add_submenu_page(
            'lopas-dashboard',
            'Payment Testing',
            'Payment Testing',
            'manage_options',
            'lopas-payment-test',
            array($this, 'render_test_page')
        );
    }
    
    /**
     * Render test page
     */
    public function render_test_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        require_once LOPAS_PATH . 'includes/vnpay/class-payment-test.php';
        $test_data = LOPAS_Payment_Test::get_test_data();
        
        ?>
        <div class="wrap">
            <h1>Payment Testing</h1>
            
            <div class="notice notice-info">
                <p><strong>Note:</strong> This page is for testing payment flow. Use it to simulate payments and verify the system works correctly.</p>
            </div>
            
            <div class="payment-test-container">
                <h2>Create Test Order</h2>
                <p>Create a test order to test the payment flow.</p>
                <button class="button button-primary" id="btn-create-test-order">Create Test Order</button>
                <div id="test-order-result"></div>
                
                <hr>
                
                <h2>Test Payment Success</h2>
                <p>Simulate a successful payment for an order.</p>
                <div class="form-group">
                    <label for="order-id-success">Order ID:</label>
                    <input type="number" id="order-id-success" placeholder="Enter order ID">
                </div>
                <button class="button button-primary" id="btn-test-success">Simulate Success</button>
                <div id="test-success-result"></div>
                
                <hr>
                
                <h2>Test Payment Failure</h2>
                <p>Simulate a failed payment for an order.</p>
                <div class="form-group">
                    <label for="order-id-failure">Order ID:</label>
                    <input type="number" id="order-id-failure" placeholder="Enter order ID">
                </div>
                <button class="button button-primary" id="btn-test-failure">Simulate Failure</button>
                <div id="test-failure-result"></div>
                
                <hr>
                
                <h2>Recent Data</h2>
                
                <h3>Recent Orders</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order Code</th>
                            <th>User ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($test_data['orders'] as $order): ?>
                        <tr>
                            <td><?php echo esc_html($order->id); ?></td>
                            <td><?php echo esc_html($order->order_code); ?></td>
                            <td><?php echo esc_html($order->user_id); ?></td>
                            <td><?php echo number_format($order->total_amount, 0, ',', '.'); ?> VND</td>
                            <td><?php echo esc_html(ucfirst($order->status)); ?></td>
                            <td><?php echo esc_html(ucfirst($order->payment_status)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h3>Recent Payments</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Transaction Code</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($test_data['payments'] as $payment): ?>
                        <tr>
                            <td><?php echo esc_html($payment->id); ?></td>
                            <td><?php echo esc_html($payment->transaction_code); ?></td>
                            <td><?php echo esc_html($payment->order_id); ?></td>
                            <td><?php echo number_format($payment->amount, 0, ',', '.'); ?> VND</td>
                            <td><?php echo esc_html(strtoupper($payment->payment_method)); ?></td>
                            <td><?php echo esc_html(ucfirst($payment->status)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <style>
                .payment-test-container {
                    background: #fff;
                    padding: 20px;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                
                .form-group {
                    margin: 15px 0;
                }
                
                .form-group label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: bold;
                }
                
                .form-group input {
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                    width: 200px;
                }
                
                #test-order-result,
                #test-success-result,
                #test-failure-result {
                    margin-top: 15px;
                    padding: 15px;
                    border-radius: 3px;
                    display: none;
                }
                
                .result-success {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                    display: block !important;
                }
                
                .result-error {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                    display: block !important;
                }
                
                hr {
                    margin: 30px 0;
                    border: none;
                    border-top: 1px solid #ddd;
                }
            </style>
            
            <script>
                document.getElementById('btn-create-test-order').addEventListener('click', function() {
                    const resultDiv = document.getElementById('test-order-result');
                    resultDiv.innerHTML = 'Creating test order...';
                    resultDiv.className = '';
                    resultDiv.style.display = 'block';
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'action=lopas_test_create_order&nonce=<?php echo wp_create_nonce('lopas_test_nonce'); ?>'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resultDiv.innerHTML = `
                                <strong>✓ Test Order Created Successfully</strong><br>
                                Booking ID: ${data.data.booking_id}<br>
                                Order ID: ${data.data.order_id}<br>
                                Salon: ${data.data.salon.name}<br>
                                Service: ${data.data.service.name}<br>
                                Amount: ${data.data.service.price.toLocaleString('vi-VN')} VND
                            `;
                            resultDiv.className = 'result-success';
                            
                            // Reload page after 2 seconds
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            resultDiv.innerHTML = `<strong>✗ Error:</strong> ${data.data.error}`;
                            resultDiv.className = 'result-error';
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = `<strong>✗ Error:</strong> ${error}`;
                        resultDiv.className = 'result-error';
                    });
                });
                
                document.getElementById('btn-test-success').addEventListener('click', function() {
                    const orderId = document.getElementById('order-id-success').value;
                    if (!orderId) {
                        alert('Please enter an Order ID');
                        return;
                    }
                    
                    const resultDiv = document.getElementById('test-success-result');
                    resultDiv.innerHTML = 'Processing...';
                    resultDiv.className = '';
                    resultDiv.style.display = 'block';
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=lopas_test_payment_success&order_id=${orderId}&nonce=<?php echo wp_create_nonce('lopas_test_nonce'); ?>`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resultDiv.innerHTML = `
                                <strong>✓ Payment Success Simulated</strong><br>
                                Payment ID: ${data.data.payment_id}<br>
                                Message: ${data.data.message}
                            `;
                            resultDiv.className = 'result-success';
                            
                            // Reload page after 2 seconds
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            resultDiv.innerHTML = `<strong>✗ Error:</strong> ${data.data.error}`;
                            resultDiv.className = 'result-error';
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = `<strong>✗ Error:</strong> ${error}`;
                        resultDiv.className = 'result-error';
                    });
                });
                
                document.getElementById('btn-test-failure').addEventListener('click', function() {
                    const orderId = document.getElementById('order-id-failure').value;
                    if (!orderId) {
                        alert('Please enter an Order ID');
                        return;
                    }
                    
                    const resultDiv = document.getElementById('test-failure-result');
                    resultDiv.innerHTML = 'Processing...';
                    resultDiv.className = '';
                    resultDiv.style.display = 'block';
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=lopas_test_payment_failure&order_id=${orderId}&nonce=<?php echo wp_create_nonce('lopas_test_nonce'); ?>`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resultDiv.innerHTML = `
                                <strong>✓ Payment Failure Simulated</strong><br>
                                Payment ID: ${data.data.payment_id}<br>
                                Message: ${data.data.message}
                            `;
                            resultDiv.className = 'result-success';
                            
                            // Reload page after 2 seconds
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            resultDiv.innerHTML = `<strong>✗ Error:</strong> ${data.data.error}`;
                            resultDiv.className = 'result-error';
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = `<strong>✗ Error:</strong> ${error}`;
                        resultDiv.className = 'result-error';
                    });
                });
            </script>
        </div>
        <?php
    }
    
    /**
     * AJAX: Create test order
     */
    public function ajax_create_test_order() {
        check_ajax_referer('lopas_test_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('error' => 'Unauthorized'));
        }
        
        require_once LOPAS_PATH . 'includes/vnpay/class-payment-test.php';
        $result = LOPAS_Payment_Test::create_test_order();
        
        if (isset($result['error'])) {
            wp_send_json_error($result);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Test payment success
     */
    public function ajax_test_payment_success() {
        check_ajax_referer('lopas_test_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('error' => 'Unauthorized'));
        }
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        
        require_once LOPAS_PATH . 'includes/vnpay/class-payment-test.php';
        $result = LOPAS_Payment_Test::simulate_payment_success($order_id);
        
        if (isset($result['error'])) {
            wp_send_json_error($result);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Test payment failure
     */
    public function ajax_test_payment_failure() {
        check_ajax_referer('lopas_test_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('error' => 'Unauthorized'));
        }
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        
        require_once LOPAS_PATH . 'includes/vnpay/class-payment-test.php';
        $result = LOPAS_Payment_Test::simulate_payment_failure($order_id);
        
        if (isset($result['error'])) {
            wp_send_json_error($result);
        }
        
        wp_send_json_success($result);
    }
}
