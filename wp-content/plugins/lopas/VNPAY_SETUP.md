# VNPay Payment Gateway Setup Guide

## Overview
LOPAS plugin now supports VNPay payment gateway integration for online payments. This guide will help you set up VNPay for your booking system.

## Prerequisites
- VNPay merchant account (https://vnpayment.vn)
- Merchant ID (TMN Code)
- Hash Secret Key
- WordPress admin access

## Step 1: Get VNPay Credentials

1. Go to VNPay website: https://vnpayment.vn
2. Sign up for a merchant account
3. After approval, log in to your merchant dashboard
4. Navigate to **Settings** or **API Configuration**
5. Find and copy:
   - **Merchant ID (TMN Code)**: Usually starts with a number
   - **Hash Secret Key**: A long string used for secure communication

## Step 2: Configure VNPay in LOPAS

1. Go to WordPress Admin Dashboard
2. Navigate to **LOPAS > Settings**
3. Click on the **VNPay Payment** tab
4. Fill in the following fields:

### Enable VNPay
- Check this box to enable VNPay payment gateway

### Merchant ID (TMN Code)
- Paste your VNPay Merchant ID here
- Example: `2QXYZ123456`

### Hash Secret
- Paste your VNPay Hash Secret Key here
- Keep this secure and never share it

### Payment URL
- For **Testing**: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`
- For **Production**: `https://pay.vnpayment.vn/vpcpay.html`

5. Click **Save VNPay Settings**

## Step 3: Configure Email Notifications

1. Go to **LOPAS > Settings**
2. Click on the **Email Notifications** tab
3. Configure:
   - **Enable Email Notifications**: Check to enable
   - **From Email Address**: Email to send notifications from
   - **From Name**: Display name for emails
4. Click **Save Email Settings**

## Step 4: Create Payment Pages

Create the following pages in WordPress to handle payment flow:

### Payment Form Page
1. Create a new page: **Payment**
2. Add shortcode: `[lopas_payment_form]`
3. Publish

### Payment Success Page
1. Create a new page: **Payment Success**
2. Add shortcode: `[lopas_payment_success]`
3. Publish

### Payment Failed Page
1. Create a new page: **Payment Failed**
2. Add shortcode: `[lopas_payment_failed]`
3. Publish

## Step 5: Test Payment Flow

### Using Sandbox (Testing)
1. Make sure Payment URL is set to sandbox URL
2. Create a test booking
3. Proceed to payment
4. Use VNPay test card:
   - Card Number: `9704198526191432198`
   - Expiry: `07/15`
   - OTP: `123456`
5. Verify payment is processed

### Using Production
1. Change Payment URL to production URL
2. Update Merchant ID and Hash Secret with production credentials
3. Test with real payment

## Payment Flow

1. **Customer creates booking** → Order is created with status "pending"
2. **Customer proceeds to payment** → Payment record is created
3. **Customer selects payment method**:
   - **COD (Cash on Delivery)**: Payment marked as pending, customer pays at salon
   - **VNPay**: Customer redirected to VNPay gateway
4. **VNPay processes payment** → Returns to your site
5. **Payment verified** → Order status updated to "confirmed"
6. **Email sent** → Confirmation email sent to customer

## Payment Status Flow

```
Order Created (pending)
    ↓
Payment Created (pending)
    ↓
Customer Selects Payment Method
    ├─ COD → Payment stays pending
    └─ VNPay → Redirected to VNPay
        ↓
    Payment Processed
        ├─ Success → Payment marked as "success", Order marked as "confirmed"
        └─ Failed → Payment marked as "failed", Customer can retry
```

## Troubleshooting

### Payment not processing
- Check Merchant ID and Hash Secret are correct
- Verify Payment URL matches your environment (sandbox/production)
- Check WordPress error logs

### Email not sending
- Verify email notifications are enabled in settings
- Check "From Email Address" is valid
- Verify WordPress mail function is working

### Invalid signature error
- Hash Secret might be incorrect
- Check for extra spaces in credentials
- Verify you're using the correct environment (sandbox/production)

### Payment shows as pending
- Check if IPN (Instant Payment Notification) is configured
- Verify return URL is accessible
- Check VNPay merchant dashboard for transaction status

## Security Notes

1. **Never share your Hash Secret** - Keep it confidential
2. **Use HTTPS** - Always use HTTPS for payment pages
3. **Validate inputs** - All payment data is validated server-side
4. **Secure storage** - Credentials are stored in WordPress options
5. **PCI Compliance** - VNPay handles PCI compliance, not your site

## Support

For VNPay support:
- VNPay Documentation: https://vnpayment.vn/docs
- VNPay Support: support@vnpayment.vn

For LOPAS plugin support:
- Check plugin documentation
- Review error logs in WordPress

## API Endpoints

The plugin automatically creates these AJAX endpoints:

- **Create Payment**: `/wp-admin/admin-ajax.php?action=lopas_create_payment`
- **VNPay Return**: `/wp-admin/admin-ajax.php?action=lopas_vnpay_return`
- **VNPay IPN**: `/wp-admin/admin-ajax.php?action=lopas_vnpay_ipn`

These are automatically configured and should not be modified.

## Testing Checklist

- [ ] VNPay credentials configured
- [ ] Payment URL set correctly (sandbox/production)
- [ ] Email notifications enabled
- [ ] Payment pages created
- [ ] Test booking created
- [ ] Test payment processed
- [ ] Confirmation email received
- [ ] Order status updated to confirmed
- [ ] Payment status shows as success

---

**Version**: 1.0.0  
**Last Updated**: May 9, 2026
