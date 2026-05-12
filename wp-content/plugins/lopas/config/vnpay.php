<?php
/**
 * VNPay Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(
    'enabled'       => get_option('lopas_vnpay_enabled', false),
    'tmn_code'      => get_option('lopas_vnpay_tmn_code', '5652YMTY'),
    'hash_secret'   => get_option('lopas_vnpay_hash_secret', '2AT2HZOW2D58PYMT5BJ6B24JHK98OGEN'),
    'pay_url'       => get_option('lopas_vnpay_pay_url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'return_url'    => home_url('/wp-admin/admin-ajax.php?action=lopas_vnpay_return'),
    'ipn_url'       => home_url('/wp-admin/admin-ajax.php?action=lopas_vnpay_ipn'),
    'version'       => '2.1.0',
    'currency'      => 'VND',
    'locale'        => 'vn'
);
