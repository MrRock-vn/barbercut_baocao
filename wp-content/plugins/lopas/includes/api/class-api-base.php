<?php
/**
 * LOPAS REST API Base Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_API_Base {
    
    protected $namespace = 'lopas/v1';
    protected $version = '1.0.0';
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register API routes
     */
    public function register_routes() {
        // Override in child classes
    }
    
    /**
     * Check API key
     */
    protected function verify_api_key($request) {
        $api_key = $request->get_header('X-API-Key');
        
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'API key is required', array('status' => 401));
        }
        
        $stored_key = get_option('lopas_api_key');
        
        if (empty($stored_key) || $api_key !== $stored_key) {
            return new WP_Error('invalid_api_key', 'Invalid API key', array('status' => 401));
        }
        
        return true;
    }
    
    /**
     * Check user authentication
     */
    protected function verify_user($request) {
        $user = wp_get_current_user();
        
        if (!$user->ID) {
            return new WP_Error('not_authenticated', 'User not authenticated', array('status' => 401));
        }
        
        return $user;
    }
    
    /**
     * Success response
     */
    protected function success_response($data, $status = 200) {
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $data
        ), $status);
    }
    
    /**
     * Error response
     */
    protected function error_response($message, $code = 'error', $status = 400) {
        return new WP_Error($code, $message, array('status' => $status));
    }
    
    /**
     * Paginate results
     */
    protected function paginate_results($items, $page = 1, $per_page = 20) {
        $total = count($items);
        $offset = ($page - 1) * $per_page;
        $paginated = array_slice($items, $offset, $per_page);
        
        return array(
            'items' => $paginated,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        );
    }
}
