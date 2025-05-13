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

// Delete servis
if (isset($_GET['delete'])) {
    $bengkelID = $_SESSION['bengkelID']; // Get the bengkelID from session
    $servisID = $_GET['delete'];

    // Delete servis from the table
    $sql_delete = "DELETE FROM servis WHERE servisID = ? AND bengkelID = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ss", $servisID, $bengkelID);
    if ($stmt_delete->execute()) {
        header("Location: bengkel_dashboard.php");
        exit();
    } else {
        echo "Failed to delete servis.";
    }
}
?>
