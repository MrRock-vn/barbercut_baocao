<?php
/**
 * Booking Wizard - Step 2: Select Staff
 */

if (!defined('ABSPATH')) exit;

$total_price = isset($wizard_data['total_price']) ? $wizard_data['total_price'] : 0;
$total_duration = isset($wizard_data['total_duration']) ? $wizard_data['total_duration'] : 0;
$services_count = isset($wizard_data['services']) ? count($wizard_data['services']) : 0;
?>

<div class="lopas-booking-wizard">
    <!-- Progress Indicator -->
    <div class="wizard-progress">
        <div class="wizard-step completed">
            <span class="step-number">✓</span>
            <span class="step-label">Services</span>
        </div>
        <div class="wizard-step active">
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
                        <p class="wizard-kicker">Step 2 of 4</p>
                        <h2 class="wizard-title">Select Staff Member</h2>
                        <p class="wizard-subtitle"><?php echo esc_html($salon->name); ?></p>
                    </div>

                    <?php if (isset($_GET['error']) && $_GET['error'] === 'no_staff'): ?>
                        <div class="alert alert-danger">Please select a staff member.</div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('lopas_wizard', 'lopas_wizard_nonce'); ?>
                        <input type="hidden" name="action" value="lopas_wizard_submit">
                        <input type="hidden" name="lopas_wizard_submit" value="1">
                        <input type="hidden" name="step" value="2">

                        <?php if (empty($staff_list)): ?>
                            <div class="alert alert-info">No staff members available at this salon.</div>
                        <?php else: ?>
                            <div class="staff-grid">
                                <?php foreach ($staff_list as $staff): ?>
                                    <div class="staff-choice">
                                        <input 
                                            type="radio" 
                                            id="staff_<?php echo esc_attr($staff->id); ?>" 
                                            name="staff_id" 
                                            value="<?php echo esc_attr($staff->id); ?>"
                                            <?php checked($selected_staff, $staff->id); ?>
                                            required
                                        >
                                        <label class="staff-card" for="staff_<?php echo esc_attr($staff->id); ?>">
                                            <div class="staff-avatar">
                                                <?php if ($staff->avatar_id): 
                                                    echo wp_get_attachment_image($staff->avatar_id, 'medium', false, array('class' => 'staff-image'));
                                                else: ?>
                                                    <div class="staff-placeholder">
                                                        <?php echo esc_html(substr($staff->name, 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="staff-info">
                                                <h3 class="staff-name"><?php echo esc_html($staff->name); ?></h3>
                                                <?php if ($staff->specialization): ?>
                                                    <p class="staff-specialization"><?php echo esc_html($staff->specialization); ?></p>
                                                <?php endif; ?>
                                                <?php if ($staff->email): ?>
                                                    <p class="staff-contact"><?php echo esc_html($staff->email); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="wizard-actions">
                            <a href="<?php echo esc_url(add_query_arg('step', 1)); ?>" class="btn btn-secondary">
                                ← Back to Services
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Continue to Date & Time →
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
                        <strong><?php echo $services_count; ?> selected</strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Staff</span>
                        <strong id="summary-staff">Not selected</strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Date & Time</span>
                        <strong>Not selected</strong>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total Duration</span>
                        <strong><?php echo $total_duration; ?> min</strong>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total Price</span>
                        <strong>$<?php echo number_format($total_price, 2); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update summary when staff is selected
    $('input[name="staff_id"]').on('change', function() {
        const staffName = $(this).closest('.staff-choice').find('.staff-name').text();
        $('#summary-staff').text(staffName);
    });
    
    // Set initial value if staff is already selected
    const selectedStaff = $('input[name="staff_id"]:checked');
    if (selectedStaff.length) {
        const staffName = selectedStaff.closest('.staff-choice').find('.staff-name').text();
        $('#summary-staff').text(staffName);
    }
});
</script>

