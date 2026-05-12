<?php
/**
 * LOPAS Owner Dashboard Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Owner_Dashboard {
    
    /**
     * Render owner dashboard
     */
    public static function render() {
        if (!is_user_logged_in()) {
            return '<div class="alert alert-warning">Bạn cần đăng nhập để truy cập trang quản lý Salon.</div>';
        }
        
        $user_id = get_current_user_id();
        
        // In a real scenario, check if user has 'salon_owner' role or is assigned to a salon
        // For now, we assume if they access this, they are testing the owner dashboard.
        // Let's get the first active salon for this user, or just the first salon for demo.
        global $wpdb;
        $salons_table = LOPAS_Database::get_table('salons');
        
        // Demo: get first salon
        $salon = $wpdb->get_row("SELECT * FROM {$salons_table} LIMIT 1");
        
        if (!$salon) {
            return '<div class="alert alert-danger">Không tìm thấy Salon nào để quản lý.</div>';
        }
        
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
        
        $output = LOPAS_Public::get_global_header();
        ob_start();
        ?>
        <style>
            .owner-layout { background: #f3f4f6; min-height: 100vh; }
            .owner-container { display: flex; flex-wrap: wrap; gap: 20px; padding: 30px 0; }
            .owner-sidebar-col { flex: 0 0 280px; }
            .owner-content-col { flex: 1; min-width: 0; }
            
            .owner-sidebar {
                background: #fff;
                border-radius: 16px;
                padding: 24px 16px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
            .sidebar-title {
                font-size: 1.1rem;
                font-weight: 700;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
                color: #111827;
            }
            .owner-sidebar .nav-link {
                display: block;
                padding: 10px 16px;
                margin-bottom: 8px;
                border-radius: 8px;
                color: #4b5563;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.2s;
            }
            .owner-sidebar .nav-link:hover, .owner-sidebar .nav-link.active {
                background: #eff6ff;
                color: #2563eb;
            }
            
            .owner-card {
                background: #fff;
                border-radius: 16px;
                padding: 24px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                margin-bottom: 24px;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 24px;
            }
            .stat-box {
                background: #fff;
                border-radius: 16px;
                padding: 20px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                border-left: 4px solid #2563eb;
            }
            .stat-title { font-size: 0.9rem; color: #6b7280; font-weight: 600; text-transform: uppercase; }
            .stat-value { font-size: 2rem; font-weight: 800; color: #111827; margin-top: 8px; }
            
            .table-custom { width: 100%; border-collapse: collapse; }
            .table-custom th, .table-custom td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #f3f4f6; }
            .table-custom th { background: #f9fafb; font-weight: 600; color: #374151; }
            .status-badge { padding: 4px 10px; border-radius: 99px; font-size: 0.85rem; font-weight: 600; }
            .status-pending { background: #fef3c7; color: #d97706; }
            .status-confirmed { background: #d1fae5; color: #059669; }
            .status-completed { background: #e0e7ff; color: #4338ca; }
            .status-cancelled { background: #fee2e2; color: #dc2626; }
        </style>

        <div class="owner-layout">
            <div class="container owner-container">
                <div class="owner-sidebar-col">
                    <div class="owner-sidebar">
                        <div class="sidebar-title">Quản lý Salon: <?php echo esc_html($salon->name); ?></div>
                        <nav>
                            <a href="?tab=dashboard" class="nav-link <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
                            <a href="?tab=bookings" class="nav-link <?php echo $current_tab === 'bookings' ? 'active' : ''; ?>">Lịch đặt (Bookings)</a>
                            <a href="?tab=services" class="nav-link <?php echo $current_tab === 'services' ? 'active' : ''; ?>">Dịch vụ (Services)</a>
                            <a href="?tab=staff" class="nav-link <?php echo $current_tab === 'staff' ? 'active' : ''; ?>">Nhân viên (Staff)</a>
                            <a href="?tab=revenue" class="nav-link <?php echo $current_tab === 'revenue' ? 'active' : ''; ?>">Doanh thu (Revenue)</a>
                            <a href="?tab=reviews" class="nav-link <?php echo $current_tab === 'reviews' ? 'active' : ''; ?>">Đánh giá (Reviews)</a>
                        </nav>
                    </div>
                </div>
                
                <div class="owner-content-col">
                    <?php 
                    switch ($current_tab) {
                        case 'bookings':
                            self::render_bookings($salon->id);
                            break;
                        case 'services':
                            self::render_services($salon->id);
                            break;
                        case 'revenue':
                            self::render_revenue($salon->id);
                            break;
                        case 'staff':
                            self::render_staff($salon->id);
                            break;
                        case 'reviews':
                            self::render_reviews($salon->id);
                            break;
                        default:
                            self::render_dashboard($salon->id);
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        $output .= ob_get_clean();
        $output .= LOPAS_Public::get_global_footer();
        return $output;
    }
    
    private static function render_dashboard($salon_id) {
        global $wpdb;
        $bookings_table = LOPAS_Database::get_table('bookings');
        
        $total_bookings = $wpdb->get_var("SELECT COUNT(*) FROM {$bookings_table} WHERE salon_id = {$salon_id}");
        $pending_bookings = $wpdb->get_var("SELECT COUNT(*) FROM {$bookings_table} WHERE salon_id = {$salon_id} AND status = 'pending'");
        $completed_bookings = $wpdb->get_var("SELECT COUNT(*) FROM {$bookings_table} WHERE salon_id = {$salon_id} AND status = 'completed'");
        
        ?>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-title">Tổng lịch đặt</div>
                <div class="stat-value"><?php echo intval($total_bookings); ?></div>
            </div>
            <div class="stat-box" style="border-left-color: #f59e0b;">
                <div class="stat-title">Chờ xác nhận</div>
                <div class="stat-value"><?php echo intval($pending_bookings); ?></div>
            </div>
            <div class="stat-box" style="border-left-color: #10b981;">
                <div class="stat-title">Đã hoàn thành</div>
                <div class="stat-value"><?php echo intval($completed_bookings); ?></div>
            </div>
        </div>
        
        <div class="owner-card">
            <h3>Lịch đặt mới nhất</h3>
            <?php self::render_bookings_table($salon_id, 5); ?>
            <div style="margin-top: 15px; text-align: right;">
                <a href="?tab=bookings" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
            </div>
        </div>
        <?php
    }
    
    private static function render_bookings($salon_id) {
        ?>
        <div class="owner-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">Quản lý Lịch đặt</h3>
            </div>
            <?php self::render_bookings_table($salon_id, 50); ?>
        </div>
        <?php
    }
    
    private static function render_bookings_table($salon_id, $limit = 10) {
        global $wpdb;
        $bookings_table = LOPAS_Database::get_table('bookings');
        $services_table = LOPAS_Database::get_table('services');
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT b.*, s.name as service_name 
             FROM {$bookings_table} b 
             LEFT JOIN {$services_table} s ON b.service_id = s.id 
             WHERE b.salon_id = %d 
             ORDER BY b.created_at DESC LIMIT %d",
            $salon_id, $limit
        ));
        
        if (empty($bookings)) {
            echo '<p>Chưa có lịch đặt nào.</p>';
            return;
        }
        
        echo '<div style="overflow-x: auto;">';
        echo '<table class="table-custom">';
        echo '<thead><tr><th>Mã</th><th>Dịch vụ</th><th>Ngày giờ</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>';
        echo '<tbody>';
        foreach ($bookings as $b) {
            $status_class = 'status-' . $b->status;
            $status_text = ucfirst($b->status);
            if ($b->status == 'pending') $status_text = 'Chờ xác nhận';
            if ($b->status == 'confirmed') $status_text = 'Đã xác nhận';
            if ($b->status == 'completed') $status_text = 'Hoàn thành';
            if ($b->status == 'cancelled') $status_text = 'Đã hủy';
            
            echo '<tr>';
            echo '<td><strong>' . esc_html($b->booking_code) . '</strong></td>';
            echo '<td>' . esc_html($b->service_name ?: 'Dịch vụ đã xóa') . '</td>';
            echo '<td>' . esc_html($b->booking_date . ' ' . $b->booking_time) . '</td>';
            echo '<td><span class="status-badge ' . $status_class . '">' . esc_html($status_text) . '</span></td>';
            echo '<td><a href="#" class="btn btn-sm btn-light">Chi tiết</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
    
    private static function render_services($salon_id) {
        echo '<div class="owner-card"><h3>Quản lý Dịch vụ</h3><p>Tính năng đang được phát triển để đồng bộ với LOPAS Admin.</p></div>';
    }
    
    private static function render_staff($salon_id) {
        echo '<div class="owner-card"><h3>Quản lý Nhân viên</h3><p>Tính năng đang được phát triển để đồng bộ với LOPAS Admin.</p></div>';
    }
    
    private static function render_revenue($salon_id) {
        echo '<div class="owner-card"><h3>Báo cáo Doanh thu</h3><p>Tính năng đang được phát triển.</p></div>';
    }
    
    private static function render_reviews($salon_id) {
        echo '<div class="owner-card"><h3>Đánh giá của khách hàng</h3><p>Chưa có đánh giá nào.</p></div>';
    }
}
