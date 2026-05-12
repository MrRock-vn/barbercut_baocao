<?php
/**
 * LOPAS Salon Details Page Handler - Premium Redesign
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Salon_Page {
    
    /**
     * Render salon details page
     */
    public static function render($salon_id) {
        $salon = LOPAS_Salon::get($salon_id);
        
        if (!$salon) {
            return '<div class="container py-5 text-center"><h3>' . __('Salon không tồn tại', 'lopas') . '</h3></div>';
        }
        
        // Get services
        $services = LOPAS_Salon::get_services($salon->id);
        
        ob_start();
        ?>
        <div class="salon-details-wrapper py-5">
            <div class="container">
                <!-- Breadcrumb/Back -->
                <div class="mb-4">
                    <a href="<?php echo home_url('/salons/'); ?>" class="btn btn-light rounded-pill px-4">
                        <i class="dashicons dashicons-arrow-left-alt" style="vertical-align: middle;"></i> Quay lại tìm kiếm
                    </a>
                </div>

                <div class="row g-4">
                    <!-- Left Column: Image & Info -->
                    <div class="col-lg-8">
                        <div class="salon-main-card mb-4">
                            <div class="salon-hero-image-container mb-4">
                                <img src="https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1200&q=80" 
                                     alt="<?php echo esc_attr($salon->name); ?>" 
                                     class="salon-hero-image"
                                     onerror="this.src='https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1200&q=80'">
                            </div>
                            
                            <div class="salon-info-panel p-4 bg-white rounded-4 shadow-sm border">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h1 class="fw-bold h2 mb-1"><?php echo esc_html($salon->name); ?></h1>
                                        <p class="text-muted"><i class="dashicons dashicons-location"></i> <?php echo esc_html($salon->address); ?></p>
                                    </div>
                                    <div class="rating-badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                                        ★ 5.00 <span class="fw-normal small text-muted ms-1">(1 đánh giá)</span>
                                    </div>
                                </div>

                                <div class="row g-3 salon-quick-stats">
                                    <div class="col-md-4">
                                        <div class="stat-item p-3 rounded-3 bg-light text-center">
                                            <div class="small text-muted mb-1">Giờ mở cửa</div>
                                            <div class="fw-bold"><?php echo date('H:i', strtotime($salon->opening_time)); ?> - <?php echo date('H:i', strtotime($salon->closing_time)); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-item p-3 rounded-3 bg-light text-center">
                                            <div class="small text-muted mb-1">Điện thoại</div>
                                            <div class="fw-bold"><?php echo esc_html($salon->phone); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-item p-3 rounded-3 bg-light text-center">
                                            <div class="small text-muted mb-1">Lượt đặt</div>
                                            <div class="fw-bold">120+</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h4 class="fw-bold mb-3">Giới thiệu</h4>
                                    <div class="salon-description text-muted">
                                        <?php echo wp_kses_post($salon->description ?: 'Chưa có mô tả chi tiết cho salon này.'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Booking Widget -->
                    <div class="col-lg-4">
                        <div class="booking-widget position-sticky" style="top: 20px;">
                            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                                <div class="card-body p-4">
                                    <h3 class="fw-bold mb-3">Đặt lịch ngay</h3>
                                    <p class="text-muted small mb-4">Chọn dịch vụ, nhân viên và giờ hẹn phù hợp chỉ trong vài bước.</p>
                                    
                                    <a href="<?php echo home_url('/booking/?salon_id=' . $salon->id); ?>" class="btn btn-danger btn-lg w-100 rounded-pill py-3 fw-bold mb-3">
                                        Đặt lịch ngay →
                                    </a>
                                    
                                    <div class="bg-info-subtle p-3 rounded-3 text-center">
                                        <span class="text-info small fw-bold">Xác nhận ngay • Thanh toán linh hoạt</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Small map placeholder -->
                            <div class="mt-4 rounded-4 overflow-hidden shadow-sm border" style="height: 200px; background: #eee;">
                                <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo urlencode($salon->address); ?>&zoom=15&size=400x200&key=YOUR_API_KEY" 
                                     alt="Map" 
                                     class="w-100 h-100 object-fit-cover"
                                     onerror="this.src='https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=400&h=200&q=80'">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .salon-hero-image { width: 100%; height: 450px; object-fit: cover; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
            .salon-main-card { border-radius: 24px; }
            .booking-widget .btn-danger { background-color: #e3342f; border-color: #e3342f; }
            .booking-widget .btn-danger:hover { background-color: #cc1f1a; border-color: #cc1f1a; }
            .stat-item { border: 1px solid rgba(0,0,0,0.03); }
        </style>
        <?php
        return ob_get_clean();
    }
}
