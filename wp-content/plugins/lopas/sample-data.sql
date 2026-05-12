-- =============================================
-- LOPAS Sample Data - Clone from Barber-Spa
-- Run this SQL to populate sample data
-- =============================================

-- Clear existing data (optional)
-- DELETE FROM wp_lopas_services;
-- DELETE FROM wp_lopas_staff;
-- DELETE FROM wp_lopas_salons;
-- DELETE FROM wp_lopas_categories;

-- =============================================
-- Categories (from barber-spa)
-- =============================================
INSERT INTO wp_lopas_categories (name, description, is_active, sort_order) VALUES
('Cắt tóc', 'Dịch vụ cắt tóc nam nữ', 1, 1),
('Uốn tóc', 'Dịch vụ uốn và tạo kiểu tóc', 1, 2),
('Nhuộm tóc', 'Dịch vụ nhuộm và phục hồi màu tóc', 1, 3),
('Spa & Chăm sóc da', 'Chăm sóc da mặt và spa thư giãn', 1, 4),
('Gội đầu & Massage', 'Gội đầu dưỡng sinh và massage thư giãn', 1, 5);

-- =============================================
-- Salons (from barber-spa)
-- =============================================
INSERT INTO wp_lopas_salons (name, address, phone, description, opening_time, closing_time, status, avg_rating, total_reviews, total_bookings) VALUES
('Barber House Quận 1', '101 Lê Thánh Tôn, Quận 1, TP.HCM', '0901111111', 'Salon tóc nam cao cấp tại trung tâm Quận 1, chuyên cắt tóc, uốn, nhuộm và chăm sóc da.', '08:00:00', '20:00:00', 'active', 4.80, 12, 85),
('Gentleman Barber Quận 3', '220 Võ Văn Tần, Quận 3, TP.HCM', '0902222222', 'Không gian barber hiện đại, phù hợp khách hàng trẻ và dân văn phòng.', '08:00:00', '20:00:00', 'active', 4.60, 8, 53),
('Luxury Spa Bình Thạnh', '58 Điện Biên Phủ, Bình Thạnh, TP.HCM', '0903333333', 'Dịch vụ chăm sóc da, massage, gội đầu dưỡng sinh và làm đẹp tổng hợp.', '08:00:00', '20:00:00', 'active', 4.90, 15, 97);

-- =============================================
-- Services for Salon 1 (Barber House)
-- =============================================
INSERT INTO wp_lopas_services (salon_id, category_id, name, description, price, duration, is_active, sort_order) VALUES
(1, 1, 'Cắt tóc Classic', 'Cắt tóc cổ điển gọn gàng, phù hợp dân văn phòng.', 120000, 45, 1, 1),
(1, 1, 'Cắt tóc Fade', 'Cắt tóc fade hiện đại, xu hướng trẻ trung.', 150000, 60, 1, 2),
(1, 2, 'Uốn texture nam', 'Uốn texture nhẹ tạo volume tự nhiên.', 450000, 120, 1, 3),
(1, 3, 'Nhuộm màu thời trang', 'Nhuộm các tông màu phù hợp phong cách cá tính.', 550000, 150, 1, 4),
(1, 5, 'Gội đầu massage', 'Gội đầu sạch da dầu kết hợp massage thư giãn.', 100000, 30, 1, 5),
(1, 4, 'Chăm sóc da cơ bản', 'Làm sạch sâu và dưỡng ẩm da mặt.', 250000, 60, 1, 6);

