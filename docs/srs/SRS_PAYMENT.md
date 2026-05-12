# Đặc tả Yêu cầu: Thanh toán trực tuyến (PAY-01)

## 1. Giới thiệu
Tích hợp cổng thanh toán VNPay để hỗ trợ khách hàng thanh toán đặt lịch nhanh chóng và an toàn.

## 2. Quy trình thanh toán
1. Sau khi xác nhận đặt lịch, khách hàng chọn phương thức "Thanh toán qua VNPay".
2. Hệ thống tạo mã giao dịch và chuyển hướng sang Sandbox VNPay.
3. Khách hàng thực hiện thanh toán giả lập.
4. VNPay trả về kết quả (IPN/Return URL) -> Hệ thống cập nhật trạng thái Booking thành "Đã thanh toán" (Paid).

## 3. Quản lý
- Admin có thể theo dõi danh sách giao dịch, mã tham chiếu và số tiền thực nhận trong Dashboard.
