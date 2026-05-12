<?php
/**
 * LOPAS Database class
 * Handles custom tables creation and management
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Database {
    
    /**
     * Create all custom database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        // Use charset & collate from WordPress
        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix . 'lopas_';
        
        // SQL queries
        $sql_array = array();
        
        // 0. CATEGORIES TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description LONGTEXT,
            icon_id INT,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY name (name)
        ) $charset_collate;";
        
        // 1. SALONS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}salons (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT,
            address VARCHAR(255),
            phone VARCHAR(20),
            email VARCHAR(100),
            latitude DECIMAL(10, 8),
            longitude DECIMAL(11, 8),
            avatar_id INT,
            cover_id INT,
            opening_time TIME,
            closing_time TIME,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_id (user_id),
            INDEX idx_status (status)
        ) $charset_collate;";
        
        // 2. SERVICES TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}services (
            id INT PRIMARY KEY AUTO_INCREMENT,
            salon_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT,
            category VARCHAR(100),
            price DECIMAL(10, 2) NOT NULL,
            duration INT NOT NULL,
            image_id INT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES {$prefix}salons(id) ON DELETE CASCADE,
            INDEX idx_salon_category (salon_id, category),
            INDEX idx_status (status)
        ) $charset_collate;";
        
        // 3. STAFF TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}staff (
            id INT PRIMARY KEY AUTO_INCREMENT,
            salon_id INT NOT NULL,
            user_id BIGINT(20) UNSIGNED,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(20),
            avatar_id INT,
            specialization VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES {$prefix}salons(id) ON DELETE CASCADE,
            INDEX idx_salon (salon_id)
        ) $charset_collate;";
        
        // 4. BOOKINGS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}bookings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            booking_code VARCHAR(20) UNIQUE,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            salon_id INT NOT NULL,
            service_id INT NOT NULL,
            staff_id INT,
            booking_date DATE NOT NULL,
            booking_time TIME NOT NULL,
            duration INT,
            note LONGTEXT,
            status ENUM('pending', 'confirmed', 'in-progress', 'completed', 'cancelled') DEFAULT 'pending',
            cancellation_reason LONGTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
            FOREIGN KEY (salon_id) REFERENCES {$prefix}salons(id),
            FOREIGN KEY (service_id) REFERENCES {$prefix}services(id),
            FOREIGN KEY (staff_id) REFERENCES {$prefix}staff(id),
            INDEX idx_user_date (user_id, booking_date),
            INDEX idx_salon_date (salon_id, booking_date),
            INDEX idx_status (status),
            UNIQUE KEY slot_booking (salon_id, staff_id, booking_date, booking_time)
        ) $charset_collate;";
        
        // 5. ORDERS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_code VARCHAR(20) UNIQUE,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            total_price DECIMAL(10, 2) NOT NULL,
            voucher_id INT,
            discount_amount DECIMAL(10, 2) DEFAULT 0,
            final_price DECIMAL(10, 2) NOT NULL,
            payment_method ENUM('vnpay', 'cod') DEFAULT 'cod',
            payment_status ENUM('unpaid', 'pending', 'paid', 'refunded') DEFAULT 'unpaid',
            order_status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
            notes LONGTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, created_at),
            INDEX idx_status (payment_status, order_status)
        ) $charset_collate;";
        
        // 6. ORDER ITEMS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            booking_id INT,
            service_id INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            quantity INT DEFAULT 1,
            subtotal DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES {$prefix}orders(id) ON DELETE CASCADE,
            FOREIGN KEY (booking_id) REFERENCES {$prefix}bookings(id),
            FOREIGN KEY (service_id) REFERENCES {$prefix}services(id)
        ) $charset_collate;";
        
        // 7. PAYMENTS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}payments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            transaction_code VARCHAR(100) UNIQUE,
            amount DECIMAL(10, 2) NOT NULL,
            payment_method ENUM('vnpay', 'cod') DEFAULT 'cod',
            status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
            response_data LONGTEXT,
            refund_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES {$prefix}orders(id),
            INDEX idx_transaction (transaction_code),
            INDEX idx_status (status)
        ) $charset_collate;";
        
        // 8. REFUNDS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}refunds (
            id INT PRIMARY KEY AUTO_INCREMENT,
            payment_id INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            reason VARCHAR(255),
            status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (payment_id) REFERENCES {$prefix}payments(id)
        ) $charset_collate;";
        
        // 9. VOUCHERS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}vouchers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            code VARCHAR(50) NOT NULL,
            description LONGTEXT,
            discount_type ENUM('fixed', 'percent') DEFAULT 'fixed',
            discount_value DECIMAL(10, 2),
            max_discount DECIMAL(10, 2),
            min_order_value DECIMAL(10, 2),
            max_uses INT,
            used_count INT DEFAULT 0,
            valid_from DATE,
            valid_to DATE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_valid_date (valid_from, valid_to),
            UNIQUE KEY code (code)
        ) $charset_collate;";
        
        // 10. REVIEWS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}reviews (
            id INT PRIMARY KEY AUTO_INCREMENT,
            booking_id INT NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            salon_id INT NOT NULL,
            rating TINYINT UNSIGNED,
            comment LONGTEXT,
            approved BOOLEAN DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES {$prefix}bookings(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID),
            FOREIGN KEY (salon_id) REFERENCES {$prefix}salons(id),
            INDEX idx_salon_rating (salon_id, rating),
            INDEX idx_approved (approved)
        ) $charset_collate;";
        
        // 11. AVAILABILITY TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}availability (
            id INT PRIMARY KEY AUTO_INCREMENT,
            staff_id INT NOT NULL,
            day_of_week INT,
            start_time TIME,
            end_time TIME,
            break_start TIME,
            break_end TIME,
            status ENUM('active', 'inactive') DEFAULT 'active',
            FOREIGN KEY (staff_id) REFERENCES {$prefix}staff(id) ON DELETE CASCADE,
            INDEX idx_staff_day (staff_id, day_of_week)
        ) $charset_collate;";
        
        // 12. BOOKING HOLDS TABLE (Phase 8)
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}booking_holds (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED,
            session_id VARCHAR(128) NOT NULL,
            staff_id INT NOT NULL,
            service_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
            FOREIGN KEY (staff_id) REFERENCES {$prefix}staff(id) ON DELETE CASCADE,
            UNIQUE KEY session_slot (session_id, staff_id, service_date, start_time),
            INDEX idx_staff_slot (staff_id, service_date, start_time, end_time),
            INDEX idx_expires (expires_at)
        ) $charset_collate;";

        // 13. BOOKING REMINDERS TABLE
        $sql_array[] = "CREATE TABLE IF NOT EXISTS {$prefix}booking_reminders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            booking_id INT NOT NULL,
            reminder_type VARCHAR(50) NOT NULL,
            sent TINYINT(1) DEFAULT 0,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES {$prefix}bookings(id) ON DELETE CASCADE,
            INDEX idx_booking_reminder (booking_id, reminder_type)
        ) $charset_collate;";
        
        // Disable foreign key checks temporarily to avoid Errno 150 on some environments
        $wpdb->query("SET FOREIGN_KEY_CHECKS = 0;");
        
        // Execute SQL directly (not using dbDelta which has issues with FOREIGN KEY)
        foreach ($sql_array as $sql) {
            $wpdb->query($sql);
        }
        
        // Re-enable foreign key checks
        $wpdb->query("SET FOREIGN_KEY_CHECKS = 1;");
        
        do_action('lopas_tables_created');
    }
    
    /**
     * Drop all custom tables (use with caution!)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $prefix = $wpdb->prefix . 'lopas_';
        
        $tables = array(
            'booking_reminders',
            'booking_holds',
            'availability',
            'reviews',
            'vouchers',
            'refunds',
            'payments',
            'order_items',
            'orders',
            'bookings',
            'staff',
            'services',
            'salons'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }
        
        do_action('lopas_tables_dropped');
    }
    
    /**
     * Check if custom tables exist
     */
    public static function tables_exist() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'lopas_salons';
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
    }
    
    /**
     * Get table name with prefix
     * 
     * @param string $table Table name (without prefix)
     * @return string Full table name
     */
    public static function get_table($table) {
        global $wpdb;
        return $wpdb->prefix . 'lopas_' . $table;
    }
}
