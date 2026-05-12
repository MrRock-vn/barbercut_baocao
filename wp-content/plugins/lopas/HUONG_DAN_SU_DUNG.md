# 📖 HƯỚNG DẪN SỬ DỤNG LOPAS - CLONE 100% BARBER-SPA

## 🎯 Giới thiệu

LOPAS là plugin WordPress clone 100% giao diện và chức năng từ dự án **barber-spa** (PHP thuần). 

**Điểm khác biệt:**
- ✅ Màu sắc: Xanh (#0d6efd) - GIỐNG HỆT barber-spa
- ✅ Layout: 8 sections - GIỐNG HỆT barber-spa
- ✅ Typography: Inter font - GIỐNG HỆT barber-spa
- ✅ Responsive: Breakpoints 991px, 767px - GIỐNG HỆT barber-spa

## 📋 Yêu cầu hệ thống

- WordPress 5.0 trở lên
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Theme hỗ trợ Bootstrap 5 (hoặc sử dụng theme mặc định)

## 🚀 Cài đặt

### Bước 1: Kích hoạt plugin

1. Vào **WordPress Admin** → **Plugins** → **Installed Plugins**
2. Tìm plugin **"LOPAS"**
3. Click **"Activate"**

Plugin sẽ tự động:
- Tạo 11 bảng database
- Tạo 8 pages (Homepage, Booking, Salons, My Bookings, Dashboard, Payment, Success, Failed)
- Enqueue CSS và JS files

### Bước 2: Import dữ liệu mẫu

Mở **phpMyAdmin** và chạy file SQL:
```
wordpress/wp-content/plugins/lopas/sample-data.sql
```

Dữ liệu sẽ được thêm:
- 5 categories (Cắt tóc, Uốn tóc, Nhuộm tóc, Spa, Gội đầu)
- 3 salons (Barber House, Gentleman Barber, Luxury Spa)
- 18 services (6 services mỗi salon)
- 9 staff (3 staff mỗi salon)

### Bước 3: Thiết lập Homepage

#### Cách 1: Sử dụng page tự động
1. Vào **Pages** → **All Pages**
2. Tìm page **"Homepage"** (đã tạo tự động)
3. Click **"Edit"**
4. Kiểm tra có shortcode `[lopas_homepage]`
5. Click **"Update"**

#### Cách 2: Tạo page mới
1. Vào **Pages** → **Add New**
2. Title: **"Trang chủ"**
3. Thêm block **Shortcode**
4. Nhập: `[lopas_homepage]`
5. Click **"Publish"**

### Bước 4: Set làm trang chủ

1. Vào **Settings** → **Reading**
2. Chọn **"A static page"**
3. **Homepage**: Chọn page vừa tạo
4. Click **"Save Changes"**

### Bước 5: Xem kết quả

Truy cập: `http://localhost/wordpress/`

Bạn sẽ thấy homepage giống 100% với barber-spa! 🎉

## 📸 Các sections trên Homepage

### 1. Hero Section
- Background: Dark overlay với ảnh barber shop
- Badge: "LOPAS BOOKING PLATFORM"
- Title lớn + subtitle
- 2 buttons: "Khám phá salon" và "Lịch hẹn của tôi"
- 3 stat cards bên dưới
- Hero image bên phải

### 2. Search Section
- Floating card màu trắng với shadow
- Search input lớn
- Button "Tìm kiếm" màu xanh
- Autocomplete (có thể enhance sau)

### 3. Categories Section
- Badge: "DANH MỤC DỊCH VỤ"
- Title + subtitle
- Pills buttons với hover effect
- Grid responsive (4 cột desktop, 2 cột tablet, 1 cột mobile)

### 4. Featured Salons Section
- Badge: "SALON NỔI BẬT"
- Title + subtitle
- 6 salon cards với:
  - Image
  - Badge "Nổi bật"
  - Tên salon
  - Địa chỉ
  - Rating pill
  - Button "Xem chi tiết"

### 5. Booking Steps Section
- Badge: "QUY TRÌNH ĐẶT LỊCH"
- Title + subtitle
- 3 step cards:
  - 01: Tìm salon
  - 02: Chọn dịch vụ
  - 03: Đặt lịch nhanh

### 6. Promo Carousel Section
- Bootstrap carousel với 3 slides
- Gradient overlay xanh đậm
- Mỗi slide có:
  - Promo tag
  - Title lớn
  - Description
  - Button "Bắt đầu ngay"
- Indicators và controls

### 7. Angels Showcase Section
- Blue line decoration
- Title: "LOPAS ANGELS"
- 4 angels với:
  - Avatar lớn
  - Ribbon badge ở góc trên
  - Tên + mô tả
  - Hover scale effect

### 8. Shine Footer
- Gradient background xanh đậm
- 2 logo boxes (LOPAS + LOPAS PRO)
- 4 cột:
  - Về chúng tôi
  - Liên hệ
  - Chính sách
  - Thanh toán (icons)
- Copyright footer

## 🎨 Màu sắc chính

```css
--primary-color: #0d6efd;  /* Xanh chính */
--dark-color: #0f172a;     /* Xanh đậm */
--dark-2: #111827;         /* Xanh đậm 2 */
--text-color: #1f2937;     /* Màu chữ */
--muted-color: #6b7280;    /* Màu chữ nhạt */
```

## 📱 Responsive Design

### Desktop (>= 992px)
- Hero: 2 cột (content + image)
- Categories: 4 cột
- Salons: 3 cột
- Angels: 4 cột

### Tablet (768px - 991px)
- Hero: 2 cột
- Categories: 3 cột
- Salons: 2 cột
- Angels: 3 cột

### Mobile (< 768px)
- Hero: 1 cột (stack)
- Categories: 2 cột
- Salons: 1 cột
- Angels: 2 cột

## 🔧 Tùy chỉnh

### Thay đổi màu sắc
Edit file: `assets/css/public.css`
```css
:root {
  --primary-color: #0d6efd; /* Đổi màu này */
}
```

### Thay đổi nội dung
Edit file: `includes/public/class-homepage.php`

### Thêm images
Copy images vào: `assets/images/`
- hero.jpg (1600x900px)
- salon1.jpg, salon2.jpg, ...
- promo1.jpg, promo2.jpg, promo3.jpg

## 🐛 Troubleshooting

### Lỗi: Page trắng
**Nguyên nhân**: Plugin chưa active hoặc shortcode sai
**Giải pháp**:
1. Kiểm tra plugin đã active
2. Kiểm tra shortcode: `[lopas_homepage]`
3. Check PHP errors trong `wp-content/debug.log`

### Lỗi: CSS không load
**Nguyên nhân**: Cache hoặc file CSS không tồn tại
**Giải pháp**:
1. Clear cache: Ctrl + F5
2. Kiểm tra file: `wp-content/plugins/lopas/assets/css/public.css`
3. Deactivate và activate lại plugin

### Lỗi: Không có dữ liệu
**Nguyên nhân**: Chưa import sample data
**Giải pháp**:
1. Chạy file `sample-data.sql` trong phpMyAdmin
2. Hoặc tạo thủ công trong Admin

### Lỗi: Images không hiển thị
**Nguyên nhân**: Images chưa có trong folder
**Giải pháp**:
1. Copy images từ barber-spa vào `assets/images/`
2. Hoặc để mặc định (sẽ dùng Unsplash URLs)

## 📞 Hỗ trợ

Nếu gặp vấn đề, kiểm tra:
1. ✅ WordPress version >= 5.0
2. ✅ PHP version >= 7.4
3. ✅ MySQL version >= 5.7
4. ✅ Plugin LOPAS đã activate
5. ✅ Shortcode đã thêm vào page
6. ✅ Page đã set làm homepage
7. ✅ Sample data đã import

## 🎓 Học thêm

### Shortcodes có sẵn
- `[lopas_homepage]` - Homepage
- `[lopas_booking_form]` - Form đặt lịch
- `[lopas_my_bookings]` - Lịch hẹn của tôi
- `[lopas_customer_dashboard]` - Dashboard khách hàng
- `[lopas_salon_page]` - Trang salon

### Admin Pages
- **Salons**: Quản lý salon
- **Services**: Quản lý dịch vụ
- **Staff**: Quản lý nhân viên
- **Bookings**: Quản lý đặt lịch
- **Orders**: Quản lý đơn hàng
- **Payments**: Quản lý thanh toán

## 🎉 Kết luận

LOPAS giờ đây là bản clone 100% của barber-spa trên WordPress!

**So sánh:**
- Barber-Spa: PHP thuần + MySQL
- LOPAS: WordPress plugin + WP Database

**Giống nhau:**
- ✅ Màu sắc (blue theme)
- ✅ Layout (8 sections)
- ✅ Typography (Inter font)
- ✅ Hover effects
- ✅ Responsive design
- ✅ Shadows và spacing

**Khác nhau:**
- ❌ Platform (WordPress vs PHP thuần)
- ❌ Database (WP tables vs MySQL tables)
- ❌ Admin (WP Admin vs Custom Admin)

---

**Chúc bạn sử dụng thành công! 🚀**
