<?php
/**
 * Booking Wizard - Step 4: Confirm & Payment
 */

if (!defined('ABSPATH')) exit;

$total_price = isset($wizard_data['total_price']) ? $wizard_data['total_price'] : 0;
$total_duration = isset($wizard_data['total_duration']) ? $wizard_data['total_duration'] : 0;
$services = isset($wizard_data['services']) ? $wizard_data['services'] : array();
$booking_date = isset($wizard_data['booking_date']) ? $wizard_data['booking_date'] : '';
$booking_time = isset($wizard_data['booking_time']) ? $wizard_data['booking_time'] : '';
?>

<div class="lopas-booking-wizard">
    <!-- Progress Indicator -->
    <div class="wizard-progress">
        <div class="wizard-step completed">
            <span class="step-number">✓</span>
            <span class="step-label">Services</span>
        </div>
        <div class="wizard-step completed">
            <span class="step-number">✓</span>
            <span class="step-label">Staff</span>
        </div>
        <div class="wizard-step completed">
            <span class="step-number">✓</span>
            <span class="step-label">Date & Time</span>
        </div>
        <div class="wizard-step active">
            <span class="step-number">4</span>
            <span class="step-label">Confirm</span>
        </div>
    </div>

    <div class="wizard-content">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="wizard-panel">
                    <div class="wizard-header">
                        <p class="wizard-kicker">Step 4 of 4</p>
                        <h2 class="wizard-title">Confirm Your Booking</h2>
                        <p class="wizard-subtitle">Review your booking details</p>
                    </div>

                    <?php if (isset($_GET['error']) && $_GET['error'] === 'booking_failed'): ?>
                        <div class="alert alert-danger">Failed to create booking. Please try again.</div>
                    <?php endif; ?>

                    <!-- Booking Details -->
                    <div class="booking-details mb-4">
                        <h4 class="mb-3">Booking Details</h4>
                        
                        <div class="detail-card">
                            <div class="detail-row">
                                <span class="detail-label">Salon</span>
                                <strong class="detail-value"><?php echo esc_html($salon->name); ?></strong>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Address</span>
                                <span class="detail-value"><?php echo esc_html($salon->address); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Staff Member</span>
                                <strong class="detail-value"><?php echo esc_html($staff->name); ?></strong>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date</span>
                                <strong class="detail-value"><?php echo date('l, F j, Y', strtotime($booking_date)); ?></strong>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Time</span>
                                <strong class="detail-value"><?php echo date('g:i A', strtotime($booking_time)); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Services -->
                    <div class="booking-services mb-4">
                        <h4 class="mb-3">Selected Services</h4>
                        <div class="services-list">
                            <?php foreach ($services as $service): ?>
                                <div class="service-item">
                                    <div class="service-item-info">
                                        <strong><?php echo esc_html($service['name']); ?></strong>
                                        <span class="text-muted"><?php echo $service['duration']; ?> min</span>
                                    </div>
                                    <div class="service-item-price">
                                        $<?php echo number_format($service['price'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="services-total">
                            <div class="total-row">
                                <span>Total Duration</span>
                                <strong><?php echo $total_duration; ?> minutes</strong>
                            </div>
                            <div class="total-row">
                                <span>Total Price</span>
                                <strong>$<?php echo number_format($total_price, 2); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Payment & Notes Form -->
                    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('lopas_wizard', 'lopas_wizard_nonce'); ?>
                        <input type="hidden" name="action" value="lopas_wizard_submit">
                        <input type="hidden" name="lopas_wizard_submit" value="1">
                        <input type="hidden" name="step" value="4">

                        <div class="form-group mb-4">
                            <label class="form-label">Payment Method</label>
                            <div class="payment-methods">
                                <div class="payment-option">
                                    <input type="radio" id="payment_cod" name="payment_method" value="cod" checked>
                                    <label for="payment_cod" class="payment-label">
                                        <span class="payment-icon">💵</span>
                                        <div class="payment-info">
                                            <strong>Cash on Delivery</strong>
                                            <small>Pay at the salon</small>
                                        </div>
                                    </label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="payment_vnpay" name="payment_method" value="vnpay">
                                    <label for="payment_vnpay" class="payment-label">
                                        <span class="payment-icon">💳</span>
                                        <div class="payment-info">
                                            <strong>VNPay</strong>
                                            <small>Pay online now</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="notes" class="form-label">Additional Notes (Optional)</label>
                            <textarea 
                                id="notes" 
                                name="notes" 
                                class="form-control" 
                                rows="3"
                                placeholder="Any special requests or notes for the salon..."
                            ></textarea>
                        </div>

                        <div class="alert alert-info">
                            <strong>Note:</strong> Your time slot is held for 10 minutes. Please complete your booking to confirm.
                        </div>

                        <div class="wizard-actions">
                            <a href="<?php echo esc_url(add_query_arg('step', 3)); ?>" class="btn btn-secondary">
                                ← Back to Date & Time
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                Confirm Booking ✓
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sticky Summary Sidebar -->
            <div class="col-lg-4">
                <div class="booking-summary sticky-top">
                    <h3 class="summary-title">Final Summary</h3>
                    
                    <div class="summary-row">
                        <span>Salon</span>
                        <strong><?php echo esc_html($salon->name); ?></strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Services</span>
                        <strong><?php echo count($services); ?> selected</strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Staff</span>
                        <strong><?php echo esc_html($staff->name); ?></strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Date & Time</span>
                        <strong><?php echo date('M j, Y', strtotime($booking_date)); ?><br><?php echo date('g:i A', strtotime($booking_time)); ?></strong>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total Duration</span>
                        <strong><?php echo $total_duration; ?> min</strong>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total Price</span>
                        <strong class="text-success">$<?php echo number_format($total_price, 2); ?></strong>
                    </div>
                    
                    <div class="summary-note">
                        <small class="text-muted">
                            <strong>Cancellation Policy:</strong> Free cancellation up to 24 hours before appointment.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

