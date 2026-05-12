<?php
/**
 * Email Notification Hooks
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_Email_Hooks {
    
    public function __construct() {
        // Booking hooks
        add_action('lopas_booking_created', array($this, 'on_booking_created'), 10, 1);
        add_action('lopas_booking_status_updated', array($this, 'on_booking_status_updated'), 10, 3);
        add_action('lopas_booking_cancelled', array($this, 'on_booking_cancelled'), 10, 1);
        
        // Payment hooks
        add_action('lopas_payment_completed', array($this, 'on_payment_completed'), 10, 2);
        add_action('lopas_refund_created', array($this, 'on_refund_created'), 10, 2);
    }
    
    /**
     * Send email when booking is created
     */
    public function on_booking_created($booking_id) {
        LOPAS_Mailer::send_booking_confirmation($booking_id);
    }
    
    /**
     * Send email when booking status is updated
     */
    public function on_booking_status_updated($booking_id, $old_status, $new_status) {
        // Only send email for specific status changes
        if ($new_status === 'confirmed') {
            LOPAS_Mailer::send_booking_confirmation($booking_id);
        }
    }
    
    /**
     * Send email when booking is cancelled
     */
    public function on_booking_cancelled($booking_id) {
        LOPAS_Mailer::send_booking_cancellation($booking_id);
    }
    
    /**
     * Send email when payment is completed
     */
    public function on_payment_completed($payment_id, $order_id) {
        LOPAS_Mailer::send_payment_confirmation($payment_id);
    }
    
    /**
     * Send email when refund is created
     */
    public function on_refund_created($refund_id, $payment_id) {
        LOPAS_Mailer::send_refund_notification($refund_id);
    }
}
