<?php
// Start session to store user information if login is successful
session_start();

// Redirect to login if not logged in as a bengkel
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Edit servis
if (isset($_POST['edit_servis'])) {
    $bengkelID = $_SESSION['bengkelID']; // Get the bengkelID from session
    $servisID = $_POST['servisID'];
    $nama_servis = $_POST['nama_servis'];
    $harga_servis = $_POST['harga_servis'];

    // Update servis details
    $sql_edit = "UPDATE servis SET nama_servis = ?, harga_servis = ? WHERE servisID = ? AND bengkelID = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("ssss", $nama_servis, $harga_servis, $servisID, $bengkelID);
    if ($stmt_edit->execute()) {
        header("Location: bengkel_dashboard.php");
        exit();
    } else {
        echo "Failed to update servis.";
    }
}
?>
