<?php
/**
 * LOPAS Service Model
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Service {
    
    /**
     * Get service by ID
     * 
     * @param int $service_id Service ID
     * @return object|null Service object or null
     */
    public static function get($service_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $service_id));
    }
    
    /**
     * Get services by salon
     * 
     * @param int $salon_id Salon ID
     * @param array $args Query arguments
     * @return array Array of service objects
     */
    public static function get_by_salon($salon_id, $args = array()) {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        
        $defaults = array(
            'status' => 'active',
            'category' => null,
            'orderby' => 'name',
            'order' => 'ASC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query = $wpdb->prepare("SELECT * FROM {$table} WHERE salon_id = %d", $salon_id);
        
        if ($args['status']) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        if ($args['category']) {
            $query .= $wpdb->prepare(" AND category = %s", $args['category']);
        }
        
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Create new service
     * 
     * @param array $data Service data
     * @return int|false Service ID or false on error
     */
    public static function create($data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        
        $defaults = array(
            'salon_id' => 0,
            'name' => '',
            'description' => '',
            'category' => '',
            'price' => 0,
            'duration' => 30,
            'image_id' => null,
            'status' => 'active'
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['salon_id']) || empty($data['name']) || empty($data['price'])) {
            return false;
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'salon_id' => intval($data['salon_id']),
                'name' => sanitize_text_field($data['name']),
                'description' => wp_kses_post($data['description']),
                'category' => sanitize_text_field($data['category']),
                'price' => floatval($data['price']),
                'duration' => intval($data['duration']),
                'image_id' => intval($data['image_id']),
                'status' => sanitize_text_field($data['status'])
            ),
            array('%d', '%s', '%s', '%s', '%f', '%d', '%d', '%s')
        );
        
        if ($result) {
            do_action('lopas_service_created', $wpdb->insert_id, $data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update service
     * 
     * @param int $service_id Service ID
     * @param array $data Service data to update
     * @return bool True on success, false on error
     */
    public static function update($service_id, $data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
            $update_format[] = '%s';
        }
        if (isset($data['description'])) {
            $update_data['description'] = wp_kses_post($data['description']);
            $update_format[] = '%s';
        }
        if (isset($data['category'])) {
            $update_data['category'] = sanitize_text_field($data['category']);
            $update_format[] = '%s';
        }
        if (isset($data['price'])) {
            $update_data['price'] = floatval($data['price']);
            $update_format[] = '%f';
        }
        if (isset($data['duration'])) {
            $update_data['duration'] = intval($data['duration']);
            $update_format[] = '%d';
        }
        if (isset($data['image_id'])) {
            $update_data['image_id'] = intval($data['image_id']);
            $update_format[] = '%d';
        }
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            $update_format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table,
            $update_data,
            array('id' => intval($service_id)),
            $update_format,
            array('%d')
        );
        
        if ($result !== false) {
            do_action('lopas_service_updated', $service_id, $data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete service
     * 
     * @param int $service_id Service ID
     * @return bool True on success, false on error
     */
    public static function delete($service_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        
        $result = $wpdb->delete(
            $table,
            array('id' => intval($service_id)),
            array('%d')
        );
        
        if ($result) {
            do_action('lopas_service_deleted', $service_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get service categories
     * 
     * @param int $salon_id Salon ID
     * @return array Array of category names
     */
    public static function get_categories($salon_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT category FROM {$table} WHERE salon_id = %d AND status = 'active' ORDER BY category ASC",
            $salon_id
        ));
        
        return wp_list_pluck($results, 'category');
    }
    
    /**
     * Count services by salon
     * 
     * @param int $salon_id Salon ID
     * @param string $status Service status filter
     * @return int Total count
     */
    public static function count($salon_id, $status = null) {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        
        $query = $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE salon_id = %d", $salon_id);
        
        if ($status) {
            $query .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        return intval($wpdb->get_var($query));
    }
}
