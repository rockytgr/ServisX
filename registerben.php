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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_bengkel  = trim($_POST['name']);
    $email_bengkel = trim($_POST['email']);
    $phone_bengkel = trim($_POST['phone']);
    $lokasi = trim($_POST['lokasi']);
    $password      = $_POST['password'];

    $errors = [];

    // === Validation Rules ===
    if (!preg_match("/^[A-Za-z\s]+$/", $nama_bengkel)) {
        $errors[] = "Nama bengkel hanya boleh mengandungi huruf dan ruang.";
    }

    if (!filter_var($email_bengkel, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Emel bengkel tidak sah.";
    }

    if (!preg_match("/^\d{10,11}$/", $phone_bengkel)) {
        $errors[] = "Nombor telefon mesti 10 atau 11 digit.";
    }

    if (!preg_match("/^(?=.*[A-Z])(?=.*[\W_]).{6,}$/", $password)) {
        $errors[] = "Katalaluan mesti sekurang-kurangnya 6 aksara, mengandungi satu huruf besar dan satu simbol.";
    }

    // === Check for duplicate email or phone ===
    $check = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ? OR telefon_bengkel = ?");
    $check->bind_param("ss", $email_bengkel, $phone_bengkel);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $errors[] = "Emel atau nombor telefon telah digunakan. Sila guna yang lain.";
    }
    $check->close();

    // === Stop if any validation failed ===
    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit();
    }

    // === Generate bengkelID ===
    $sql = "SELECT MAX(CAST(SUBSTRING(bengkelID, 2) AS UNSIGNED)) AS max_id FROM bengkel";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $next_id = $row['max_id'] + 1;
    $bengkelID = 'B' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

    // === Hash password securely ===
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // === Insert bengkel ===
    $stmt = $conn->prepare("INSERT INTO bengkel (bengkelID, nama_bengkel, emel_bengkel, telefon_bengkel, kata_laluan, lokasi, status_pengesahan) 
                        VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("ssssss", $bengkelID, $nama_bengkel, $email_bengkel, $phone_bengkel, $hashedPassword, $lokasi);

    if ($stmt->execute()) {
        echo "<script>alert('Pendaftaran bengkel berjaya! Menunggu pengesahan.'); window.location.href='login.html';</script>";
    } else {
        echo "Ralat: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
