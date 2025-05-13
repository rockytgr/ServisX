<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];

    // === Validation Rules ===
    $errors = [];

    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        $errors[] = "Nama hanya boleh mengandungi huruf dan ruang.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Emel tidak sah.";
    }

    if (!preg_match("/^\d{10,11}$/", $phone)) {
        $errors[] = "Nombor telefon mesti mengandungi 10 atau 11 digit sahaja.";
    }

    if (!preg_match("/^(?=.*[A-Z])(?=.*[\W_]).{6,}$/", $password)) {
        $errors[] = "Katalaluan mesti sekurang-kurangnya 6 aksara, mempunyai sekurang-kurangnya satu huruf besar dan satu simbol.";
    }

    // === Check for duplicate email ===
    $check = $conn->prepare("SELECT emel_p FROM pelanggan WHERE emel_p = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Emel telah digunakan. Sila guna emel lain.";
    }

    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit();
    }

    // === Generate pelangganID ===
    $sql = "SELECT MAX(CAST(SUBSTRING(pelangganID, 2) AS UNSIGNED)) AS max_id FROM pelanggan";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $next_id = $row['max_id'] + 1;
    $pelangganID = 'P' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

    // === Secure password hashing ===
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // === Insert user ===
    $stmt = $conn->prepare("INSERT INTO pelanggan (pelangganID, nama_p, emel_p, telefon_p, kata_laluan) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $pelangganID, $name, $email, $phone, $hashedPassword);

    if ($stmt->execute()) {
        echo "<script>alert('Pendaftaran berjaya!'); window.location.href = 'indexin.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
