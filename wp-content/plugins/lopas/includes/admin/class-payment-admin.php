<?php
/**
 * LOPAS Payment Admin Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Payment_Admin {
    
    public function __construct() {
        // AJAX handlers
        add_action('wp_ajax_lopas_update_payment_status', array($this, 'ajax_update_payment_status'));
        add_action('wp_ajax_lopas_create_refund', array($this, 'ajax_create_refund'));
        add_action('wp_ajax_lopas_get_payment_details', array($this, 'ajax_get_payment_details'));
    }
    
    /**
     * Render payment details modal
     */
    public static function render_details($payment_id) {
        $payment = LOPAS_Payment::get($payment_id);
        
        if (!$payment) {
            return;
        }
        
        $order = LOPAS_Order::get($payment->order_id);
        $refunds = self::get_payment_refunds($payment_id);
        
        ?>
        <div class="lopas-modal-content">
            <h2><?php _e('Payment Details', 'lopas'); ?></h2>
            
            <div class="payment-details">
                <div class="detail-row">
                    <span class="label"><?php _e('Transaction Code:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html($payment->transaction_code); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Order Code:', 'lopas'); ?></span>
                    <span class="value"><?php echo $order ? esc_html($order->order_code) : '-'; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Amount:', 'lopas'); ?></span>
                    <span class="value"><?php echo lopas_format_currency($payment->amount); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Payment Method:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html($payment->payment_method); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Status:', 'lopas'); ?></span>
                    <span class="value">
                        <span class="status-<?php echo esc_attr($payment->status); ?>">
                            <?php echo esc_html($payment->status); ?>
                        </span>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Created:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html(lopas_format_date($payment->created_at)); ?></span>
                </div>
            </div>
            
            <?php if (!empty($refunds)): ?>
            <h3><?php _e('Refunds', 'lopas'); ?></h3>
            <table class="refunds-table">
                <thead>
                    <tr>
                        <th><?php _e('Amount', 'lopas'); ?></th>
                        <th><?php _e('Reason', 'lopas'); ?></th>
                        <th><?php _e('Status', 'lopas'); ?></th>
                        <th><?php _e('Date', 'lopas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($refunds as $refund): ?>
                    <tr>
                        <td><?php echo lopas_format_currency($refund->amount); ?></td>
                        <td><?php echo esc_html($refund->reason); ?></td>
                        <td><span class="status-<?php echo esc_attr($refund->status); ?>"><?php echo esc_html($refund->status); ?></span></td>
                        <td><?php echo esc_html(lopas_format_date($refund->created_at)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <div class="payment-actions">
                <?php if ($payment->status !== 'success'): ?>
                <select id="payment_status_select" class="payment-status-select">
                    <option value=""><?php _e('-- Change Status --', 'lopas'); ?></option>
                    <option value="pending" <?php echo $payment->status === 'pending' ? 'selected' : ''; ?>>
                        <?php _e('Pending', 'lopas'); ?>
                    </option>
                    <option value="success" <?php echo $payment->status === 'success' ? 'selected' : ''; ?>>
                        <?php _e('Success', 'lopas'); ?>
                    </option>
                    <option value="failed" <?php echo $payment->status === 'failed' ? 'selected' : ''; ?>>
                        <?php _e('Failed', 'lopas'); ?>
                    </option>
                </select>
                
                <button type="button" class="btn btn-primary" onclick="lopasUpdatePaymentStatus(<?php echo $payment->id; ?>)">
                    <?php _e('Update Status', 'lopas'); ?>
                </button>
                <?php endif; ?>
                
                <?php if ($payment->status === 'success'): ?>
                <button type="button" class="btn btn-warning" onclick="lopasCreateRefund(<?php echo $payment->id; ?>, <?php echo $payment->amount; ?>)">
                    <?php _e('Create Refund', 'lopas'); ?>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get payment refunds
     */
    private static function get_payment_refunds($payment_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('refunds');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE payment_id = %d ORDER BY created_at DESC",
            $payment_id
        ));
    }
    
    /**
     * AJAX: Update payment status
     */
    public function ajax_update_payment_status() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $payment_id = intval($_POST['payment_id']);
        $status = sanitize_text_field($_POST['status']);
        
        if (!$payment_id) {
            wp_send_json_error(__('Invalid payment ID', 'lopas'));
        }
        
        $valid_statuses = array('pending', 'success', 'failed', 'refunded');
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(__('Invalid status', 'lopas'));
        }
        
        $payment = LOPAS_Payment::get($payment_id);
        
        if ($status === 'success') {
            $result = LOPAS_Payment::mark_success($payment_id);
        } elseif ($status === 'failed') {
            $result = LOPAS_Payment::mark_failed($payment_id);
        } else {
            $result = LOPAS_Payment::update($payment_id, array('status' => $status));
        }
        
        if (!$result) {
            wp_send_json_error(__('Failed to update payment', 'lopas'));
        }
        
        wp_send_json_success(array(
            'message' => __('Payment status updated successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Create refund
     */
    public function ajax_create_refund() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $payment_id = intval($_POST['payment_id']);
        $amount = floatval($_POST['amount']);
        $reason = sanitize_text_field($_POST['reason']);
        
        if (!$payment_id) {
            wp_send_json_error(__('Invalid payment ID', 'lopas'));
        }
        
        if ($amount <= 0) {
            wp_send_json_error(__('Refund amount must be greater than 0', 'lopas'));
        }
        
        $refund_id = LOPAS_Payment::create_refund($payment_id, $amount, $reason);
        
        if (!$refund_id) {
            wp_send_json_error(__('Failed to create refund', 'lopas'));
        }
        
        wp_send_json_success(array(
            'refund_id' => $refund_id,
            'message' => __('Refund created successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Get payment details
     */
    public function ajax_get_payment_details() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $payment_id = intval($_POST['payment_id']);
        
        if (!$payment_id) {
            wp_send_json_error(__('Invalid payment ID', 'lopas'));
        }
        
        $payment = LOPAS_Payment::get($payment_id);
        
        if (!$payment) {
            wp_send_json_error(__('Payment not found', 'lopas'));
        }
        
        wp_send_json_success($payment);
    }
}
