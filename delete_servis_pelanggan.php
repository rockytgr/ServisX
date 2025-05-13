<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['tempahanID']) || !isset($_GET['servisID'])) {
    echo "Maklumat tidak lengkap.";
    exit();
}

$tempahanID = $_GET['tempahanID'];
$servisID = $_GET['servisID'];

// DB connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete from tempahan_servis table
$stmt = $conn->prepare("DELETE FROM tempahan_servis WHERE tempahanID = ? AND servisID = ?");
$stmt->bind_param("ss", $tempahanID, $servisID);
if ($stmt->execute()) {
    header("Location: kemaskini_tempahan.php?tempahanID=" . $tempahanID);
    exit();
} else {
    echo "Ralat semasa memadam servis pelanggan.";
}

$stmt->close();
$conn->close();
?>
