<?php
/**
 * LOPAS Booking Reminder
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Booking_Reminder {
    
    public function __construct() {
        // Schedule reminder emails
        add_action('wp_scheduled_event_lopas_send_reminders', array($this, 'send_reminders'));
        
        // Register cron schedule
        if (!wp_next_scheduled('wp_scheduled_event_lopas_send_reminders')) {
            wp_schedule_event(time(), 'hourly', 'wp_scheduled_event_lopas_send_reminders');
        }
    }
    
    /**
     * Send booking reminders
     */
    public function send_reminders() {
        global $wpdb;
        
        if (!get_option('lopas_reminders_enabled', true)) {
            return;
        }
        
        $bookings_table = LOPAS_Database::get_table('bookings');
        $reminders_table = LOPAS_Database::get_table('booking_reminders');
        
        // Get bookings that need reminders (24 hours before)
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $now = date('H:i:s');
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT b.* FROM {$bookings_table} b
            WHERE b.booking_date = %s
            AND b.status IN ('pending', 'confirmed')
            AND b.id NOT IN (
                SELECT booking_id FROM {$reminders_table} 
                WHERE reminder_type = 'before_24h' AND sent = 1
            )
            LIMIT 100",
            $tomorrow
        ));
        
        if (is_array($bookings)) {
            foreach ($bookings as $booking) {
                $this->send_reminder_email($booking, 'before_24h');
                $this->log_reminder($booking->id, 'before_24h');
            }
        }
        
        // Get bookings that need reminders (1 hour before)
        $today = date('Y-m-d');
        $one_hour_later = date('H:i:s', strtotime('+1 hour'));
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT b.* FROM {$bookings_table} b
            WHERE b.booking_date = %s
            AND TIME(CONCAT(b.booking_date, ' ', b.booking_time)) BETWEEN %s AND %s
            AND b.status IN ('pending', 'confirmed')
            AND b.id NOT IN (
                SELECT booking_id FROM {$reminders_table} 
                WHERE reminder_type = 'before_1h' AND sent = 1
            )
            LIMIT 100",
            $today,
            $now,
            $one_hour_later
        ));
        
        if (is_array($bookings)) {
            foreach ($bookings as $booking) {
                $this->send_reminder_email($booking, 'before_1h');
                $this->log_reminder($booking->id, 'before_1h');
            }
        }
    }
    
    /**
     * Send reminder email
     */
    private function send_reminder_email($booking, $reminder_type) {
        $user = get_user_by('id', $booking->user_id);
        if (!$user) {
            return false;
        }
        
        $salon = LOPAS_Salon::get($booking->salon_id);
        $service = LOPAS_Service::get($booking->service_id);
        
        $to = $user->user_email;
        $booking_date = date('d/m/Y H:i', strtotime($booking->booking_date . ' ' . $booking->booking_time));
        
        if ($reminder_type === 'before_24h') {
            $subject = 'Reminder: Your booking is tomorrow - ' . $booking->booking_code;
            $message = $this->get_reminder_template_24h($booking, $user, $salon, $service, $booking_date);
        } else {
            $subject = 'Reminder: Your booking is in 1 hour - ' . $booking->booking_code;
            $message = $this->get_reminder_template_1h($booking, $user, $salon, $service, $booking_date);
        }
        
        return wp_mail($to, $subject, $message, $this->get_email_headers());
    }
    
    /**
     * Get 24-hour reminder template
     */
    private function get_reminder_template_24h($booking, $user, $salon, $service, $booking_date) {
        $message = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #007bff; color: #fff; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .details { margin: 20px 0; }
                    .details p { margin: 10px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Booking Reminder</h1>
                    </div>
                    <div class='content'>
                        <p>Hi " . esc_html($user->display_name) . ",</p>
                        <p>This is a reminder that your booking is scheduled for tomorrow!</p>
                        
                        <div class='details'>
                            <p><strong>Booking Code:</strong> " . esc_html($booking->booking_code) . "</p>
                            <p><strong>Salon:</strong> " . esc_html($salon->name) . "</p>
                            <p><strong>Service:</strong> " . esc_html($service->name) . "</p>
                            <p><strong>Date & Time:</strong> " . esc_html($booking_date) . "</p>
                            <p><strong>Location:</strong> " . esc_html($salon->address) . "</p>
                        </div>
                        
                        <p>Please arrive 10 minutes before your appointment.</p>
                        <p>If you need to cancel or reschedule, please contact us as soon as possible.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated reminder. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        return $message;
    }
    
    /**
     * Get 1-hour reminder template
     */
    private function get_reminder_template_1h($booking, $user, $salon, $service, $booking_date) {
        $message = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #ff6b6b; color: #fff; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .details { margin: 20px 0; }
                    .details p { margin: 10px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Booking Reminder - 1 Hour</h1>
                    </div>
                    <div class='content'>
                        <p>Hi " . esc_html($user->display_name) . ",</p>
                        <p><strong>Your booking is in 1 hour!</strong></p>
                        
                        <div class='details'>
                            <p><strong>Booking Code:</strong> " . esc_html($booking->booking_code) . "</p>
                            <p><strong>Salon:</strong> " . esc_html($salon->name) . "</p>
                            <p><strong>Service:</strong> " . esc_html($service->name) . "</p>
                            <p><strong>Date & Time:</strong> " . esc_html($booking_date) . "</p>
                            <p><strong>Location:</strong> " . esc_html($salon->address) . "</p>
                        </div>
                        
                        <p>Please head to the salon now. We look forward to seeing you!</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated reminder. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        return $message;
    }
    
    /**
     * Log reminder
     */
    private function log_reminder($booking_id, $reminder_type) {
        global $wpdb;
        $table = LOPAS_Database::get_table('booking_reminders');
        
        $wpdb->insert(
            $table,
            array(
                'booking_id' => intval($booking_id),
                'reminder_type' => sanitize_text_field($reminder_type),
                'sent' => 1
            ),
            array('%d', '%s', '%d')
        );
    }
    
    /**
     * Get email headers
     */
    private function get_email_headers() {
        $from_name = get_option('lopas_email_from_name', get_option('blogname'));
        $from_email = get_option('lopas_email_from', get_option('admin_email'));
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        return $headers;
    }
}
