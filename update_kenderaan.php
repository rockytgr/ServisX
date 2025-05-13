<<<<<<< HEAD
<?php
session_start();

if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];

// Check required POST values
if (
    !isset($_POST['keretaID']) || !isset($_POST['edit_plate']) ||
    !isset($_POST['edit_jenama']) || !isset($_POST['edit_model']) || !isset($_POST['edit_tahun'])
) {
    echo "<script>alert('Maklumat tidak lengkap.'); window.history.back();</script>";
    exit();
}

// Sanitize input
$keretaID = trim($_POST['keretaID']);
$plate_kereta = trim($_POST['edit_plate']);
$jenama_kereta = trim($_POST['edit_jenama']);
$model_kereta = trim($_POST['edit_model']);
$tahun_kereta = trim($_POST['edit_tahun']);

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure the kereta belongs to this pelanggan
$check_sql = "SELECT keretaID FROM kereta WHERE keretaID = ? AND pelangganID = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ss", $keretaID, $pelangganID);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    echo "<script>alert('Kenderaan tidak dijumpai atau bukan milik anda.'); window.history.back();</script>";
    exit();
}
$check_stmt->close();

// Update car
$update_sql = "UPDATE kereta 
               SET plate_kereta = ?, jenama_kereta = ?, model_kereta = ?, tahun_kereta = ? 
               WHERE keretaID = ? AND pelangganID = ?";

$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ssssss", $plate_kereta, $jenama_kereta, $model_kereta, $tahun_kereta, $keretaID, $pelangganID);

if ($update_stmt->execute()) {
    echo "<script>alert('Maklumat kenderaan berjaya dikemaskini.'); window.location.href = 'profil_pelanggan.php';</script>";
} else {
    echo "<script>alert('Ralat semasa mengemaskini kenderaan: " . $update_stmt->error . "'); window.history.back();</script>";
}

$update_stmt->close();
$conn->close();
?>
=======
<?php
session_start();

if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];

// Check required POST values
if (
    !isset($_POST['keretaID']) || !isset($_POST['edit_plate']) ||
    !isset($_POST['edit_jenama']) || !isset($_POST['edit_model']) || !isset($_POST['edit_tahun'])
) {
    echo "<script>alert('Maklumat tidak lengkap.'); window.history.back();</script>";
    exit();
}

// Sanitize input
$keretaID = trim($_POST['keretaID']);
$plate_kereta = trim($_POST['edit_plate']);
$jenama_kereta = trim($_POST['edit_jenama']);
$model_kereta = trim($_POST['edit_model']);
$tahun_kereta = trim($_POST['edit_tahun']);

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure the kereta belongs to this pelanggan
$check_sql = "SELECT keretaID FROM kereta WHERE keretaID = ? AND pelangganID = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ss", $keretaID, $pelangganID);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    echo "<script>alert('Kenderaan tidak dijumpai atau bukan milik anda.'); window.history.back();</script>";
    exit();
}
$check_stmt->close();

// Update car
$update_sql = "UPDATE kereta 
               SET plate_kereta = ?, jenama_kereta = ?, model_kereta = ?, tahun_kereta = ? 
               WHERE keretaID = ? AND pelangganID = ?";

$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ssssss", $plate_kereta, $jenama_kereta, $model_kereta, $tahun_kereta, $keretaID, $pelangganID);

if ($update_stmt->execute()) {
    echo "<script>alert('Maklumat kenderaan berjaya dikemaskini.'); window.location.href = 'profil_pelanggan.php';</script>";
} else {
    echo "<script>alert('Ralat semasa mengemaskini kenderaan: " . $update_stmt->error . "'); window.history.back();</script>";
}

$update_stmt->close();
$conn->close();
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
