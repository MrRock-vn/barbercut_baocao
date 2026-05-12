<?php
/**
 * LOPAS Booking Form Handler - Premium Redesign
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Booking_Form {
    
    /**
     * Render enhanced booking form
     */
    public static function render($atts = array()) {
        $atts = shortcode_atts(array(
            'salon_id' => 0,
            'service_id' => 0,
            'title' => __('Đặt lịch Barber Spa', 'lopas')
        ), $atts);
        
        $initial_salon_id = isset($_GET['salon_id']) ? intval($_GET['salon_id']) : $atts['salon_id'];
        
        ob_start();
        ?>
        <div class="lopas-booking-container py-5">
            <div class="container">
                <div class="booking-wizard shadow-lg rounded-4 overflow-hidden bg-white">
                    <!-- Progress Sidebar -->
                    <div class="row g-0">
                        <div class="col-lg-3 bg-dark p-4 text-white booking-sidebar">
                            <h4 class="fw-bold mb-4">Quy trình đặt lịch</h4>
                            <div class="booking-steps-nav">
                                <div class="step-nav-item active" data-step="1">
                                    <span class="step-num">1</span>
                                    <div class="step-label">
                                        <div class="small opacity-50">Bước 1</div>
                                        <div class="fw-bold">Chọn Salon</div>
                                    </div>
                                </div>
                                <div class="step-nav-item" data-step="2">
                                    <span class="step-num">2</span>
                                    <div class="step-label">
                                        <div class="small opacity-50">Bước 2</div>
                                        <div class="fw-bold">Chọn dịch vụ</div>
                                    </div>
                                </div>
                                <div class="step-nav-item" data-step="3">
                                    <span class="step-num">3</span>
                                    <div class="step-label">
                                        <div class="small opacity-50">Bước 3</div>
                                        <div class="fw-bold">Thời gian</div>
                                    </div>
                                </div>
                                <div class="step-nav-item" data-step="4">
                                    <span class="step-num">4</span>
                                    <div class="step-label">
                                        <div class="small opacity-50">Bước 4</div>
                                        <div class="fw-bold">Hoàn tất</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-auto pt-5 mt-5">
                                <div class="p-3 rounded-3 bg-white bg-opacity-10 small">
                                    <i class="dashicons dashicons-shield"></i> Thanh toán an toàn và bảo mật thông tin.
                                </div>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <div class="col-lg-9 p-4 p-md-5">
                            <form id="lopas-booking-form" class="needs-validation" novalidate>
                                <?php wp_nonce_field('lopas_booking_nonce', 'booking_nonce'); ?>
                                <input type="hidden" id="selected_salon_id" name="salon_id" value="<?php echo $initial_salon_id; ?>">
                                
                                <!-- Step 1: Salon Selection -->
                                <div class="booking-step-content active" id="step-1">
                                    <h3 class="fw-bold mb-4">Chọn Salon gần bạn</h3>
                                    <div class="row g-3">
                                        <?php
                                        $salons = LOPAS_Salon::get_all(array('status' => 'active'));
                                        foreach ($salons as $salon):
                                            $is_selected = ($salon->id == $initial_salon_id);
                                        ?>
                                        <div class="col-md-6">
                                            <div class="salon-selection-card p-3 rounded-3 border-2 border <?php echo $is_selected ? 'selected border-primary' : ''; ?>" 
                                                 data-id="<?php echo $salon->id; ?>" 
                                                 style="cursor: pointer;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="salon-icon bg-light rounded-circle p-2">
                                                        <i class="dashicons dashicons-store" style="font-size: 24px; width: 24px; height: 24px;"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo esc_html($salon->name); ?></div>
                                                        <div class="small text-muted"><?php echo esc_html($salon->address); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Step 2: Service Selection -->
                                <div class="booking-step-content" id="step-2" style="display:none;">
                                    <h3 class="fw-bold mb-4">Chọn dịch vụ</h3>
                                    <div id="services-container" class="row g-3">
                                        <!-- Loaded via AJAX -->
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status"></div>
                                            <p class="mt-2 text-muted">Đang tải dịch vụ...</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Date & Time -->
                                <div class="booking-step-content" id="step-3" style="display:none;">
                                    <h3 class="fw-bold mb-4">Thời gian hẹn</h3>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Chọn ngày</label>
                                            <input type="date" class="form-control form-control-lg rounded-3" id="booking_date" name="booking_date" 
                                                   min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Chọn khung giờ</label>
                                            <div id="slots-container" class="d-flex flex-wrap gap-2">
                                                <p class="text-muted small">Vui lòng chọn ngày để xem giờ trống.</p>
                                            </div>
                                            <input type="hidden" id="booking_time" name="booking_time" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 4: Info & Payment -->
                                <div class="booking-step-content" id="step-4" style="display:none;">
                                    <h3 class="fw-bold mb-4">Xác nhận thông tin</h3>
                                    <div class="row g-4">
                                        <div class="col-md-7">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Ghi chú thêm</label>
                                                <textarea class="form-control rounded-3" name="note" rows="3" placeholder="Yêu cầu đặc biệt cho thợ..."></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Phương thức thanh toán</label>
                                                <div class="payment-methods d-flex gap-3">
                                                    <div class="payment-option border p-3 rounded-3 flex-grow-1 text-center selected" data-method="cod">
                                                        <div class="fw-bold">Tiền mặt</div>
                                                        <div class="small text-muted">Thanh toán tại quầy</div>
                                                    </div>
                                                    <div class="payment-option border p-3 rounded-3 flex-grow-1 text-center" data-method="vnpay">
                                                        <div class="fw-bold text-primary">VNPay</div>
                                                        <div class="small text-muted">Thanh toán online</div>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="payment_method" name="payment_method" value="cod">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="summary-card bg-light p-4 rounded-4">
                                                <h5 class="fw-bold mb-3">Tóm tắt lịch hẹn</h5>
                                                <div id="booking-summary-details">
                                                    <!-- Filled via JS -->
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between fw-bold h5">
                                                    <span>Tổng cộng:</span>
                                                    <span id="summary-total">0đ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation -->
                                <div class="d-flex justify-content-between mt-5 pt-4 border-top">
                                    <button type="button" class="btn btn-light px-4 rounded-pill fw-bold" id="btn-prev" style="display:none;">Quay lại</button>
                                    <button type="button" class="btn btn-primary px-5 rounded-pill fw-bold ms-auto" id="btn-next">Tiếp theo →</button>
                                    <button type="submit" class="btn btn-success px-5 rounded-pill fw-bold ms-auto" id="btn-submit" style="display:none;">Xác nhận đặt lịch</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .booking-wizard { border: 1px solid rgba(0,0,0,0.05); }
            .booking-sidebar { min-height: 500px; }
            .step-nav-item { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; opacity: 0.5; transition: 0.3s; }
            .step-nav-item.active { opacity: 1; }
            .step-num { width: 32px; height: 32px; border-radius: 50%; border: 2px solid #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; }
            .step-nav-item.active .step-num { background: #fff; color: #000; }
            
            .salon-selection-card:hover { background: #f8fafc; }
            .salon-selection-card.selected { background: #eff6ff; border-color: #3b82f6 !important; }
            
            .service-card { cursor: pointer; transition: 0.3s; }
            .service-card:hover { background: #f8fafc; }
            .service-card.selected { border-color: #3b82f6 !important; background: #eff6ff; }
            
            .time-slot { cursor: pointer; padding: 10px 20px; border-radius: 12px; border: 1px solid #dee2e6; transition: 0.3s; }
            .time-slot:hover { border-color: #3b82f6; color: #3b82f6; }
            .time-slot.selected { background: #3b82f6; border-color: #3b82f6; color: #fff; }
            
            .payment-option { cursor: pointer; transition: 0.3s; }
            .payment-option.selected { border-color: #3b82f6 !important; background: #eff6ff; }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX: Get Salon Services
     */
    public static function ajax_get_services() {
        error_log('LOPAS Debug: Getting services for salon ' . $_POST['salon_id']);
        check_ajax_referer('lopas_public_nonce', 'nonce');
        $salon_id = intval($_POST['salon_id']);
        $services = LOPAS_Service::get_by_salon($salon_id);
        
        ob_start();
        if (empty($services)) {
            echo '<div class="col-12 text-center py-4">Chưa có dịch vụ nào cho salon này.</div>';
        } else {
            foreach ($services as $svc) {
                ?>
                <div class="col-md-6">
                    <div class="service-card p-3 rounded-3 border h-100" data-id="<?php echo $svc->id; ?>" data-price="<?php echo $svc->price; ?>" data-name="<?php echo esc_html($svc->name); ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold"><?php echo esc_html($svc->name); ?></div>
                                <div class="small text-muted"><?php echo $svc->duration; ?> phút</div>
                            </div>
                            <div class="fw-bold text-primary"><?php echo number_format($svc->price, 0, ',', '.'); ?>đ</div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        $html = ob_get_clean();
        wp_send_json_success(array('html' => $html));
    }

    public static function ajax_get_available_slots() {
        check_ajax_referer('lopas_public_nonce', 'nonce');
        
        $slots = array('08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00');
        
        $html = '';
        foreach ($slots as $slot) {
            $html .= '<div class="time-slot" data-time="' . esc_attr($slot) . '">' . esc_html($slot) . '</div>';
        }
        
        wp_send_json_success(array('html' => $html));
    }

    /**
     * AJAX handlers for LOPAS_Booking_Form (moved logic from LOPAS_Public)
     */
    public static function ajax_get_salon_details() {
        check_ajax_referer('lopas_public_nonce', 'nonce');
        $salon_id = intval($_POST['salon_id']);
        $salon = LOPAS_Salon::get($salon_id);
        if (!$salon) wp_send_json_error('Salon not found');
        wp_send_json_success($salon);
    }
}
