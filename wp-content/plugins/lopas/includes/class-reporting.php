<?php
/**
 * LOPAS Reporting & Analytics
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Reporting {
    
    /**
     * Get dashboard statistics
     */
    public static function get_dashboard_stats($period = 'month') {
        global $wpdb;
        
        $date_range = self::get_date_range($period);
        
        return array(
            'total_bookings' => self::count_bookings($date_range),
            'total_revenue' => self::get_total_revenue($date_range),
            'total_orders' => self::count_orders($date_range),
            'total_payments' => self::count_payments($date_range),
            'completed_bookings' => self::count_completed_bookings($date_range),
            'cancelled_bookings' => self::count_cancelled_bookings($date_range),
            'average_booking_value' => self::get_average_booking_value($date_range),
            'payment_success_rate' => self::get_payment_success_rate($date_range)
        );
    }
    
    /**
     * Get revenue report
     */
    public static function get_revenue_report($period = 'month') {
        global $wpdb;
        
        $date_range = self::get_date_range($period);
        $payments_table = LOPAS_Database::get_table('payments');
        
        $query = $wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as transaction_count,
                SUM(amount) as total_amount,
                payment_method
            FROM {$payments_table}
            WHERE status = 'success'
            AND created_at BETWEEN %s AND %s
            GROUP BY DATE(created_at), payment_method
            ORDER BY date DESC",
            $date_range['start'],
            $date_range['end']
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get booking report
     */
    public static function get_booking_report($period = 'month') {
        global $wpdb;
        
        $date_range = self::get_date_range($period);
        $bookings_table = LOPAS_Database::get_table('bookings');
        
        $query = $wpdb->prepare(
            "SELECT 
                DATE(booking_date) as date,
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM {$bookings_table}
            WHERE booking_date BETWEEN %s AND %s
            GROUP BY DATE(booking_date)
            ORDER BY date DESC",
            $date_range['start'],
            $date_range['end']
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get service popularity report
     */
    public static function get_service_popularity($limit = 10) {
        global $wpdb;
        
        $bookings_table = LOPAS_Database::get_table('bookings');
        $services_table = LOPAS_Database::get_table('services');
        
        $query = $wpdb->prepare(
            "SELECT 
                s.id,
                s.name,
                COUNT(b.id) as booking_count,
                SUM(s.price) as total_revenue
            FROM {$services_table} s
            LEFT JOIN {$bookings_table} b ON s.id = b.service_id
            GROUP BY s.id, s.name
            ORDER BY booking_count DESC
            LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get salon performance report
     */
    public static function get_salon_performance($limit = 10) {
        global $wpdb;
        
        $bookings_table = LOPAS_Database::get_table('bookings');
        $salons_table = LOPAS_Database::get_table('salons');
        
        $query = $wpdb->prepare(
            "SELECT 
                s.id,
                s.name,
                COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                ROUND(SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) / COUNT(b.id) * 100, 2) as completion_rate
            FROM {$salons_table} s
            LEFT JOIN {$bookings_table} b ON s.id = b.salon_id
            GROUP BY s.id, s.name
            ORDER BY total_bookings DESC
            LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get customer report
     */
    public static function get_customer_report($limit = 10) {
        global $wpdb;
        
        $bookings_table = LOPAS_Database::get_table('bookings');
        $orders_table = LOPAS_Database::get_table('orders');
        
        $query = $wpdb->prepare(
            "SELECT 
                o.user_id,
                COUNT(DISTINCT b.id) as total_bookings,
                COUNT(DISTINCT o.id) as total_orders,
                SUM(o.total_amount) as total_spent,
                MAX(b.booking_date) as last_booking
            FROM {$orders_table} o
            LEFT JOIN {$bookings_table} b ON o.id = b.id
            GROUP BY o.user_id
            ORDER BY total_spent DESC
            LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get payment method report
     */
    public static function get_payment_method_report($period = 'month') {
        global $wpdb;
        
        $date_range = self::get_date_range($period);
        $payments_table = LOPAS_Database::get_table('payments');
        
        $query = $wpdb->prepare(
            "SELECT 
                payment_method,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(amount) as total_amount
            FROM {$payments_table}
            WHERE created_at BETWEEN %s AND %s
            GROUP BY payment_method",
            $date_range['start'],
            $date_range['end']
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get date range
     */
    private static function get_date_range($period) {
        $end = date('Y-m-d 23:59:59');
        
        switch ($period) {
            case 'week':
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case 'quarter':
                $start = date('Y-m-d 00:00:00', strtotime('-90 days'));
                break;
            case 'year':
                $start = date('Y-m-d 00:00:00', strtotime('-365 days'));
                break;
            default:
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
        }
        
        return array('start' => $start, 'end' => $end);
    }
    
    /**
     * Count bookings
     */
    private static function count_bookings($date_range) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE booking_date BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        )));
    }
    
    /**
     * Count completed bookings
     */
    private static function count_completed_bookings($date_range) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE status = 'completed' AND booking_date BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        )));
    }
    
    /**
     * Count cancelled bookings
     */
    private static function count_cancelled_bookings($date_range) {
        global $wpdb;
        $table = LOPAS_Database::get_table('bookings');
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE status = 'cancelled' AND booking_date BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        )));
    }
    
    /**
     * Get total revenue
     */
    private static function get_total_revenue($date_range) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$table} WHERE status = 'success' AND created_at BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        ));
        
        return floatval($result ?? 0);
    }
    
    /**
     * Count orders
     */
    private static function count_orders($date_range) {
        global $wpdb;
        $table = LOPAS_Database::get_table('orders');
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE created_at BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        )));
    }
    
    /**
     * Count payments
     */
    private static function count_payments($date_range) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE created_at BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        )));
    }
    
    /**
     * Get average booking value
     */
    private static function get_average_booking_value($date_range) {
        global $wpdb;
        $orders_table = LOPAS_Database::get_table('orders');
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(total_amount) FROM {$orders_table} WHERE created_at BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        ));
        
        return floatval($result ?? 0);
    }
    
    /**
     * Get payment success rate
     */
    private static function get_payment_success_rate($date_range) {
        global $wpdb;
        $table = LOPAS_Database::get_table('payments');
        
        $total = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE created_at BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        )));
        
        if ($total === 0) {
            return 0;
        }
        
        $successful = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE status = 'success' AND created_at BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        )));
        
        return round(($successful / $total) * 100, 2);
    }
}