-- =============================================
-- Services for Salon 2 (Gentleman Barber)
-- =============================================
INSERT INTO wp_lopas_services (salon_id, category_id, name, description, price, duration, is_active, sort_order) VALUES
(2, 1, 'Cắt tóc Gentleman', 'Cắt tóc lịch lãm phong cách gentleman.', 130000, 45, 1, 1),
(2, 1, 'Cắt tóc Modern Pompadour', 'Tạo kiểu pompadour hiện đại.', 170000, 60, 1, 2),
(2, 2, 'Uốn layer', 'Uốn layer nhẹ giúp tóc bồng và vào nếp.', 420000, 120, 1, 3),
(2, 3, 'Nhuộm nâu lạnh', 'Nhuộm tông nâu lạnh thanh lịch.', 480000, 140, 1, 4),
(2, 5, 'Gội đầu thư giãn', 'Gội đầu và massage vai cổ gáy.', 90000, 30, 1, 5),
(2, 4, 'Detox da đầu', 'Làm sạch da dầu và dưỡng nang tóc.', 220000, 50, 1, 6);

-- =============================================
-- Services for Salon 3 (Luxury Spa)
-- =============================================
INSERT INTO wp_lopas_services (salon_id, category_id, name, description, price, duration, is_active, sort_order) VALUES
(3, 4, 'Facial cơ bản', 'Chăm sóc da mặt cơ bản, làm sạch và dưỡng ẩm.', 300000, 60, 1, 1),
(3, 4, 'Facial chuyên sâu', 'Chăm sóc da chuyên sâu, hút mụn, phục hồi da.', 550000, 90, 1, 2),
(3, 5, 'Gội đầu dưỡng sinh', 'Gội đầu kết hợp massage thư giãn.', 180000, 45, 1, 3),
(3, 5, 'Massage cổ vai gáy', 'Massage giảm mỏi, giải tỏa căng thẳng.', 250000, 60, 1, 4),
(3, 3, 'Nhuộm tóc phủ bạc', 'Nhuộm phủ bạc tự nhiên và bền màu.', 350000, 90, 1, 5),
(3, 2, 'Uốn phục hồi', 'Uốn tóc kết hợp dưỡng chất phục hồi.', 500000, 140, 1, 6);

-- =============================================
-- Staff for Salon 1
-- =============================================
INSERT INTO wp_lopas_staff (salon_id, name, phone, specialization, is_active) VALUES
(1, 'Trần Minh Barber', '0911000001', 'Chuyên cắt tóc fade, classic, texture', 1),
(1, 'Lê Quang Stylist', '0911000002', 'Chuyên nhuộm, uốn, chăm sóc da', 1),
(1, 'Phạm Anh Spa', '0911000003', 'Chuyên massage, gội đầu, facial', 1);

-- =============================================
-- Staff for Salon 2
-- =============================================
INSERT INTO wp_lopas_staff (salon_id, name, phone, specialization, is_active) VALUES
(2, 'Nguyễn Hoàng', '0912000001', 'Chuyên gentleman, pompadour', 1),
(2, 'Đỗ Thanh', '0912000002', 'Chuyên uốn, nhuộm', 1),
(2, 'Võ Tuấn', '0912000003', 'Chuyên gội đầu, massage', 1);

-- =============================================
-- Staff for Salon 3
-- =============================================
INSERT INTO wp_lopas_staff (salon_id, name, phone, specialization, is_active) VALUES
(3, 'Mai Linh Spa', '0913000001', 'Chuyên facial, chăm sóc da', 1),
(3, 'Bảo Ngọc', '0913000002', 'Chuyên gội đầu, massage', 1),
(3, 'Minh Thu', '0913000003', 'Chuyên uốn, nhuộm, phủ bạc', 1);

-- =============================================
-- Verify data
-- =============================================
SELECT 'Categories:', COUNT(*) FROM wp_lopas_categories;
SELECT 'Salons:', COUNT(*) FROM wp_lopas_salons;
SELECT 'Services:', COUNT(*) FROM wp_lopas_services;
SELECT 'Staff:', COUNT(*) FROM wp_lopas_staff;

-- =============================================
-- Expected results:
-- Categories: 5
-- Salons: 3
-- Services: 18 (6 per salon)
-- Staff: 9 (3 per salon)
-- =============================================
