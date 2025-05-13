<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tempahanID = $_POST['tempahanID'] ?? '';
$cardName = $_POST['cardName'] ?? '';
$cardNumber = str_replace(' ', '', $_POST['cardNumber'] ?? ''); // remove spacing
$expiry = $_POST['expiry'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Validate required fields
if (empty($tempahanID) || empty($cardName) || empty($cardNumber) || empty($expiry) || empty($cvv)) {
    echo "<script>alert('Sila isi semua maklumat kad kredit.'); history.back();</script>";
    exit();
}

// Generate payment ID
$pembayaranID = 'PM' . strtoupper(uniqid());

// Get total price from tempahan table
$stmt = $conn->prepare("SELECT jumlah_harga FROM tempahan WHERE tempahanID = ?");
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$stmt->bind_result($jumlah_harga);
$stmt->fetch();
$stmt->close();

// Insert payment record
$today = date('Y-m-d');
$insert = $conn->prepare("INSERT INTO pembayaran (pembayaranID, tempahanID, jumlah_harga, tarikh_pembayaran, kaedah_bayaran) VALUES (?, ?, ?, ?, 'Kad Kredit')");
$insert->bind_param("ssds", $pembayaranID, $tempahanID, $jumlah_harga, $today);

if ($insert->execute()) {
    echo "<script>alert('Pembayaran berjaya!'); window.location='maklumat_servis.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan data pembayaran.'); history.back();</script>";
}

$conn->close();
?>
=======
<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tempahanID = $_POST['tempahanID'] ?? '';
$cardName = $_POST['cardName'] ?? '';
$cardNumber = str_replace(' ', '', $_POST['cardNumber'] ?? ''); // remove spacing
$expiry = $_POST['expiry'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Validate required fields
if (empty($tempahanID) || empty($cardName) || empty($cardNumber) || empty($expiry) || empty($cvv)) {
    echo "<script>alert('Sila isi semua maklumat kad kredit.'); history.back();</script>";
    exit();
}

// Generate payment ID
$pembayaranID = 'PM' . strtoupper(uniqid());

// Get total price from tempahan table
$stmt = $conn->prepare("SELECT jumlah_harga FROM tempahan WHERE tempahanID = ?");
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$stmt->bind_result($jumlah_harga);
$stmt->fetch();
$stmt->close();

// Insert payment record
$today = date('Y-m-d');
$insert = $conn->prepare("INSERT INTO pembayaran (pembayaranID, tempahanID, jumlah_harga, tarikh_pembayaran, kaedah_bayaran) VALUES (?, ?, ?, ?, 'Kad Kredit')");
$insert->bind_param("ssds", $pembayaranID, $tempahanID, $jumlah_harga, $today);

if ($insert->execute()) {
    echo "<script>alert('Pembayaran berjaya!'); window.location='maklumat_servis.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan data pembayaran.'); history.back();</script>";
}

$conn->close();
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
