# Đặc tả Yêu cầu: Xác thực người dùng (AUTH-01)

## 1. Giới thiệu
Chức năng này cho phép người dùng (Khách hàng, Chủ salon, Admin) đăng ký, đăng nhập và quản lý tài khoản trên hệ thống Barber Spa.

## 2. Luồng xử lý (Flow)
1. **Đăng ký:** Người dùng nhập Email, Mật khẩu, Họ tên -> Hệ thống kiểm tra trùng lặp -> Tạo tài khoản WordPress.
2. **Đăng nhập:** Người dùng nhập Email/Username và Mật khẩu -> Hệ thống xác thực qua WordPress Core -> Phân quyền truy cập.
3. **Quên mật khẩu:** Người dùng nhập Email -> Hệ thống gửi link reset mật khẩu.

## 3. Ràng buộc
- Mật khẩu phải có ít nhất 8 ký tự.
- Email phải đúng định dạng.
- Khóa tài khoản nếu đăng nhập sai quá 5 lần (Security feature).
