<?php
/**
 * LOPAS Salon API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Salon_API extends LOPAS_API_Base {
    
    public function register_routes() {
        // Get all salons
        register_rest_route($this->namespace, '/salons', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_salons'),
            'permission_callback' => '__return_true'
        ));
        
        // Get single salon
        register_rest_route($this->namespace, '/salons/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_salon'),
            'permission_callback' => '__return_true'
        ));
        
        // Create salon (admin only)
        register_rest_route($this->namespace, '/salons', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_salon'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Update salon (admin only)
        register_rest_route($this->namespace, '/salons/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_salon'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Delete salon (admin only)
        register_rest_route($this->namespace, '/salons/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_salon'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
    }
    
    /**
     * Get all salons
     */
    public function get_salons($request) {
        $page = $request->get_param('page') ?? 1;
        $per_page = $request->get_param('per_page') ?? 20;
        $status = $request->get_param('status');
        
        global $wpdb;
        $table = LOPAS_Database::get_table('salons');
        
        $query = "SELECT * FROM {$table}";
        
        if ($status) {
            $query .= $wpdb->prepare(" WHERE status = %s", $status);
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $salons = $wpdb->get_results($query);
        
        $paginated = $this->paginate_results($salons, $page, $per_page);
        
        return $this->success_response($paginated);
    }
    
    /**
     * Get single salon
     */
    public function get_salon($request) {
        $salon_id = $request->get_param('id');
        
        $salon = LOPAS_Salon::get($salon_id);
        
        if (!$salon) {
            return $this->error_response('Salon not found', 'not_found', 404);
        }
        
        return $this->success_response($salon);
    }
    
    /**
     * Create salon
     */
    public function create_salon($request) {
        $params = $request->get_json_params();
        
        $salon_id = LOPAS_Salon::create(array(
            'name' => $params['name'] ?? '',
            'description' => $params['description'] ?? '',
            'address' => $params['address'] ?? '',
            'phone' => $params['phone'] ?? '',
            'email' => $params['email'] ?? '',
            'opening_time' => $params['opening_time'] ?? '08:00:00',
            'closing_time' => $params['closing_time'] ?? '18:00:00',
            'status' => $params['status'] ?? 'active'
        ));
        
        if (!$salon_id) {
            return $this->error_response('Failed to create salon', 'creation_failed', 400);
        }
        
        $salon = LOPAS_Salon::get($salon_id);
        
        return $this->success_response($salon, 201);
    }
    
    /**
     * Update salon
     */
    public function update_salon($request) {
        $salon_id = $request->get_param('id');
        $params = $request->get_json_params();
        
        $salon = LOPAS_Salon::get($salon_id);
        
        if (!$salon) {
            return $this->error_response('Salon not found', 'not_found', 404);
        }
        
        $result = LOPAS_Salon::update($salon_id, $params);
        
        if (!$result) {
            return $this->error_response('Failed to update salon', 'update_failed', 400);
        }
        
        $updated_salon = LOPAS_Salon::get($salon_id);
        
        return $this->success_response($updated_salon);
    }
    
    /**
     * Delete salon
     */
    public function delete_salon($request) {
        $salon_id = $request->get_param('id');
        
        $salon = LOPAS_Salon::get($salon_id);
        
        if (!$salon) {
            return $this->error_response('Salon not found', 'not_found', 404);
        }
        
        $result = LOPAS_Salon::delete($salon_id);
        
        if (!$result) {
            return $this->error_response('Failed to delete salon', 'deletion_failed', 400);
        }
        
        return $this->success_response(array('message' => 'Salon deleted successfully'));
    }
    
    /**
     * Check admin permission
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
}
