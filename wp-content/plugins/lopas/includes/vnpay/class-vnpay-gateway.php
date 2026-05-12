<?php
/**
 * VNPay Payment Gateway
 */

if (!defined('ABSPATH')) {
    exit;
}

class LOPAS_VNPay_Gateway {
    
    private $config;
    private $tmn_code;
    private $hash_secret;
    private $pay_url;
    private $return_url;
    private $ipn_url;
    
    public function __construct() {
        $this->config = require LOPAS_PATH . 'config/vnpay.php';
        $this->tmn_code = $this->config['tmn_code'];
        $this->hash_secret = $this->config['hash_secret'];
        $this->pay_url = $this->config['pay_url'];
        $this->return_url = $this->config['return_url'];
        $this->ipn_url = $this->config['ipn_url'];
    }
    
    /**
     * Check if VNPay is enabled and configured
     * 
     * @return bool
     */
    public function is_enabled() {
        return !empty($this->tmn_code) && !empty($this->hash_secret);
    }
    
    /**
     * Create payment URL for VNPay
     * 
     * @param int $order_id Order ID
     * @param float $amount Amount in VND
     * @param string $order_code Order code
     * @return string|false Payment URL or false on error
     */
    public function create_payment_url($order_id, $amount, $order_code) {
        if (!$this->is_enabled()) {
            return false;
        }
        
        // Validate inputs
        if (empty($order_id) || empty($amount) || empty($order_code)) {
            return false;
        }
        
        // Build request data
        $vnp_data = array(
            'vnp_Version'   => $this->config['version'],
            'vnp_Command'   => 'pay',
            'vnp_TmnCode'   => $this->tmn_code,
            'vnp_Amount'    => intval($amount * 100), // VNPay requires amount in cents
            'vnp_CurrCode'  => $this->config['currency'],
            'vnp_TxnRef'    => $order_code,
            'vnp_OrderInfo' => 'Thanh toan don hang: ' . $order_code,
            'vnp_OrderType' => 'billpayment',
            'vnp_Locale'    => $this->config['locale'],
            'vnp_ReturnUrl' => $this->return_url,
            'vnp_IpAddr'    => $this->get_client_ip(),
            'vnp_CreateDate' => date('YmdHis')
        );
        
        // Sort data by key
        ksort($vnp_data);
        
        // Build query string
        $hashData = "";
        $query = "";
        $i = 0;
        foreach ($vnp_data as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        
        $vnp_Url = $this->pay_url . "?" . $query;
        $secure_hash = hash_hmac('sha512', $hashData, $this->hash_secret);
        $vnp_Url .= 'vnp_SecureHash=' . $secure_hash;

        error_log("VNPay Request: " . $vnp_Url);
        
        return $vnp_Url;
    }
    
    /**
     * Verify VNPay response
     * 
     * @param array $response VNPay response data
     * @return bool True if valid, false otherwise
     */
    public function verify_response($response) {
        if (empty($response['vnp_SecureHash'])) {
            return false;
        }
        
        $vnp_SecureHash = $response['vnp_SecureHash'];
        unset($response['vnp_SecureHash']);
        unset($response['vnp_SecureHashType']);
        
        // Sort data by key
        ksort($response);
        
        // Build query string
        $hashData = "";
        $i = 0;
        foreach ($response as $key => $value) {
            if (substr($key, 0, 4) !== 'vnp_') {
                continue;
            }
            
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $this->hash_secret);
        
        if ($secureHash == $vnp_SecureHash) {
            return true;
        } else {
            error_log("VNPay Verify Error: Hash Mismatch.");
            error_log("Calculated Hash: " . $secureHash);
            error_log("Received Hash: " . $vnp_SecureHash);
            error_log("Hash Data: " . $hashData);
            return false;
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP
     */
    private function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validate IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        return '127.0.0.1';
    }
    
    /**
     * Parse VNPay response
     * 
     * @param array $response VNPay response data
     * @return array Parsed response
     */
    public function parse_response($response) {
        return array(
            'transaction_code' => isset($response['vnp_TransactionNo']) ? $response['vnp_TransactionNo'] : '',
            'order_code' => isset($response['vnp_TxnRef']) ? $response['vnp_TxnRef'] : '',
            'amount' => isset($response['vnp_Amount']) ? intval($response['vnp_Amount']) / 100 : 0,
            'response_code' => isset($response['vnp_ResponseCode']) ? $response['vnp_ResponseCode'] : '',
            'transaction_status' => isset($response['vnp_TransactionStatus']) ? $response['vnp_TransactionStatus'] : '',
            'bank_code' => isset($response['vnp_BankCode']) ? $response['vnp_BankCode'] : '',
            'bank_tran_no' => isset($response['vnp_BankTranNo']) ? $response['vnp_BankTranNo'] : '',
            'pay_date' => isset($response['vnp_PayDate']) ? $response['vnp_PayDate'] : '',
            'raw_response' => $response
        );
    }
    
    /**
     * Check if payment is successful
     * 
     * @param string $response_code VNPay response code
     * @return bool
     */
    public function is_payment_success($response_code) {
        return $response_code === '00';
    }
}
