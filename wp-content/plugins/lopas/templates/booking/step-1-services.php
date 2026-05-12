<?php
/**
 * Booking Wizard - Step 1: Select Services
 */

if (!defined('ABSPATH')) exit;

$selected_service_ids = array_column($selected_services, 'id');
$total_price = array_sum(array_column($selected_services, 'price'));
$total_duration = array_sum(array_column($selected_services, 'duration'));
?>

<div class="lopas-booking-wizard">
    <!-- Progress Indicator -->
    <div class="wizard-progress">
        <div class="wizard-step active">
            <span class="step-number">1</span>
            <span class="step-label">Services</span>
        </div>
        <div class="wizard-step">
            <span class="step-number">2</span>
            <span class="step-label">Staff</span>
        </div>
        <div class="wizard-step">
            <span class="step-number">3</span>
            <span class="step-label">Date & Time</span>
        </div>
        <div class="wizard-step">
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
                        <p class="wizard-kicker">Step 1 of 4</p>
                        <h2 class="wizard-title">Select Services</h2>
                        <p class="wizard-subtitle"><?php echo esc_html($salon->name); ?></p>
                    </div>

                    <?php if (isset($_GET['error']) && $_GET['error'] === 'no_services'): ?>
                        <div class="alert alert-danger">Please select at least one service.</div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="wizard-form-step1">
                        <?php wp_nonce_field('lopas_wizard', 'lopas_wizard_nonce'); ?>
                        <input type="hidden" name="action" value="lopas_wizard_submit">
                        <input type="hidden" name="lopas_wizard_submit" value="1">
                        <input type="hidden" name="step" value="1">

                        <?php if (empty($services)): ?>
                            <div class="alert alert-info">No services available at this salon.</div>
                        <?php else: ?>
                            <div class="services-grid">
                                <?php foreach ($services as $service): ?>
                                    <div class="service-choice">
                                        <input 
                                            type="checkbox" 
                                            id="service_<?php echo esc_attr($service->id); ?>" 
                                            name="service_ids[]" 
                                            value="<?php echo esc_attr($service->id); ?>"
                                            <?php checked(in_array($service->id, $selected_service_ids)); ?>
                                            data-price="<?php echo esc_attr($service->price); ?>"
                                            data-duration="<?php echo esc_attr($service->duration); ?>"
                                            class="service-checkbox"
                                        >
                                        <label class="service-card" for="service_<?php echo esc_attr($service->id); ?>">
                                            <div class="service-header">
                                                <h3 class="service-name"><?php echo esc_html($service->name); ?></h3>
                                                <div class="service-price">$<?php echo number_format($service->price, 2); ?></div>
                                            </div>
                                            <p class="service-description">
                                                <?php echo esc_html($service->description ?: 'Professional service'); ?>
                                            </p>
                                            <div class="service-meta">
                                                <span class="service-duration"><?php echo esc_html($service->duration); ?> min</span>
                                                <?php if ($service->category): ?>
                                                    <span class="service-category"><?php echo esc_html($service->category); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="wizard-actions">
                            <a href="<?php echo esc_url(home_url('/salons/')); ?>" class="btn btn-secondary">
                                ← Back to Salons
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Continue to Staff →
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sticky Summary Sidebar -->
            <div class="col-lg-4">
                <div class="booking-summary sticky-top">
                    <h3 class="summary-title">Booking Summary</h3>
                    
                    <div class="summary-row">
                        <span>Salon</span>
                        <strong><?php echo esc_html($salon->name); ?></strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Services</span>
                        <strong id="summary-services">
                            <?php echo !empty($selected_services) ? count($selected_services) . ' selected' : 'Not selected'; ?>
                        </strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Staff</span>
                        <strong>Not selected</strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Date & Time</span>
                        <strong>Not selected</strong>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total Duration</span>
                        <strong id="summary-duration"><?php echo $total_duration; ?> min</strong>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total Price</span>
                        <strong id="summary-price">$<?php echo number_format($total_price, 2); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update summary when services are selected
    $('.service-checkbox').on('change', function() {
        let totalPrice = 0;
        let totalDuration = 0;
        let selectedCount = 0;
        
        $('.service-checkbox:checked').each(function() {
            totalPrice += parseFloat($(this).data('price'));
            totalDuration += parseInt($(this).data('duration'));
            selectedCount++;
        });
        
        $('#summary-services').text(selectedCount > 0 ? selectedCount + ' selected' : 'Not selected');
        $('#summary-duration').text(totalDuration + ' min');
        $('#summary-price').text('$' + totalPrice.toFixed(2));
    });
});
</script>

