<?php
session_start();

// 1. Make sure user is logged in
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];

// 2. Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. Get submitted form data
$plate_kereta = trim($_POST['no_plate']);
$jenama_kereta = trim($_POST['jenama']);
$model_kereta = trim($_POST['model']);
$tahun_kereta = trim($_POST['tahun']);

// 4. Auto-generate next keretaID (K001, K002, ...)
$next_id_sql = "SELECT keretaID FROM kereta ORDER BY keretaID DESC LIMIT 1";
$result = $conn->query($next_id_sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_id = intval(substr($row['keretaID'], 1)); // remove 'K'
    $new_id_num = $last_id + 1;
    $new_keretaID = "K" . str_pad($new_id_num, 3, '0', STR_PAD_LEFT);
} else {
    $new_keretaID = "K001";
}

// 5. Insert into kereta table
$insert_sql = "INSERT INTO kereta (keretaID, plate_kereta, jenama_kereta, model_kereta, tahun_kereta, pelangganID)
               VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("ssssss", $new_keretaID, $plate_kereta, $jenama_kereta, $model_kereta, $tahun_kereta, $pelangganID);

if ($stmt->execute()) {
    echo "<script>alert('Kenderaan berjaya ditambah.'); window.location.href = 'profil_pelanggan.php';</script>";
} else {
    echo "<script>alert('Ralat: " . $stmt->error . "'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
