/**
 * LOPAS Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize admin functionality
        initializeAdmin();
    });

    /**
     * Initialize admin functionality
     */
    function initializeAdmin() {
        // Handle salon form submission
        $(document).on('submit', '#lopas-salon-form', function(e) {
            e.preventDefault();
            submitSalonForm();
        });
        
        // Handle service form submission
        $(document).on('submit', '#lopas-service-form', function(e) {
            e.preventDefault();
            submitServiceForm();
        });
    }

    /**
     * Open salon form modal
     */
    window.lopasOpenSalonForm = function() {
        $('#lopas-salon-form-container').html('');
        $('#lopas-salon-modal').show();
        
        // Load form via AJAX
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_load_salon_form',
                nonce: lopasAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#lopas-salon-form-container').html(response.data);
                } else {
                    $('#lopas-salon-form-container').html('<div class="alert alert-danger">' + (response.data || 'Error loading form') + '</div>');
                }
            }
        });
    };

    /**
     * Edit salon
     */
    window.lopasEditSalon = function(salonId) {
        $('#lopas-salon-form-container').html('');
        $('#lopas-salon-modal').show();
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_load_salon_form',
                nonce: lopasAdmin.nonce,
                salon_id: salonId
            },
            success: function(response) {
                if (response.success) {
                    $('#lopas-salon-form-container').html(response.data);
                } else {
                    $('#lopas-salon-form-container').html('<div class="alert alert-danger">' + (response.data || 'Error loading form') + '</div>');
                }
            }
        });
    };

    /**
     * Delete salon
     */
    window.lopasDeleteSalon = function(salonId) {
        if (!confirm('Are you sure you want to delete this salon?')) {
            return;
        }
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_delete_salon',
                nonce: lopasAdmin.nonce,
                salon_id: salonId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    };

    /**
     * Submit salon form
     */
    function submitSalonForm() {
        var $form = $('#lopas-salon-form');
        var salonId = $form.find('input[name="salon_id"]').val();
        var action = salonId ? 'lopas_edit_salon' : 'lopas_add_salon';
        
        var data = {
            action: action,
            nonce: lopasAdmin.nonce,
            salon_id: salonId,
            name: $form.find('input[name="name"]').val(),
            description: $form.find('textarea[name="description"]').val(),
            address: $form.find('input[name="address"]').val(),
            phone: $form.find('input[name="phone"]').val(),
            email: $form.find('input[name="email"]').val(),
            opening_time: $form.find('input[name="opening_time"]').val(),
            closing_time: $form.find('input[name="closing_time"]').val(),
            status: $form.find('select[name="status"]').val()
        };
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    lopasCloseModal();
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    }

    /**
     * Open service form modal
     */
    window.lopasOpenServiceForm = function() {
        $('#lopas-service-form-container').html('');
        $('#lopas-service-modal').show();
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_load_service_form',
                nonce: lopasAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#lopas-service-form-container').html(response.data);
                } else {
                    $('#lopas-service-form-container').html('<div class="alert alert-danger">' + (response.data || 'Error loading form') + '</div>');
                }
            }
        });
    };

    /**
     * Edit service
     */
    window.lopasEditService = function(serviceId) {
        $('#lopas-service-form-container').html('');
        $('#lopas-service-modal').show();
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_load_service_form',
                nonce: lopasAdmin.nonce,
                service_id: serviceId
            },
            success: function(response) {
                if (response.success) {
                    $('#lopas-service-form-container').html(response.data);
                } else {
                    $('#lopas-service-form-container').html('<div class="alert alert-danger">' + (response.data || 'Error loading form') + '</div>');
                }
            }
        });
    };

    /**
     * Delete service
     */
    window.lopasDeleteService = function(serviceId) {
        if (!confirm('Are you sure you want to delete this service?')) {
            return;
        }
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_delete_service',
                nonce: lopasAdmin.nonce,
                service_id: serviceId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    };

    /**
     * Submit service form
     */
    function submitServiceForm() {
        var $form = $('#lopas-service-form');
        var serviceId = $form.find('input[name="service_id"]').val();
        var action = serviceId ? 'lopas_edit_service' : 'lopas_add_service';
        
        var data = {
            action: action,
            nonce: lopasAdmin.nonce,
            service_id: serviceId,
            salon_id: $form.find('select[name="salon_id"]').val(),
            name: $form.find('input[name="name"]').val(),
            description: $form.find('textarea[name="description"]').val(),
            category: $form.find('input[name="category"]').val(),
            price: $form.find('input[name="price"]').val(),
            duration: $form.find('input[name="duration"]').val(),
            status: $form.find('select[name="status"]').val()
        };
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    lopasCloseModal();
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    }

    /**
     * View booking
     */
    window.lopasViewBooking = function(bookingId) {
        $('#lopas-booking-details-container').html('');
        $('#lopas-booking-modal').show();
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_load_booking_details',
                nonce: lopasAdmin.nonce,
                booking_id: bookingId
            },
            success: function(response) {
                if (response.success) {
                    $('#lopas-booking-details-container').html(response.data);
                } else {
                    $('#lopas-booking-details-container').html('<div class="alert alert-danger">' + (response.data || 'Error loading details') + '</div>');
                }
            }
        });
    };

    /**
     * Update booking status
     */
    window.lopasUpdateBookingStatus = function(bookingId) {
        var status = $('#booking_status_select').val();
        
        if (!status) {
            alert('Please select a status');
            return;
        }
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_update_booking_status',
                nonce: lopasAdmin.nonce,
                booking_id: bookingId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    lopasCloseModal();
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    };

    /**
     * Cancel booking
     */
    window.lopasCancelBooking = function(bookingId) {
        var reason = prompt('Enter cancellation reason:');
        
        if (reason === null) {
            return;
        }
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_cancel_booking',
                nonce: lopasAdmin.nonce,
                booking_id: bookingId,
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    lopasCloseModal();
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    };

    /**
     * Close modal
     */
    window.lopasCloseModal = function() {
        $('.lopas-modal').hide();
    };

    /**
     * Close modal when clicking overlay
     */
    $(document).on('click', '.lopas-modal-overlay', function() {
        lopasCloseModal();
    });

    /**
     * View order
     */
    window.lopasViewOrder = function(orderId) {
        $('#lopas-order-details-container').html('');
        $('#lopas-order-modal').show();
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_load_order_details',
                nonce: lopasAdmin.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    $('#lopas-order-details-container').html(response.data);
                } else {
                    $('#lopas-order-details-container').html('<div class="alert alert-danger">' + (response.data || 'Error loading details') + '</div>');
                }
            }
        });
    };

    /**
     * Update order status
     */
    window.lopasUpdateOrderStatus = function(orderId) {
        var status = $('#order_status_select').val();
        
        if (!status) {
            alert('Please select a status');
            return;
        }
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_update_order_status',
                nonce: lopasAdmin.nonce,
                order_id: orderId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    lopasCloseModal();
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    };

    /**
     * View payment
     */
    window.lopasViewPayment = function(paymentId) {
        $('#lopas-payment-details-container').html('');
        $('#lopas-payment-modal').show();
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_load_payment_details',
                nonce: lopasAdmin.nonce,
                payment_id: paymentId
            },
            success: function(response) {
                if (response.success) {
                    $('#lopas-payment-details-container').html(response.data);
                } else {
                    $('#lopas-payment-details-container').html('<div class="alert alert-danger">' + (response.data || 'Error loading details') + '</div>');
                }
            }
        });
    };

    /**
     * Update payment status
     */
    window.lopasUpdatePaymentStatus = function(paymentId) {
        var status = $('#payment_status_select').val();
        
        if (!status) {
            alert('Please select a status');
            return;
        }
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_update_payment_status',
                nonce: lopasAdmin.nonce,
                payment_id: paymentId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    lopasCloseModal();
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    };

    /**
     * Create refund
     */
    window.lopasCreateRefund = function(paymentId, maxAmount) {
        var amount = prompt('Enter refund amount (max: ' + maxAmount + '):');
        
        if (amount === null) {
            return;
        }
        
        amount = parseFloat(amount);
        
        if (isNaN(amount) || amount <= 0 || amount > maxAmount) {
            alert('Invalid amount');
            return;
        }
        
        var reason = prompt('Enter refund reason:');
        
        if (reason === null) {
            return;
        }
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_create_refund',
                nonce: lopasAdmin.nonce,
                payment_id: paymentId,
                amount: amount,
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    };

    /**
     * Confirm booking
     */
    window.lopasConfirmBooking = function(bookingId) {
        if (!confirm('Bạn có chắc chắn muốn xác nhận lịch hẹn này?')) {
            return;
        }
        
        $.ajax({
            url: lopasAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'lopas_confirm_booking',
                nonce: lopasAdmin.nonce,
                booking_id: bookingId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                } else {
                    alert('Lỗi: ' + response.data);
                }
            }
        });
    };
})(jQuery);
