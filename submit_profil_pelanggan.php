<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];
$nama_p = trim($_POST['nama_p']);
$emel_p = trim($_POST['emel_p']);
$telefon_p = trim($_POST['telefon_p']);
$keretaID = isset($_POST['keretaID']) && $_POST['keretaID'] !== '' ? $_POST['keretaID'] : null;
$old_password = trim($_POST['old_password']);
$new_password = trim($_POST['new_password']);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Check old password
$check_sql = "SELECT kata_laluan FROM pelanggan WHERE pelangganID = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $pelangganID);
$check_stmt->execute();
$check_stmt->bind_result($current_password);
$check_stmt->fetch();
$check_stmt->close();

if (!password_verify($old_password, $current_password)) {
    echo "<script>alert('Katalaluan lama tidak sepadan!'); window.history.back();</script>";
    exit();
}

// 2. Handle profile picture upload
$foto_profil = null;
if ($_FILES['foto_profil']['error'] == 0) {
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));

    if (in_array($file_extension, $allowed_extensions)) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = uniqid("profil_") . "." . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
            $foto_profil = $target_file;
        } else {
            echo "<script>alert('Gagal memuat naik gambar.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Format gambar tidak sah. Hanya JPG, JPEG dan PNG dibenarkan.'); window.history.back();</script>";
        exit();
    }
}

// 3. Update user profile
if ($foto_profil) {
    if ($keretaID === null) {
        // Update query with the profile picture included
        $sql = "UPDATE pelanggan SET nama_p=?, emel_p=?, telefon_p=?, keretaID=NULL, kata_laluan=?, foto_profil=? WHERE pelangganID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nama_p, $emel_p, $telefon_p, password_hash($new_password, PASSWORD_DEFAULT), $foto_profil, $pelangganID);
    } else {
        // Update query including keretaID and profile picture
        $sql = "UPDATE pelanggan SET nama_p=?, emel_p=?, telefon_p=?, keretaID=?, kata_laluan=?, foto_profil=? WHERE pelangganID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $nama_p, $emel_p, $telefon_p, $keretaID, password_hash($new_password, PASSWORD_DEFAULT), $foto_profil, $pelangganID);
    }
} else {
    // If no new profile picture, only update the other fields
    if ($keretaID === null) {
        $sql = "UPDATE pelanggan SET nama_p=?, emel_p=?, telefon_p=?, keretaID=NULL, kata_laluan=? WHERE pelangganID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nama_p, $emel_p, $telefon_p, password_hash($new_password, PASSWORD_DEFAULT), $pelangganID);
    } else {
        $sql = "UPDATE pelanggan SET nama_p=?, emel_p=?, telefon_p=?, keretaID=?, kata_laluan=? WHERE pelangganID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nama_p, $emel_p, $telefon_p, $keretaID, password_hash($new_password, PASSWORD_DEFAULT), $pelangganID);
    }
}

if ($stmt->execute()) {
    echo "<script>alert('Profil berjaya dikemas kini.'); window.location.href = 'profil_pelanggan.php';</script>";
} else {
    echo "Ralat semasa mengemaskini profil: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
