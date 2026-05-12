<?php
/**
 * LOPAS Service API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Service_API extends LOPAS_API_Base {
    
    public function register_routes() {
        // Get all services
        register_rest_route($this->namespace, '/services', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_services'),
            'permission_callback' => '__return_true'
        ));
        
        // Get single service
        register_rest_route($this->namespace, '/services/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_service'),
            'permission_callback' => '__return_true'
        ));
        
        // Get services by salon
        register_rest_route($this->namespace, '/salons/(?P<salon_id>\d+)/services', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_salon_services'),
            'permission_callback' => '__return_true'
        ));
        
        // Create service (admin only)
        register_rest_route($this->namespace, '/services', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_service'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Update service (admin only)
        register_rest_route($this->namespace, '/services/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_service'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Delete service (admin only)
        register_rest_route($this->namespace, '/services/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_service'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
    }
    
    /**
     * Get all services
     */
    public function get_services($request) {
        $page = $request->get_param('page') ?? 1;
        $per_page = $request->get_param('per_page') ?? 20;
        $status = $request->get_param('status');
        $category = $request->get_param('category');
        
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        
        $query = "SELECT * FROM {$table}";
        $conditions = array();
        
        if ($status) {
            $conditions[] = $wpdb->prepare("status = %s", $status);
        }
        
        if ($category) {
            $conditions[] = $wpdb->prepare("category = %s", $category);
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $services = $wpdb->get_results($query);
        
        $paginated = $this->paginate_results($services, $page, $per_page);
        
        return $this->success_response($paginated);
    }
    
    /**
     * Get single service
     */
    public function get_service($request) {
        $service_id = $request->get_param('id');
        
        $service = LOPAS_Service::get($service_id);
        
        if (!$service) {
            return $this->error_response('Service not found', 'not_found', 404);
        }
        
        return $this->success_response($service);
    }
    
    /**
     * Get services by salon
     */
    public function get_salon_services($request) {
        $salon_id = $request->get_param('salon_id');
        $page = $request->get_param('page') ?? 1;
        $per_page = $request->get_param('per_page') ?? 20;
        
        global $wpdb;
        $table = LOPAS_Database::get_table('services');
        
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE salon_id = %d AND status = 'active' ORDER BY created_at DESC",
            $salon_id
        ));
        
        $paginated = $this->paginate_results($services, $page, $per_page);
        
        return $this->success_response($paginated);
    }
    
    /**
     * Create service
     */
    public function create_service($request) {
        $params = $request->get_json_params();
        
        $service_id = LOPAS_Service::create(array(
            'salon_id' => $params['salon_id'] ?? 0,
            'name' => $params['name'] ?? '',
            'description' => $params['description'] ?? '',
            'category' => $params['category'] ?? '',
            'price' => $params['price'] ?? 0,
            'duration' => $params['duration'] ?? 30,
            'status' => $params['status'] ?? 'active'
        ));
        
        if (!$service_id) {
            return $this->error_response('Failed to create service', 'creation_failed', 400);
        }
        
        $service = LOPAS_Service::get($service_id);
        
        return $this->success_response($service, 201);
    }
    
    /**
     * Update service
     */
    public function update_service($request) {
        $service_id = $request->get_param('id');
        $params = $request->get_json_params();
        
        $service = LOPAS_Service::get($service_id);
        
        if (!$service) {
            return $this->error_response('Service not found', 'not_found', 404);
        }
        
        $result = LOPAS_Service::update($service_id, $params);
        
        if (!$result) {
            return $this->error_response('Failed to update service', 'update_failed', 400);
        }
        
        $updated_service = LOPAS_Service::get($service_id);
        
        return $this->success_response($updated_service);
    }
    
    /**
     * Delete service
     */
    public function delete_service($request) {
        $service_id = $request->get_param('id');
        
        $service = LOPAS_Service::get($service_id);
        
        if (!$service) {
            return $this->error_response('Service not found', 'not_found', 404);
        }
        
        $result = LOPAS_Service::delete($service_id);
        
        if (!$result) {
            return $this->error_response('Failed to delete service', 'deletion_failed', 400);
        }
        
        return $this->success_response(array('message' => 'Service deleted successfully'));
    }
    
    /**
     * Check admin permission
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
}
