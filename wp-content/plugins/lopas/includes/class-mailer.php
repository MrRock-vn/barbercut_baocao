<?php
/**
 * LOPAS Email Mailer
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Mailer {
    
    /**
     * Send booking confirmation email
     */
    public static function send_booking_confirmation($booking_id) {
        if (!get_option('lopas_email_notifications_enabled', true)) {
            return false;
        }
        
        $booking = LOPAS_Booking::get($booking_id);
        if (!$booking) {
            return false;
        }
        
        $user = get_user_by('id', $booking->user_id);
        if (!$user) {
            return false;
        }
        
        $salon = LOPAS_Salon::get($booking->salon_id);
        $service = LOPAS_Service::get($booking->service_id);
        
        $to = $user->user_email;
        $subject = 'Booking Confirmation - ' . $booking->booking_code;
        
        $message = self::get_booking_confirmation_template($booking, $user, $salon, $service);
        
        return wp_mail($to, $subject, $message, self::get_email_headers());
    }
    
    /**
     * Send payment confirmation email
     */
    public static function send_payment_confirmation($payment_id) {
        if (!get_option('lopas_email_notifications_enabled', true)) {
            return false;
        }
        
        $payment = LOPAS_Payment::get($payment_id);
        if (!$payment) {
            return false;
        }
        
        $order = LOPAS_Order::get($payment->order_id);
        if (!$order) {
            return false;
        }
        
        $user = get_user_by('id', $order->user_id);
        if (!$user) {
            return false;
        }
        
        $to = $user->user_email;
        $subject = 'Payment Confirmation - ' . $payment->transaction_code;
        
        $message = self::get_payment_confirmation_template($payment, $order, $user);
        
        return wp_mail($to, $subject, $message, self::get_email_headers());
    }
    
    /**
     * Send booking cancellation email
     */
    public static function send_booking_cancellation($booking_id) {
        if (!get_option('lopas_email_notifications_enabled', true)) {
            return false;
        }
        
        $booking = LOPAS_Booking::get($booking_id);
        if (!$booking) {
            return false;
        }
        
        $user = get_user_by('id', $booking->user_id);
        if (!$user) {
            return false;
        }
        
        $salon = LOPAS_Salon::get($booking->salon_id);
        $service = LOPAS_Service::get($booking->service_id);
        
        $to = $user->user_email;
        $subject = 'Booking Cancelled - ' . $booking->booking_code;
        
        $message = self::get_booking_cancellation_template($booking, $user, $salon, $service);
        
        return wp_mail($to, $subject, $message, self::get_email_headers());
    }
    
    /**
     * Send refund notification email
     */
    public static function send_refund_notification($refund_id) {
        if (!get_option('lopas_email_notifications_enabled', true)) {
            return false;
        }
        
        $refund = LOPAS_Payment::get_refund($refund_id);
        if (!$refund) {
            return false;
        }
        
        $payment = LOPAS_Payment::get($refund->payment_id);
        if (!$payment) {
            return false;
        }
        
        $order = LOPAS_Order::get($payment->order_id);
        if (!$order) {
            return false;
        }
        
        $user = get_user_by('id', $order->user_id);
        if (!$user) {
            return false;
        }
        
        $to = $user->user_email;
        $subject = 'Refund Processed - ' . $payment->transaction_code;
        
        $message = self::get_refund_notification_template($refund, $payment, $order, $user);
        
        return wp_mail($to, $subject, $message, self::get_email_headers());
    }
    
    /**
     * Get booking confirmation email template
     */
    private static function get_booking_confirmation_template($booking, $user, $salon, $service) {
        $booking_date = date('d/m/Y H:i', strtotime($booking->booking_date . ' ' . $booking->booking_time));
        
        $message = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #333; color: #fff; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .details { margin: 20px 0; }
                    .details p { margin: 10px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Booking Confirmation</h1>
                    </div>
                    <div class='content'>
                        <p>Dear " . esc_html($user->display_name) . ",</p>
                        <p>Your booking has been confirmed. Here are the details:</p>
                        
                        <div class='details'>
                            <p><strong>Booking Code:</strong> " . esc_html($booking->booking_code) . "</p>
                            <p><strong>Salon:</strong> " . esc_html($salon->name) . "</p>
                            <p><strong>Service:</strong> " . esc_html($service->name) . "</p>
                            <p><strong>Date & Time:</strong> " . esc_html($booking_date) . "</p>
                            <p><strong>Status:</strong> " . esc_html(ucfirst($booking->status)) . "</p>
                        </div>
                        
                        <p>Please arrive 10 minutes before your appointment.</p>
                        <p>If you need to cancel or reschedule, please contact us as soon as possible.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        return $message;
    }
    
    /**
     * Get payment confirmation email template
     */
    private static function get_payment_confirmation_template($payment, $order, $user) {
        $message = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #333; color: #fff; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .details { margin: 20px 0; }
                    .details p { margin: 10px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                    .success { color: #28a745; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Payment Confirmation</h1>
                    </div>
                    <div class='content'>
                        <p>Dear " . esc_html($user->display_name) . ",</p>
                        <p class='success'><strong>Your payment has been successfully processed.</strong></p>
                        
                        <div class='details'>
                            <p><strong>Transaction Code:</strong> " . esc_html($payment->transaction_code) . "</p>
                            <p><strong>Order Code:</strong> " . esc_html($order->order_code) . "</p>
                            <p><strong>Amount:</strong> " . number_format($payment->amount, 0, ',', '.') . " VND</p>
                            <p><strong>Payment Method:</strong> " . esc_html(strtoupper($payment->payment_method)) . "</p>
                            <p><strong>Status:</strong> " . esc_html(ucfirst($payment->status)) . "</p>
                        </div>
                        
                        <p>Thank you for your payment. Your booking is now confirmed.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        return $message;
    }
    
    /**
     * Get booking cancellation email template
     */
    private static function get_booking_cancellation_template($booking, $user, $salon, $service) {
        $message = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #dc3545; color: #fff; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .details { margin: 20px 0; }
                    .details p { margin: 10px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Booking Cancelled</h1>
                    </div>
                    <div class='content'>
                        <p>Dear " . esc_html($user->display_name) . ",</p>
                        <p>Your booking has been cancelled. Here are the details:</p>
                        
                        <div class='details'>
                            <p><strong>Booking Code:</strong> " . esc_html($booking->booking_code) . "</p>
                            <p><strong>Salon:</strong> " . esc_html($salon->name) . "</p>
                            <p><strong>Service:</strong> " . esc_html($service->name) . "</p>
                        </div>
                        
                        <p>If you have any questions, please contact us.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        return $message;
    }
    
    /**
     * Get refund notification email template
     */
    private static function get_refund_notification_template($refund, $payment, $order, $user) {
        $message = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #333; color: #fff; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .details { margin: 20px 0; }
                    .details p { margin: 10px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                    .success { color: #28a745; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Refund Processed</h1>
                    </div>
                    <div class='content'>
                        <p>Dear " . esc_html($user->display_name) . ",</p>
                        <p class='success'><strong>Your refund has been processed successfully.</strong></p>
                        
                        <div class='details'>
                            <p><strong>Transaction Code:</strong> " . esc_html($payment->transaction_code) . "</p>
                            <p><strong>Refund Amount:</strong> " . number_format($refund->amount, 0, ',', '.') . " VND</p>
                            <p><strong>Reason:</strong> " . esc_html($refund->reason) . "</p>
                            <p><strong>Status:</strong> " . esc_html(ucfirst($refund->status)) . "</p>
                        </div>
                        
                        <p>The refund will be credited to your original payment method within 3-5 business days.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        return $message;
    }
    
    /**
     * Get email headers
     */
    private static function get_email_headers() {
        $from_name = get_option('lopas_email_from_name', get_option('blogname'));
        $from_email = get_option('lopas_email_from', get_option('admin_email'));
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        return $headers;
    }
}
