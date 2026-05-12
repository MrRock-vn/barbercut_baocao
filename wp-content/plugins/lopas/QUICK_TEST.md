# 🚀 QUICK TEST - LOPAS Clone Barber-Spa

## Bước 1: Kiểm tra Plugin đã Active chưa

```bash
# Vào WordPress Admin
http://localhost/wordpress/wp-admin

# Vào Plugins → Installed Plugins
# Tìm "LOPAS" và click "Activate" nếu chưa active
```

## Bước 2: Tạo Homepage

### Cách 1: Tạo page mới
1. Vào **Pages → Add New**
2. Title: `Home` hoặc `Trang chủ`
3. Thêm block **Shortcode**
4. Nhập: `[lopas_homepage]`
5. Click **Publish**

### Cách 2: Sử dụng page có sẵn
1. Vào **Pages → All Pages**
2. Edit page "Homepage" (đã tạo tự động khi activate plugin)
3. Kiểm tra có shortcode `[lopas_homepage]` chưa
4. Nếu chưa có, thêm vào
5. Click **Update**

## Bước 3: Set làm Homepage

1. Vào **Settings → Reading**
2. Chọn **A static page**
3. **Homepage**: Chọn page vừa tạo (Home hoặc Homepage)
4. Click **Save Changes**

## Bước 4: Xem kết quả

Truy cập:
```
http://localhost/wordpress/
```

Bạn sẽ thấy:
- ✅ Hero section với background xanh đậm
- ✅ Search bar floating màu trắng
- ✅ Categories pills (nếu đã có data)
- ✅ Featured salons (nếu đã có data)
- ✅ Booking steps (3 bước)
- ✅ Promo carousel (3 slides)
- ✅ Angels showcase (4 angels)
- ✅ Shine footer màu xanh đậm

## Bước 5: Thêm Sample Data (Optional)

Nếu chưa có data, chạy SQL này:

```sql
-- Thêm categories
INSERT INTO wp_lopas_categories (name, description, is_active, sort_order) VALUES
('Cắt tóc', 'Dịch vụ cắt tóc nam nữ', 1, 1),
('Uốn tóc', 'Dịch vụ uốn và tạo kiểu', 1, 2),
('Nhuộm tóc', 'Dịch vụ nhuộm màu', 1, 3),
('Spa & Chăm sóc da', 'Chăm sóc da mặt', 1, 4),
('Gội đầu & Massage', 'Gội đầu thư giãn', 1, 5);

-- Thêm salon mẫu
INSERT INTO wp_lopas_salons (name, address, phone, description, opening_time, closing_time, status, avg_rating, total_bookings) VALUES
('Barber House Quận 1', '101 Lê Thánh Tôn, Q1, TP.HCM', '0901111111', 'Salon tóc nam cao cấp', '08:00:00', '20:00:00', 'active', 4.8, 85),
('Gentleman Barber Quận 3', '220 Võ Văn Tần, Q3, TP.HCM', '0902222222', 'Không gian barber hiện đại', '08:00:00', '20:00:00', 'active', 4.6, 53),
('Luxury Spa Bình Thạnh', '58 Điện Biên Phủ, Bình Thạnh, TP.HCM', '0903333333', 'Dịch vụ spa cao cấp', '08:00:00', '20:00:00', 'active', 4.9, 97);
```

## Bước 6: Kiểm tra Responsive

Test trên các kích thước:
- **Desktop**: 1920px - Full layout
- **Tablet**: 768px - 2 columns
- **Mobile**: 375px - 1 column

## Bước 7: So sánh với Barber-Spa

Mở 2 tabs:
1. **Tab 1**: `http://localhost/barber-spa/` (PHP thuần)
2. **Tab 2**: `http://localhost/wordpress/` (WordPress LOPAS)

So sánh:
- ✅ Màu sắc giống nhau (xanh #0d6efd)
- ✅ Layout giống nhau
- ✅ Typography giống nhau (Inter font)
- ✅ Hover effects giống nhau
- ✅ Responsive breakpoints giống nhau

## 🎯 Kết quả mong đợi

Homepage LOPAS phải giống 100% với barber-spa về:
1. **Colors**: Blue theme (#0d6efd, #0f172a)
2. **Layout**: Hero, Search, Categories, Salons, Steps, Promo, Angels, Footer
3. **Typography**: Inter font family
4. **Spacing**: Padding, margins giống hệt
5. **Shadows**: Box shadows giống hệt
6. **Hover effects**: Transform, scale, color changes
7. **Responsive**: Breakpoints 991px, 767px

## ❌ Troubleshooting

### Lỗi: Page trắng
- Kiểm tra plugin đã active chưa
- Kiểm tra shortcode đúng chưa: `[lopas_homepage]`
- Check PHP errors: `wp-content/debug.log`

### Lỗi: CSS không load
- Clear cache: Ctrl + F5
- Kiểm tra file CSS tồn tại: `wp-content/plugins/lopas/assets/css/public.css`
- Kiểm tra enqueue trong `lopas.php`

### Lỗi: Không có data
- Chạy SQL insert categories và salons
- Hoặc tạo thủ công trong Admin

### Lỗi: Images không hiển thị
- Images sẽ fallback sang Unsplash URLs
- Hoặc copy images từ barber-spa vào `assets/images/`

## 📞 Support

Nếu có vấn đề, kiểm tra:
1. WordPress version >= 5.0
2. PHP version >= 7.4
3. MySQL version >= 5.7
4. Plugin LOPAS đã activate
5. Shortcode đã thêm vào page
6. Page đã set làm homepage

---

**Chúc bạn test thành công! 🎉**
