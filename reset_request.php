<<<<<<< HEAD
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'conn.php'; // Your DB connection
require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Asia/Kuala_Lumpur');





// Process the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
$email = trim($_POST['email']);

if ($user_type === 'pelanggan') {
    $stmt = $conn->prepare("SELECT pelangganID FROM pelanggan WHERE emel_p = ?");
} elseif ($user_type === 'bengkel') {
    $stmt = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ?");
} else {
    die("Invalid user type.");
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "<script>alert('Emel tidak dijumpai');history.back();</script>";
    exit();
}

// Continue generating token...
$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

$conn->query("DELETE FROM reset_tokens WHERE email = '$email'");

$insert = $conn->prepare("INSERT INTO reset_tokens (email, token, expires_at, user_type) VALUES (?, ?, ?, ?)");
$insert->bind_param("ssss", $email, $token, $expires_at, $user_type);
$insert->execute();

// PHPMailer code remains the same...


    // Prepare PHPMailer
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'amirith27@gmail.com'; // YOUR GMAIL
        $mail->Password = 'kkdyuqattojgwihw'; // APP PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email Settings
        $mail->setFrom('amirith27@gmail.com', 'Servis-X');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Kata Laluan Anda - SERVIS-X';

        $resetLink = "http://localhost/fyp/resetf_password.php?token=$token";
        $mail->Body = "
            <h3>Reset Kata Laluan Anda</h3>
            <p>Klik pautan di bawah untuk menetapkan semula kata laluan anda:</p>
            <a href='$resetLink'>$resetLink</a><br><br>
            <small>Pautan ini akan tamat selepas 1 jam.</small>
        ";

        $mail->send();
        echo "<script>alert('Pautan reset telah dihantar ke emel anda.');window.location='login.html';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Emel gagal dihantar: {$mail->ErrorInfo}');history.back();</script>";
    }
}
?>
=======
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'conn.php'; // Your DB connection
require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Asia/Kuala_Lumpur');





// Process the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
$email = trim($_POST['email']);

if ($user_type === 'pelanggan') {
    $stmt = $conn->prepare("SELECT pelangganID FROM pelanggan WHERE emel_p = ?");
} elseif ($user_type === 'bengkel') {
    $stmt = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ?");
} else {
    die("Invalid user type.");
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "<script>alert('Emel tidak dijumpai');history.back();</script>";
    exit();
}

// Continue generating token...
$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

$conn->query("DELETE FROM reset_tokens WHERE email = '$email'");

$insert = $conn->prepare("INSERT INTO reset_tokens (email, token, expires_at, user_type) VALUES (?, ?, ?, ?)");
$insert->bind_param("ssss", $email, $token, $expires_at, $user_type);
$insert->execute();

// PHPMailer code remains the same...


    // Prepare PHPMailer
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'amirith27@gmail.com'; // YOUR GMAIL
        $mail->Password = 'kkdyuqattojgwihw'; // APP PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email Settings
        $mail->setFrom('amirith27@gmail.com', 'Servis-X');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Kata Laluan Anda - SERVIS-X';

        $resetLink = "http://localhost/fyp/resetf_password.php?token=$token";
        $mail->Body = "
            <h3>Reset Kata Laluan Anda</h3>
            <p>Klik pautan di bawah untuk menetapkan semula kata laluan anda:</p>
            <a href='$resetLink'>$resetLink</a><br><br>
            <small>Pautan ini akan tamat selepas 1 jam.</small>
        ";

        $mail->send();
        echo "<script>alert('Pautan reset telah dihantar ke emel anda.');window.location='login.html';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Emel gagal dihantar: {$mail->ErrorInfo}');history.back();</script>";
    }
}
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
