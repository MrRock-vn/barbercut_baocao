<?php
/**
 * LOPAS Homepage - 100% Clone of Barber-Spa
 * Exact structure from barber-spa/views/search/home.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Homepage {
    
    public function __construct() {
        add_shortcode('lopas_homepage', array($this, 'render_homepage'));
    }
    
    /**
     * Render homepage - 100% clone of barber-spa
     */
    public function render_homepage($atts) {
        global $wpdb;
        
        // Get categories
        $categories_table = LOPAS_Database::get_table('categories');
        $categories = $wpdb->get_results("SELECT * FROM {$categories_table} ORDER BY sort_order ASC LIMIT 6");
        
        // Get featured salons
        $salons_table = LOPAS_Database::get_table('salons');
        $featured_salons = $wpdb->get_results("SELECT * FROM {$salons_table} WHERE status = 'active' ORDER BY id DESC LIMIT 6");
        
        $output = LOPAS_Public::get_global_header();
        ob_start();
        ?>

        <!-- Hero Section -->
        <section class="hero-section d-flex align-items-center text-white">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="hero-badge mb-3">BARBER SPA BOOKING PLATFORM</span>
                        <h1 class="hero-title mb-3">
                            Đặt lịch cắt tóc, gội đầu và chăm sóc diện mạo thật nhanh
                        </h1>
                        <p class="hero-subtitle mb-4">
                            Tìm salon phù hợp, chọn dịch vụ yêu thích và đặt lịch online chỉ trong vài bước đơn giản.
                        </p>

                        <div class="d-flex flex-wrap gap-3 mb-4">
                            <a href="<?php echo home_url('/salons/'); ?>" class="btn btn-primary hero-btn-primary">
                                Khám phá salon
                            </a>
                            <a href="<?php echo home_url('/my-bookings/'); ?>" class="btn btn-outline-light hero-btn-secondary">
                                Lịch hẹn của tôi
                            </a>
                        </div>

                        <div class="row g-3 hero-stats">
                            <div class="col-6 col-md-4">
                                <div class="hero-stat-card">
                                    <h4>Salon đáng tin cậy</h4>
                                    <p>Đội ngũ chuyên nghiệp</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="hero-stat-card">
                                    <h4>Dịch vụ đa dạng</h4>
                                    <p>Phù hợp nhiều nhu cầu</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="hero-stat-card">
                                    <h4>Đặt lịch dễ dàng</h4>
                                    <p>Quy trình nhanh gọn</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="hero-image-card">
                            <img
                                loading="lazy"
                                src="<?php echo plugins_url('lopas/assets/images/hero.jpg'); ?>"
                                alt="Barber Spa Salon"
                                class="hero-main-image"
                                onerror="this.src='https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=800&q=80'"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Search Section -->
        <section class="search-section">
            <div class="container">
                <div class="search-card">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-4">
                            <h2 class="mb-0">Tìm salon phù hợp với bạn</h2>
                        </div>
                        <div class="col-lg-8">
                            <form action="<?php echo home_url('/salons/'); ?>" method="GET" class="row g-2">
                                <div class="col-md-9 position-relative">
                                    <input
                                        type="text"
                                        name="keyword"
                                        id="homeSearchInput"
                                        class="form-control form-control-lg search-input"
                                        placeholder="Nhập tên salon, khu vực hoặc dịch vụ..."
                                        maxlength="100"
                                        autocomplete="off"
                                        value=""
                                    >
                                    <div id="homeSearchSuggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 20;"></div>
                                </div>
                                <div class="col-md-3 d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg search-btn">
                                        Tìm kiếm
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($categories)): ?>
        <!-- Categories Section -->
        <section class="categories-section py-5">
            <div class="container">
                <div class="section-heading text-center mb-4">
                    <span class="section-badge">DANH MỤC DỊCH VỤ</span>
                    <h2 class="section-title">Lựa chọn dịch vụ yêu thích</h2>
                    <p class="section-subtitle">
                        Khám phá các nhóm dịch vụ nổi bật để bắt đầu trải nghiệm của bạn.
                    </p>
                </div>

                <div class="row g-3">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="<?php echo home_url('/salons/?keyword=' . urlencode($category->name)); ?>" class="category-pill d-flex align-items-center justify-content-center text-center text-decoration-none">
                                <?php echo esc_html($category->name); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php else: ?>
        <section class="categories-section py-5">
            <div class="container">
                <div class="section-heading text-center mb-4">
                    <span class="section-badge">DANH MỤC DỊCH VỤ</span>
                    <h2 class="section-title">Danh mục đang cập nhật</h2>
                    <p class="section-subtitle">
                        Vui lòng sử dụng thanh tìm kiếm để tìm salon và dịch vụ phù hợp.
                    </p>
                </div>
                <div class="text-center text-muted py-5">
                    Chưa có danh mục dịch vụ khả dụng. Dùng tìm kiếm để tiếp tục.
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($featured_salons)): ?>
        <!-- Featured Salons Section -->
        <section class="featured-salons-section py-5">
            <div class="container">
                <div class="section-heading text-center mb-5">
                    <span class="section-badge">SALON NỔI BẬT</span>
                    <h2 class="section-title">Những địa điểm được khách hàng yêu thích</h2>
                    <p class="section-subtitle">
                        Chọn salon chất lượng cao với đội ngũ chuyên nghiệp và nhiều đánh giá tích cực.
                    </p>
                </div>

                <div class="row g-4">
                    <?php
                    $salon_images = array(
                        'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1620331311520-246422fd82f9?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1605497788044-5a32c7078486?auto=format&fit=crop&w=800&q=80'
                    );
                    $img_index = 0;
                    ?>
                    <?php foreach ($featured_salons as $salon): ?>
                        <?php 
                        $salon_img = $salon_images[$img_index % count($salon_images)];
                        $img_index++;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card salon-card shadow-sm border-0 h-100">
                                <div class="salon-image-wrap">
                                    <img
                                        loading="lazy"
                                        src="<?php echo esc_url($salon_img); ?>"
                                        alt="<?php echo esc_attr($salon->name); ?>"
                                        class="salon-image"
                                    >
                                    <span class="salon-badge">Nổi bật</span>
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <h5 class="salon-title mb-2"><?php echo esc_html($salon->name); ?></h5>
                                    <p class="salon-address text-muted mb-3">
                                        <?php echo esc_html($salon->address ?? 'Địa chỉ đang cập nhật'); ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <?php if (!empty($salon->avg_rating)): ?>
                                            <span class="rating-pill">
                                                ⭐ <?php echo esc_html(number_format($salon->avg_rating, 1)); ?>
                                            </span>
                                        <?php endif; ?>
                                        <a href="<?php echo home_url('/salons/?salon_id=' . $salon->id); ?>" class="btn btn-primary salon-btn">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php else: ?>
        <section class="featured-salons-section py-5">
            <div class="container">
                <div class="section-heading text-center mb-5">
                    <span class="section-badge">SALON NỔI BẬT</span>
                    <h2 class="section-title">Những địa điểm được khách hàng yêu thích</h2>
                    <p class="section-subtitle">
                        Chọn salon chất lượng cao với đội ngũ chuyên nghiệp và nhiều đánh giá tích cực.
                    </p>
                </div>
                <div class="text-center text-muted py-5">
                    Chưa có salon nổi bật để hiển thị. Vui lòng thử lại sau hoặc tìm kiếm trực tiếp.
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Booking Steps Section -->
        <section class="booking-steps-section py-5">
            <div class="container">
                <div class="section-heading text-center mb-5">
                    <span class="section-badge">QUY TRÌNH ĐẶT LỊCH</span>
                    <h2 class="section-title">Đặt lịch chỉ với 3 bước</h2>
                    <p class="section-subtitle">
                        Hệ thống giúp bạn hoàn tất booking nhanh chóng, thuận tiện và dễ sử dụng.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">01</div>
                            <h5>Tìm salon</h5>
                            <p>Nhập từ khóa để tìm salon hoặc khu vực phù hợp với bạn.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">02</div>
                            <h5>Chọn dịch vụ</h5>
                            <p>Chọn danh mục hoặc xem chi tiết salon để tìm dịch vụ mong muốn.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">03</div>
                            <h5>Đặt lịch nhanh</h5>
                            <p>Tiếp tục quy trình booking với các chức năng của hệ thống.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Promo Carousel Section -->
        <section class="promo-section py-5">
            <div class="container">
                <div id="promoCarousel" class="carousel slide promo-carousel" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                        <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="2"></button>
                    </div>

                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="promo-slide">
                                <img
                                    loading="lazy"
                                    src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1600&q=80"
                                    alt="Promo 1"
                                    class="promo-slide-image"
                                >
                                <div class="promo-overlay">
                                    <div class="promo-content">
                                        <span class="promo-tag">ƯU ĐÃI HOT</span>
                                        <h2>Sẵn sàng làm mới diện mạo của bạn?</h2>
                                        <p>Tìm salon phù hợp và bắt đầu đặt lịch ngay hôm nay với trải nghiệm hiện đại và chuyên nghiệp.</p>
                                        <a href="<?php echo home_url('/salons/'); ?>" class="btn btn-light btn-lg">
                                            Bắt đầu ngay
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item">
                            <div class="promo-slide">
                                <img
                                    loading="lazy"
                                    src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1600&q=80"
                                    alt="Promo 2"
                                    class="promo-slide-image"
                                >
                                <div class="promo-overlay">
                                    <div class="promo-content">
                                        <span class="promo-tag">XU HƯỚNG MỚI</span>
                                        <h2>Phong cách barber hiện đại, lịch lãm và tự tin</h2>
                                        <p>Khám phá không gian dịch vụ đẳng cấp và đội ngũ barber tận tâm tại BARBER SPA.</p>
                                        <a href="<?php echo home_url('/salons/'); ?>" class="btn btn-light btn-lg">
                                            Khám phá salon
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item">
                            <div class="promo-slide">
                                <img
                                    loading="lazy"
                                    src="https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=1600&q=80"
                                    alt="Promo 3"
                                    class="promo-slide-image"
                                >
                                <div class="promo-overlay">
                                    <div class="promo-content">
                                        <span class="promo-tag">TRẢI NGHIỆM CAO CẤP</span>
                                        <h2>Thư giãn - làm đẹp - nâng tầm phong cách</h2>
                                        <p>Không chỉ là cắt tóc, đây là trải nghiệm chăm sóc toàn diện dành cho bạn.</p>
                                        <a href="<?php echo home_url('/salons/'); ?>" class="btn btn-light btn-lg">
                                            Xem ngay
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Angels Showcase Section -->
        <section class="angels-showcase py-5">
            <div class="container">
                <div class="angels-heading">
                    <div class="angels-line"></div>
                    <div>
                        <h2>BARBER SPA ANGELS</h2>
                        <p>Đội ngũ chuyên gia BARBER SPA, tận tâm phục vụ khách hàng.</p>
                    </div>
                </div>

                <div class="row g-4">
                    <?php
                    $angels = array(
                        array('name' => 'Chuyên gia cắt tóc', 'desc' => 'Phong cách hiện đại, tinh tế', 'img' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', 'title' => 'Senior Stylist'),
                        array('name' => 'Tạo kiểu nam chuẩn salon', 'desc' => 'Phong cách lịch lãm, nam tính', 'img' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 'title' => 'Master Barber'),
                        array('name' => 'Tư vấn phong cách cá nhân', 'desc' => 'Đầu tư phong cách chuẩn chuyên nghiệp', 'img' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80', 'title' => 'Style Advisor'),
                        array('name' => 'Chăm sóc tóc và da đầu', 'desc' => 'Phong cách barber chuyên sâu', 'img' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80', 'title' => 'Grooming Expert')
                    );
                    foreach ($angels as $angel):
                    ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="<?php echo home_url('/salons/'); ?>" class="angel-item">
                            <div class="angel-thumb-wrap">
                                <img loading="lazy" src="<?php echo esc_url($angel['img']); ?>" alt="<?php echo esc_attr($angel['name']); ?>" class="angel-thumb">
                                <div class="angel-ribbon"><?php echo esc_html($angel['title']); ?></div>
                            </div>
                            <div class="angel-meta">
                                <div class="angel-channel"><?php echo esc_html($angel['name']); ?></div>
                                <div class="angel-address"><?php echo esc_html($angel['desc']); ?></div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Shine Footer -->
        <footer class="shine-footer">
            <div class="container">
                <div class="shine-footer-top row g-4">
                    <div class="col-md-6">
                        <div class="shine-logo-box">
                            <div class="shine-logo-text">BARBER SPA</div>
                            <div class="shine-logo-desc">Hệ thống đặt lịch salon & spa hiện đại</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="shine-logo-box">
                            <div class="shine-logo-text">BARBER SPA PRO</div>
                            <div class="shine-logo-desc">Phiên bản thương hiệu hiện đại mới</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 shine-footer-main">
                    <div class="col-md-3">
                        <h5>Về chúng tôi</h5>
                        <ul>
                            <li><a href="<?php echo home_url('/'); ?>">Về chúng tôi</a></li>
                            <li><a href="<?php echo home_url('/salons/'); ?>">Tìm salon gần nhất</a></li>
                            <li><a href="<?php echo home_url('/my-bookings/'); ?>">Lịch hẹn của tôi</a></li>
                        </ul>
                    </div>

                    <div class="col-md-3">
                        <h5>Liên hệ</h5>
                        <ul>
                            <li>Hotline: 1900.27.27.03</li>
                            <li>Email: contact@barberspa.vn</li>
                            <li>Liên hệ quảng cáo</li>
                        </ul>
                    </div>

                    <div class="col-md-3">
                        <h5>Chính sách</h5>
                        <ul>
                            <li>Giờ phục vụ: 8h30 - 20h30</li>
                            <li>Chính sách bảo mật</li>
                            <li>Điều kiện giao dịch</li>
                        </ul>
                    </div>

                    <div class="col-md-3">
                        <h5>Thanh toán</h5>
                        <div class="payment-icons">
                            <span>💵</span>
                            <span>🏦</span>
                            <span>💳</span>
                            <span>VISA</span>
                            <span>MC</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shine-footer-bottom">
                © <?php echo date('Y'); ?> BARBER SPA / Hệ thống đặt lịch salon & spa hiện đại
            </div>
        </footer>

        <script>
        // Autocomplete search (optional - can be enhanced later)
        const homeSearchInput = document.getElementById('homeSearchInput');
        const homeSearchSuggestions = document.getElementById('homeSearchSuggestions');
        
        if (homeSearchInput && homeSearchSuggestions) {
            let searchTimer = null;
            
            homeSearchInput.addEventListener('input', function () {
                const q = this.value.trim();
                clearTimeout(searchTimer);

                if (q.length < 2) {
                    homeSearchSuggestions.classList.add('d-none');
                    homeSearchSuggestions.innerHTML = '';
                    return;
                }

                searchTimer = setTimeout(async () => {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'lopas_search_salons');
                        formData.append('q', q);
                        formData.append('_ajax_nonce', lopasPublic.nonce);

                        const response = await fetch(lopasPublic.ajaxUrl, {
                            method: 'POST',
                            body: formData
                        });
                        
                        const res = await response.json();
                        if (res.success && res.data && res.data.length > 0) {
                            let html = '<div class="list-group list-group-flush">';
                            res.data.forEach(salon => {
                                html += `<a href="${salon.url}" class="list-group-item list-group-item-action d-flex align-items-center p-3">
                                            <div class="ms-2">
                                                <h6 class="mb-0 fw-bold">${salon.name}</h6>
                                                <small class="text-muted">${salon.address}</small>
                                            </div>
                                         </a>`;
                            });
                            html += '</div>';
                            homeSearchSuggestions.innerHTML = html;
                            homeSearchSuggestions.classList.remove('d-none');
                        } else {
                            homeSearchSuggestions.innerHTML = '<div class="p-3 text-muted text-center">Không tìm thấy salon nào phù hợp.</div>';
                            homeSearchSuggestions.classList.remove('d-none');
                        }
                    } catch (error) {
                        console.error('Lỗi khi tìm kiếm:', error);
                    }
                }, 300);
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!homeSearchInput.contains(e.target) && !homeSearchSuggestions.contains(e.target)) {
                    homeSearchSuggestions.classList.add('d-none');
                }
            });
        }
        </script>
        
        <?php
        $output .= ob_get_clean();
        return $output;
    }
}
