<?php
// ToyyibPay Configuration
define('TOYYIBPAY_USER_SECRET_KEY', 'na5dmpl1-p9ws-xzj8-i9zv-nqdpvt8f4ba0');
define('TOYYIBPAY_CATEGORY_CODE', 'ijfy4kbm');
define('TOYYIBPAY_IS_SANDBOX', true);

// Set API URL based on sandbox mode
define('TOYYIBPAY_API_URL', 'https://dev.toyyibpay.com/index.php/api');

// Set callback and return URLs
define('TOYYIBPAY_CALLBACK_URL', 'http://localhost/fyp/payment/toyyibpay_callback.php');
define('TOYYIBPAY_RETURN_URL', 'http://localhost/fyp/maklumat_servis.php'); 