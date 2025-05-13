<<<<<<< HEAD
<?php
session_start();

if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Ralat: ID servis tidak diberikan.";
    exit();
}

$id = $_GET['id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get tempahanID before delete so we can redirect back
$getTempahanSQL = "SELECT tempahanID FROM disyorkan_servis WHERE id = ?";
$stmt = $conn->prepare($getTempahanSQL);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($tempahanID);
$stmt->fetch();
$stmt->close();

// Now delete the record
$deleteSQL = "DELETE FROM disyorkan_servis WHERE id = ?";
$stmt = $conn->prepare($deleteSQL);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: kemaskini_tempahan.php?tempahanID=$tempahanID");
    exit();
} else {
    echo "Gagal memadam servis.";
}

$stmt->close();
$conn->close();
?>
=======
<?php
session_start();

if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Ralat: ID servis tidak diberikan.";
    exit();
}

$id = $_GET['id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get tempahanID before delete so we can redirect back
$getTempahanSQL = "SELECT tempahanID FROM disyorkan_servis WHERE id = ?";
$stmt = $conn->prepare($getTempahanSQL);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($tempahanID);
$stmt->fetch();
$stmt->close();

// Now delete the record
$deleteSQL = "DELETE FROM disyorkan_servis WHERE id = ?";
$stmt = $conn->prepare($deleteSQL);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: kemaskini_tempahan.php?tempahanID=$tempahanID");
    exit();
} else {
    echo "Gagal memadam servis.";
}

$stmt->close();
$conn->close();
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
