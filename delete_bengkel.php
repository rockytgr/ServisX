<?php
// Start session to store user information if login is successful
session_start();

// Redirect to login if not logged in as an admin
if (!isset($_SESSION['adminID'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "servisx"; // your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' parameter is passed for deletion
if (isset($_GET['id'])) {
    $bengkelID = $_GET['id'];

    // First, delete the record from the 'pengesahan' table
    $sql_delete_pengesahan = "DELETE FROM pengesahan WHERE bengkelID = ?";
    $stmt_pengesahan = $conn->prepare($sql_delete_pengesahan);
    $stmt_pengesahan->bind_param("s", $bengkelID);
    $stmt_pengesahan->execute();
    $stmt_pengesahan->close();

    // Now, delete the record from the 'bengkel' table
    $sql_delete_bengkel = "DELETE FROM bengkel WHERE bengkelID = ?";
    $stmt_bengkel = $conn->prepare($sql_delete_bengkel);
    $stmt_bengkel->bind_param("s", $bengkelID);
    $stmt_bengkel->execute();
    $stmt_bengkel->close();

    // Close the connection
    $conn->close();

    // Redirect back to the admin page after deletion
    header("Location: admin.php");
    exit();
}
?>
