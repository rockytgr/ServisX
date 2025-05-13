<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DB Connection
    $conn = new mysqli("localhost", "root", "", "servisx");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $tempahanID = $_POST['tempahanID'];
    $nama_servis = $_POST['nama_servis'];
    $harga_servis = $_POST['harga_servis'];

    // Generate next servisID
    $result = $conn->query("SELECT MAX(servisID) AS lastID FROM disyorkan_servis WHERE servisID LIKE 'DS%'");
    $row = $result->fetch_assoc();
    $lastID = $row['lastID'];

    $nextNumber = 1;
    if ($lastID) {
        $num = (int)substr($lastID, 2);
        $nextNumber = $num + 1;
    }
    $newServisID = 'DS' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

    // Insert into disyorkan_servis
    $stmt = $conn->prepare("INSERT INTO disyorkan_servis (servisID, tempahanID, nama_servis, harga_servis) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $newServisID, $tempahanID, $nama_servis, $harga_servis);

    if ($stmt->execute()) {
        header("Location: kemaskini_tempahan.php?tempahanID=$tempahanID");
        exit();
    } else {
        echo "Ralat semasa menyimpan servis disyorkan: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
=======
<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DB Connection
    $conn = new mysqli("localhost", "root", "", "servisx");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $tempahanID = $_POST['tempahanID'];
    $nama_servis = $_POST['nama_servis'];
    $harga_servis = $_POST['harga_servis'];

    // Generate next servisID
    $result = $conn->query("SELECT MAX(servisID) AS lastID FROM disyorkan_servis WHERE servisID LIKE 'DS%'");
    $row = $result->fetch_assoc();
    $lastID = $row['lastID'];

    $nextNumber = 1;
    if ($lastID) {
        $num = (int)substr($lastID, 2);
        $nextNumber = $num + 1;
    }
    $newServisID = 'DS' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

    // Insert into disyorkan_servis
    $stmt = $conn->prepare("INSERT INTO disyorkan_servis (servisID, tempahanID, nama_servis, harga_servis) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $newServisID, $tempahanID, $nama_servis, $harga_servis);

    if ($stmt->execute()) {
        header("Location: kemaskini_tempahan.php?tempahanID=$tempahanID");
        exit();
    } else {
        echo "Ralat semasa menyimpan servis disyorkan: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
