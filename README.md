# 💈 Barber Spa - Website Đặt Lịch Salon / Barber Trực Tuyến

Đồ án / bài tập lớn Phần mềm mã nguồn mở  
Xây dựng hệ thống website trên nền tảng **WordPress** cho phép khách hàng tìm kiếm salon, đặt lịch dịch vụ, thanh toán, đánh giá và quản lý lịch hẹn trực tuyến thông qua Plugin **LOPAS**.

## 👥 Thành viên nhóm

| STT | Họ và tên          | MSSV          | Vai trò     |
| --- | ------------------ | ------------- | ----------- |
| 1   | [Nguyễn Công Sơn]  | [23810310102] | Nhóm trưởng |
| 2   | [Nguyễn Văn Quang] | [23810310108] | Thành viên  |
| 3   | [Nguyễn Văn Danh]  | [23810310136] | Thành viên  |

## 🎯 Giới thiệu

Barber Spa là nền tảng đặt lịch salon/barber trực tuyến, kết nối khách hàng với các salon có dịch vụ cắt tóc, gội đầu, massage, chăm sóc da và làm đẹp. Hệ thống được phát triển dựa trên **WordPress** với Plugin **LOPAS** tự xây dựng, hỗ trợ 3 vai trò chính:

- **Khách hàng (Customer):** tìm kiếm salon, xem dịch vụ, đặt lịch, thanh toán, theo dõi booking, viết review.
- **Chủ salon (Owner):** quản lý salon, nhân viên, dịch vụ, lịch hẹn, doanh thu và review của salon mình.
- **Quản trị viên (Admin):** quản lý người dùng, salon, danh mục, booking, payment, review và dashboard toàn hệ thống.

## 🚀 Công nghệ sử dụng

| Thành phần | Công nghệ                             |
| ---------- | ------------------------------------- |
| Core       | WordPress 6.x+, PHP 8.0+              |
| Frontend   | HTML5, CSS3, JavaScript, Bootstrap 5  |
| Backend    | PHP 8 (Plugin LOPAS theo mô hình MVC) |
| Database   | MySQL / MariaDB                       |
| Web server | Apache (XAMPP)                        |
| UI / Chart | Bootstrap 5 CDN, Chart.js             |
| Auth       | WordPress User Sessions               |
| Payment    | VNPay Sandbox                         |
| Mail       | PHPMailer / SMTP (WP Mail)            |

## 📋 Tài liệu Đặc tả Yêu cầu Phần mềm (SRS)

| Mã        | Chức năng                 | Trạng thái |
| --------- | ------------------------- | ---------- |
| AUTH-01   | Xác thực người dùng       | ✅         |
| SEARCH-01 | Tìm kiếm & khám phá salon | ✅         |
| BOOK-01   | Đặt lịch hẹn              | ✅         |
| PAY-01    | Thanh toán                | ✅         |
| SALON-01  | Quản lý salon             | ✅         |
| REVIEW-01 | Đánh giá & review         | ✅         |
| ADMIN-01  | Quản trị hệ thống         | ✅         |

## 🗂️ Cấu trúc thư mục dự án (Plugin LOPAS)

Dự án được tập trung phát triển trong Plugin `lopas`:

```text
wordpress/
├── wp-content/
│   └── plugins/
│       └── lopas/
│           ├── assets/        (CSS, JS, Images)
│           ├── includes/      (Controllers, Models, API, Admin)
│           │   ├── admin/     (Quản trị salon, thợ, lịch hẹn)
│           │   ├── api/       (VNPay, Slot logic)
│           │   └── models/    (Salon, Service, Booking, Staff, Review)
│           ├── templates/     (Views: Giao diện đặt lịch & danh sách)
│           ├── lopas.php      (File chính Plugin)
│           └── README.md      (Tài liệu kỹ thuật Plugin)
├── wp-config.php              (Cấu hình Database)
└── README.md                  (Tài liệu này)
```

## ✨ Chức năng chính của hệ thống

### 1. Khách hàng (Customer)
- Đăng ký, đăng nhập, đăng xuất qua WordPress.
- Tìm kiếm salon theo từ khóa, khu vực, dịch vụ.
- Autocomplete gợi ý salon / dịch vụ theo thời gian thực.
- Đặt lịch qua 4 bước: Chọn dịch vụ -> Chọn nhân viên -> Chọn ngày giờ -> Xác nhận.
- Hệ thống giữ slot tạm 10 phút khi đang thực hiện đặt lịch.
- Thanh toán online qua VNPay sandbox hoặc thanh toán tại quầy.
- Xem My Bookings, viết/sửa/xóa review sau khi dịch vụ hoàn thành.

### 2. Chủ salon (Owner)
- Dashboard thống kê: booking, doanh thu, nhân viên, dịch vụ, rating, review.
- Biểu đồ booking 7 ngày và doanh thu 6 tháng (Chart.js).
- Quản lý booking: xác nhận, hoàn thành, hủy.
- Quản lý nhân viên, lịch làm việc và ngày nghỉ (day off).
- Phản hồi review của khách hàng.

### 3. Quản trị viên (Admin)
- Dashboard tổng quan toàn hệ thống.
- Quản lý users: khóa/mở tài khoản.
- Quản lý salons: duyệt, ẩn, mở lại, xóa mềm.
- Kiểm duyệt reviews: publish, flag, remove review vi phạm.

## ⭐ Tính năng nổi bật
- **Hold slot 10 phút:** tránh hai khách đặt cùng một khung giờ.
- **Staff schedule:** chỉ hiển thị slot hợp lệ theo lịch làm việc thực tế của thợ.
- **Verified review:** review gắn với booking đã hoàn thành.
- **Dashboard thật:** dùng dữ liệu MySQL và Chart.js.

## ⚙️ Hướng dẫn cài đặt

1. **Môi trường:** Cài đặt XAMPP (PHP 8.0+, MySQL).
2. **Source Code:** Copy dự án vào `htdocs/Wordpressnangcao/wordpress`.
3. **Database:** Tạo database và import file SQL hoặc cài đặt WP mới.
4. **Kích hoạt:** Truy cập Admin -> Plugins -> Activate "LOPAS Barber Spa".
5. **Cấu hình:** Sử dụng Shortcode `[lopas_booking_form]` trên các trang WordPress.

## 🔐 Tài khoản test

| Vai trò  | Email                     | Mật khẩu       | Ghi chú            |
| -------- | ------------------------- | -------------- | ------------------ |

| Customer | `damtrungson09@gmail.com` | `Anhhd@12345`  |   |

## 📄 License & Liên hệ
Dự án được phát triển cho môn học **Phần mềm mã nguồn mở**.
Hà Nội, 2026.
