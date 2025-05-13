<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Make sure path is correct

if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];
$bengkelID = $_SESSION['bengkelID'];
$tarikh = $_SESSION['tarikh'];
$masa = $_SESSION['masa'];
$keretaID = $_SESSION['keretaID'];

$komen = $_POST['komen'];
$servisIDs = explode(',', $_POST['servis_ids']);
$status = "Menunggu";
$tarikh_tempahan = date("Y-m-d"); // current date

// DB connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Generate next tempahanID (e.g., T001, T002...)
$result = $conn->query("SELECT tempahanID FROM tempahan ORDER BY tempahanID DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $lastID = intval(substr($row['tempahanID'], 1));
    $nextID = 'T' . str_pad($lastID + 1, 3, '0', STR_PAD_LEFT);
} else {
    $nextID = 'T001';
}

// 2. Insert into tempahan table
$stmt = $conn->prepare("INSERT INTO tempahan (tempahanID, pelangganID, bengkelID, keretaID, tarikh, masa, komen, tarikh_tempahan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $nextID, $pelangganID, $bengkelID, $keretaID, $tarikh, $masa, $komen, $tarikh_tempahan, $status);
if (!$stmt->execute()) {
    die("Gagal simpan tempahan: " . $stmt->error);
}
$stmt->close();

// 3. Insert into tempahan_servis for each selected servisID
foreach ($servisIDs as $servisID) {
    $stmt = $conn->prepare("INSERT INTO tempahan_servis (tempahanID, servisID) VALUES (?, ?)");
    $stmt->bind_param("ss", $nextID, $servisID);
    if (!$stmt->execute()) {
        die("Gagal simpan servis: " . $stmt->error);
    }
    $stmt->close();
}

$conn->close();



function log_debug($message) {
    file_put_contents("debug.log", date("Y-m-d H:i:s") . " | " . $message . "\n", FILE_APPEND);
}

// Reconnect to DB to get emails
$conn = new mysqli("localhost", "root", "", "servisx");

// Pelanggan
$stmt = $conn->prepare("SELECT emel_p, nama_p FROM pelanggan WHERE pelangganID = ?");
$stmt->bind_param("s", $pelangganID);
$stmt->execute();
$stmt->bind_result($emel_pelanggan, $nama_pelanggan);
$stmt->fetch();
$stmt->close();

// Bengkel
$stmt = $conn->prepare("SELECT emel_bengkel, nama_bengkel FROM bengkel WHERE bengkelID = ?");
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$stmt->bind_result($emel_bengkel, $nama_bengkel);
$stmt->fetch();
$stmt->close();
$conn->close();

log_debug("Booking email to: $emel_pelanggan & $emel_bengkel");

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'amirith27@gmail.com';
    $mail->Password = 'kkdyuqattojgwihw'; // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function ($str, $level) {
        log_debug("SMTP: $str");
    };

    $mail->setFrom('amirith27@gmail.com', 'ServisX');

    // Send to Pelanggan
    $mail->clearAllRecipients();
    $mail->addAddress($emel_pelanggan, $nama_pelanggan);
    $mail->isHTML(false);
    $mail->Subject = "Tempahan Anda ($nextID)";
    $mail->Body = "Hi $nama_pelanggan,\n\nTempahan anda ($nextID) telah berjaya dibuat untuk $tarikh pada jam $masa.\n\nSila tunggu pengesahan daripada bengkel.\n\nTerima kasih,\nServisX";
    $mail->send();
    log_debug("Email sent to pelanggan: $emel_pelanggan");

    // Send to Bengkel
    $mail->clearAllRecipients();
    $mail->addAddress($emel_bengkel, $nama_bengkel);
    $mail->Subject = "Tempahan Baru dari $nama_pelanggan ($nextID)";
    $mail->Body = "Hai $nama_bengkel,\n\nAnda menerima tempahan baru ($nextID) dari $nama_pelanggan untuk $tarikh pada jam $masa.\n\nSila semak sistem untuk mengesahkan tempahan.\n\nTerima kasih,\nServisX";
    $mail->send();
    log_debug("Email sent to bengkel: $emel_bengkel");

} catch (Exception $e) {
    log_debug("PHPMailer error: " . $mail->ErrorInfo);
    // Optional: show error to user
}
echo "<script>alert('Tempahan berjaya dibuat. Sila tunggu pengesahan.'); window.location.href='indexin.php';</script>";
?>
