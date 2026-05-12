<?php
/**
 * LOPAS Admin Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Admin {
    
    public function __construct() {
        // Load admin classes
        require_once LOPAS_PATH . 'includes/admin/class-salon-admin.php';
        require_once LOPAS_PATH . 'includes/admin/class-service-admin.php';
        require_once LOPAS_PATH . 'includes/admin/class-booking-admin.php';
        require_once LOPAS_PATH . 'includes/admin/class-order-admin.php';
        require_once LOPAS_PATH . 'includes/admin/class-payment-admin.php';
        require_once LOPAS_PATH . 'includes/admin/class-settings.php';
        require_once LOPAS_PATH . 'includes/admin/class-payment-test-page.php';
        require_once LOPAS_PATH . 'includes/admin/class-reporting-admin.php';
        require_once LOPAS_PATH . 'includes/admin/class-voucher-admin.php';
        
        // Initialize admin classes
        new LOPAS_Salon_Admin();
        new LOPAS_Service_Admin();
        new LOPAS_Booking_Admin();
        new LOPAS_Order_Admin();
        new LOPAS_Payment_Admin();
        new LOPAS_Settings();
        new LOPAS_Payment_Test_Page();
        new LOPAS_Reporting_Admin();
        new LOPAS_Voucher_Admin();
        
        // Register admin menu
        add_action('admin_menu', array($this, 'register_menu'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Register admin pages
        add_action('admin_init', array($this, 'register_pages'));
    }
    
    /**
     * Register admin menu
     */
    public function register_menu() {
        // Main menu
        add_menu_page(
            __('LOPAS Booking', 'lopas'),
            __('LOPAS', 'lopas'),
            'manage_options',
            'lopas',
            array($this, 'render_dashboard'),
            'dashicons-calendar',
            25
        );
        
        // Dashboard submenu
        add_submenu_page(
            'lopas',
            __('Dashboard', 'lopas'),
            __('Dashboard', 'lopas'),
            'manage_options',
            'lopas',
            array($this, 'render_dashboard')
        );
        
        // Salons submenu
        add_submenu_page(
            'lopas',
            __('Salons', 'lopas'),
            __('Salons', 'lopas'),
            'manage_options',
            'lopas-salons',
            array($this, 'render_salons')
        );
        
        // Services submenu
        add_submenu_page(
            'lopas',
            __('Services', 'lopas'),
            __('Services', 'lopas'),
            'manage_options',
            'lopas-services',
            array($this, 'render_services')
        );
        
        // Bookings submenu
        add_submenu_page(
            'lopas',
            __('Bookings', 'lopas'),
            __('Bookings', 'lopas'),
            'manage_options',
            'lopas-bookings',
            array($this, 'render_bookings')
        );
        
        // Orders submenu
        add_submenu_page(
            'lopas',
            __('Orders', 'lopas'),
            __('Orders', 'lopas'),
            'manage_options',
            'lopas-orders',
            array($this, 'render_orders')
        );
        
        // Payments submenu
        add_submenu_page(
            'lopas',
            __('Payments', 'lopas'),
            __('Payments', 'lopas'),
            'manage_options',
            'lopas-payments',
            array($this, 'render_payments')
        );
        
        // Settings submenu
        add_submenu_page(
            'lopas',
            __('Settings', 'lopas'),
            __('Settings', 'lopas'),
            'manage_options',
            'lopas-settings',
            array($this, 'render_settings')
        );
    }
    
    /**
     * Register admin pages
     */
    public function register_pages() {
        // Register settings sections and fields
        register_setting('lopas_settings', 'lopas_vnpay_merchant_id');
        register_setting('lopas_settings', 'lopas_vnpay_hash_secret');
        register_setting('lopas_settings', 'lopas_vnpay_url');
        
        // AJAX handlers for loading forms
        add_action('wp_ajax_lopas_load_salon_form', array($this, 'ajax_load_salon_form'));
        add_action('wp_ajax_lopas_load_service_form', array($this, 'ajax_load_service_form'));
        add_action('wp_ajax_lopas_load_booking_details', array($this, 'ajax_load_booking_details'));
        add_action('wp_ajax_lopas_load_order_details', array($this, 'ajax_load_order_details'));
        add_action('wp_ajax_lopas_load_payment_details', array($this, 'ajax_load_payment_details'));
        add_action('wp_ajax_lopas_confirm_booking', array($this, 'ajax_confirm_booking'));
    }
    
    /**
     * AJAX: Load salon form
     */
    public function ajax_load_salon_form() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $salon_id = isset($_POST['salon_id']) ? intval($_POST['salon_id']) : null;
        
        ob_start();
        LOPAS_Salon_Admin::render_form($salon_id);
        $form = ob_get_clean();
        
        wp_send_json_success($form);
    }
    
    /**
     * AJAX: Load service form
     */
    public function ajax_load_service_form() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
        
        ob_start();
        LOPAS_Service_Admin::render_form($service_id);
        $form = ob_get_clean();
        
        wp_send_json_success($form);
    }
    
    /**
     * AJAX: Load booking details
     */
    public function ajax_load_booking_details() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $booking_id = intval($_POST['booking_id']);
        
        ob_start();
        LOPAS_Booking_Admin::render_details($booking_id);
        $details = ob_get_clean();
        
        wp_send_json_success($details);
    }
    
    /**
     * AJAX: Load order details
     */
    public function ajax_load_order_details() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $order_id = intval($_POST['order_id']);
        
        ob_start();
        LOPAS_Order_Admin::render_details($order_id);
        $details = ob_get_clean();
        
        wp_send_json_success($details);
    }
    
    /**
     * AJAX: Load payment details
     */
    public function ajax_load_payment_details() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $payment_id = intval($_POST['payment_id']);
        
        ob_start();
        LOPAS_Payment_Admin::render_details($payment_id);
        $details = ob_get_clean();
        
        wp_send_json_success($details);
    }
    
    /**
     * AJAX: Confirm booking
     */
    public function ajax_confirm_booking() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $booking_id = intval($_POST['booking_id']);
        
        $result = LOPAS_Booking::update($booking_id, array(
            'status' => 'confirmed'
        ));
        
        if ($result) {
            wp_send_json_success(__('Booking confirmed successfully', 'lopas'));
        } else {
            wp_send_json_error(__('Failed to confirm booking', 'lopas'));
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        // Only load on LOPAS pages
        if (strpos($hook, 'lopas') === false) {
            return;
        }
        
        // Enqueue admin CSS
        wp_enqueue_style(
            'lopas-admin',
            LOPAS_URL . 'assets/css/admin.css',
            array(),
            LOPAS_VERSION
        );
        
        // Enqueue admin JS
        wp_enqueue_script(
            'lopas-admin',
            LOPAS_URL . 'assets/js/admin.js',
            array('jquery'),
            LOPAS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('lopas-admin', 'lopasAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lopas_admin_nonce')
        ));
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        ?>
        <div class="wrap">
            <h1><?php _e('LOPAS Dashboard', 'lopas'); ?></h1>
            
            <div class="lopas-dashboard">
                <div class="lopas-stats">
                    <div class="stat-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                        <i class="dashicons dashicons-store" style="font-size: 30px; margin-bottom: 10px;"></i>
                        <h3><?php _e('Total Salons', 'lopas'); ?></h3>
                        <p class="stat-number"><?php echo LOPAS_Salon::count(); ?></p>
                    </div>
                    
                    <div class="stat-box" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%); color: #fff;">
                        <i class="dashicons dashicons-admin-tools" style="font-size: 30px; margin-bottom: 10px;"></i>
                        <h3><?php _e('Total Services', 'lopas'); ?></h3>
                        <p class="stat-number"><?php echo $this->count_all_services(); ?></p>
                    </div>
                    
                    <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff;">
                        <i class="dashicons dashicons-calendar-alt" style="font-size: 30px; margin-bottom: 10px;"></i>
                        <h3><?php _e('Total Bookings', 'lopas'); ?></h3>
                        <p class="stat-number"><?php echo $this->count_all_bookings(); ?></p>
                    </div>
                    
                    <div class="stat-box" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #fff;">
                        <i class="dashicons dashicons-money-alt" style="font-size: 30px; margin-bottom: 10px;"></i>
                        <h3><?php _e('Total Orders', 'lopas'); ?></h3>
                        <p class="stat-number"><?php echo $this->count_all_orders(); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render salons page
     */
    public function render_salons() {
        ?>
        <div class="wrap">
            <h1><?php _e('Salons', 'lopas'); ?></h1>
            
            <button class="btn btn-primary" onclick="lopasOpenSalonForm()">
                <?php _e('Add New Salon', 'lopas'); ?>
            </button>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'lopas'); ?></th>
                        <th><?php _e('Name', 'lopas'); ?></th>
                        <th><?php _e('Owner', 'lopas'); ?></th>
                        <th><?php _e('Phone', 'lopas'); ?></th>
                        <th><?php _e('Status', 'lopas'); ?></th>
                        <th><?php _e('Actions', 'lopas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $salons = LOPAS_Salon::get_all(array('limit' => -1));
                    if (empty($salons)) {
                        echo '<tr><td colspan="6">' . __('No salons found.', 'lopas') . '</td></tr>';
                    } else {
                        foreach ($salons as $salon) {
                            $user = get_userdata($salon->user_id);
                            echo '<tr>';
                            echo '<td>' . $salon->id . '</td>';
                            echo '<td>' . esc_html($salon->name) . '</td>';
                            echo '<td>' . ($user ? esc_html($user->display_name) : '-') . '</td>';
                            echo '<td>' . esc_html($salon->phone) . '</td>';
                            echo '<td><span class="status-' . esc_attr($salon->status) . '">' . esc_html($salon->status) . '</span></td>';
                            echo '<td>';
                            echo '<a href="#" onclick="lopasEditSalon(' . $salon->id . '); return false;" class="btn btn-sm">' . __('Edit', 'lopas') . '</a> | ';
                            echo '<a href="#" onclick="lopasDeleteSalon(' . $salon->id . '); return false;" class="btn btn-sm btn-danger">' . __('Delete', 'lopas') . '</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal for salon form -->
        <div id="lopas-salon-modal" class="lopas-modal" style="display:none;">
            <div class="lopas-modal-overlay" onclick="lopasCloseModal()"></div>
            <div class="lopas-modal-dialog">
                <div class="lopas-modal-header">
                    <button type="button" class="lopas-modal-close" onclick="lopasCloseModal()">×</button>
                </div>
                <div class="lopas-modal-body" id="lopas-salon-form-container">
                    <!-- Form will be loaded here -->
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render services page
     */
    public function render_services() {
        ?>
        <div class="wrap">
            <h1><?php _e('Services', 'lopas'); ?></h1>
            
            <button class="btn btn-primary" onclick="lopasOpenServiceForm()">
                <?php _e('Add New Service', 'lopas'); ?>
            </button>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'lopas'); ?></th>
                        <th><?php _e('Name', 'lopas'); ?></th>
                        <th><?php _e('Salon', 'lopas'); ?></th>
                        <th><?php _e('Category', 'lopas'); ?></th>
                        <th><?php _e('Price', 'lopas'); ?></th>
                        <th><?php _e('Duration', 'lopas'); ?></th>
                        <th><?php _e('Status', 'lopas'); ?></th>
                        <th><?php _e('Actions', 'lopas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $wpdb;
                    $table = LOPAS_Database::get_table('services');
                    $services = $wpdb->get_results("SELECT * FROM {$table} LIMIT 50");
                    if (empty($services)) {
                        echo '<tr><td colspan="8">' . __('No services found.', 'lopas') . '</td></tr>';
                    } else {
                        foreach ($services as $service) {
                            $salon = LOPAS_Salon::get($service->salon_id);
                            echo '<tr>';
                            echo '<td>' . $service->id . '</td>';
                            echo '<td>' . esc_html($service->name) . '</td>';
                            echo '<td>' . ($salon ? esc_html($salon->name) : '-') . '</td>';
                            echo '<td>' . esc_html($service->category) . '</td>';
                            echo '<td>' . lopas_format_currency($service->price) . '</td>';
                            echo '<td>' . $service->duration . ' ' . __('min', 'lopas') . '</td>';
                            echo '<td><span class="status-' . esc_attr($service->status) . '">' . esc_html($service->status) . '</span></td>';
                            echo '<td>';
                            echo '<a href="#" onclick="lopasEditService(' . $service->id . '); return false;" class="btn btn-sm">' . __('Edit', 'lopas') . '</a> | ';
                            echo '<a href="#" onclick="lopasDeleteService(' . $service->id . '); return false;" class="btn btn-sm btn-danger">' . __('Delete', 'lopas') . '</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal for service form -->
        <div id="lopas-service-modal" class="lopas-modal" style="display:none;">
            <div class="lopas-modal-overlay" onclick="lopasCloseModal()"></div>
            <div class="lopas-modal-dialog">
                <div class="lopas-modal-header">
                    <button type="button" class="lopas-modal-close" onclick="lopasCloseModal()">×</button>
                </div>
                <div class="lopas-modal-body" id="lopas-service-form-container">
                    <!-- Form will be loaded here -->
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render bookings page
     */
    public function render_bookings() {
        ?>
        <div class="wrap">
            <h1><?php _e('Bookings', 'lopas'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Code', 'lopas'); ?></th>
                        <th><?php _e('Customer', 'lopas'); ?></th>
                        <th><?php _e('Salon', 'lopas'); ?></th>
                        <th><?php _e('Service', 'lopas'); ?></th>
                        <th><?php _e('Date', 'lopas'); ?></th>
                        <th><?php _e('Time', 'lopas'); ?></th>
                        <th><?php _e('Status', 'lopas'); ?></th>
                        <th><?php _e('Actions', 'lopas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $wpdb;
                    $table = LOPAS_Database::get_table('bookings');
                    $bookings = $wpdb->get_results("SELECT * FROM {$table} ORDER BY booking_date DESC LIMIT 50");
                    if (empty($bookings)) {
                        echo '<tr><td colspan="8">' . __('No bookings found.', 'lopas') . '</td></tr>';
                    } else {
                        foreach ($bookings as $booking) {
                            $user = get_userdata($booking->user_id);
                            $salon = LOPAS_Salon::get($booking->salon_id);
                            $service = LOPAS_Service::get($booking->service_id);
                            echo '<tr>';
                            echo '<td>' . esc_html($booking->booking_code) . '</td>';
                            echo '<td>' . ($user ? esc_html($user->display_name) : '-') . '</td>';
                            echo '<td>' . ($salon ? esc_html($salon->name) : '-') . '</td>';
                            echo '<td>' . ($service ? esc_html($service->name) : '-') . '</td>';
                            echo '<td>' . esc_html($booking->booking_date) . '</td>';
                            echo '<td>' . esc_html($booking->booking_time) . '</td>';
                            echo '<td><span class="status-' . esc_attr($booking->status) . '">' . esc_html($booking->status) . '</span></td>';
                            echo '<td>';
                            echo '<a href="#" onclick="lopasViewBooking(' . $booking->id . '); return false;" class="btn btn-sm btn-info">' . __('Xem', 'lopas') . '</a> ';
                            if ($booking->status === 'pending') {
                                echo '<a href="#" onclick="lopasConfirmBooking(' . $booking->id . '); return false;" class="btn btn-sm btn-success">' . __('Xác nhận', 'lopas') . '</a>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal for booking details -->
        <div id="lopas-booking-modal" class="lopas-modal" style="display:none;">
            <div class="lopas-modal-overlay" onclick="lopasCloseModal()"></div>
            <div class="lopas-modal-dialog">
                <div class="lopas-modal-header">
                    <button type="button" class="lopas-modal-close" onclick="lopasCloseModal()">×</button>
                </div>
                <div class="lopas-modal-body" id="lopas-booking-details-container">
                    <!-- Details will be loaded here -->
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render orders page
     */
    public function render_orders() {
        ?>
        <div class="wrap">
            <h1><?php _e('Orders', 'lopas'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Code', 'lopas'); ?></th>
                        <th><?php _e('Customer', 'lopas'); ?></th>
                        <th><?php _e('Total', 'lopas'); ?></th>
                        <th><?php _e('Payment Status', 'lopas'); ?></th>
                        <th><?php _e('Order Status', 'lopas'); ?></th>
                        <th><?php _e('Date', 'lopas'); ?></th>
                        <th><?php _e('Actions', 'lopas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $wpdb;
                    $table = LOPAS_Database::get_table('orders');
                    $orders = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 50");
                    if (empty($orders)) {
                        echo '<tr><td colspan="7">' . __('No orders found.', 'lopas') . '</td></tr>';
                    } else {
                        foreach ($orders as $order) {
                            $user = get_userdata($order->user_id);
                            echo '<tr>';
                            echo '<td>' . esc_html($order->order_code) . '</td>';
                            echo '<td>' . ($user ? esc_html($user->display_name) : '-') . '</td>';
                            echo '<td>' . lopas_format_currency($order->final_price) . '</td>';
                            echo '<td><span class="status-' . esc_attr($order->payment_status) . '">' . esc_html($order->payment_status) . '</span></td>';
                            echo '<td><span class="status-' . esc_attr($order->order_status) . '">' . esc_html($order->order_status) . '</span></td>';
                            echo '<td>' . esc_html(lopas_format_date($order->created_at)) . '</td>';
                            echo '<td>';
                            echo '<a href="#" onclick="lopasViewOrder(' . $order->id . '); return false;" class="btn btn-sm">' . __('View', 'lopas') . '</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal for order details -->
        <div id="lopas-order-modal" class="lopas-modal" style="display:none;">
            <div class="lopas-modal-overlay" onclick="lopasCloseModal()"></div>
            <div class="lopas-modal-dialog">
                <div class="lopas-modal-header">
                    <button type="button" class="lopas-modal-close" onclick="lopasCloseModal()">×</button>
                </div>
                <div class="lopas-modal-body" id="lopas-order-details-container">
                    <!-- Details will be loaded here -->
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render payments page
     */
    public function render_payments() {
        ?>
        <div class="wrap">
            <h1><?php _e('Payments', 'lopas'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Transaction Code', 'lopas'); ?></th>
                        <th><?php _e('Order', 'lopas'); ?></th>
                        <th><?php _e('Amount', 'lopas'); ?></th>
                        <th><?php _e('Method', 'lopas'); ?></th>
                        <th><?php _e('Status', 'lopas'); ?></th>
                        <th><?php _e('Date', 'lopas'); ?></th>
                        <th><?php _e('Actions', 'lopas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $wpdb;
                    $table = LOPAS_Database::get_table('payments');
                    $payments = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 50");
                    if (empty($payments)) {
                        echo '<tr><td colspan="7">' . __('No payments found.', 'lopas') . '</td></tr>';
                    } else {
                        foreach ($payments as $payment) {
                            $order = LOPAS_Order::get($payment->order_id);
                            echo '<tr>';
                            echo '<td>' . esc_html($payment->transaction_code) . '</td>';
                            echo '<td>' . ($order ? esc_html($order->order_code) : '-') . '</td>';
                            echo '<td>' . lopas_format_currency($payment->amount) . '</td>';
                            echo '<td>' . esc_html($payment->payment_method) . '</td>';
                            echo '<td><span class="status-' . esc_attr($payment->status) . '">' . esc_html($payment->status) . '</span></td>';
                            echo '<td>' . esc_html(lopas_format_date($payment->created_at)) . '</td>';
                            echo '<td>';
                            echo '<a href="#" onclick="lopasViewPayment(' . $payment->id . '); return false;" class="btn btn-sm">' . __('View', 'lopas') . '</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal for payment details -->
        <div id="lopas-payment-modal" class="lopas-modal" style="display:none;">
            <div class="lopas-modal-overlay" onclick="lopasCloseModal()"></div>
            <div class="lopas-modal-dialog">
                <div class="lopas-modal-header">
                    <button type="button" class="lopas-modal-close" onclick="lopasCloseModal()">×</button>
                </div>
                <div class="lopas-modal-body" id="lopas-payment-details-container">
                    <!-- Details will be loaded here -->
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings() {
        ?>
        <div class="wrap">
            <h1><?php _e('LOPAS Settings', 'lopas'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('lopas_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="lopas_vnpay_merchant_id"><?php _e('VNPay Merchant ID', 'lopas'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="lopas_vnpay_merchant_id" name="lopas_vnpay_merchant_id" 
                                   value="<?php echo esc_attr(get_option('lopas_vnpay_merchant_id')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="lopas_vnpay_hash_secret"><?php _e('VNPay Hash Secret', 'lopas'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="lopas_vnpay_hash_secret" name="lopas_vnpay_hash_secret" 
                                   value="<?php echo esc_attr(get_option('lopas_vnpay_hash_secret')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="lopas_vnpay_url"><?php _e('VNPay URL', 'lopas'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="lopas_vnpay_url" name="lopas_vnpay_url" 
                                   value="<?php echo esc_attr(get_option('lopas_vnpay_url')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Count all services
     */
    private function count_all_services() {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        return intval($wpdb->get_var("SELECT COUNT(*) FROM {$table}"));
    }
    
    /**
     * Count all bookings
     */
    private function count_all_bookings() {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        return intval($wpdb->get_var("SELECT COUNT(*) FROM {$table}"));
    }
    
    /**
     * Count all orders
     */
    private function count_all_orders() {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        return intval($wpdb->get_var("SELECT COUNT(*) FROM {$table}"));
    }
}
