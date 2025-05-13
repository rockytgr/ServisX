<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];
$tempahanID = $_POST['tempahanID'] ?? '';
$choice = $_POST['choice'] ?? ''; // '1' or '2'

$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($choice === '1') {
    // Only accept servis pelanggan
    $stmt = $conn->prepare("UPDATE tempahan SET status = 'Diterima', diterima_jenis = 'pelanggan' WHERE tempahanID = ? AND pelangganID = ?");
} elseif ($choice === '2') {
    // Accept semua (pelanggan + disyorkan)
    $stmt = $conn->prepare("UPDATE tempahan SET status = 'Diterima', diterima_jenis = 'semua' WHERE tempahanID = ? AND pelangganID = ?");
} else {
    echo "<script>alert('Pilihan tidak sah.'); window.history.back();</script>";
    exit;
}

$stmt->bind_param("ss", $tempahanID, $pelangganID);

if ($stmt->execute()) {
    echo "<script>alert('Tempahan telah diterima.'); window.location.href='maklumat_servis.php';</script>";
} else {
    echo "<script>alert('Ralat ketika mengemas kini.'); window.history.back();</script>";
}
$stmt->close();
$conn->close();
?>
=======
<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];
$tempahanID = $_POST['tempahanID'] ?? '';
$choice = $_POST['choice'] ?? ''; // '1' or '2'

$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($choice === '1') {
    // Only accept servis pelanggan
    $stmt = $conn->prepare("UPDATE tempahan SET status = 'Diterima', diterima_jenis = 'pelanggan' WHERE tempahanID = ? AND pelangganID = ?");
} elseif ($choice === '2') {
    // Accept semua (pelanggan + disyorkan)
    $stmt = $conn->prepare("UPDATE tempahan SET status = 'Diterima', diterima_jenis = 'semua' WHERE tempahanID = ? AND pelangganID = ?");
} else {
    echo "<script>alert('Pilihan tidak sah.'); window.history.back();</script>";
    exit;
}

$stmt->bind_param("ss", $tempahanID, $pelangganID);

if ($stmt->execute()) {
    echo "<script>alert('Tempahan telah diterima.'); window.location.href='maklumat_servis.php';</script>";
} else {
    echo "<script>alert('Ralat ketika mengemas kini.'); window.history.back();</script>";
}
$stmt->close();
$conn->close();
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
