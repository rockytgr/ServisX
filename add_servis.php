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

// Add new servis
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_servis'])) {
    $bengkelID = $_SESSION['bengkelID']; // Get the bengkelID from session
    $nama_servis = $_POST['nama_servis'];
    $harga_servis = $_POST['harga_servis'];

    // Fetch the latest servisID for the bengkelID and generate new servisID
    $sql_last_servis = "SELECT MAX(CAST(SUBSTRING(servisID, 3) AS UNSIGNED)) AS last_id FROM servis WHERE bengkelID = ?";
    $stmt_last_servis = $conn->prepare($sql_last_servis);
    $stmt_last_servis->bind_param("s", $bengkelID);
    $stmt_last_servis->execute();
    $result = $stmt_last_servis->get_result();

    // Default servisID to SS01 if no servis exists yet
    $servisID = "SS01";

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['last_id'];

        // Generate the new servisID by incrementing the last_id
        $servisID = "SS" . str_pad($last_id + 1, 2, "0", STR_PAD_LEFT);
    }

    // Insert new servis into the servis table
    $sql_insert = "INSERT INTO servis (bengkelID, servisID, nama_servis, harga_servis) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $bengkelID, $servisID, $nama_servis, $harga_servis);
    if ($stmt_insert->execute()) {
        header("Location: bengkel_dashboard.php");
        exit();
    } else {
        echo "Failed to add servis.";
    }
}
?>
