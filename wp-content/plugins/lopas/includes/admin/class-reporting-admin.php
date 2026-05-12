<?php
/**
 * LOPAS Reporting Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Reporting_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_reporting_page'));
    }
    
    /**
     * Add reporting page to admin menu
     */
    public function add_reporting_page() {
        add_submenu_page(
            'lopas-dashboard',
            'Reports',
            'Reports',
            'manage_options',
            'lopas-reports',
            array($this, 'render_reporting_page')
        );
    }
    
    /**
     * Render reporting page
     */
    public function render_reporting_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        require_once LOPAS_PATH . 'includes/class-reporting.php';
        
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'month';
        $stats = LOPAS_Reporting::get_dashboard_stats($period);
        $revenue_report = LOPAS_Reporting::get_revenue_report($period);
        $booking_report = LOPAS_Reporting::get_booking_report($period);
        $service_popularity = LOPAS_Reporting::get_service_popularity(10);
        $salon_performance = LOPAS_Reporting::get_salon_performance(10);
        $payment_methods = LOPAS_Reporting::get_payment_method_report($period);
        
        ?>
        <div class="wrap">
            <h1>Reports & Analytics</h1>
            
            <div class="period-selector">
                <label for="period">Select Period:</label>
                <select id="period" onchange="window.location.href='?page=lopas-reports&period=' + this.value">
                    <option value="week" <?php selected($period, 'week'); ?>>Last 7 Days</option>
                    <option value="month" <?php selected($period, 'month'); ?>>Last 30 Days</option>
                    <option value="quarter" <?php selected($period, 'quarter'); ?>>Last 90 Days</option>
                    <option value="year" <?php selected($period, 'year'); ?>>Last Year</option>
                </select>
            </div>
            
            <!-- Dashboard Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <p class="stat-value"><?php echo esc_html($stats['total_bookings']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <p class="stat-value"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> VND</p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p class="stat-value"><?php echo esc_html($stats['total_orders']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Completed Bookings</h3>
                    <p class="stat-value"><?php echo esc_html($stats['completed_bookings']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Cancelled Bookings</h3>
                    <p class="stat-value"><?php echo esc_html($stats['cancelled_bookings']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Avg Booking Value</h3>
                    <p class="stat-value"><?php echo number_format($stats['average_booking_value'], 0, ',', '.'); ?> VND</p>
                </div>
                <div class="stat-card">
                    <h3>Payment Success Rate</h3>
                    <p class="stat-value"><?php echo esc_html($stats['payment_success_rate']); ?>%</p>
                </div>
                <div class="stat-card">
                    <h3>Total Payments</h3>
                    <p class="stat-value"><?php echo esc_html($stats['total_payments']); ?></p>
                </div>
            </div>
            
            <!-- Revenue Report -->
            <div class="report-section">
                <h2>Revenue Report</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transactions</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenue_report as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->date); ?></td>
                            <td><?php echo esc_html($row->transaction_count); ?></td>
                            <td><?php echo number_format($row->total_amount, 0, ',', '.'); ?> VND</td>
                            <td><?php echo esc_html(strtoupper($row->payment_method)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Booking Report -->
            <div class="report-section">
                <h2>Booking Report</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Completed</th>
                            <th>Cancelled</th>
                            <th>Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($booking_report as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->date); ?></td>
                            <td><?php echo esc_html($row->total_bookings); ?></td>
                            <td><?php echo esc_html($row->completed); ?></td>
                            <td><?php echo esc_html($row->cancelled); ?></td>
                            <td><?php echo esc_html($row->pending); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Service Popularity -->
            <div class="report-section">
                <h2>Top Services</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($service_popularity as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->name); ?></td>
                            <td><?php echo esc_html($row->booking_count); ?></td>
                            <td><?php echo number_format($row->total_revenue, 0, ',', '.'); ?> VND</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Salon Performance -->
            <div class="report-section">
                <h2>Salon Performance</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Salon Name</th>
                            <th>Total Bookings</th>
                            <th>Completed</th>
                            <th>Cancelled</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salon_performance as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->name); ?></td>
                            <td><?php echo esc_html($row->total_bookings); ?></td>
                            <td><?php echo esc_html($row->completed_bookings); ?></td>
                            <td><?php echo esc_html($row->cancelled_bookings); ?></td>
                            <td><?php echo esc_html($row->completion_rate); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Payment Methods -->
            <div class="report-section">
                <h2>Payment Methods</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th>Transactions</th>
                            <th>Successful</th>
                            <th>Failed</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payment_methods as $row): ?>
                        <tr>
                            <td><?php echo esc_html(strtoupper($row->payment_method)); ?></td>
                            <td><?php echo esc_html($row->transaction_count); ?></td>
                            <td><?php echo esc_html($row->successful); ?></td>
                            <td><?php echo esc_html($row->failed); ?></td>
                            <td><?php echo number_format($row->total_amount, 0, ',', '.'); ?> VND</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <style>
                .period-selector {
                    margin: 20px 0;
                    padding: 15px;
                    background: #f9f9f9;
                    border-radius: 5px;
                }
                
                .period-selector label {
                    margin-right: 10px;
                    font-weight: bold;
                }
                
                .period-selector select {
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                }
                
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin: 20px 0;
                }
                
                .stat-card {
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 20px;
                    text-align: center;
                }
                
                .stat-card h3 {
                    margin: 0 0 10px 0;
                    color: #666;
                    font-size: 14px;
                }
                
                .stat-value {
                    margin: 0;
                    font-size: 28px;
                    font-weight: bold;
                    color: #007bff;
                }
                
                .report-section {
                    background: #fff;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 5px;
                    border: 1px solid #ddd;
                }
                
                .report-section h2 {
                    margin-top: 0;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 10px;
                }
                
                .widefat {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                .widefat th,
                .widefat td {
                    padding: 12px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                
                .widefat th {
                    background: #f5f5f5;
                    font-weight: bold;
                }
                
                .widefat tbody tr:hover {
                    background: #f9f9f9;
                }
            </style>
        </div>
        <?php
    }
}
