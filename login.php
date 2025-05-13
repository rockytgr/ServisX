<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_phone = $_POST['email'];
    $password = trim($_POST['password']);

    // === Admin Login ===
    $sql_admin = "SELECT adminID, nama_admin, kata_laluan FROM admin WHERE nama_admin = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("s", $email_or_phone);
    $stmt_admin->execute();
    $stmt_admin->store_result();

    if ($stmt_admin->num_rows > 0) {
        $stmt_admin->bind_result($adminID, $nama_admin, $db_password_admin);
        $stmt_admin->fetch();
    
        // ✅ Log the password verification attempt
        file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " | Admin login attempt | Username: $email_or_phone | Entered Password: $password | Stored Hash: $db_password_admin\n", FILE_APPEND);
    
        if (password_verify($password, $db_password_admin)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['adminID'] = $adminID;
            $_SESSION['admin_name'] = $nama_admin;
            header("Location: admin.php");
            exit();
        } else {
            $error_message = "Katalaluan salah untuk admin!";
        }
    } else {
        // === Bengkel Login ===
        $sql_bengkel = "SELECT bengkelID, nama_bengkel, kata_laluan, status_pengesahan FROM bengkel WHERE emel_bengkel = ? OR telefon_bengkel = ?";
        $stmt_bengkel = $conn->prepare($sql_bengkel);
        $stmt_bengkel->bind_param("ss", $email_or_phone, $email_or_phone);
        $stmt_bengkel->execute();
        $stmt_bengkel->store_result();

        if ($stmt_bengkel->num_rows > 0) {
            $stmt_bengkel->bind_result($bengkelID, $nama_bengkel, $db_password_bengkel, $status_pengesahan);
            $stmt_bengkel->fetch();

            if (password_verify($password, $db_password_bengkel)) {
                if ($status_pengesahan === 'Confirmed') {
                    $_SESSION['bengkelID'] = $bengkelID;
                    $_SESSION['nama_bengkel'] = $nama_bengkel;
                    header("Location: bengkel_dashboard.php");
                    exit();
                } else {
                    $error_message = "Bengkel anda masih menunggu pengesahan.";
                }
            } else {
                $error_message = "Katalaluan bengkel tidak betul!";
            }
        } else {
            // === Pelanggan Login ===
            $sql_user = "SELECT pelangganID, nama_p, kata_laluan FROM pelanggan WHERE emel_p = ? OR telefon_p = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("ss", $email_or_phone, $email_or_phone);
            $stmt_user->execute();
            $stmt_user->store_result();

            if ($stmt_user->num_rows > 0) {
                $stmt_user->bind_result($pelangganID, $nama_p, $db_password_user);
                $stmt_user->fetch();

                if (password_verify($password, $db_password_user)) {
                    $_SESSION['pelangganID'] = $pelangganID;
                    $_SESSION['nama_p'] = $nama_p;
                    header("Location: indexin.php");
                    exit();
                } else {
                    $error_message = "Katalaluan pengguna tidak betul!";
                }
            } else {
                $error_message = "Pengguna tidak dijumpai!";
            }
        }
    }

    // Close statements
    if (isset($stmt_admin)) $stmt_admin->close();
    if (isset($stmt_bengkel)) $stmt_bengkel->close();
    if (isset($stmt_user)) $stmt_user->close();
    $conn->close();
}
?>

<!-- Display Error -->
<?php if (!empty($error_message)): ?>
  <div class="alert alert-danger text-center">
    <?php echo $error_message; ?>
  </div>
<?php endif; ?>
