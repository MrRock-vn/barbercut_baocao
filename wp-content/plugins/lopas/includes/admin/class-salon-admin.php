<?php
/**
 * LOPAS Salon Admin Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Salon_Admin {
    
    public function __construct() {
        // AJAX handlers
        add_action('wp_ajax_lopas_add_salon', array($this, 'ajax_add_salon'));
        add_action('wp_ajax_lopas_edit_salon', array($this, 'ajax_edit_salon'));
        add_action('wp_ajax_lopas_delete_salon', array($this, 'ajax_delete_salon'));
        add_action('wp_ajax_lopas_get_salon', array($this, 'ajax_get_salon'));
    }
    
    /**
     * Render salon form
     */
    public static function render_form($salon_id = null) {
        $salon = null;
        $title = __('Add New Salon', 'lopas');
        
        if ($salon_id) {
            $salon = LOPAS_Salon::get($salon_id);
            $title = __('Edit Salon', 'lopas');
        }
        
        ?>
        <div class="lopas-form-container">
            <h2><?php echo esc_html($title); ?></h2>
            
            <form id="lopas-salon-form" class="lopas-form">
                <?php wp_nonce_field('lopas_salon_nonce'); ?>
                
                <input type="hidden" name="salon_id" value="<?php echo $salon ? $salon->id : ''; ?>">
                
                <div class="form-group">
                    <label for="salon_name"><?php _e('Salon Name', 'lopas'); ?> <span class="required">*</span></label>
                    <input type="text" id="salon_name" name="name" required 
                           value="<?php echo $salon ? esc_attr($salon->name) : ''; ?>" 
                           placeholder="<?php _e('Enter salon name', 'lopas'); ?>">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label for="salon_description"><?php _e('Description', 'lopas'); ?></label>
                    <textarea id="salon_description" name="description" rows="4"
                              placeholder="<?php _e('Enter salon description', 'lopas'); ?>"><?php echo $salon ? esc_textarea($salon->description) : ''; ?></textarea>
                    <span class="error-message"></span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="salon_address"><?php _e('Address', 'lopas'); ?></label>
                        <input type="text" id="salon_address" name="address" 
                               value="<?php echo $salon ? esc_attr($salon->address) : ''; ?>"
                               placeholder="<?php _e('Enter address', 'lopas'); ?>">
                        <span class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="salon_phone"><?php _e('Phone', 'lopas'); ?></label>
                        <input type="tel" id="salon_phone" name="phone" 
                               value="<?php echo $salon ? esc_attr($salon->phone) : ''; ?>"
                               placeholder="<?php _e('0123456789', 'lopas'); ?>">
                        <span class="error-message"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="salon_email"><?php _e('Email', 'lopas'); ?></label>
                        <input type="email" id="salon_email" name="email" 
                               value="<?php echo $salon ? esc_attr($salon->email) : ''; ?>"
                               placeholder="<?php _e('email@example.com', 'lopas'); ?>">
                        <span class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="salon_status"><?php _e('Status', 'lopas'); ?></label>
                        <select id="salon_status" name="status">
                            <option value="inactive" <?php echo $salon && $salon->status === 'inactive' ? 'selected' : ''; ?>>
                                <?php _e('Inactive', 'lopas'); ?>
                            </option>
                            <option value="active" <?php echo $salon && $salon->status === 'active' ? 'selected' : ''; ?>>
                                <?php _e('Active', 'lopas'); ?>
                            </option>
                            <option value="suspended" <?php echo $salon && $salon->status === 'suspended' ? 'selected' : ''; ?>>
                                <?php _e('Suspended', 'lopas'); ?>
                            </option>
                        </select>
                        <span class="error-message"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="salon_opening_time"><?php _e('Opening Time', 'lopas'); ?></label>
                        <input type="time" id="salon_opening_time" name="opening_time" 
                               value="<?php echo $salon ? esc_attr($salon->opening_time) : '08:00'; ?>">
                        <span class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="salon_closing_time"><?php _e('Closing Time', 'lopas'); ?></label>
                        <input type="time" id="salon_closing_time" name="closing_time" 
                               value="<?php echo $salon ? esc_attr($salon->closing_time) : '18:00'; ?>">
                        <span class="error-message"></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $salon ? __('Update Salon', 'lopas') : __('Add Salon', 'lopas'); ?>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="lopasCloseModal()">
                        <?php _e('Cancel', 'lopas'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * AJAX: Add salon
     */
    public function ajax_add_salon() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $data = array(
            'user_id' => get_current_user_id(),
            'name' => sanitize_text_field($_POST['name']),
            'description' => wp_kses_post($_POST['description']),
            'address' => sanitize_text_field($_POST['address']),
            'phone' => sanitize_text_field($_POST['phone']),
            'email' => sanitize_email($_POST['email']),
            'opening_time' => sanitize_text_field($_POST['opening_time']),
            'closing_time' => sanitize_text_field($_POST['closing_time']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        // Validate
        if (empty($data['name'])) {
            wp_send_json_error(__('Salon name is required', 'lopas'));
        }
        
        if (!empty($data['email']) && !is_email($data['email'])) {
            wp_send_json_error(__('Invalid email address', 'lopas'));
        }
        
        if (!empty($data['phone']) && !lopas_is_valid_phone($data['phone'])) {
            wp_send_json_error(__('Invalid phone number', 'lopas'));
        }
        
        $salon_id = LOPAS_Salon::create($data);
        
        if (!$salon_id) {
            wp_send_json_error(__('Failed to create salon', 'lopas'));
        }
        
        wp_send_json_success(array(
            'salon_id' => $salon_id,
            'message' => __('Salon created successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Edit salon
     */
    public function ajax_edit_salon() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $salon_id = intval($_POST['salon_id']);
        
        if (!$salon_id) {
            wp_send_json_error(__('Invalid salon ID', 'lopas'));
        }
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'description' => wp_kses_post($_POST['description']),
            'address' => sanitize_text_field($_POST['address']),
            'phone' => sanitize_text_field($_POST['phone']),
            'email' => sanitize_email($_POST['email']),
            'opening_time' => sanitize_text_field($_POST['opening_time']),
            'closing_time' => sanitize_text_field($_POST['closing_time']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        // Validate
        if (empty($data['name'])) {
            wp_send_json_error(__('Salon name is required', 'lopas'));
        }
        
        if (!empty($data['email']) && !is_email($data['email'])) {
            wp_send_json_error(__('Invalid email address', 'lopas'));
        }
        
        if (!empty($data['phone']) && !lopas_is_valid_phone($data['phone'])) {
            wp_send_json_error(__('Invalid phone number', 'lopas'));
        }
        
        $result = LOPAS_Salon::update($salon_id, $data);
        
        if (!$result) {
            wp_send_json_error(__('Failed to update salon', 'lopas'));
        }
        
        wp_send_json_success(array(
            'message' => __('Salon updated successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Delete salon
     */
    public function ajax_delete_salon() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $salon_id = intval($_POST['salon_id']);
        
        if (!$salon_id) {
            wp_send_json_error(__('Invalid salon ID', 'lopas'));
        }
        
        $result = LOPAS_Salon::delete($salon_id);
        
        if (!$result) {
            wp_send_json_error(__('Failed to delete salon', 'lopas'));
        }
        
        wp_send_json_success(array(
            'message' => __('Salon deleted successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Get salon
     */
    public function ajax_get_salon() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $salon_id = intval($_POST['salon_id']);
        
        if (!$salon_id) {
            wp_send_json_error(__('Invalid salon ID', 'lopas'));
        }
        
        $salon = LOPAS_Salon::get($salon_id);
        
        if (!$salon) {
            wp_send_json_error(__('Salon not found', 'lopas'));
        }
        
        wp_send_json_success($salon);
    }
}
