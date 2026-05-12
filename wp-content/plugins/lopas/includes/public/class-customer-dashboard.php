<?php
/**
 * LOPAS Customer Dashboard Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Customer_Dashboard {
    
    /**
     * Render my bookings only (for shortcode)
     */
    public static function render_my_bookings() {
        if (!is_user_logged_in()) {
            return '<p>' . __('Vui lòng đăng nhập để xem lịch hẹn.', 'lopas') . '</p>';
        }
        
        $user_id = get_current_user_id();
        ob_start();
        ?>
        <div class="lopas-my-bookings-shortcode">
            <style>
                .booking-card { background: #fff; border-radius: 15px; border: 1px solid #e2e8f0; margin-bottom: 20px; transition: transform 0.2s; }
                .booking-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
                .booking-status { padding: 4px 12px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; }
                .status-pending { background: #fef3c7; color: #92400e; }
                .status-confirmed { background: #dcfce7; color: #166534; }
                .status-completed { background: #f1f5f9; color: #475569; }
                .status-cancelled { background: #fee2e2; color: #991b1b; }
            </style>
            <?php
            $bookings = LOPAS_Booking::get_by_user($user_id, array('limit' => -1));
            
            if (empty($bookings)) {
                echo '<p>' . __('Bạn chưa có lịch hẹn nào.', 'lopas') . '</p>';
            } else {
                echo '<div class="row g-4">';
                foreach ($bookings as $booking) {
                    $salon = LOPAS_Salon::get($booking->salon_id);
                    $service = LOPAS_Service::get($booking->service_id);
                    $status_class = 'status-' . $booking->status;
                    
                    $status_labels = array(
                        'pending'     => 'Chờ xác nhận',
                        'confirmed'   => 'Đã xác nhận',
                        'in-progress' => 'Đang thực hiện',
                        'completed'   => 'Hoàn thành',
                        'cancelled'   => 'Đã hủy'
                    );
                    $status_text = isset($status_labels[$booking->status]) ? $status_labels[$booking->status] : $booking->status;
                    ?>
                    <div class="col-md-6">
                        <div class="booking-card p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0"><?php echo $salon ? esc_html($salon->name) : '-'; ?></h5>
                                    <small class="text-muted">Mã: <?php echo esc_html($booking->booking_code); ?></small>
                                </div>
                                <span class="booking-status <?php echo $status_class; ?>"><?php echo esc_html($status_text); ?></span>
                            </div>
                            <div class="mb-3">
                                <div class="mb-1"><small class="text-muted">Dịch vụ:</small> <strong><?php echo $service ? esc_html($service->name) : '-'; ?></strong></div>
                                <div><small class="text-muted">Thời gian:</small> <strong><?php echo lopas_format_date_vi($booking->booking_date); ?> <?php echo $booking->booking_time; ?></strong></div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                echo '</div>';
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render customer dashboard
     */
    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your dashboard', 'lopas') . '</p>';
        }
        
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        
        ob_start();
        ?>
        <div class="lopas-customer-dashboard">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1><?php _e('My Dashboard', 'lopas'); ?></h1>
                <p><?php printf(__('Welcome, %s!', 'lopas'), esc_html($user->display_name)); ?></p>
            </div>
            
            <!-- Dashboard Stats -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-content">
                        <h3><?php _e('Total Bookings', 'lopas'); ?></h3>
                        <p class="stat-number"><?php echo self::count_user_bookings($user_id); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3><?php _e('Upcoming', 'lopas'); ?></h3>
                        <p class="stat-number"><?php echo self::count_upcoming_bookings($user_id); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✓</div>
                    <div class="stat-content">
                        <h3><?php _e('Completed', 'lopas'); ?></h3>
                        <p class="stat-number"><?php echo self::count_completed_bookings($user_id); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3><?php _e('Total Spent', 'lopas'); ?></h3>
                        <p class="stat-number"><?php echo lopas_format_currency(self::get_total_spent($user_id)); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="dashboard-tabs">
                <button class="tab-btn active" onclick="lopasSwitchTab(this, 'bookings')">
                    <?php _e('Lịch hẹn của tôi', 'lopas'); ?>
                </button>
                <button class="tab-btn" onclick="lopasSwitchTab(this, 'orders')">
                    <?php _e('Đơn hàng / Thanh toán', 'lopas'); ?>
                </button>
                <button class="tab-btn" onclick="lopasSwitchTab(this, 'profile')">
                    <?php _e('Hồ sơ cá nhân', 'lopas'); ?>
                </button>
            </div>
            
            <style>
                .lopas-customer-dashboard { padding: 40px 0; max-width: 1100px; margin: 0 auto; }
                .dashboard-header { margin-bottom: 30px; }
                .dashboard-header h1 { font-weight: 800; color: #0f172a; margin-bottom: 5px; }
                
                .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
                .stat-card { background: #fff; padding: 24px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid rgba(15,23,42,0.08); display: flex; align-items: center; gap: 15px; }
                .stat-icon { font-size: 2rem; background: #f8fafc; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 15px; }
                .stat-content h3 { font-size: 0.9rem; color: #64748b; margin: 0; font-weight: 600; }
                .stat-number { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
                
                .dashboard-tabs { display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; }
                .tab-btn { background: none; border: none; padding: 10px 20px; font-weight: 700; color: #64748b; border-radius: 999px; transition: all 0.2s; }
                .tab-btn.active { background: #0f172a; color: #fff; }
                
                .tab-content { display: none; }
                .tab-content.active { display: block; }
                
                .profile-form { background: #fff; padding: 30px; border-radius: 20px; border: 1px solid #e2e8f0; max-width: 600px; }
                .form-group { margin-bottom: 20px; }
                .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #0f172a; }
                .form-control { border-radius: 12px; min-height: 50px; background: #f8fafc; }
            </style>
            
            <!-- Bookings Tab -->
            <div class="tab-content active" id="tab-bookings">
                <h2><?php _e('My Bookings', 'lopas'); ?></h2>
                
                <?php
                $bookings = LOPAS_Booking::get_by_user($user_id, array('limit' => -1));
                
                if (empty($bookings)) {
                    echo '<p>' . __('No bookings yet', 'lopas') . '</p>';
                } else {
                    echo '<div class="row g-4">';
                    
                    foreach ($bookings as $booking) {
                        $salon = LOPAS_Salon::get($booking->salon_id);
                        $service = LOPAS_Service::get($booking->service_id);
                        $status_class = 'status-' . $booking->status;
                        
                        echo '<div class="col-md-6">';
                        echo '<div class="booking-card p-4">';
                        
                        echo '<div class="d-flex justify-content-between align-items-start mb-3">';
                        echo '<div><h5 class="fw-bold mb-0">' . ($salon ? esc_html($salon->name) : '-') . '</h5>';
                        echo '<small class="text-muted">Mã: ' . esc_html($booking->booking_code) . '</small></div>';
                        echo '<span class="booking-status ' . $status_class . '">' . esc_html($booking->status) . '</span>';
                        echo '</div>';
                        
                        echo '<div class="row mb-3">';
                        echo '<div class="col-6"><small class="text-muted d-block">Dịch vụ</small><span class="fw-bold">' . ($service ? esc_html($service->name) : '-') . '</span></div>';
                        echo '<div class="col-6"><small class="text-muted d-block">Thời gian</small><span class="fw-bold">' . esc_html(lopas_format_date_vi($booking->booking_date)) . ' ' . esc_html($booking->booking_time) . '</span></div>';
                        echo '</div>';
                        
                        echo '<div class="d-flex gap-2">';
                        if ($booking->status === 'completed' && !self::has_review($booking->id)) {
                            echo '<button class="btn btn-sm btn-warning rounded-pill px-3" onclick="lopasLeaveReview(' . $booking->id . ')">Đánh giá</button>';
                        }
                        if (in_array($booking->status, ['pending', 'confirmed'])) {
                            echo '<button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="lopasCancelBookingCustomer(' . $booking->id . ')">Hủy lịch</button>';
                        }
                        echo '</div>';
                        
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                ?>
            </div>
            
            <!-- Orders Tab -->
            <div class="tab-content" id="tab-orders">
                <h2><?php _e('My Orders', 'lopas'); ?></h2>
                
                <?php
                $orders = LOPAS_Order::get_by_user($user_id, array('limit' => -1));
                
                if (empty($orders)) {
                    echo '<p>' . __('No orders yet', 'lopas') . '</p>';
                } else {
                    echo '<table class="orders-table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>' . __('Order Code', 'lopas') . '</th>';
                    echo '<th>' . __('Total', 'lopas') . '</th>';
                    echo '<th>' . __('Payment Status', 'lopas') . '</th>';
                    echo '<th>' . __('Order Status', 'lopas') . '</th>';
                    echo '<th>' . __('Date', 'lopas') . '</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($orders as $order) {
                        echo '<tr>';
                        echo '<td>' . esc_html($order->order_code) . '</td>';
                        echo '<td>' . lopas_format_currency($order->final_price) . '</td>';
                        echo '<td><span class="status-' . esc_attr($order->payment_status) . '">' . esc_html($order->payment_status) . '</span></td>';
                        echo '<td><span class="status-' . esc_attr($order->order_status) . '">' . esc_html($order->order_status) . '</span></td>';
                        echo '<td>' . esc_html(lopas_format_date($order->created_at)) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                }
                ?>
            </div>
            
            <!-- Profile Tab -->
            <div class="tab-content" id="tab-profile">
                <h2><?php _e('My Profile', 'lopas'); ?></h2>
                
                <div class="profile-form">
                    <div class="form-group">
                        <label><?php _e('Email', 'lopas'); ?></label>
                        <input type="email" value="<?php echo esc_attr($user->user_email); ?>" disabled class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label><?php _e('First Name', 'lopas'); ?></label>
                        <input type="text" value="<?php echo esc_attr(get_user_meta($user_id, 'first_name', true)); ?>" disabled class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label><?php _e('Last Name', 'lopas'); ?></label>
                        <input type="text" value="<?php echo esc_attr(get_user_meta($user_id, 'last_name', true)); ?>" disabled class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label><?php _e('Phone', 'lopas'); ?></label>
                        <input type="tel" value="<?php echo esc_attr(get_user_meta($user_id, 'phone', true)); ?>" disabled class="form-control">
                    </div>
                    
                    <p class="profile-note">
                        <?php printf(__('To edit your profile, please visit <a href="%s">your account page</a>', 'lopas'), esc_url(admin_url('profile.php'))); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div id="lopasReviewModal" class="lopas-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
            <div style="background:#fff; padding:30px; border-radius:12px; max-width:500px; width:90%; position:relative;">
                <h3 style="margin-top:0;">Đánh giá dịch vụ</h3>
                <input type="hidden" id="review_booking_id" value="">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Đánh giá (1-5 sao):</label>
                    <select id="review_rating" class="form-control" style="width:100%; padding:8px;">
                        <option value="5">5 - Tuyệt vời</option>
                        <option value="4">4 - Rất tốt</option>
                        <option value="3">3 - Bình thường</option>
                        <option value="2">2 - Kém</option>
                        <option value="1">1 - Tồi tệ</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Nhận xét của bạn:</label>
                    <textarea id="review_comment" class="form-control" rows="4" style="width:100%; padding:8px;" placeholder="Dịch vụ rất tốt..."></textarea>
                </div>
                
                <div style="text-align:right;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('lopasReviewModal').style.display='none'">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="lopasSubmitReview()">Gửi đánh giá</button>
                </div>
            </div>
        </div>

        <script>
        function lopasSwitchTab(btn, tabId) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            btn.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        }

        function lopasLeaveReview(bookingId) {
            document.getElementById('review_booking_id').value = bookingId;
            document.getElementById('review_rating').value = '5';
            document.getElementById('review_comment').value = '';
            document.getElementById('lopasReviewModal').style.display = 'flex';
        }

        async function lopasSubmitReview() {
            const bookingId = document.getElementById('review_booking_id').value;
            const rating = document.getElementById('review_rating').value;
            const comment = document.getElementById('review_comment').value;

            if (!bookingId || !rating) return;

            try {
                const formData = new FormData();
                formData.append('action', 'lopas_submit_review');
                formData.append('booking_id', bookingId);
                formData.append('rating', rating);
                formData.append('comment', comment);
                formData.append('_ajax_nonce', lopasPublic.nonce);

                const response = await fetch(lopasPublic.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const res = await response.json();
                if (res.success) {
                    alert('Cảm ơn bạn đã đánh giá!');
                    window.location.reload();
                } else {
                    alert(res.data || 'Có lỗi xảy ra.');
                }
            } catch (error) {
                console.error(error);
                alert('Có lỗi xảy ra.');
            }
        }
        
        // Handle Cancel Booking Customer
        async function lopasCancelBookingCustomer(bookingId) {
            if (!confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'lopas_cancel_booking');
                formData.append('booking_id', bookingId);
                formData.append('_ajax_nonce', lopasPublic.nonce);

                const response = await fetch(lopasPublic.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const res = await response.json();
                if (res.success) {
                    alert('Hủy lịch thành công!');
                    window.location.reload();
                } else {
                    alert(res.data || 'Không thể hủy lịch.');
                }
            } catch (e) {
                console.error(e);
            }
        }
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Count user bookings
     */
    private static function count_user_bookings($user_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE user_id = %d",
            $user_id
        )));
    }
    
    /**
     * Count upcoming bookings
     */
    private static function count_upcoming_bookings($user_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        $now = current_time('mysql');
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND CONCAT(booking_date, ' ', booking_time) > %s AND status != 'cancelled'",
            $user_id,
            $now
        )));
    }
    
    /**
     * Count completed bookings
     */
    private static function count_completed_bookings($user_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND status = 'completed'",
            $user_id
        )));
    }
    
    /**
     * Get total spent
     */
    private static function get_total_spent($user_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(final_price) FROM {$table} WHERE user_id = %d AND payment_status = 'paid'",
            $user_id
        ));
        return $result ?: 0;
    }
    
    /**
     * Check if user has review for booking
     */
    private static function has_review($booking_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('reviews');
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE booking_id = %d",
            $booking_id
        ))) > 0;
    }
}
