/**
 * LOPAS Public JavaScript - Premium Wizard
 */

(function($) {
    'use strict';

    var currentStep = 1;
    var totalSteps = 4;
    var bookingData = {
        salon_id: 0,
        salon_name: '',
        service_id: 0,
        service_name: '',
        service_price: 0,
        booking_date: '',
        booking_time: '',
        payment_method: 'cod'
    };

    $(document).ready(function() {
        initializeBookingForm();
        initializeDashboard();
        
        // If salon_id is pre-selected
        var preSalonId = $('#selected_salon_id').val();
        if (preSalonId) {
            bookingData.salon_id = preSalonId;
            loadServices(preSalonId);
        }
    });

    /**
     * Initialize booking form
     */
    function initializeBookingForm() {
        var $form = $('#lopas-booking-form');
        if ($form.length === 0) return;

        // Salon selection
        $('.salon-selection-card').on('click', function() {
            $('.salon-selection-card').removeClass('selected border-primary');
            $(this).addClass('selected border-primary');
            
            var id = $(this).data('id');
            bookingData.salon_id = id;
            bookingData.salon_name = $(this).find('.fw-bold').text();
            $('#selected_salon_id').val(id);
            
            loadServices(id);
        });

        // Service selection (delegated because services are dynamic)
        $(document).on('click', '.service-card', function() {
            $('.service-card').removeClass('selected border-primary');
            $(this).addClass('selected border-primary');
            
            bookingData.service_id = $(this).data('id');
            bookingData.service_name = $(this).data('name');
            bookingData.service_price = $(this).data('price');
        });

        // Date selection
        $('#booking_date').on('change', function() {
            var date = $(this).val();
            bookingData.booking_date = date;
            if (bookingData.salon_id && date) {
                loadSlots(bookingData.salon_id, date);
            }
        });

        // Slot selection
        $(document).on('click', '.time-slot', function() {
            $('.time-slot').removeClass('selected');
            $(this).addClass('selected');
            
            var time = $(this).data('time');
            bookingData.booking_time = time;
            $('#booking_time').val(time);
        });

        // Payment method
        $('.payment-option').on('click', function() {
            $('.payment-option').removeClass('selected');
            $(this).addClass('selected');
            
            var method = $(this).data('method');
            bookingData.payment_method = method;
            $('#payment_method').val(method);
        });

        // Navigation
        $('#btn-next').on('click', function() {
            if (validateStep(currentStep)) {
                currentStep++;
                updateWizard();
            }
        });

        $('#btn-prev').on('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateWizard();
            }
        });

        // Submission
        $form.on('submit', function(e) {
            e.preventDefault();
            
            var formData = {
                action: 'lopas_create_booking',
                nonce: lopasPublic.nonce,
                salon_id: bookingData.salon_id,
                service_id: bookingData.service_id,
                booking_date: bookingData.booking_date,
                booking_time: bookingData.booking_time,
                payment_method: bookingData.payment_method,
                note: $form.find('textarea[name="note"]').val()
            };

            $('#btn-submit').prop('disabled', true).text('Đang xử lý...');

            $.ajax({
                url: lopasPublic.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        if (response.data.payment_url) {
                            alert('Đặt lịch thành công! Đang chuyển hướng đến cổng thanh toán VNPay...');
                            window.location.href = response.data.payment_url;
                        } else {
                            alert('Đặt lịch thành công! Cảm ơn bạn đã sử dụng dịch vụ.');
                            window.location.href = response.data.redirect_url || (lopasPublic.homeUrl + 'my-bookings/');
                        }
                    } else {
                        alert('Lỗi: ' + (response.data || 'Không thể tạo lịch hẹn.'));
                        $('#btn-submit').prop('disabled', false).text('Xác nhận đặt lịch');
                    }
                },
                error: function() {
                    alert('Lỗi kết nối máy chủ. Vui lòng thử lại sau.');
                    $('#btn-submit').prop('disabled', false).text('Xác nhận đặt lịch');
                }
            });
        });
    }

    function loadServices(salonId) {
        $('#services-container').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Đang tải dịch vụ...</p></div>');
        
        $.ajax({
            url: lopasPublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_get_services',
                nonce: lopasPublic.nonce,
                salon_id: salonId
            },
            success: function(response) {
                if (response.success) {
                    $('#services-container').html(response.data.html);
                } else {
                    $('#services-container').html('<div class="alert alert-danger">Lỗi: ' + (response.data || 'Không thể tải dịch vụ.') + '</div>');
                }
            },
            error: function() {
                $('#services-container').html('<div class="alert alert-danger">Lỗi kết nối máy chủ.</div>');
            }
        });
    }

    function loadSlots(salonId, date) {
        $('#slots-container').html('<div class="spinner-border spinner-border-sm text-primary"></div>');
        
        $.ajax({
            url: lopasPublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_fetch_time_slots',
                nonce: lopasPublic.nonce,
                salon_id: salonId,
                date: date
            },
            success: function(response) {
                if (response.success) {
                    $('#slots-container').html(response.data.html);
                } else {
                    $('#slots-container').html('<div class="text-danger small">Không thể tải khung giờ.</div>');
                }
            },
            error: function() {
                $('#slots-container').html('<div class="text-danger small">Lỗi kết nối.</div>');
            }
        });
    }

    function validateStep(step) {
        switch(step) {
            case 1:
                if (!bookingData.salon_id) { alert('Vui lòng chọn một Salon.'); return false; }
                break;
            case 2:
                if (!bookingData.service_id) { alert('Vui lòng chọn một dịch vụ.'); return false; }
                break;
            case 3:
                if (!bookingData.booking_date || !bookingData.booking_time) { alert('Vui lòng chọn ngày và giờ.'); return false; }
                break;
        }
        return true;
    }

    function updateWizard() {
        // Update navigation steps
        $('.step-nav-item').removeClass('active');
        $('.step-nav-item[data-step="' + currentStep + '"]').addClass('active');

        // Show/Hide contents
        $('.booking-step-content').hide();
        $('#step-' + currentStep).show();

        // Update buttons
        $('#btn-prev').toggle(currentStep > 1);
        $('#btn-next').toggle(currentStep < totalSteps);
        $('#btn-submit').toggle(currentStep === totalSteps);

        if (currentStep === 3) {
            var date = $('#booking_date').val();
            var salonId = bookingData.salon_id || $('#selected_salon_id').val();
            if (date && salonId) {
                bookingData.booking_date = date;
                loadSlots(salonId, date);
            }
        }

        if (currentStep === totalSteps) {
            updateSummary();
        }
    }

    function updateSummary() {
        var html = '<div class="mb-2"><span class="text-muted">Salon:</span> <span class="fw-bold float-end">' + bookingData.salon_name + '</span></div>';
        html += '<div class="mb-2"><span class="text-muted">Dịch vụ:</span> <span class="fw-bold float-end">' + bookingData.service_name + '</span></div>';
        html += '<div class="mb-2"><span class="text-muted">Ngày:</span> <span class="fw-bold float-end">' + bookingData.booking_date + '</span></div>';
        html += '<div class="mb-2"><span class="text-muted">Giờ:</span> <span class="fw-bold float-end">' + bookingData.booking_time + '</span></div>';
        
        $('#booking-summary-details').html(html);
        $('#summary-total').text(formatCurrency(bookingData.service_price));
    }

    function initializeDashboard() {
        $(document).on('click', '.tab-button', function() {
            var tab = $(this).data('tab');
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            $('.tab-content').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount).replace('₫', 'đ');
    }

})(jQuery);
