# BÁO CÁO ĐỒ ÁN MÔN HỌC: PHẦN MỀM MÃ NGUỒN MỞ

---

## 1. THÔNG TIN CHUNG
- **Tên đề tài:** Barber Spa - Website Đặt Lịch Salon / Barber Trực Tuyến
- **Tên môn học:** Phần mềm mã nguồn mở
- **Nhóm thực hiện:** Nhóm 1
- **Giảng viên hướng dẫn:** (Vui lòng điền tên giảng viên)

### Danh sách thành viên:
| STT | Họ và tên | MSSV | Vai trò |
| --- | --- | --- | --- |
| 1 | Nguyễn Công Sơn | 23810310102 | Nhóm trưởng |
| 2 | Nguyễn Văn Quang | 23810310108 | Thành viên |
| 3 | Nguyễn Văn Danh | 23810310136 | Thành viên |

---

## 2. GIỚI THIỆU ĐỀ TÀI
### 2.1 Giới thiệu
Barber Spa là nền tảng đặt lịch salon/barber trực tuyến, kết nối khách hàng với các salon có dịch vụ cắt tóc, gội đầu, massage, chăm sóc da và làm đẹp. Hệ thống được phát triển dựa trên WordPress với Plugin LOPAS tự xây dựng, hỗ trợ 3 vai trò chính:
- **Khách hàng (Customer):** tìm kiếm salon, xem dịch vụ, đặt lịch, thanh toán, theo dõi booking, viết review.
- **Chủ salon (Owner):** quản lý salon, nhân viên, dịch vụ, lịch hẹn, doanh thu và review của salon mình.
- **Quản trị viên (Admin):** quản lý người dùng, salon, danh mục, booking, payment, review và dashboard toàn hệ thống.

### 2.2 Mục tiêu hệ thống
- Xây dựng nền tảng kết nối khách hàng với salon theo thời gian thực.
- Tự động hóa quy trình đặt chỗ, tránh chồng chéo lịch hẹn.
- Tích hợp thanh toán trực tuyến an toàn qua VNPay.
- Cung cấp công cụ quản lý doanh thu và nhân sự cho chủ salon.

### 2.3 Ý nghĩa thực tế
Giúp các cơ sở kinh doanh làm đẹp giảm thiểu thời gian chờ đợi của khách, tối ưu hóa công suất làm việc của nhân viên và mở rộng kênh tiếp cận khách hàng trên môi trường internet.

### 2.4 Đối tượng sử dụng
- **Khách hàng:** Người có nhu cầu tìm kiếm và sử dụng dịch vụ làm đẹp.
- **Chủ Salon:** Người quản lý vận hành cơ sở.
- **Quản trị viên (Admin):** Người điều phối toàn bộ hệ thống.

---

## 3. DANH SÁCH CHỨC NĂNG CHÍNH
- **Xác thực:** Đăng ký, đăng nhập, quản lý hồ sơ qua WordPress.
- **Tìm kiếm:** Tra cứu salon theo địa điểm, dịch vụ, đánh giá.
- **Đặt lịch:** Quy trình 4 bước (Dịch vụ -> Thợ -> Giờ -> Xác nhận).
- **Thanh toán:** Tích hợp VNPay Sandbox.
- **Quản trị Salon:** Quản lý thợ, dịch vụ, lịch làm việc, báo cáo doanh thu.
- **Dashboard:** Thống kê biểu đồ trực quan (Chart.js).
- **Đánh giá:** Review và rating sau khi hoàn thành dịch vụ.

---

## 4. KIẾN TRÚC HỆ THỐNG & CÔNG NGHỆ
### 4.1 Kiến trúc
Hệ thống được xây dựng trên nền tảng **WordPress** (Core mã nguồn mở lớn nhất thế giới). Toàn bộ logic nghiệp vụ được đóng gói trong Plugin **LOPAS** theo mô hình **MVC** (Model-View-Controller).

### 4.2 Công nghệ sử dụng
- **Ngôn ngữ:** PHP 8.0, JavaScript, SQL.
- **Cơ sở dữ liệu:** MySQL.
- **Framework/Thư viện:** Bootstrap 5 (UI), Chart.js (Báo cáo), VNPay SDK (Thanh toán).
- **Môi trường:** XAMPP (Local), Hosting (Live).

---

## 5. PHÂN CÔNG CÔNG VIỆC
| Thành viên | Phụ trách chính | Mức độ đóng góp |
| --- | --- | --- |
| **Nguyễn Công Sơn** | Nhóm trưởng, Thiết kế DB, Plugin Core, Backend Admin, API VNPay | 35% |
| **Nguyễn Văn Quang** | Frontend UI/UX, Quy trình Booking, AJAX Slot logic, Search API | 33% |
| **Nguyễn Văn Danh** | Thống kê Dashboard, Quản lý User/Review, Soạn thảo tài liệu báo cáo | 32% |

---

## 6. KẾT QUẢ THỰC HIỆN
### 6.1 Chức năng đã hoàn thành
- Hoàn thành 100% các tính năng cốt lõi (Đặt lịch, Thanh toán, Quản lý salon).
- Tích hợp thành công biểu đồ báo cáo và hệ thống giữ slot (Hold slot).

### 6.2 Chức năng nâng cao
- **Hold slot 10 phút:** Chống đặt trùng lịch thời gian thực.
- **Verified Review:** Đảm bảo tính khách quan của đánh giá.

---

## 7. HƯỚNG DẪN CÀI ĐẶT
1. Giải nén source code vào thư mục `htdocs`.
2. Tạo database MySQL và import dữ liệu từ file `sample-data.sql`.
3. Cấu hình file `wp-config.php` (DB_NAME, DB_USER, DB_PASSWORD).
4. Đăng nhập vào WordPress Admin và kích hoạt Plugin **LOPAS**.

---

## 8. KẾT LUẬN & HƯỚNG PHÁT TRIỂN
### 8.1 Kết luận
Dự án đã hoàn thành đúng tiến độ, đáp ứng đầy đủ các yêu cầu của môn học Phần mềm mã nguồn mở. Hệ thống chạy ổn định cả trên môi trường local và online.

### 8.2 Hướng phát triển
- Phát triển ứng dụng Mobile (React Native) đồng bộ với hệ thống.
- Tích hợp AI để gợi ý thợ/dịch vụ phù hợp dựa trên sở thích khách hàng.

---
*(Lưu ý: Bạn hãy copy nội dung này vào file Word, chèn thêm ảnh chụp màn hình vào mục 6 để hoàn thiện báo cáo).*
