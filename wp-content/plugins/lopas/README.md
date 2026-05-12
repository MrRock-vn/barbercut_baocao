# LOPAS - Barber & Spa Booking System

Professional booking system plugin for WordPress designed for barber shops and spa services with integrated payment processing.

## Features

### Core Functionality
- **Salon Management** - Create and manage multiple salon locations
- **Service Management** - Define services with pricing and duration
- **Staff Management** - Manage staff members and their specializations
- **Booking System** - Customers can book appointments with real-time slot availability
- **Order Management** - Track customer orders and services
- **Payment Processing** - Support for multiple payment methods (COD, VNPay)
- **Review System** - Customers can leave reviews and ratings

### Admin Features
- Comprehensive dashboard with statistics
- Manage salons, services, staff, and bookings
- View all orders and payments
- Configure VNPay payment settings
- User-friendly admin interface

### Frontend Features
- Booking form shortcode with dynamic service loading
- Salon listing with details
- Customer booking history
- Responsive design for mobile devices
- AJAX-powered interactions

## Installation

1. Download the plugin files
2. Upload to `wp-content/plugins/lopas/`
3. Activate the plugin from WordPress admin
4. Navigate to LOPAS menu to configure settings

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Usage

### Shortcodes

#### Booking Form
```
[lopas_booking_form]
```
Displays a booking form where customers can select salon, service, date, and time.

#### Salon List
```
[lopas_salon_list limit="12"]
```
Displays a grid of available salons.

#### My Bookings
```
[lopas_my_bookings]
```
Shows logged-in user's booking history (requires user to be logged in).

### Admin Pages

Access all management features from the LOPAS menu in WordPress admin:
- **Dashboard** - Overview statistics
- **Salons** - Manage salon locations
- **Services** - Manage services and pricing
- **Bookings** - View and manage bookings
- **Orders** - Track customer orders
- **Payments** - Monitor payment transactions
- **Settings** - Configure VNPay and other settings

## Database Tables

The plugin creates the following tables:

- `wp_lopas_salons` - Salon information
- `wp_lopas_services` - Services offered
- `wp_lopas_staff` - Staff members
- `wp_lopas_bookings` - Customer bookings
- `wp_lopas_orders` - Orders
- `wp_lopas_order_items` - Order line items
- `wp_lopas_payments` - Payment transactions
- `wp_lopas_refunds` - Refund records
- `wp_lopas_reviews` - Customer reviews
- `wp_lopas_vouchers` - Discount vouchers
- `wp_lopas_availability` - Staff availability

## API Reference

### Salon Model
```php
LOPAS_Salon::get($salon_id)
LOPAS_Salon::get_all($args)
LOPAS_Salon::get_by_user($user_id)
LOPAS_Salon::create($data)
LOPAS_Salon::update($salon_id, $data)
LOPAS_Salon::delete($salon_id)
LOPAS_Salon::get_services($salon_id)
LOPAS_Salon::get_staff($salon_id)
LOPAS_Salon::count($status)
```

### Service Model
```php
LOPAS_Service::get($service_id)
LOPAS_Service::get_by_salon($salon_id, $args)
LOPAS_Service::create($data)
LOPAS_Service::update($service_id, $data)
LOPAS_Service::delete($service_id)
LOPAS_Service::get_categories($salon_id)
LOPAS_Service::count($salon_id, $status)
```

### Booking Model
```php
LOPAS_Booking::get($booking_id)
LOPAS_Booking::get_by_code($booking_code)
LOPAS_Booking::get_by_user($user_id, $args)
LOPAS_Booking::get_by_salon($salon_id, $args)
LOPAS_Booking::create($data)
LOPAS_Booking::update($booking_id, $data)
LOPAS_Booking::cancel($booking_id, $reason)
LOPAS_Booking::is_slot_available($salon_id, $staff_id, $date, $time)
LOPAS_Booking::get_available_slots($salon_id, $date, $duration)
LOPAS_Booking::count($salon_id, $status)
```

### Order Model
```php
LOPAS_Order::get($order_id)
LOPAS_Order::get_by_code($order_code)
LOPAS_Order::get_by_user($user_id, $args)
LOPAS_Order::create($data)
LOPAS_Order::update($order_id, $data)
LOPAS_Order::add_item($order_id, $service_id, $price, $booking_id, $quantity)
LOPAS_Order::get_items($order_id)
LOPAS_Order::count($user_id, $status)
```

### Payment Model
```php
LOPAS_Payment::get($payment_id)
LOPAS_Payment::get_by_transaction($transaction_code)
LOPAS_Payment::get_by_order($order_id)
LOPAS_Payment::create($data)
LOPAS_Payment::update($payment_id, $data)
LOPAS_Payment::mark_success($payment_id, $response_data)
LOPAS_Payment::mark_failed($payment_id, $response_data)
LOPAS_Payment::create_refund($payment_id, $amount, $reason)
LOPAS_Payment::get_refund($refund_id)
LOPAS_Payment::update_refund_status($refund_id, $status)
LOPAS_Payment::count($order_id, $status)
```

## Helper Functions

```php
lopas_get_prefix()                    // Get database prefix
lopas_generate_code($prefix)          // Generate unique code
lopas_generate_short_code($prefix)    // Generate short code
lopas_format_currency($amount)        // Format Vietnamese currency
lopas_format_date($date)              // Format date
lopas_format_date_vi($date)           // Format date in Vietnamese
lopas_get_salon($salon_id)            // Get salon
lopas_get_service($service_id)        // Get service
lopas_get_booking($booking_id)        // Get booking
lopas_is_salon_owner($user_id, $salon_id)  // Check ownership
lopas_send_email($email, $subject, $message, $headers)  // Send email
lopas_api_response($success, $data, $message, $code)    // API response
lopas_get_user_fullname($user_id)     // Get user full name
lopas_is_valid_email($email)          // Validate email
lopas_is_valid_phone($phone)          // Validate phone (Vietnam)
lopas_get_setting($key, $default)     // Get setting
lopas_update_setting($key, $value)    // Update setting
```

## Hooks

### Actions
- `lopas_tables_created` - After database tables are created
- `lopas_tables_dropped` - After database tables are dropped
- `lopas_salon_created` - After salon is created
- `lopas_salon_updated` - After salon is updated
- `lopas_salon_deleted` - After salon is deleted
- `lopas_service_created` - After service is created
- `lopas_service_updated` - After service is updated
- `lopas_service_deleted` - After service is deleted
- `lopas_booking_created` - After booking is created
- `lopas_booking_updated` - After booking is updated
- `lopas_order_created` - After order is created
- `lopas_order_updated` - After order is updated
- `lopas_payment_created` - After payment is created
- `lopas_payment_updated` - After payment is updated
- `lopas_payment_success` - After payment is successful
- `lopas_refund_created` - After refund is created
- `lopas_refund_status_updated` - After refund status is updated

## Configuration

### VNPay Settings
Configure VNPay payment gateway in LOPAS Settings:
1. Go to LOPAS > Settings
2. Enter your VNPay Merchant ID
3. Enter your VNPay Hash Secret
4. Enter your VNPay URL

## Security

- All database queries use prepared statements
- User input is sanitized before storage
- Output is escaped before display
- Nonce verification for AJAX requests
- User capability checks in admin
- CSRF protection via WordPress nonces

## Support

For issues, feature requests, or contributions, please contact the development team.

## License

GPL v2 or later

## Changelog

### Version 1.0.0
- Initial release
- Core booking functionality
- Admin interface
- Frontend shortcodes
- Payment integration (placeholder)
