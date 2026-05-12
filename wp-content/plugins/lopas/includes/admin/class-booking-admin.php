<?php
/**
 * LOPAS Booking Admin Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Booking_Admin {
    
    public function __construct() {
        // AJAX handlers
        add_action('wp_ajax_lopas_update_booking_status', array($this, 'ajax_update_booking_status'));
        add_action('wp_ajax_lopas_cancel_booking', array($this, 'ajax_cancel_booking'));
        add_action('wp_ajax_lopas_get_booking', array($this, 'ajax_get_booking'));
    }
    
    /**
     * Render booking details modal
     */
    public static function render_details($booking_id) {
        $booking = LOPAS_Booking::get($booking_id);
        
        if (!$booking) {
            return;
        }
        
        $user = get_userdata($booking->user_id);
        $salon = LOPAS_Salon::get($booking->salon_id);
        $service = LOPAS_Service::get($booking->service_id);
        $staff = $booking->staff_id ? $this->get_staff($booking->staff_id) : null;
        
        ?>
        <div class="lopas-modal-content">
            <h2><?php _e('Booking Details', 'lopas'); ?></h2>
            
            <div class="booking-details">
                <div class="detail-row">
                    <span class="label"><?php _e('Booking Code:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html($booking->booking_code); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Customer:', 'lopas'); ?></span>
                    <span class="value"><?php echo $user ? esc_html($user->display_name) : '-'; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Email:', 'lopas'); ?></span>
                    <span class="value"><?php echo $user ? esc_html($user->user_email) : '-'; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Salon:', 'lopas'); ?></span>
                    <span class="value"><?php echo $salon ? esc_html($salon->name) : '-'; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Service:', 'lopas'); ?></span>
                    <span class="value"><?php echo $service ? esc_html($service->name) : '-'; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Staff:', 'lopas'); ?></span>
                    <span class="value"><?php echo $staff ? esc_html($staff->name) : __('Not assigned', 'lopas'); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Date:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html(lopas_format_date_vi($booking->booking_date)); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Time:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html($booking->booking_time); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Duration:', 'lopas'); ?></span>
                    <span class="value"><?php echo $booking->duration . ' ' . __('minutes', 'lopas'); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Status:', 'lopas'); ?></span>
                    <span class="value">
                        <span class="status-<?php echo esc_attr($booking->status); ?>">
                            <?php echo esc_html($booking->status); ?>
                        </span>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Note:', 'lopas'); ?></span>
                    <span class="value"><?php echo $booking->note ? esc_html($booking->note) : '-'; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Created:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html(lopas_format_date($booking->created_at)); ?></span>
                </div>
            </div>
            
            <div class="booking-actions">
                <select id="booking_status_select" class="booking-status-select">
                    <option value=""><?php _e('-- Change Status --', 'lopas'); ?></option>
                    <option value="pending" <?php echo $booking->status === 'pending' ? 'selected' : ''; ?>>
                        <?php _e('Pending', 'lopas'); ?>
                    </option>
                    <option value="confirmed" <?php echo $booking->status === 'confirmed' ? 'selected' : ''; ?>>
                        <?php _e('Confirmed', 'lopas'); ?>
                    </option>
                    <option value="in-progress" <?php echo $booking->status === 'in-progress' ? 'selected' : ''; ?>>
                        <?php _e('In Progress', 'lopas'); ?>
                    </option>
                    <option value="completed" <?php echo $booking->status === 'completed' ? 'selected' : ''; ?>>
                        <?php _e('Completed', 'lopas'); ?>
                    </option>
                </select>
                
                <button type="button" class="btn btn-primary" onclick="lopasUpdateBookingStatus(<?php echo $booking->id; ?>)">
                    <?php _e('Update Status', 'lopas'); ?>
                </button>
                
                <button type="button" class="btn btn-danger" onclick="lopasCancelBooking(<?php echo $booking->id; ?>)">
                    <?php _e('Cancel Booking', 'lopas'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get staff by ID
     */
    private static function get_staff($staff_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('staff');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $staff_id));
    }
    
    /**
     * AJAX: Update booking status
     */
    public function ajax_update_booking_status() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $booking_id = intval($_POST['booking_id']);
        $status = sanitize_text_field($_POST['status']);
        
        if (!$booking_id) {
            wp_send_json_error(__('Invalid booking ID', 'lopas'));
        }
        
        $valid_statuses = array('pending', 'confirmed', 'in-progress', 'completed', 'cancelled');
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(__('Invalid status', 'lopas'));
        }
        
        $result = LOPAS_Booking::update($booking_id, array('status' => $status));
        
        if (!$result) {
            wp_send_json_error(__('Failed to update booking', 'lopas'));
        }
        
        wp_send_json_success(array(
            'message' => __('Booking status updated successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Cancel booking
     */
    public function ajax_cancel_booking() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $booking_id = intval($_POST['booking_id']);
        $reason = sanitize_text_field($_POST['reason']);
        
        if (!$booking_id) {
            wp_send_json_error(__('Invalid booking ID', 'lopas'));
        }
        
        $result = LOPAS_Booking::cancel($booking_id, $reason);
        
        if (!$result) {
            wp_send_json_error(__('Failed to cancel booking', 'lopas'));
        }
        
        wp_send_json_success(array(
            'message' => __('Booking cancelled successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Get booking
     */
    public function ajax_get_booking() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $booking_id = intval($_POST['booking_id']);
        
        if (!$booking_id) {
            wp_send_json_error(__('Invalid booking ID', 'lopas'));
        }
        
        $booking = LOPAS_Booking::get($booking_id);
        
        if (!$booking) {
            wp_send_json_error(__('Booking not found', 'lopas'));
        }
        
        wp_send_json_success($booking);
    }
}
