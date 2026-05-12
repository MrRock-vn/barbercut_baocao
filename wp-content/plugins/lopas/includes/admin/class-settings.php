<?php
/**
 * LOPAS Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_lopas_save_settings', array($this, 'save_settings_ajax'));
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'lopas-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'lopas-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // VNPay settings
        register_setting('lopas_vnpay_settings', 'lopas_vnpay_enabled');
        register_setting('lopas_vnpay_settings', 'lopas_vnpay_tmn_code');
        register_setting('lopas_vnpay_settings', 'lopas_vnpay_hash_secret');
        register_setting('lopas_vnpay_settings', 'lopas_vnpay_pay_url');
        
        // Email settings
        register_setting('lopas_email_settings', 'lopas_email_from');
        register_setting('lopas_email_settings', 'lopas_email_from_name');
        register_setting('lopas_email_settings', 'lopas_email_notifications_enabled');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $vnpay_enabled = get_option('lopas_vnpay_enabled', false);
        $vnpay_tmn_code = get_option('lopas_vnpay_tmn_code', '');
        $vnpay_hash_secret = get_option('lopas_vnpay_hash_secret', '');
        $vnpay_pay_url = get_option('lopas_vnpay_pay_url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        
        $email_from = get_option('lopas_email_from', get_option('admin_email'));
        $email_from_name = get_option('lopas_email_from_name', get_option('blogname'));
        $email_notifications_enabled = get_option('lopas_email_notifications_enabled', true);
        
        ?>
        <div class="wrap">
            <h1>LOPAS Settings</h1>
            
            <div class="nav-tab-wrapper">
                <a href="#vnpay" class="nav-tab nav-tab-active">VNPay Payment</a>
                <a href="#email" class="nav-tab">Email Notifications</a>
            </div>
            
            <!-- VNPay Settings Tab -->
            <div id="vnpay" class="tab-content">
                <h2>VNPay Payment Gateway Configuration</h2>
                <form method="post" action="admin-ajax.php" id="vnpay-settings-form">
                    <?php wp_nonce_field('lopas_settings_nonce', 'nonce'); ?>
                    <input type="hidden" name="action" value="lopas_save_settings">
                    <input type="hidden" name="settings_type" value="vnpay">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="vnpay_enabled">Enable VNPay</label>
                            </th>
                            <td>
                                <input type="checkbox" id="vnpay_enabled" name="vnpay_enabled" value="1" <?php checked($vnpay_enabled, 1); ?>>
                                <p class="description">Enable VNPay payment gateway</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="vnpay_tmn_code">Merchant ID (TMN Code)</label>
                            </th>
                            <td>
                                <input type="text" id="vnpay_tmn_code" name="vnpay_tmn_code" value="<?php echo esc_attr($vnpay_tmn_code); ?>" class="regular-text">
                                <p class="description">Your VNPay merchant ID</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="vnpay_hash_secret">Hash Secret</label>
                            </th>
                            <td>
                                <input type="password" id="vnpay_hash_secret" name="vnpay_hash_secret" value="<?php echo esc_attr($vnpay_hash_secret); ?>" class="regular-text">
                                <p class="description">Your VNPay hash secret key</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="vnpay_pay_url">Payment URL</label>
                            </th>
                            <td>
                                <input type="text" id="vnpay_pay_url" name="vnpay_pay_url" value="<?php echo esc_attr($vnpay_pay_url); ?>" class="regular-text">
                                <p class="description">VNPay payment gateway URL (sandbox or production)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">Save VNPay Settings</button>
                    </p>
                </form>
            </div>
            
            <!-- Email Settings Tab -->
            <div id="email" class="tab-content" style="display: none;">
                <h2>Email Notification Settings</h2>
                <form method="post" action="admin-ajax.php" id="email-settings-form">
                    <?php wp_nonce_field('lopas_settings_nonce', 'nonce'); ?>
                    <input type="hidden" name="action" value="lopas_save_settings">
                    <input type="hidden" name="settings_type" value="email">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="email_notifications_enabled">Enable Email Notifications</label>
                            </th>
                            <td>
                                <input type="checkbox" id="email_notifications_enabled" name="email_notifications_enabled" value="1" <?php checked($email_notifications_enabled, 1); ?>>
                                <p class="description">Send email notifications for bookings and payments</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="email_from">From Email Address</label>
                            </th>
                            <td>
                                <input type="email" id="email_from" name="email_from" value="<?php echo esc_attr($email_from); ?>" class="regular-text">
                                <p class="description">Email address to send notifications from</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="email_from_name">From Name</label>
                            </th>
                            <td>
                                <input type="text" id="email_from_name" name="email_from_name" value="<?php echo esc_attr($email_from_name); ?>" class="regular-text">
                                <p class="description">Name to display in email from field</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">Save Email Settings</button>
                    </p>
                </form>
            </div>
            
            <style>
                .nav-tab-wrapper {
                    margin-bottom: 20px;
                    border-bottom: 1px solid #ccc;
                }
                .nav-tab {
                    padding: 10px 15px;
                    margin-right: 5px;
                    background: #f5f5f5;
                    border: 1px solid #ccc;
                    border-bottom: none;
                    cursor: pointer;
                    text-decoration: none;
                    color: #0073aa;
                }
                .nav-tab.nav-tab-active {
                    background: #fff;
                    border-bottom: 1px solid #fff;
                    color: #000;
                }
                .tab-content {
                    background: #fff;
                    padding: 20px;
                    border: 1px solid #ccc;
                    border-top: none;
                }
            </style>
            
            <script>
                document.querySelectorAll('.nav-tab').forEach(tab => {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Hide all tabs
                        document.querySelectorAll('.tab-content').forEach(content => {
                            content.style.display = 'none';
                        });
                        
                        // Remove active class
                        document.querySelectorAll('.nav-tab').forEach(t => {
                            t.classList.remove('nav-tab-active');
                        });
                        
                        // Show selected tab
                        const tabId = this.getAttribute('href').substring(1);
                        document.getElementById(tabId).style.display = 'block';
                        this.classList.add('nav-tab-active');
                    });
                });
                
                // Handle form submissions
                document.getElementById('vnpay-settings-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('VNPay settings saved successfully');
                        } else {
                            alert('Error saving settings: ' + data.data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error);
                    });
                });
                
                document.getElementById('email-settings-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Email settings saved successfully');
                        } else {
                            alert('Error saving settings: ' + data.data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error);
                    });
                });
            </script>
        </div>
        <?php
    }
    
    /**
     * Save settings via AJAX
     */
    public function save_settings_ajax() {
        check_ajax_referer('lopas_settings_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $settings_type = isset($_POST['settings_type']) ? sanitize_text_field($_POST['settings_type']) : '';
        
        if ($settings_type === 'vnpay') {
            update_option('lopas_vnpay_enabled', isset($_POST['vnpay_enabled']) ? 1 : 0);
            update_option('lopas_vnpay_tmn_code', sanitize_text_field($_POST['vnpay_tmn_code'] ?? ''));
            update_option('lopas_vnpay_hash_secret', sanitize_text_field($_POST['vnpay_hash_secret'] ?? ''));
            update_option('lopas_vnpay_pay_url', esc_url($_POST['vnpay_pay_url'] ?? ''));
            
            wp_send_json_success(array('message' => 'VNPay settings saved'));
        } elseif ($settings_type === 'email') {
            update_option('lopas_email_notifications_enabled', isset($_POST['email_notifications_enabled']) ? 1 : 0);
            update_option('lopas_email_from', sanitize_email($_POST['email_from'] ?? ''));
            update_option('lopas_email_from_name', sanitize_text_field($_POST['email_from_name'] ?? ''));
            
            wp_send_json_success(array('message' => 'Email settings saved'));
        }
        
        wp_send_json_error(array('message' => 'Invalid settings type'));
    }
}
