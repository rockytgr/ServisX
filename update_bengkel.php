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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the bengkel data from the form
    $bengkelID = $_POST['bengkelID'];
    $nama_bengkel = $_POST['nama_bengkel'];
    $emel_bengkel = $_POST['emel_bengkel'];
    $status_pengesahan = $_POST['status_pengesahan'];

    // Update bengkel details in the database
    $sql_update = "UPDATE bengkel 
                   SET nama_bengkel = ?, emel_bengkel = ?, status_pengesahan = ? 
                   WHERE bengkelID = ?";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssss", $nama_bengkel, $emel_bengkel, $status_pengesahan, $bengkelID);
    $stmt->execute();
    
    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Redirect back to the admin page after update
    header("Location: admin.php");
    exit();
}
?>
