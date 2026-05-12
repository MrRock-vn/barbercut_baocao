<?php
/**
 * Booking Wizard - Step 3: Select Date & Time
 */

if (!defined('ABSPATH')) exit;

$total_price = isset($wizard_data['total_price']) ? $wizard_data['total_price'] : 0;
$total_duration = isset($wizard_data['total_duration']) ? $wizard_data['total_duration'] : 0;
$services_count = isset($wizard_data['services']) ? count($wizard_data['services']) : 0;
$staff_id = isset($wizard_data['staff_id']) ? $wizard_data['staff_id'] : 0;
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
        <div class="wizard-step active">
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
                        <p class="wizard-kicker">Step 3 of 4</p>
                        <h2 class="wizard-title">Select Date & Time</h2>
                        <p class="wizard-subtitle"><?php echo esc_html($salon->name); ?> · <?php echo esc_html($staff->name); ?></p>
                    </div>

                    <?php if (isset($_GET['error']) && $_GET['error'] === 'no_datetime'): ?>
                        <div class="alert alert-danger">Please select date and time.</div>
                    <?php endif; ?>

                    <div class="booking-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-label">Total Duration</span>
                                    <strong class="info-value"><?php echo $total_duration; ?> minutes</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-label">Total Price</span>
                                    <strong class="info-value">$<?php echo number_format($total_price, 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="datetime-form">
                        <?php wp_nonce_field('lopas_wizard', 'lopas_wizard_nonce'); ?>
                        <input type="hidden" name="action" value="lopas_wizard_submit">
                        <input type="hidden" name="lopas_wizard_submit" value="1">
                        <input type="hidden" name="step" value="3">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="booking_date" class="form-label">Select Date</label>
                                    <input 
                                        type="date" 
                                        id="booking_date" 
                                        name="booking_date" 
                                        class="form-control form-control-lg"
                                        value="<?php echo esc_attr($selected_date); ?>"
                                        min="<?php echo date('Y-m-d'); ?>"
                                        required
                                    >
                                    <small class="form-text text-muted">Select from today onwards</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="booking_time" class="form-label">Available Time Slots</label>
                                    <select 
                                        id="booking_time" 
                                        name="booking_time" 
                                        class="form-select form-select-lg"
                                        required
                                    >
                                        <option value="">-- Select time --</option>
                                        <?php if ($selected_time): ?>
                                            <option value="<?php echo esc_attr($selected_time); ?>" selected>
                                                <?php echo esc_html($selected_time); ?>
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Slot will be held for 10 minutes</small>
                                    <div id="slot-loading" class="text-muted mt-2" style="display: none;">
                                        <span class="spinner-border spinner-border-sm"></span> Loading slots...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wizard-actions mt-4">
                            <a href="<?php echo esc_url(add_query_arg('step', 2)); ?>" class="btn btn-secondary">
                                ← Back to Staff
                            </a>
                            <button type="submit" class="btn btn-primary" id="continue-btn">
                                Continue to Confirm →
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
                        <strong><?php echo esc_html($staff->name); ?></strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Date & Time</span>
                        <strong id="summary-datetime">Not selected</strong>
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
    const staffId = <?php echo $staff_id; ?>;
    const duration = <?php echo $total_duration; ?>;
    const nonce = '<?php echo wp_create_nonce('lopas_slot_nonce'); ?>';
    
    // Load slots when date changes
    $('#booking_date').on('change', function() {
        const date = $(this).val();
        if (!date) return;
        
        $('#slot-loading').show();
        $('#booking_time').prop('disabled', true).html('<option value="">Loading...</option>');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'GET',
            data: {
                action: 'lopas_get_available_slots',
                staff_id: staffId,
                date: date,
                duration: duration,
                nonce: nonce
            },
            success: function(response) {
                $('#slot-loading').hide();
                $('#booking_time').prop('disabled', false);
                
                if (response.success && response.data.slots.length > 0) {
                    let options = '<option value="">-- Select time --</option>';
                    response.data.slots.forEach(function(slot) {
                        options += '<option value="' + slot + '">' + slot + '</option>';
                    });
                    $('#booking_time').html(options);
                } else {
                    $('#booking_time').html('<option value="">No available slots</option>');
                }
            },
            error: function() {
                $('#slot-loading').hide();
                $('#booking_time').prop('disabled', false).html('<option value="">Error loading slots</option>');
            }
        });
    });
    
    // Hold slot when time is selected
    $('#booking_time').on('change', function() {
        const date = $('#booking_date').val();
        const time = $(this).val();
        
        if (!date || !time) return;
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'lopas_hold_slot',
                staff_id: staffId,
                date: date,
                start_time: time,
                duration: duration,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#summary-datetime').text(date + ' at ' + time);
                    console.log('Slot held successfully');
                } else {
                    alert(response.data.message || 'Failed to hold slot');
                    $('#booking_time').val('');
                }
            },
            error: function() {
                alert('Error holding slot. Please try again.');
                $('#booking_time').val('');
            }
        });
    });
    
    // Update summary
    function updateSummary() {
        const date = $('#booking_date').val();
        const time = $('#booking_time').val();
        
        if (date && time) {
            $('#summary-datetime').text(date + ' at ' + time);
        } else {
            $('#summary-datetime').text('Not selected');
        }
    }
    
    $('#booking_date, #booking_time').on('change', updateSummary);
    
    // Initial update
    updateSummary();
});
</script>

