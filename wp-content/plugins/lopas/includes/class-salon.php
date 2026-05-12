<?php
/**
 * LOPAS Salon Model
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Salon {
    
    /**
     * Get salon by ID
     * 
     * @param int $salon_id Salon ID
     * @return object|null Salon object or null
     */
    public static function get($salon_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $salon_id));
    }
    
    /**
     * Get all salons
     * 
     * @param array $args Query arguments
     * @return array Array of salon objects
     */
    public static function get_all($args = array()) {
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        
        $defaults = array(
            'status' => 'active',
            'limit' => -1,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'keyword' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query = "SELECT * FROM {$table}";
        $where = array();
        
        if ($args['status']) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }
        
        if ($args['keyword']) {
            $like = '%' . $wpdb->esc_like($args['keyword']) . '%';
            $where[] = $wpdb->prepare("(name LIKE %s OR address LIKE %s OR description LIKE %s)", $like, $like, $like);
        }
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get salon by user ID
     * 
     * @param int $user_id User ID
     * @return object|null Salon object or null
     */
    public static function get_by_user($user_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE user_id = %d", $user_id));
    }
    
    /**
     * Create new salon
     * 
     * @param array $data Salon data
     * @return int|false Salon ID or false on error
     */
    public static function create($data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        
        $defaults = array(
            'user_id' => get_current_user_id(),
            'name' => '',
            'description' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'latitude' => null,
            'longitude' => null,
            'avatar_id' => null,
            'cover_id' => null,
            'opening_time' => '08:00:00',
            'closing_time' => '18:00:00',
            'status' => 'inactive'
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['name']) || empty($data['user_id'])) {
            return false;
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => intval($data['user_id']),
                'name' => sanitize_text_field($data['name']),
                'description' => wp_kses_post($data['description']),
                'address' => sanitize_text_field($data['address']),
                'phone' => sanitize_text_field($data['phone']),
                'email' => sanitize_email($data['email']),
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'avatar_id' => intval($data['avatar_id']),
                'cover_id' => intval($data['cover_id']),
                'opening_time' => sanitize_text_field($data['opening_time']),
                'closing_time' => sanitize_text_field($data['closing_time']),
                'status' => sanitize_text_field($data['status'])
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%d', '%d', '%s', '%s', '%s')
        );
        
        if ($result) {
            do_action('lopas_salon_created', $wpdb->insert_id, $data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update salon
     * 
     * @param int $salon_id Salon ID
     * @param array $data Salon data to update
     * @return bool True on success, false on error
     */
    public static function update($salon_id, $data) {
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        
        // Sanitize data
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
        if (isset($data['address'])) {
            $update_data['address'] = sanitize_text_field($data['address']);
            $update_format[] = '%s';
        }
        if (isset($data['phone'])) {
            $update_data['phone'] = sanitize_text_field($data['phone']);
            $update_format[] = '%s';
        }
        if (isset($data['email'])) {
            $update_data['email'] = sanitize_email($data['email']);
            $update_format[] = '%s';
        }
        if (isset($data['latitude'])) {
            $update_data['latitude'] = $data['latitude'];
            $update_format[] = '%f';
        }
        if (isset($data['longitude'])) {
            $update_data['longitude'] = $data['longitude'];
            $update_format[] = '%f';
        }
        if (isset($data['avatar_id'])) {
            $update_data['avatar_id'] = intval($data['avatar_id']);
            $update_format[] = '%d';
        }
        if (isset($data['cover_id'])) {
            $update_data['cover_id'] = intval($data['cover_id']);
            $update_format[] = '%d';
        }
        if (isset($data['opening_time'])) {
            $update_data['opening_time'] = sanitize_text_field($data['opening_time']);
            $update_format[] = '%s';
        }
        if (isset($data['closing_time'])) {
            $update_data['closing_time'] = sanitize_text_field($data['closing_time']);
            $update_format[] = '%s';
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
            array('id' => intval($salon_id)),
            $update_format,
            array('%d')
        );
        
        if ($result !== false) {
            do_action('lopas_salon_updated', $salon_id, $data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete salon
     * 
     * @param int $salon_id Salon ID
     * @return bool True on success, false on error
     */
    public static function delete($salon_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        
        $result = $wpdb->delete(
            $table,
            array('id' => intval($salon_id)),
            array('%d')
        );
        
        if ($result) {
            do_action('lopas_salon_deleted', $salon_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get salon services
     * 
     * @param int $salon_id Salon ID
     * @return array Array of service objects
     */
    public static function get_services($salon_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE salon_id = %d AND status = 'active' ORDER BY name ASC",
            $salon_id
        ));
    }
    
    /**
     * Get salon staff
     * 
     * @param int $salon_id Salon ID
     * @return array Array of staff objects
     */
    public static function get_staff($salon_id) {
        global $wpdb;
        $table = LOPAS_Database::get_table('staff');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE salon_id = %d AND status = 'active' ORDER BY name ASC",
            $salon_id
        ));
    }
    
    /**
     * Count total salons
     * 
     * @param string $status Salon status filter
     * @return int Total count
     */
    public static function count($status = null) {
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        
        $query = "SELECT COUNT(*) FROM {$table}";
        
        if ($status) {
            $query .= $wpdb->prepare(" WHERE status = %s", $status);
        }
        
        return intval($wpdb->get_var($query));
    }
}
