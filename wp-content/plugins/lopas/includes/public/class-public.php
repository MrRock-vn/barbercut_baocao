<?php
/**
 * LOPAS Public Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Public {
    
    public function __construct() {
        // Load public classes
        require_once LOPAS_PATH . 'includes/public/class-booking-form.php';
        require_once LOPAS_PATH . 'includes/public/class-salon-page.php';
        require_once LOPAS_PATH . 'includes/public/class-customer-dashboard.php';
        require_once LOPAS_PATH . 'includes/public/class-payment-page.php';
        require_once LOPAS_PATH . 'includes/public/class-homepage.php';
        require_once LOPAS_PATH . 'includes/public/class-auth.php';
        require_once LOPAS_PATH . 'includes/public/class-owner-dashboard.php';
        
        // Initialize payment page and homepage
        new LOPAS_Payment_Page();
        new LOPAS_Homepage();
        
        // Register hooks
        add_action('init', array($this, 'register_shortcodes'));
        add_action('init', array($this, 'check_required_pages'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('lopas_booking_form', array($this, 'render_booking_form'));
        add_shortcode('lopas_salon_list', array($this, 'render_salon_list'));
        add_shortcode('lopas_salon_details', array($this, 'render_salon_details'));
        add_shortcode('lopas_my_bookings', array($this, 'render_my_bookings'));
        add_shortcode('lopas_customer_dashboard', array($this, 'render_customer_dashboard'));
        add_shortcode('lopas_owner_dashboard', array('LOPAS_Owner_Dashboard', 'render'));
    }
    
    /**
     * Enqueue public assets
     */
    public function enqueue_assets() {
        // Enqueue public CSS
        wp_enqueue_style(
            'lopas-public',
            LOPAS_URL . 'assets/css/public.css',
            array(),
            LOPAS_VERSION
        );
        
        // Enqueue booking wizard CSS
        wp_enqueue_style(
            'lopas-booking-wizard',
            LOPAS_URL . 'assets/css/booking-wizard.css',
            array(),
            LOPAS_VERSION
        );
        
        // Enqueue public JS
        wp_enqueue_script(
            'lopas-public',
            LOPAS_URL . 'assets/js/public.js',
            array('jquery'),
            LOPAS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('lopas-public', 'lopasPublic', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'homeUrl' => home_url('/'),
            'nonce' => wp_create_nonce('lopas_public_nonce'),
            'slotNonce' => wp_create_nonce('lopas_slot_nonce')
        ));
    }
    
    /**
     * Render booking form shortcode
     */
    public function render_booking_form($atts) {
        $atts = shortcode_atts(array(
            'salon_id' => 0
        ), $atts);
        
        if (!$atts['salon_id']) {
            $atts['salon_id'] = isset($_GET['salon_id']) ? intval($_GET['salon_id']) : 0;
        }
        
        $output = self::get_global_header();
        $output .= LOPAS_Booking_Form::render($atts['salon_id']);
        $output .= self::get_global_footer();
        return $output;
    }
    
    /**
     * Render salon list shortcode
     */
    public function render_salon_list($atts) {
        $atts = shortcode_atts(array(
            'limit' => 12
        ), $atts);
        
        $keyword = isset($_GET['keyword']) ? sanitize_text_field($_GET['keyword']) : '';
        
        $output = self::get_global_header();
        ob_start();
        ?>
        <div class="lopas-search-results py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="fw-bold mb-1">
                            <?php echo $keyword ? sprintf(__('Kết quả tìm kiếm cho: "%s"', 'lopas'), esc_html($keyword)) : __('Khám phá Salon', 'lopas'); ?>
                        </h1>
                        <p class="text-muted"><?php _e('Tìm thấy những địa điểm chất lượng nhất dành cho bạn.', 'lopas'); ?></p>
                    </div>
                </div>
                
                <div class="row g-4">
                    <?php
                    $salons = LOPAS_Salon::get_all(array(
                        'status' => 'active',
                        'limit' => intval($atts['limit']),
                        'keyword' => $keyword
                    ));
                    
                    if (empty($salons)) {
                        echo '<div class="col-12 text-center py-5">';
                        echo '<div class="ui-empty-state">';
                        echo '<h3 class="fw-bold">Không tìm thấy salon nào</h3>';
                        echo '<p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc xem danh sách tất cả salon.</p>';
                        echo '<a href="' . home_url('/salons/') . '" class="btn btn-primary mt-3">Xem tất cả salon</a>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        foreach ($salons as $salon) {
                            $salon_url = home_url('/salons/?salon_id=' . $salon->id);
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="booking-card premium-salon-card">
                                    <div class="card-image-wrapper position-relative">
                                        <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80" 
                                             alt="<?php echo esc_attr($salon->name); ?>" 
                                             class="card-img-top"
                                             onerror="this.src='https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=800&q=80'">
                                        <span class="badge-featured">Nổi bật</span>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="fw-bold h5 mb-2"><?php echo esc_html($salon->name); ?></h3>
                                        <p class="text-muted small mb-3"><i class="dashicons dashicons-location"></i> <?php echo esc_html($salon->address); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="salon-meta">
                                                <span class="text-warning"><i class="dashicons dashicons-star-filled"></i> 4.9</span>
                                                <span class="text-muted ms-2">(120+ đánh giá)</span>
                                            </div>
                                            <a href="<?php echo esc_url($salon_url); ?>" class="btn btn-primary rounded-pill px-4">Xem chi tiết</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        $output .= ob_get_clean();
        $output .= self::get_global_footer();
        return $output;
    }
    
    /**
     * Render salon details shortcode
     */
    public function render_salon_details($atts) {
        $atts = shortcode_atts(array(
            'salon_id' => 0
        ), $atts);
        
        $debug_text = '<!-- DEBUG: Called render_salon_details -->';
        
        if (!$atts['salon_id']) {
            // Try to get from URL parameter (salon_id or id)
            if (isset($_REQUEST['salon_id'])) {
                $atts['salon_id'] = intval($_REQUEST['salon_id']);
            } elseif (isset($_REQUEST['id'])) {
                $atts['salon_id'] = intval($_REQUEST['id']);
            }
        }
        
        if (!$atts['salon_id']) {
            return $debug_text . $this->render_salon_list($atts);
        }
        
        $output = $debug_text . self::get_global_header();
        $output .= LOPAS_Salon_Page::render($atts['salon_id']);
        $output .= self::get_global_footer();
        return $output;
    }
    
    /**
     * Render my bookings shortcode
     */
    public function render_my_bookings($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Vui lòng đăng nhập để xem lịch hẹn.', 'lopas') . '</p>';
        }
        
        $output = self::get_global_header();
        $output .= LOPAS_Customer_Dashboard::render_my_bookings();
        $output .= self::get_global_footer();
        return $output;
    }
    
    /**
     * Render customer dashboard shortcode
     */
    public function render_customer_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Vui lòng đăng nhập để xem trang cá nhân.', 'lopas') . '</p>';
        }
        
        $output = self::get_global_header();
        $output .= LOPAS_Customer_Dashboard::render();
        $output .= self::get_global_footer();
        return $output;
    }
    
    /**
     * AJAX: Create booking
     */
    public function ajax_create_booking() {
        check_ajax_referer('lopas_public_nonce', 'nonce');
        
        $salon_id = intval($_POST['salon_id']);
        $service_id = intval($_POST['service_id']);
        $booking_date = sanitize_text_field($_POST['booking_date']);
        $booking_time = sanitize_text_field($_POST['booking_time']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $note = sanitize_textarea_field($_POST['note']);
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(__('Vui lòng đăng nhập để đặt lịch.', 'lopas'));
        }
        
        $service = LOPAS_Service::get($service_id);
        if (!$service) {
            wp_send_json_error(__('Dịch vụ không tồn tại.', 'lopas'));
        }
        
        $status = ($payment_method === 'cod') ? 'confirmed' : 'pending';
        
        // Create booking
        $booking_id = LOPAS_Booking::create(array(
            'user_id' => $user_id,
            'salon_id' => $salon_id,
            'service_id' => $service_id,
            'booking_date' => $booking_date,
            'booking_time' => $booking_time,
            'status' => $status,
            'note' => $note
        ));
        
        if (!$booking_id) {
            wp_send_json_error(__('Lỗi tạo lịch hẹn.', 'lopas'));
        }
        
        // Create order
        $order_id = LOPAS_Order::create(array(
            'user_id' => $user_id,
            'booking_id' => $booking_id,
            'total_price' => $service->price,
            'payment_method' => $payment_method,
            'payment_status' => ($payment_method === 'cod') ? 'pending' : 'unpaid',
            'order_status' => $status
        ));
        
        if ($payment_method === 'vnpay') {
            // Create payment record
            LOPAS_Payment::create(array(
                'order_id' => $order_id,
                'amount' => $service->price,
                'payment_method' => 'vnpay',
                'status' => 'pending'
            ));
            
            $payment_url = LOPAS_Payment_Controller::get_vnpay_url($order_id);
            wp_send_json_success(array(
                'payment_url' => $payment_url,
                'order_id' => $order_id
            ));
        } else {
            wp_send_json_success(array(
                'redirect_url' => home_url('/my-bookings/'),
                'booking_id' => $booking_id
            ));
        }
    }
    
    /**
     * AJAX: Search salons
     */
    public function ajax_search_salons() {
        $keyword = sanitize_text_field($_POST['keyword']);
        $salons = LOPAS_Salon::get_all(array(
            'keyword' => $keyword,
            'status' => 'active'
        ));
        
        ob_start();
        if (empty($salons)) {
            echo '<p>' . __('Không tìm thấy salon nào.', 'lopas') . '</p>';
        } else {
            foreach ($salons as $salon) {
                // Render salon card
            }
        }
        $html = ob_get_clean();
        wp_send_json_success($html);
    }
    
    /**
     * AJAX: Cancel booking
     */
    public function ajax_cancel_booking() {
        check_ajax_referer('lopas_public_nonce', 'nonce');
        $booking_id = intval($_POST['booking_id']);
        
        $booking = LOPAS_Booking::get($booking_id);
        if ($booking && $booking->user_id == get_current_user_id()) {
            LOPAS_Booking::update($booking_id, array('status' => 'cancelled'));
            wp_send_json_success(__('Đã hủy lịch hẹn.', 'lopas'));
        } else {
            wp_send_json_error(__('Không có quyền thực hiện.', 'lopas'));
        }
    }
    
    /**
     * AJAX: Submit review
     */
    public function ajax_submit_review() {
        // Implement review submission
        wp_send_json_success();
    }
    
    /**
     * Get global header
     */
    public static function get_global_header() {
        return ''; // Theme handles header
    }
    
    /**
     * Get global footer
     */
    public static function get_global_footer() {
        return ''; // Theme handles footer
    }
    
    /**
     * Check and create required pages
     */
    public function check_required_pages() {
        $pages = array(
            'salons' => array(
                'title'   => 'Chi tiết Salon',
                'content' => '[lopas_salon_details]',
            ),
            'booking' => array(
                'title'   => 'Đặt lịch',
                'content' => '[lopas_booking_form]',
            ),
            'my-bookings' => array(
                'title'   => 'Lịch hẹn của tôi',
                'content' => '[lopas_my_bookings]',
            ),
            'payment' => array(
                'title'   => 'Thanh toán',
                'content' => '[lopas_payment_form]',
            ),
        );
        
        foreach ($pages as $slug => $page) {
            $existing_page = get_page_by_path($slug);
            if (!$existing_page) {
                wp_insert_post(array(
                    'post_title'   => $page['title'],
                    'post_content' => $page['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_name'    => $slug,
                ));
            }
        }
    }
}
