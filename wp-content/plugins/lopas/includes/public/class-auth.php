<?php
/**
 * LOPAS Auth (Login/Register)
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Auth {
    
    /**
     * Auth errors
     */
    private static $errors = array();
    
    /**
     * Initialize
     */
    public static function init() {
        add_shortcode('lopas_login', array(__CLASS__, 'render_login'));
        add_shortcode('lopas_register', array(__CLASS__, 'render_register'));
        
        add_action('init', array(__CLASS__, 'process_auth_forms'));
    }
    
    /**
     * Process auth forms
     */
    public static function process_auth_forms() {
        if (isset($_POST['lopas_login_submit']) && wp_verify_nonce($_POST['lopas_auth_nonce'], 'lopas_login_action')) {
            self::process_login();
        }
        
        if (isset($_POST['lopas_register_submit']) && wp_verify_nonce($_POST['lopas_auth_nonce'], 'lopas_register_action')) {
            self::process_register();
        }
    }
    
    /**
     * Process login
     */
    private static function process_login() {
        if (is_user_logged_in()) {
            return;
        }
        
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            self::$errors[] = 'Vui lòng nhập đầy đủ thông tin.';
            return;
        }
        
        // Find user by email
        $user = get_user_by('email', $email);
        $username = $user ? $user->user_login : $email; // fallback to email if not found
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true
        );
        
        $user = wp_signon($creds, false);
        
        if (is_wp_error($user)) {
            self::$errors[] = 'Email hoặc mật khẩu không đúng.';
        } else {
            // Redirect after successful login
            wp_redirect(home_url('/'));
            exit;
        }
    }
    
    /**
     * Process register
     */
    private static function process_register() {
        if (is_user_logged_in()) {
            return;
        }
        
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $password_confirmation = $_POST['password_confirmation'];
        
        if (empty($name) || empty($email) || empty($password) || empty($password_confirmation)) {
            self::$errors[] = 'Vui lòng điền đầy đủ thông tin.';
            return;
        }
        
        if (!is_email($email)) {
            self::$errors[] = 'Email không hợp lệ.';
            return;
        }
        
        if ($password !== $password_confirmation) {
            self::$errors[] = 'Mật khẩu không khớp.';
            return;
        }
        
        if (email_exists($email)) {
            self::$errors[] = 'Email này đã được sử dụng.';
            return;
        }
        
        // Generate a username from email
        $username = sanitize_user(current(explode('@', $email)), true);
        if (username_exists($username)) {
            $username .= '_' . rand(100, 999);
        }
        
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            self::$errors[] = $user_id->get_error_message();
        } else {
            // Update name
            $name_parts = explode(' ', $name, 2);
            wp_update_user(array(
                'ID' => $user_id,
                'first_name' => $name_parts[0],
                'last_name' => isset($name_parts[1]) ? $name_parts[1] : '',
                'display_name' => $name
            ));
            
            // Auto login after registration
            $creds = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => true
            );
            wp_signon($creds, false);
            
            wp_redirect(home_url('/'));
            exit;
        }
    }
    
    /**
     * Render login form
     */
    public static function render_login() {
        if (is_user_logged_in()) {
            return '<p>Bạn đã đăng nhập.</p>';
        }
        
        $output = LOPAS_Public::get_global_header();
        ob_start();
        ?>
        <section class="auth-page">
            <div class="container py-5">
                <div class="auth-panel">
                    <div class="auth-card">
                        <div class="auth-card-side">
                            <div class="auth-brand">BARBER SPA</div>
                            <h3 class="auth-side-title">Chào mừng trở lại</h3>
                            <p class="auth-side-text">Đăng nhập để quản lý lịch hẹn nhanh gọn, ưu đãi trực tiếp và thông tin cá nhân an toàn.</p>
                            <ul class="auth-features">
                                <li class="auth-feature-item">Quản lý lịch hẹn mọi lúc</li>
                                <li class="auth-feature-item">Lưu thông tin cá nhân</li>
                                <li class="auth-feature-item">Thanh toán nhanh và bảo mật</li>
                            </ul>
                        </div>
                        <div class="auth-card-body">
                            
                            <?php if (!empty(self::$errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach (self::$errors as $error) echo esc_html($error) . '<br>'; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <?php wp_nonce_field('lopas_login_action', 'lopas_auth_nonce'); ?>
                                <input type="hidden" name="lopas_login_submit" value="1">
                                
                                <div class="mb-3 auth-form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control auth-form-control" value="<?php echo isset($_POST['email']) ? esc_attr($_POST['email']) : ''; ?>" placeholder="mail@domain.com" required autocomplete="email">
                                </div>
        
                                <div class="mb-4 auth-form-group">
                                    <label class="form-label">Mật khẩu</label>
                                    <input type="password" name="password" class="form-control auth-form-control" placeholder="Nhập mật khẩu" required autocomplete="current-password">
                                </div>
        
                                <button type="submit" class="btn btn-primary w-100 btn-lg">Đăng nhập</button>
                            </form>
        
                            <div class="auth-footer">
                                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Quên mật khẩu?</a>
                                <a href="<?php echo esc_url(home_url('/register/')); ?>">Chưa có tài khoản? Đăng ký</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        $output .= ob_get_clean();
        $output .= LOPAS_Public::get_global_footer();
        return $output;
    }
    
    /**
     * Render register form
     */
    public static function render_register() {
        if (is_user_logged_in()) {
            return '<p>Bạn đã đăng nhập.</p>';
        }
        
        $output = LOPAS_Public::get_global_header();
        ob_start();
        ?>
        <section class="auth-page">
            <div class="container py-5">
                <div class="auth-panel">
                    <div class="auth-card">
                        <div class="auth-card-side">
                            <div class="auth-brand">BARBER SPA</div>
                            <h3 class="auth-side-title">Bắt đầu ngay hôm nay</h3>
                            <p class="auth-side-text">Tạo tài khoản để truy cập ưu đãi, quản lý profile và đặt dịch vụ barber chuyên nghiệp.</p>
                            <ul class="auth-features">
                                <li class="auth-feature-item">Đặt lịch nhanh chóng</li>
                                <li class="auth-feature-item">Lưu thông tin tức thì</li>
                                <li class="auth-feature-item">Theo dõi lịch sử booking</li>
                            </ul>
                        </div>
                        <div class="auth-card-body">
                            
                            <?php if (!empty(self::$errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach (self::$errors as $error) echo esc_html($error) . '<br>'; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <?php wp_nonce_field('lopas_register_action', 'lopas_auth_nonce'); ?>
                                <input type="hidden" name="lopas_register_submit" value="1">
                                
                                <div class="mb-3 auth-form-group">
                                    <label class="form-label">Họ và tên</label>
                                    <input type="text" name="name" class="form-control auth-form-control" value="<?php echo isset($_POST['name']) ? esc_attr($_POST['name']) : ''; ?>" placeholder="Nguyễn Văn A" required>
                                </div>
        
                                <div class="mb-3 auth-form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control auth-form-control" value="<?php echo isset($_POST['email']) ? esc_attr($_POST['email']) : ''; ?>" placeholder="mail@domain.com" required autocomplete="email">
                                </div>
        
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6 auth-form-group">
                                        <label class="form-label">Mật khẩu</label>
                                        <input type="password" name="password" class="form-control auth-form-control" placeholder="Tối thiểu 8 ký tự" required autocomplete="new-password">
                                    </div>
        
                                    <div class="col-md-6 auth-form-group">
                                        <label class="form-label">Nhập lại mật khẩu</label>
                                        <input type="password" name="password_confirmation" class="form-control auth-form-control" placeholder="Nhập lại mật khẩu" required>
                                    </div>
                                </div>
        
                                <button type="submit" class="btn btn-primary w-100 btn-lg">Đăng ký</button>
                            </form>
        
                            <div class="auth-footer">
                                <a href="<?php echo esc_url(home_url('/login/')); ?>">Đã có tài khoản? Đăng nhập</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        $output .= ob_get_clean();
        $output .= LOPAS_Public::get_global_footer();
        return $output;
    }
}

// Initialize Auth
LOPAS_Auth::init();
