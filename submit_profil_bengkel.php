<?php
session_start();

if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$bengkelID = $_SESSION['bengkelID'];

$sql = "SELECT nama_bengkel, emel_bengkel, telefon_bengkel, lokasi, foto_profil, kata_laluan FROM bengkel WHERE bengkelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($nama_bengkel, $emel_bengkel, $telefon_bengkel, $lokasi, $foto_profil, $kata_laluan);
$stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_nama_bengkel = $_POST['nama_bengkel'];
    $new_emel = $_POST['emel'];
    $new_telefon_bengkel = $_POST['telefon_bengkel'];
    $new_lokasi = $_POST['lokasi'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $error = "";

    // === Update password only if both old & new password are filled ===
    if (!empty($old_password) && !empty($new_password)) {
        if (password_verify($old_password, $kata_laluan)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update_password = "UPDATE bengkel SET kata_laluan = ? WHERE bengkelID = ?";
            $stmt_update_password = $conn->prepare($sql_update_password);
            $stmt_update_password->bind_param("ss", $hashed_password, $bengkelID);
            $stmt_update_password->execute();
        } else {
            $_SESSION['error_message'] = "Old password is incorrect.";
            header("Location: profil_bengkel.php");
            exit();
        }
    }

    // === Update general profile info ===
    $sql_update_profile = "UPDATE bengkel SET nama_bengkel = ?, emel_bengkel = ?, telefon_bengkel = ?, lokasi = ? WHERE bengkelID = ?";
    $stmt_update_profile = $conn->prepare($sql_update_profile);
    $stmt_update_profile->bind_param("sssss", $new_nama_bengkel, $new_emel, $new_telefon_bengkel, $new_lokasi, $bengkelID);
    $stmt_update_profile->execute();

    // === Optional profile photo upload ===
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $new_filename = uniqid("bengkel_", true) . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
                $sql_update_photo = "UPDATE bengkel SET foto_profil = ? WHERE bengkelID = ?";
                $stmt_update_photo = $conn->prepare($sql_update_photo);
                $stmt_update_photo->bind_param("ss", $target_file, $bengkelID);
                $stmt_update_photo->execute();
            } else {
                $_SESSION['error_message'] = "Gagal memuat naik gambar profil.";
                header("Location: profil_bengkel.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Format fail tidak sah. Hanya JPG, JPEG, PNG dibenarkan.";
            header("Location: profil_bengkel.php");
            exit();
        }
    }

    // ✅ Final redirect
    $_SESSION['success_message'] = "Profil berjaya dikemas kini.";
    header("Location: profil_bengkel.php");
    exit();
}
?>
