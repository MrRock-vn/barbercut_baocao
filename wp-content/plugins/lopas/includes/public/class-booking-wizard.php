<?php
/**
 * LOPAS Booking Wizard
 * Multi-step booking process with session management
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Booking_Wizard {
    
    public function __construct() {
        add_shortcode('lopas_booking_wizard', array($this, 'render_wizard'));
        add_action('init', array($this, 'handle_wizard_submission'));
    }
    
    /**
     * Render booking wizard
     */
    public function render_wizard($atts) {
        // Start session if not started
        if (!session_id()) {
            session_start();
        }
        
        // Get current step
        $step = isset($_GET['step']) ? absint($_GET['step']) : 1;
        $step = max(1, min(4, $step)); // Clamp between 1-4
        
        // Get wizard data from session
        $wizard_data = isset($_SESSION['lopas_booking_wizard']) ? $_SESSION['lopas_booking_wizard'] : array();
        
        // Get salon ID
        $salon_id = isset($_GET['salon_id']) ? absint($_GET['salon_id']) : 0;
        if ($salon_id > 0) {
            $wizard_data['salon_id'] = $salon_id;
            $_SESSION['lopas_booking_wizard'] = $wizard_data;
        } elseif (isset($wizard_data['salon_id'])) {
            $salon_id = $wizard_data['salon_id'];
        }
        
        // Validate salon
        if (empty($salon_id)) {
            return '<div class="alert alert-warning">Please select a salon first.</div>';
        }
        
        ob_start();
        
        // Render appropriate step
        switch ($step) {
            case 1:
                $this->render_step_1($salon_id, $wizard_data);
                break;
            case 2:
                $this->render_step_2($salon_id, $wizard_data);
                break;
            case 3:
                $this->render_step_3($salon_id, $wizard_data);
                break;
            case 4:
                $this->render_step_4($salon_id, $wizard_data);
                break;
        }
        
        return ob_get_clean();
    }
    
    /**
     * Step 1: Select Services
     */
    private function render_step_1($salon_id, $wizard_data) {
        global $wpdb;
        
        // Get salon
        $salon_table = $wpdb->prefix . 'lopas_salons';
        $salon = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$salon_table} WHERE id = %d", $salon_id));
        
        if (!$salon) {
            echo '<div class="alert alert-danger">Salon not found.</div>';
            return;
        }
        
        // Get services
        $services_table = $wpdb->prefix . 'lopas_services';
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$services_table} WHERE salon_id = %d AND status = 'active' ORDER BY name",
            $salon_id
        ));
        
        $selected_services = isset($wizard_data['services']) ? $wizard_data['services'] : array();
        
        include LOPAS_PATH . 'templates/booking/step-1-services.php';
    }
    
    /**
     * Step 2: Select Staff
     */
    private function render_step_2($salon_id, $wizard_data) {
        global $wpdb;
        
        // Validate previous step
        if (empty($wizard_data['services'])) {
            echo '<div class="alert alert-warning">Please select services first.</div>';
            echo '<a href="' . esc_url(add_query_arg('step', 1)) . '" class="btn btn-primary">Go to Step 1</a>';
            return;
        }
        
        // Get salon
        $salon_table = $wpdb->prefix . 'lopas_salons';
        $salon = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$salon_table} WHERE id = %d", $salon_id));
        
        // Get staff
        $staff_table = $wpdb->prefix . 'lopas_staff';
        $staff_list = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$staff_table} WHERE salon_id = %d AND status = 'active' ORDER BY name",
            $salon_id
        ));
        
        $selected_staff = isset($wizard_data['staff_id']) ? $wizard_data['staff_id'] : 0;
        
        include LOPAS_PATH . 'templates/booking/step-2-staff.php';
    }
    
    /**
     * Step 3: Select Date & Time
     */
    private function render_step_3($salon_id, $wizard_data) {
        global $wpdb;
        
        // Validate previous steps
        if (empty($wizard_data['services']) || empty($wizard_data['staff_id'])) {
            echo '<div class="alert alert-warning">Please complete previous steps first.</div>';
            echo '<a href="' . esc_url(add_query_arg('step', 1)) . '" class="btn btn-primary">Go to Step 1</a>';
            return;
        }
        
        // Get salon
        $salon_table = $wpdb->prefix . 'lopas_salons';
        $salon = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$salon_table} WHERE id = %d", $salon_id));
        
        // Get staff
        $staff_table = $wpdb->prefix . 'lopas_staff';
        $staff = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$staff_table} WHERE id = %d", $wizard_data['staff_id']));
        
        $selected_date = isset($wizard_data['booking_date']) ? $wizard_data['booking_date'] : '';
        $selected_time = isset($wizard_data['booking_time']) ? $wizard_data['booking_time'] : '';
        
        include LOPAS_PATH . 'templates/booking/step-3-datetime.php';
    }
    
    /**
     * Step 4: Confirm & Payment
     */
    private function render_step_4($salon_id, $wizard_data) {
        global $wpdb;
        
        // Validate all previous steps
        if (empty($wizard_data['services']) || empty($wizard_data['staff_id']) || 
            empty($wizard_data['booking_date']) || empty($wizard_data['booking_time'])) {
            echo '<div class="alert alert-warning">Please complete all previous steps first.</div>';
            echo '<a href="' . esc_url(add_query_arg('step', 1)) . '" class="btn btn-primary">Go to Step 1</a>';
            return;
        }
        
        // Get salon
        $salon_table = $wpdb->prefix . 'lopas_salons';
        $salon = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$salon_table} WHERE id = %d", $salon_id));
        
        // Get staff
        $staff_table = $wpdb->prefix . 'lopas_staff';
        $staff = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$staff_table} WHERE id = %d", $wizard_data['staff_id']));
        
        include LOPAS_PATH . 'templates/booking/step-4-confirm.php';
    }
    
    /**
     * Handle wizard form submissions
     */
    public function handle_wizard_submission() {
        if (!isset($_POST['lopas_wizard_submit'])) {
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['lopas_wizard_nonce']) || !wp_verify_nonce($_POST['lopas_wizard_nonce'], 'lopas_wizard')) {
            wp_die('Security check failed');
        }
        
        // Start session
        if (!session_id()) {
            session_start();
        }
        
        $step = isset($_POST['step']) ? absint($_POST['step']) : 1;
        $wizard_data = isset($_SESSION['lopas_booking_wizard']) ? $_SESSION['lopas_booking_wizard'] : array();
        
        switch ($step) {
            case 1:
                $this->process_step_1($wizard_data);
                break;
            case 2:
                $this->process_step_2($wizard_data);
                break;
            case 3:
                $this->process_step_3($wizard_data);
                break;
            case 4:
                $this->process_step_4($wizard_data);
                break;
        }
    }
    
    /**
     * Process Step 1: Services
     */
    private function process_step_1(&$wizard_data) {
        $service_ids = isset($_POST['service_ids']) ? array_map('absint', $_POST['service_ids']) : array();
        
        if (empty($service_ids)) {
            wp_redirect(add_query_arg(array('step' => 1, 'error' => 'no_services'), wp_get_referer()));
            exit;
        }
        
        // Get services details
        global $wpdb;
        $services_table = $wpdb->prefix . 'lopas_services';
        $placeholders = implode(',', array_fill(0, count($service_ids), '%d'));
        
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$services_table} WHERE id IN ($placeholders)",
            $service_ids
        ));
        
        // Calculate totals
        $total_price = 0;
        $total_duration = 0;
        $service_data = array();
        
        foreach ($services as $service) {
            $total_price += $service->price;
            $total_duration += $service->duration;
            $service_data[] = array(
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'duration' => $service->duration
            );
        }
        
        $wizard_data['services'] = $service_data;
        $wizard_data['total_price'] = $total_price;
        $wizard_data['total_duration'] = $total_duration;
        
        $_SESSION['lopas_booking_wizard'] = $wizard_data;
        
        wp_redirect(add_query_arg('step', 2));
        exit;
    }
    
    /**
     * Process Step 2: Staff
     */
    private function process_step_2(&$wizard_data) {
        $staff_id = isset($_POST['staff_id']) ? absint($_POST['staff_id']) : 0;
        
        if (empty($staff_id)) {
            wp_redirect(add_query_arg(array('step' => 2, 'error' => 'no_staff'), wp_get_referer()));
            exit;
        }
        
        $wizard_data['staff_id'] = $staff_id;
        $_SESSION['lopas_booking_wizard'] = $wizard_data;
        
        wp_redirect(add_query_arg('step', 3));
        exit;
    }
    
    /**
     * Process Step 3: Date & Time
     */
    private function process_step_3(&$wizard_data) {
        $booking_date = isset($_POST['booking_date']) ? sanitize_text_field($_POST['booking_date']) : '';
        $booking_time = isset($_POST['booking_time']) ? sanitize_text_field($_POST['booking_time']) : '';
        
        if (empty($booking_date) || empty($booking_time)) {
            wp_redirect(add_query_arg(array('step' => 3, 'error' => 'no_datetime'), wp_get_referer()));
            exit;
        }
        
        $wizard_data['booking_date'] = $booking_date;
        $wizard_data['booking_time'] = $booking_time;
        $_SESSION['lopas_booking_wizard'] = $wizard_data;
        
        wp_redirect(add_query_arg('step', 4));
        exit;
    }
    
    /**
     * Process Step 4: Confirm & Create Booking
     */
    private function process_step_4(&$wizard_data) {
        // User must be logged in
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(add_query_arg('step', 4)));
            exit;
        }
        
        $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : 'cod';
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        // Create booking
        require_once LOPAS_PATH . 'includes/class-booking.php';
        $booking_model = new LOPAS_Booking();
        
        $booking_data = array(
            'user_id' => get_current_user_id(),
            'salon_id' => $wizard_data['salon_id'],
            'service_id' => $wizard_data['services'][0]['id'], // First service
            'staff_id' => $wizard_data['staff_id'],
            'booking_date' => $wizard_data['booking_date'],
            'booking_time' => $wizard_data['booking_time'],
            'duration' => $wizard_data['total_duration'],
            'note' => $notes,
            'status' => 'pending'
        );
        
        $booking_id = $booking_model->create($booking_data);
        
        if ($booking_id) {
            // Clear wizard data
            unset($_SESSION['lopas_booking_wizard']);
            
            // Release holds
            require_once LOPAS_PATH . 'includes/class-booking-hold.php';
            $hold_model = new LOPAS_Booking_Hold();
            $hold_model->release_session_holds(session_id());
            
            // Redirect based on payment method
            if ($payment_method === 'vnpay') {
                wp_redirect(home_url('/payment/?booking_id=' . $booking_id));
            } else {
                wp_redirect(home_url('/booking-success/?booking_id=' . $booking_id));
            }
            exit;
        } else {
            wp_redirect(add_query_arg(array('step' => 4, 'error' => 'booking_failed'), wp_get_referer()));
            exit;
        }
    }
}

// Initialize
new LOPAS_Booking_Wizard();

