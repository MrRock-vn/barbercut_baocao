<?php
/**
 * LOPAS Service Admin Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Service_Admin {
    
    public function __construct() {
        // AJAX handlers
        add_action('wp_ajax_lopas_add_service', array($this, 'ajax_add_service'));
        add_action('wp_ajax_lopas_edit_service', array($this, 'ajax_edit_service'));
        add_action('wp_ajax_lopas_delete_service', array($this, 'ajax_delete_service'));
        add_action('wp_ajax_lopas_get_service', array($this, 'ajax_get_service'));
    }
    
    /**
     * Render service form
     */
    public static function render_form($service_id = null, $salon_id = null) {
        $service = null;
        $title = __('Add New Service', 'lopas');
        
        if ($service_id) {
            $service = LOPAS_Service::get($service_id);
            $title = __('Edit Service', 'lopas');
            $salon_id = $service->salon_id;
        }
        
        ?>
        <div class="lopas-form-container">
            <h2><?php echo esc_html($title); ?></h2>
            
            <form id="lopas-service-form" class="lopas-form">
                <?php wp_nonce_field('lopas_service_nonce'); ?>
                
                <input type="hidden" name="service_id" value="<?php echo $service ? $service->id : ''; ?>">
                
                <div class="form-group">
                    <label for="service_salon_id"><?php _e('Salon', 'lopas'); ?> <span class="required">*</span></label>
                    <select id="service_salon_id" name="salon_id" required>
                        <option value=""><?php _e('-- Select Salon --', 'lopas'); ?></option>
                        <?php
                        $salons = LOPAS_Salon::get_all(array('limit' => -1));
                        foreach ($salons as $salon) {
                            $selected = $salon_id && $salon->id == $salon_id ? 'selected' : '';
                            echo '<option value="' . $salon->id . '" ' . $selected . '>' . esc_html($salon->name) . '</option>';
                        }
                        ?>
                    </select>
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label for="service_name"><?php _e('Service Name', 'lopas'); ?> <span class="required">*</span></label>
                    <input type="text" id="service_name" name="name" required 
                           value="<?php echo $service ? esc_attr($service->name) : ''; ?>"
                           placeholder="<?php _e('Enter service name', 'lopas'); ?>">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label for="service_description"><?php _e('Description', 'lopas'); ?></label>
                    <textarea id="service_description" name="description" rows="4"
                              placeholder="<?php _e('Enter service description', 'lopas'); ?>"><?php echo $service ? esc_textarea($service->description) : ''; ?></textarea>
                    <span class="error-message"></span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="service_category"><?php _e('Category', 'lopas'); ?></label>
                        <input type="text" id="service_category" name="category" 
                               value="<?php echo $service ? esc_attr($service->category) : ''; ?>"
                               placeholder="<?php _e('e.g., Hair, Beard', 'lopas'); ?>">
                        <span class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="service_price"><?php _e('Price (VND)', 'lopas'); ?> <span class="required">*</span></label>
                        <input type="number" id="service_price" name="price" required step="1000"
                               value="<?php echo $service ? $service->price : ''; ?>"
                               placeholder="<?php _e('0', 'lopas'); ?>">
                        <span class="error-message"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="service_duration"><?php _e('Duration (minutes)', 'lopas'); ?> <span class="required">*</span></label>
                        <input type="number" id="service_duration" name="duration" required min="5" step="5"
                               value="<?php echo $service ? $service->duration : '30'; ?>"
                               placeholder="<?php _e('30', 'lopas'); ?>">
                        <span class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="service_status"><?php _e('Status', 'lopas'); ?></label>
                        <select id="service_status" name="status">
                            <option value="inactive" <?php echo $service && $service->status === 'inactive' ? 'selected' : ''; ?>>
                                <?php _e('Inactive', 'lopas'); ?>
                            </option>
                            <option value="active" <?php echo !$service || $service->status === 'active' ? 'selected' : ''; ?>>
                                <?php _e('Active', 'lopas'); ?>
                            </option>
                        </select>
                        <span class="error-message"></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $service ? __('Update Service', 'lopas') : __('Add Service', 'lopas'); ?>
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
     * AJAX: Add service
     */
    public function ajax_add_service() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $data = array(
            'salon_id' => intval($_POST['salon_id']),
            'name' => sanitize_text_field($_POST['name']),
            'description' => wp_kses_post($_POST['description']),
            'category' => sanitize_text_field($_POST['category']),
            'price' => floatval($_POST['price']),
            'duration' => intval($_POST['duration']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        // Validate
        if (empty($data['salon_id'])) {
            wp_send_json_error(__('Salon is required', 'lopas'));
        }
        
        if (empty($data['name'])) {
            wp_send_json_error(__('Service name is required', 'lopas'));
        }
        
        if ($data['price'] <= 0) {
            wp_send_json_error(__('Price must be greater than 0', 'lopas'));
        }
        
        if ($data['duration'] < 5) {
            wp_send_json_error(__('Duration must be at least 5 minutes', 'lopas'));
        }
        
        $service_id = LOPAS_Service::create($data);
        
        if (!$service_id) {
            wp_send_json_error(__('Failed to create service', 'lopas'));
        }
        
        wp_send_json_success(array(
            'service_id' => $service_id,
            'message' => __('Service created successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Edit service
     */
    public function ajax_edit_service() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $service_id = intval($_POST['service_id']);
        
        if (!$service_id) {
            wp_send_json_error(__('Invalid service ID', 'lopas'));
        }
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'description' => wp_kses_post($_POST['description']),
            'category' => sanitize_text_field($_POST['category']),
            'price' => floatval($_POST['price']),
            'duration' => intval($_POST['duration']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        // Validate
        if (empty($data['name'])) {
            wp_send_json_error(__('Service name is required', 'lopas'));
        }
        
        if ($data['price'] <= 0) {
            wp_send_json_error(__('Price must be greater than 0', 'lopas'));
        }
        
        if ($data['duration'] < 5) {
            wp_send_json_error(__('Duration must be at least 5 minutes', 'lopas'));
        }
        
        $result = LOPAS_Service::update($service_id, $data);
        
        if (!$result) {
            wp_send_json_error(__('Failed to update service', 'lopas'));
        }
        
        wp_send_json_success(array(
            'message' => __('Service updated successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Delete service
     */
    public function ajax_delete_service() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $service_id = intval($_POST['service_id']);
        
        if (!$service_id) {
            wp_send_json_error(__('Invalid service ID', 'lopas'));
        }
        
        $result = LOPAS_Service::delete($service_id);
        
        if (!$result) {
            wp_send_json_error(__('Failed to delete service', 'lopas'));
        }
        
        wp_send_json_success(array(
            'message' => __('Service deleted successfully', 'lopas')
        ));
    }
    
    /**
     * AJAX: Get service
     */
    public function ajax_get_service() {
        check_ajax_referer('lopas_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'lopas'));
        }
        
        $service_id = intval($_POST['service_id']);
        
        if (!$service_id) {
            wp_send_json_error(__('Invalid service ID', 'lopas'));
        }
        
        $service = LOPAS_Service::get($service_id);
        
        if (!$service) {
            wp_send_json_error(__('Service not found', 'lopas'));
        }
        
        wp_send_json_success($service);
    }
}
