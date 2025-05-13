<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "servisx");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $tempahanID = $_POST['tempahanID'];
    $jumlah_harga = $_POST['jumlah_harga'];
    $new_status = $_POST['status'];

    // ✅ Check current status in database
    $stmt = $conn->prepare("SELECT status FROM tempahan WHERE tempahanID = ?");
    $stmt->bind_param("s", $tempahanID);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    // ✅ Only proceed if status actually changed
    if ($new_status !== $current_status) {

        // Update tempahan table
        $stmt = $conn->prepare("UPDATE tempahan SET jumlah_harga = ?, status = ? WHERE tempahanID = ?");
        $stmt->bind_param("dss", $jumlah_harga, $new_status, $tempahanID);

        if ($stmt->execute()) {
            $stmt->close();

            // === Fetch pelanggan info ===
            $stmt = $conn->prepare("SELECT p.nama_p, p.emel_p, t.tarikh, t.masa FROM tempahan t
                                    JOIN pelanggan p ON t.pelangganID = p.pelangganID
                                    WHERE t.tempahanID = ?");
            $stmt->bind_param("s", $tempahanID);
            $stmt->execute();
            $stmt->bind_result($nama, $emel, $tarikh, $masa);
            $stmt->fetch();
            $stmt->close();

            // === Email content based on status ===
            switch ($new_status) {
                case 'Menunggu Kelulusan':
                    $subject = "Tempahan Anda Menunggu Kelulusan";
                    $body = "Hi $nama,<br><br>Tempahan anda ID <strong>$tempahanID</strong> telah dikemaskini kepada status <strong>$new_status</strong>.<br>Sila log masuk untuk menyemak servis, disyorkan servis, dan harga.<br><br>Tarikh: $tarikh<br>Masa: $masa<br><br>Terima kasih.";
                    break;
                case 'Sedang Berjalan':
                    $subject = "Tempahan Anda Sedang Diservis";
                    $body = "Hi $nama,<br><br>Mekanik sedang melakukan servis untuk tempahan <strong>$tempahanID</strong>.<br><br>Tarikh: $tarikh<br>Masa: $masa<br><br>Terima kasih.";
                    break;
                case 'Selesai':
                    $subject = "Tempahan Selesai - Sila Buat Bayaran";
                    $body = "Hi $nama,<br><br>Tempahan anda ID <strong>$tempahanID</strong> telah selesai.<br>Sila buat bayaran dan ambil semula kenderaan anda.<br><br>Terima kasih kerana memilih ServisX.";
                    break;
                default:
                    $subject = "Kemaskini Tempahan";
                    $body = "Hi $nama,<br><br>Status tempahan anda ($tempahanID) telah dikemaskini ke <strong>$new_status</strong>.";
            }

            // === Send Email ===
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'amirith27@gmail.com';   // Your Gmail
                $mail->Password   = 'kkdyuqattojgwihw';       // App password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('amirith27@gmail.com', 'ServisX');
                $mail->addAddress($emel, $nama);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;

                $mail->send();
            } catch (Exception $e) {
                error_log("Gagal hantar emel status tempahan: " . $mail->ErrorInfo);
            }
        }
    } else {
        // ✅ Status not changed — still update price
        $stmt = $conn->prepare("UPDATE tempahan SET jumlah_harga = ? WHERE tempahanID = ?");
        $stmt->bind_param("ds", $jumlah_harga, $tempahanID);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    header("Location: kemaskini_tempahan.php?tempahanID=$tempahanID&success=1");
    exit();
}
?>
=======
<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "servisx");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $tempahanID = $_POST['tempahanID'];
    $jumlah_harga = $_POST['jumlah_harga'];
    $new_status = $_POST['status'];

    // ✅ Check current status in database
    $stmt = $conn->prepare("SELECT status FROM tempahan WHERE tempahanID = ?");
    $stmt->bind_param("s", $tempahanID);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    // ✅ Only proceed if status actually changed
    if ($new_status !== $current_status) {

        // Update tempahan table
        $stmt = $conn->prepare("UPDATE tempahan SET jumlah_harga = ?, status = ? WHERE tempahanID = ?");
        $stmt->bind_param("dss", $jumlah_harga, $new_status, $tempahanID);

        if ($stmt->execute()) {
            $stmt->close();

            // === Fetch pelanggan info ===
            $stmt = $conn->prepare("SELECT p.nama_p, p.emel_p, t.tarikh, t.masa FROM tempahan t
                                    JOIN pelanggan p ON t.pelangganID = p.pelangganID
                                    WHERE t.tempahanID = ?");
            $stmt->bind_param("s", $tempahanID);
            $stmt->execute();
            $stmt->bind_result($nama, $emel, $tarikh, $masa);
            $stmt->fetch();
            $stmt->close();

            // === Email content based on status ===
            switch ($new_status) {
                case 'Menunggu Kelulusan':
                    $subject = "Tempahan Anda Menunggu Kelulusan";
                    $body = "Hi $nama,<br><br>Tempahan anda ID <strong>$tempahanID</strong> telah dikemaskini kepada status <strong>$new_status</strong>.<br>Sila log masuk untuk menyemak servis, disyorkan servis, dan harga.<br><br>Tarikh: $tarikh<br>Masa: $masa<br><br>Terima kasih.";
                    break;
                case 'Sedang Berjalan':
                    $subject = "Tempahan Anda Sedang Diservis";
                    $body = "Hi $nama,<br><br>Mekanik sedang melakukan servis untuk tempahan <strong>$tempahanID</strong>.<br><br>Tarikh: $tarikh<br>Masa: $masa<br><br>Terima kasih.";
                    break;
                case 'Selesai':
                    $subject = "Tempahan Selesai - Sila Buat Bayaran";
                    $body = "Hi $nama,<br><br>Tempahan anda ID <strong>$tempahanID</strong> telah selesai.<br>Sila buat bayaran dan ambil semula kenderaan anda.<br><br>Terima kasih kerana memilih ServisX.";
                    break;
                default:
                    $subject = "Kemaskini Tempahan";
                    $body = "Hi $nama,<br><br>Status tempahan anda ($tempahanID) telah dikemaskini ke <strong>$new_status</strong>.";
            }

            // === Send Email ===
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'amirith27@gmail.com';   // Your Gmail
                $mail->Password   = 'kkdyuqattojgwihw';       // App password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('amirith27@gmail.com', 'ServisX');
                $mail->addAddress($emel, $nama);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;

                $mail->send();
            } catch (Exception $e) {
                error_log("Gagal hantar emel status tempahan: " . $mail->ErrorInfo);
            }
        }
    } else {
        // ✅ Status not changed — still update price
        $stmt = $conn->prepare("UPDATE tempahan SET jumlah_harga = ? WHERE tempahanID = ?");
        $stmt->bind_param("ds", $jumlah_harga, $tempahanID);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    header("Location: kemaskini_tempahan.php?tempahanID=$tempahanID&success=1");
    exit();
}
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
