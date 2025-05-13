<<<<<<< HEAD
<?php
require_once 'toyyibpay_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['pelangganID'])) {
    header("Location: ../login.html");
    exit();
}

// Validate required POST data
$required_fields = ['tempahanID', 'amount', 'name', 'email', 'phone'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    die("Error: Missing required fields: " . implode(', ', $missing_fields));
}

// Get form data
$tempahanID = $_POST['tempahanID'];
$amount = $_POST['amount'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];

// Generate payment ID
$pembayaranID = 'PM' . strtoupper(uniqid());

// Prepare data for ToyyibPay
$data = array(
    'userSecretKey' => TOYYIBPAY_USER_SECRET_KEY,
    'categoryCode' => TOYYIBPAY_CATEGORY_CODE,
    'billName' => 'Servis-X Payment',
    'billDescription' => 'Payment for booking ID: ' . $tempahanID,
    'billPriceSetting' => 1, // 1 for fixed price
    'billPayorInfo' => 1, // 1 to collect customer info
    'billAmount' => $amount * 100, // Convert to cents
    'billReturnUrl' => TOYYIBPAY_RETURN_URL,
    'billCallbackUrl' => TOYYIBPAY_CALLBACK_URL,
    'billExternalReferenceNo' => $tempahanID,
    'billTo' => $name,
    'billEmail' => $email,
    'billPhone' => $phone,
    'billContentEmail' => 'Thank you for your payment!',
    'billChargeToCustomer' => 1,
    'billSplitPayment' => 0,
    'billSplitPaymentArgs' => '',
    'billPaymentChannel' => '0', // 0 for all payment channels
    'billDisplayMerchant' => 1
);

// Initialize cURL session
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, TOYYIBPAY_API_URL . '/createBill');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // For testing only
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // For testing only

// Execute cURL request
$result = curl_exec($curl);

// Get cURL errors if any
$curl_error = '';
if(curl_errno($curl)) {
    $curl_error = curl_error($curl);
}

$info = curl_getinfo($curl);
curl_close($curl);

// Process response
$response = json_decode($result, true);

if ($response && is_array($response) && isset($response[0]['BillCode'])) {
    // Store payment information in session for callback
    $_SESSION['pending_payment'] = array(
        'pembayaranID' => $pembayaranID,
        'tempahanID' => $tempahanID,
        'amount' => $amount
    );
    
    // Redirect to ToyyibPay payment page
    $billCode = $response[0]['BillCode'];
    $paymentUrl = 'https://dev.toyyibpay.com/' . $billCode;
    header('Location: ' . $paymentUrl);
    exit();
} else {
    // Show debug information
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Payment Error</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .debug-info { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .error { color: red; }
            .back-button { 
                background: #28a745; 
                color: white; 
                padding: 10px 20px; 
                border: none; 
                border-radius: 5px; 
                cursor: pointer; 
                text-decoration: none;
                display: inline-block;
            }
            .back-button:hover { background: #218838; }
        </style>
    </head>
    <body>
        <h2>Payment Error</h2>
        <div class="debug-info">
            <h3>Data Sent to ToyyibPay:</h3>
            <pre>' . print_r($data, true) . '</pre>
            
            <h3>cURL Error (if any):</h3>
            <pre>' . ($curl_error ? $curl_error : 'No cURL errors') . '</pre>
            
            <h3>Raw API Response:</h3>
            <pre>' . htmlspecialchars($result) . '</pre>
            
            <h3>Decoded Response:</h3>
            <pre>' . print_r($response, true) . '</pre>
        </div>
        
        <a href="../bayar.php?id=' . $tempahanID . '" class="back-button">Back to Payment Page</a>
    </body>
    </html>';
    exit();
}
=======
<?php
require_once 'toyyibpay_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['pelangganID'])) {
    header("Location: ../login.html");
    exit();
}

// Validate required POST data
$required_fields = ['tempahanID', 'amount', 'name', 'email', 'phone'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    die("Error: Missing required fields: " . implode(', ', $missing_fields));
}

// Get form data
$tempahanID = $_POST['tempahanID'];
$amount = $_POST['amount'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];

// Generate payment ID
$pembayaranID = 'PM' . strtoupper(uniqid());

// Prepare data for ToyyibPay
$data = array(
    'userSecretKey' => TOYYIBPAY_USER_SECRET_KEY,
    'categoryCode' => TOYYIBPAY_CATEGORY_CODE,
    'billName' => 'Servis-X Payment',
    'billDescription' => 'Payment for booking ID: ' . $tempahanID,
    'billPriceSetting' => 1, // 1 for fixed price
    'billPayorInfo' => 1, // 1 to collect customer info
    'billAmount' => $amount * 100, // Convert to cents
    'billReturnUrl' => TOYYIBPAY_RETURN_URL,
    'billCallbackUrl' => TOYYIBPAY_CALLBACK_URL,
    'billExternalReferenceNo' => $tempahanID,
    'billTo' => $name,
    'billEmail' => $email,
    'billPhone' => $phone,
    'billContentEmail' => 'Thank you for your payment!',
    'billChargeToCustomer' => 1,
    'billSplitPayment' => 0,
    'billSplitPaymentArgs' => '',
    'billPaymentChannel' => '0', // 0 for all payment channels
    'billDisplayMerchant' => 1
);

// Initialize cURL session
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, TOYYIBPAY_API_URL . '/createBill');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // For testing only
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // For testing only

// Execute cURL request
$result = curl_exec($curl);

// Get cURL errors if any
$curl_error = '';
if(curl_errno($curl)) {
    $curl_error = curl_error($curl);
}

$info = curl_getinfo($curl);
curl_close($curl);

// Process response
$response = json_decode($result, true);

if ($response && is_array($response) && isset($response[0]['BillCode'])) {
    // Store payment information in session for callback
    $_SESSION['pending_payment'] = array(
        'pembayaranID' => $pembayaranID,
        'tempahanID' => $tempahanID,
        'amount' => $amount
    );
    
    // Redirect to ToyyibPay payment page
    $billCode = $response[0]['BillCode'];
    $paymentUrl = 'https://dev.toyyibpay.com/' . $billCode;
    header('Location: ' . $paymentUrl);
    exit();
} else {
    // Show debug information
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Payment Error</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .debug-info { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .error { color: red; }
            .back-button { 
                background: #28a745; 
                color: white; 
                padding: 10px 20px; 
                border: none; 
                border-radius: 5px; 
                cursor: pointer; 
                text-decoration: none;
                display: inline-block;
            }
            .back-button:hover { background: #218838; }
        </style>
    </head>
    <body>
        <h2>Payment Error</h2>
        <div class="debug-info">
            <h3>Data Sent to ToyyibPay:</h3>
            <pre>' . print_r($data, true) . '</pre>
            
            <h3>cURL Error (if any):</h3>
            <pre>' . ($curl_error ? $curl_error : 'No cURL errors') . '</pre>
            
            <h3>Raw API Response:</h3>
            <pre>' . htmlspecialchars($result) . '</pre>
            
            <h3>Decoded Response:</h3>
            <pre>' . print_r($response, true) . '</pre>
        </div>
        
        <a href="../bayar.php?id=' . $tempahanID . '" class="back-button">Back to Payment Page</a>
    </body>
    </html>';
    exit();
}
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
?> 