<?php
/**
 * LOPAS Order Admin Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Order_Admin {
    
    public function __construct() {
        // AJAX handlers
        add_action('wp_ajax_lopas_update_order_status', array($this, 'ajax_update_order_status'));
        add_action('wp_ajax_lopas_get_order_details', array($this, 'ajax_get_order_details'));
    }
    
    /**
     * Render order details modal
     */
    public static function render_details($order_id) {
        $order = LOPAS_Order::get($order_id);
        
        if (!$order) {
            return;
        }
        
        $user = get_userdata($order->user_id);
        $items = LOPAS_Order::get_items($order_id);
        
        ?>
        <div class="lopas-modal-content">
            <h2><?php _e('Order Details', 'lopas'); ?></h2>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="label"><?php _e('Order Code:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html($order->order_code); ?></span>
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
                    <span class="label"><?php _e('Total Price:', 'lopas'); ?></span>
                    <span class="value"><?php echo lopas_format_currency($order->total_price); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Discount:', 'lopas'); ?></span>
                    <span class="value"><?php echo lopas_format_currency($order->discount_amount); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Final Price:', 'lopas'); ?></span>
                    <span class="value"><strong><?php echo lopas_format_currency($order->final_price); ?></strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Payment Method:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html($order->payment_method); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Payment Status:', 'lopas'); ?></span>
                    <span class="value">
                        <span class="status-<?php echo esc_attr($order->payment_status); ?>">
                            <?php echo esc_html($order->payment_status); ?>
                        </span>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Order Status:', 'lopas'); ?></span>
                    <span class="value">
                        <span class="status-<?php echo esc_attr($order->order_status); ?>">
                            <?php echo esc_html($order->order_status); ?>
                        </span>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="label"><?php _e('Created:', 'lopas'); ?></span>
                    <span class="value"><?php echo esc_html(lopas_format_date($order->created_at)); ?></span>
                </div>
            </div>
            
            <h3><?php _e('Order Items', 'lopas'); ?></h3>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th><?php _e('Service', 'lopas'); ?></th>
                        <th><?php _e('Price', 'lopas'); ?></th>
                        <th><?php _e('Quantity', 'lopas'); ?></th>
                        <th><?php _e('Subtotal', 'lopas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($items)) {
                        echo '<tr><td colspan="4">' . __('No items', 'lopas') . '</td></tr>';
                    } else {
                        foreach ($items as $item) {
                            $service = LOPAS_Service::get($item->service_id);
                            echo '<tr>';
                            echo '<td>' . ($service ? esc_html($service->name) : '-') . '</td>';
                            echo '<td>' . lopas_format_currency($item->price) . '</td>';
                            echo '<td>' . $item->quantity . '</td>';
                            echo '<td>' . lopas_format_currency($item->subtotal) . '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
            
            <div class="order-actions">
                <select id="order_status_select" class="order-status-select">
                    <option value=""><?php _e('-- Change Status --', 'lopas'); ?></option>
                    <option value="pending" <?php echo $order->order_status === 'pending' ? 'selected' : ''; ?>>
                        <?php _e('Pending', 'lopas'); ?>
                    </option>
                    <option value="confirmed" <?php echo $order->order_status === 'confirmed' ? 'selected' : ''; ?>>
                        <?php _e('Confirmed', 'lopas'); ?>
                    </option>
                    <option value="completed" <?php echo $order->order_status === 'completed' ? 'selected' : ''; ?>>
                        <?php _e('Completed', 'lopas'); ?>
                    </option>
                    <option value="cancelled" <?php echo $order->order_status === 'cancelled' ? 'selected' : ''; ?>>
                        <?php _e('Cancelled', 'lopas'); ?>
                    </option>
                </select>
                
                <button type="button" class="btn btn-primary" onclick="lopasUpdateOrderStatus(<?php echo $order->id; ?>)">
                    <?php _e('Update Status', 'lopas'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Update order status
     */
    public function ajax_update_order_status() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $order_id = intval($_POST['order_id']);
        $status = sanitize_text_field($_POST['status']);
        
        if (!$order_id) {
            wp_send_json_error(__('Invalid order ID', 'lopas'));
        }
        
        $valid_statuses = array('pending', 'confirmed', 'completed', 'cancelled');
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(__('Invalid status', 'lopas'));
        }
        
        $result = LOPAS_Order::update($order_id, array('order_status' => $status));
        
        if (!$result) {
            wp_send_json_error(__('Failed to update order', 'lopas'));
        }
        
        wp_send_json_success(array(
            'message' => __('Order status updated successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Get order details
     */
    public function ajax_get_order_details() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $order_id = intval($_POST['order_id']);
        
        if (!$order_id) {
            wp_send_json_error(__('Invalid order ID', 'lopas'));
        }
        
        $order = LOPAS_Order::get($order_id);
        
        if (!$order) {
            wp_send_json_error(__('Order not found', 'lopas'));
        }
        
        wp_send_json_success($order);
    }
}
