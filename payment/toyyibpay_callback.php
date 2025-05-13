<<<<<<< HEAD
<?php
require_once 'payment/toyyibpay_config.php';
session_start();

// Get callback data
$input = file_get_contents('php://input');
$callback_data = json_decode($input, true);

// Validate callback data
if (!$callback_data) {
    die("Invalid callback data");
}

// Extract data
$billcode = $callback_data['billcode'];
$order_id = $callback_data['order_id'];
$status_id = $callback_data['status_id'];

// Connect to database
$conn = new mysqli("localhost", "root", "", "servisx");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only insert if status_id is 1 (Berjaya) or 2 (Pending)
if ($status_id == 1 || $status_id == 2) {
    $tempahanID = $order_id;
    // Generate a unique pembayaranID
    $pembayaranID = 'PM' . strtoupper(uniqid());

    // Get total price from tempahan table
    $stmt = $conn->prepare("SELECT jumlah_harga FROM tempahan WHERE tempahanID = ?");
    $stmt->bind_param("s", $tempahanID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_price = $row['jumlah_harga'];

    // Debug output
    file_put_contents('callback_debug.txt', print_r([
        'callback_data' => $callback_data,
        'tempahanID' => $tempahanID,
        'pembayaranID' => $pembayaranID,
        'total_price' => $total_price,
        'status_id' => $status_id
    ], true), FILE_APPEND);

    // Insert into pembayaran table
    $stmt = $conn->prepare("INSERT INTO pembayaran (pembayaranID, tempahanID, jumlah_harga, tarikh_pembayaran, kaedah_bayaran) VALUES (?, ?, ?, NOW(), ?)");
    $kaedah_bayaran = 'ToyyibPay';
    $stmt->bind_param("ssds", $pembayaranID, $tempahanID, $total_price, $kaedah_bayaran);

    if ($stmt->execute()) {
        echo "Payment processed (inserted) with status_id: $status_id";
    } else {
        echo "Error processing payment: " . $stmt->error;
    }
} else {
    echo "Payment not inserted (status_id: $status_id)";
}

=======
<?php
require_once 'payment/toyyibpay_config.php';
session_start();

// Get callback data
$input = file_get_contents('php://input');
$callback_data = json_decode($input, true);

// Validate callback data
if (!$callback_data) {
    die("Invalid callback data");
}

// Extract data
$billcode = $callback_data['billcode'];
$order_id = $callback_data['order_id'];
$status_id = $callback_data['status_id'];

// Connect to database
$conn = new mysqli("localhost", "root", "", "servisx");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only insert if status_id is 1 (Berjaya) or 2 (Pending)
if ($status_id == 1 || $status_id == 2) {
    $tempahanID = $order_id;
    // Generate a unique pembayaranID
    $pembayaranID = 'PM' . strtoupper(uniqid());

    // Get total price from tempahan table
    $stmt = $conn->prepare("SELECT jumlah_harga FROM tempahan WHERE tempahanID = ?");
    $stmt->bind_param("s", $tempahanID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_price = $row['jumlah_harga'];

    // Debug output
    file_put_contents('callback_debug.txt', print_r([
        'callback_data' => $callback_data,
        'tempahanID' => $tempahanID,
        'pembayaranID' => $pembayaranID,
        'total_price' => $total_price,
        'status_id' => $status_id
    ], true), FILE_APPEND);

    // Insert into pembayaran table
    $stmt = $conn->prepare("INSERT INTO pembayaran (pembayaranID, tempahanID, jumlah_harga, tarikh_pembayaran, kaedah_bayaran) VALUES (?, ?, ?, NOW(), ?)");
    $kaedah_bayaran = 'ToyyibPay';
    $stmt->bind_param("ssds", $pembayaranID, $tempahanID, $total_price, $kaedah_bayaran);

    if ($stmt->execute()) {
        echo "Payment processed (inserted) with status_id: $status_id";
    } else {
        echo "Error processing payment: " . $stmt->error;
    }
} else {
    echo "Payment not inserted (status_id: $status_id)";
}

>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
$conn->close(); 