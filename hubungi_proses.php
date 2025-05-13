<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function log_debug($message) {
    file_put_contents("debug.log", date("Y-m-d H:i:s") . " | " . $message . "\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['name'] ?? '';
    $email_user = $_POST['email'] ?? '';
    $mesej = $_POST['message'] ?? '';
    $bengkelID = $_POST['workshop'] ?? '';

    log_debug("Form submitted");
    log_debug("Name: $nama");
    log_debug("Email: $email_user");
    log_debug("Workshop ID: $bengkelID");
    log_debug("Message: $mesej");

    // DB Connection
    $conn = new mysqli("localhost", "root", "", "servisx");
    if ($conn->connect_error) {
        log_debug("Database connection failed: " . $conn->connect_error);
        die("Connection failed: " . $conn->connect_error);
    }

    // Get bengkel email
    $stmt = $conn->prepare("SELECT emel_bengkel FROM bengkel WHERE bengkelID = ?");
    $stmt->bind_param("s", $bengkelID);
    $stmt->execute();
    $stmt->bind_result($emel_bengkel);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    log_debug("Retrieved bengkel email: $emel_bengkel");

    if (!$emel_bengkel) {
        log_debug("Invalid bengkel ID");
        echo "<script>alert('Bengkel tidak sah.'); window.history.back();</script>";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'amirith27@gmail.com';
        $mail->Password = 'kkdyuqattojgwihw'; // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Debugging
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function ($str, $level) {
            log_debug("SMTP: $str");
        };

        $mail->setFrom('amirith27@gmail.com', 'Customer Service Servis-X');
        $mail->addAddress($emel_bengkel);
        $mail->addReplyTo($email_user, $nama);

        $mail->isHTML(false);
        $mail->Subject = "Pertanyaan dari Pelanggan: $nama";
        $mail->Body = "Nama: $nama\nEmel Pelanggan: $email_user\n\nMesej:\n$mesej";

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
                'cafile'            => 'C:/xampp/php/extras/ssl/cacert.pem'
            ]
        ];
        

        $mail->send();
        log_debug("Email sent successfully");
        echo "<script>alert('Emel berjaya dihantar ke bengkel.'); window.location.href='hubungin.php';</script>";
    } catch (Exception $e) {
        log_debug("PHPMailer error: " . $mail->ErrorInfo);
        echo "<script>alert('Ralat semasa menghantar emel: {$mail->ErrorInfo}'); window.history.back();</script>";
    }
}
