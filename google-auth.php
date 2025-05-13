<<<<<<< HEAD
<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;

// Get the incoming request data
$data = json_decode(file_get_contents("php://input"), true);
$credential = $data['credential'] ?? '';
$role = $data['role'] ?? 'pelanggan';

// Decode Google JWT token (basic payload decoding)
$parts = explode('.', $credential);
if (count($parts) !== 3) {
    echo json_encode(["success" => false, "error" => "Invalid token format"]);
    exit();
}
$payload = json_decode(base64_decode($parts[1]), true);

// Prevent same email across both roles
if ($role === 'pelanggan') {
    // Check if already exists in bengkel
    $check = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Emel ini telah digunakan untuk akaun bengkel."]);
        exit();
    }
}

if ($role === 'bengkel') {
    // Check if already exists in pelanggan
    $check = $conn->prepare("SELECT pelangganID FROM pelanggan WHERE emel_p = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Emel ini telah digunakan untuk akaun pelanggan."]);
        exit();
    }
}


// Extract info from Google
$email   = $payload['email'] ?? '';
$name    = $payload['name'] ?? 'Unknown';
$picture = $payload['picture'] ?? '';

if (empty($email)) {
    echo json_encode(["success" => false, "error" => "Missing email in token"]);
    exit();
}

// Connect to MySQL
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection error"]);
    exit();
}

// Start session
session_start();

if ($role === 'pelanggan') {
    // Check if pelanggan exists
    $stmt = $conn->prepare("SELECT pelangganID FROM pelanggan WHERE emel_p = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Register new pelanggan
        $res = $conn->query("SELECT MAX(CAST(SUBSTRING(pelangganID, 2) AS UNSIGNED)) AS max_id FROM pelanggan");
        $next_id = ($res->fetch_assoc()['max_id'] ?? 0) + 1;
        $pelangganID = 'P' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO pelanggan (pelangganID, nama_p, emel_p, foto_profil, kata_laluan) VALUES (?, ?, ?, ?, NULL)");
        $stmt->bind_param("ssss", $pelangganID, $name, $email, $picture);
        $stmt->execute();
    }

    // Fetch pelangganID for session
    $stmt = $conn->prepare("SELECT pelangganID FROM pelanggan WHERE emel_p = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($pelangganID);
    $stmt->fetch();
    $_SESSION['pelangganID'] = $pelangganID;

    echo json_encode(["success" => true, "redirect" => "indexin.php"]);
    exit();
}

if ($role === 'bengkel') {
    // Check if bengkel exists
    $stmt = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Register new bengkel
        $res = $conn->query("SELECT MAX(CAST(SUBSTRING(bengkelID, 2) AS UNSIGNED)) AS max_id FROM bengkel");
        $next_id = ($res->fetch_assoc()['max_id'] ?? 0) + 1;
        $bengkelID = 'B' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO bengkel (bengkelID, nama_bengkel, emel_bengkel, foto_profil, status_pengesahan, kata_laluan) VALUES (?, ?, ?, ?, 'Pending', NULL)");
        $stmt->bind_param("ssss", $bengkelID, $name, $email, $picture);
        $stmt->execute();
    }

    // Fetch bengkelID for session
    $stmt = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($bengkelID);
    $stmt->fetch();
    $_SESSION['bengkelID'] = $bengkelID;

    echo json_encode(["success" => true, "redirect" => "bengkel_dashboard.php"]);
    exit();
}

echo json_encode(["success" => false, "error" => "Unknown role"]);
$conn->close();
?>
=======
<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;

// Get the incoming request data
$data = json_decode(file_get_contents("php://input"), true);
$credential = $data['credential'] ?? '';
$role = $data['role'] ?? 'pelanggan';

// Decode Google JWT token (basic payload decoding)
$parts = explode('.', $credential);
if (count($parts) !== 3) {
    echo json_encode(["success" => false, "error" => "Invalid token format"]);
    exit();
}
$payload = json_decode(base64_decode($parts[1]), true);

// Prevent same email across both roles
if ($role === 'pelanggan') {
    // Check if already exists in bengkel
    $check = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Emel ini telah digunakan untuk akaun bengkel."]);
        exit();
    }
}

if ($role === 'bengkel') {
    // Check if already exists in pelanggan
    $check = $conn->prepare("SELECT pelangganID FROM pelanggan WHERE emel_p = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Emel ini telah digunakan untuk akaun pelanggan."]);
        exit();
    }
}


// Extract info from Google
$email   = $payload['email'] ?? '';
$name    = $payload['name'] ?? 'Unknown';
$picture = $payload['picture'] ?? '';

if (empty($email)) {
    echo json_encode(["success" => false, "error" => "Missing email in token"]);
    exit();
}

// Connect to MySQL
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection error"]);
    exit();
}

// Start session
session_start();

if ($role === 'pelanggan') {
    // Check if pelanggan exists
    $stmt = $conn->prepare("SELECT pelangganID FROM pelanggan WHERE emel_p = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Register new pelanggan
        $res = $conn->query("SELECT MAX(CAST(SUBSTRING(pelangganID, 2) AS UNSIGNED)) AS max_id FROM pelanggan");
        $next_id = ($res->fetch_assoc()['max_id'] ?? 0) + 1;
        $pelangganID = 'P' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO pelanggan (pelangganID, nama_p, emel_p, foto_profil, kata_laluan) VALUES (?, ?, ?, ?, NULL)");
        $stmt->bind_param("ssss", $pelangganID, $name, $email, $picture);
        $stmt->execute();
    }

    // Fetch pelangganID for session
    $stmt = $conn->prepare("SELECT pelangganID FROM pelanggan WHERE emel_p = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($pelangganID);
    $stmt->fetch();
    $_SESSION['pelangganID'] = $pelangganID;

    echo json_encode(["success" => true, "redirect" => "indexin.php"]);
    exit();
}

if ($role === 'bengkel') {
    // Check if bengkel exists
    $stmt = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Register new bengkel
        $res = $conn->query("SELECT MAX(CAST(SUBSTRING(bengkelID, 2) AS UNSIGNED)) AS max_id FROM bengkel");
        $next_id = ($res->fetch_assoc()['max_id'] ?? 0) + 1;
        $bengkelID = 'B' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO bengkel (bengkelID, nama_bengkel, emel_bengkel, foto_profil, status_pengesahan, kata_laluan) VALUES (?, ?, ?, ?, 'Pending', NULL)");
        $stmt->bind_param("ssss", $bengkelID, $name, $email, $picture);
        $stmt->execute();
    }

    // Fetch bengkelID for session
    $stmt = $conn->prepare("SELECT bengkelID FROM bengkel WHERE emel_bengkel = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($bengkelID);
    $stmt->fetch();
    $_SESSION['bengkelID'] = $bengkelID;

    echo json_encode(["success" => true, "redirect" => "bengkel_dashboard.php"]);
    exit();
}

echo json_encode(["success" => false, "error" => "Unknown role"]);
$conn->close();
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
