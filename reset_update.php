<<<<<<< HEAD
<?php
require 'conn.php';

$token = $_POST['token'] ?? '';
$password = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($password !== $confirm) {
    echo "<script>alert('Katalaluan tidak sepadan');history.back();</script>";
    exit();
}

$stmt = $conn->prepare("SELECT email, user_type FROM reset_tokens WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "<script>alert('Token tidak sah.'); window.location='login.html';</script>";
    exit();
}

$stmt->bind_result($email, $user_type);
$stmt->fetch();

// Hash password
$hashed = password_hash($password, PASSWORD_BCRYPT);

// 🔄 Update correct table
if ($user_type === 'pelanggan') {
    $update = $conn->prepare("UPDATE pelanggan SET kata_laluan = ? WHERE emel_p = ?");
} elseif ($user_type === 'bengkel') {
    $update = $conn->prepare("UPDATE bengkel SET kata_laluan = ? WHERE emel_bengkel = ?");
}
$update->bind_param("ss", $hashed, $email);
$update->execute();

// ✅ Clean up token
$conn->query("DELETE FROM reset_tokens WHERE email = '$email'");

echo "<script>alert('Katalaluan telah ditetapkan semula.'); window.location='login.html';</script>";

=======
<?php
require 'conn.php';

$token = $_POST['token'] ?? '';
$password = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($password !== $confirm) {
    echo "<script>alert('Katalaluan tidak sepadan');history.back();</script>";
    exit();
}

$stmt = $conn->prepare("SELECT email, user_type FROM reset_tokens WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "<script>alert('Token tidak sah.'); window.location='login.html';</script>";
    exit();
}

$stmt->bind_result($email, $user_type);
$stmt->fetch();

// Hash password
$hashed = password_hash($password, PASSWORD_BCRYPT);

// 🔄 Update correct table
if ($user_type === 'pelanggan') {
    $update = $conn->prepare("UPDATE pelanggan SET kata_laluan = ? WHERE emel_p = ?");
} elseif ($user_type === 'bengkel') {
    $update = $conn->prepare("UPDATE bengkel SET kata_laluan = ? WHERE emel_bengkel = ?");
}
$update->bind_param("ss", $hashed, $email);
$update->execute();

// ✅ Clean up token
$conn->query("DELETE FROM reset_tokens WHERE email = '$email'");

echo "<script>alert('Katalaluan telah ditetapkan semula.'); window.location='login.html';</script>";

>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
