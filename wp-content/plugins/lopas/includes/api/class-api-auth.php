<?php
/**
 * LOPAS API Authentication
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_API_Auth {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_auth_routes'));
    }
    
    /**
     * Register authentication routes
     */
    public function register_auth_routes() {
        register_rest_route('lopas/v1', '/auth/login', array(
            'methods' => 'POST',
            'callback' => array($this, 'login'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('lopas/v1', '/auth/logout', array(
            'methods' => 'POST',
            'callback' => array($this, 'logout'),
            'permission_callback' => array($this, 'check_auth')
        ));
        
        register_rest_route('lopas/v1', '/auth/me', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_current_user'),
            'permission_callback' => array($this, 'check_auth')
        ));
        
        register_rest_route('lopas/v1', '/auth/refresh', array(
            'methods' => 'POST',
            'callback' => array($this, 'refresh_token'),
            'permission_callback' => array($this, 'check_auth')
        ));
    }
    
    /**
     * Login endpoint
     */
    public function login($request) {
        $params = $request->get_json_params();
        
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            return new WP_Error('missing_credentials', 'Username and password are required', array('status' => 400));
        }
        
        $user = wp_authenticate($username, $password);
        
        if (is_wp_error($user)) {
            return new WP_Error('invalid_credentials', 'Invalid username or password', array('status' => 401));
        }
        
        // Generate JWT token
        $token = $this->generate_token($user->ID);
        
        return new WP_REST_Response(array(
            'success' => true,
            'token' => $token,
            'user' => array(
                'id' => $user->ID,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'name' => $user->display_name
            )
        ), 200);
    }
    
    /**
     * Logout endpoint
     */
    public function logout($request) {
        // Invalidate token by removing from cache
        $user = wp_get_current_user();
        delete_user_meta($user->ID, 'lopas_api_token');
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Logged out successfully'
        ), 200);
    }
    
    /**
     * Get current user
     */
    public function get_current_user($request) {
        $user = wp_get_current_user();
        
        return new WP_REST_Response(array(
            'success' => true,
            'user' => array(
                'id' => $user->ID,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'name' => $user->display_name,
                'roles' => $user->roles
            )
        ), 200);
    }
    
    /**
     * Refresh token
     */
    public function refresh_token($request) {
        $user = wp_get_current_user();
        
        $token = $this->generate_token($user->ID);
        
        return new WP_REST_Response(array(
            'success' => true,
            'token' => $token
        ), 200);
    }
    
    /**
     * Generate JWT token
     */
    private function generate_token($user_id) {
        $header = array(
            'alg' => 'HS256',
            'typ' => 'JWT'
        );
        
        $payload = array(
            'user_id' => $user_id,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60) // 7 days
        );
        
        $secret = get_option('lopas_api_secret', wp_salt('auth'));
        
        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $secret, true);
        $signature_encoded = $this->base64url_encode($signature);
        
        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }
    
    /**
     * Verify JWT token
     */
    public function verify_token($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
        
        $secret = get_option('lopas_api_secret', wp_salt('auth'));
        
        $signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $secret, true);
        $signature_expected = $this->base64url_encode($signature);
        
        if (!hash_equals($signature_encoded, $signature_expected)) {
            return false;
        }
        
        $payload = json_decode($this->base64url_decode($payload_encoded), true);
        
        if ($payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Check authentication
     */
    public function check_auth($request) {
        $token = $request->get_header('Authorization');
        
        if (empty($token)) {
            return new WP_Error('missing_token', 'Authorization token is required', array('status' => 401));
        }
        
        // Remove "Bearer " prefix
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        $payload = $this->verify_token($token);
        
        if (!$payload) {
            return new WP_Error('invalid_token', 'Invalid or expired token', array('status' => 401));
        }
        
        // Set current user
        wp_set_current_user($payload['user_id']);
        
        return true;
    }
    
    /**
     * Base64 URL encode
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }
}
