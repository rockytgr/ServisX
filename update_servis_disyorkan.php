<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"];
    $tempahanID = $_POST["tempahanID"];
    $nama_servis = $_POST["nama_servis"];
    $harga_servis = $_POST["harga_servis"];

    $conn = new mysqli("localhost", "root", "", "servisx");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE disyorkan_servis SET nama_servis = ?, harga_servis = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $nama_servis, $harga_servis, $id);

    if ($stmt->execute()) {
        header("Location: kemaskini_tempahan.php?tempahanID=" . $tempahanID);
        exit();
    } else {
        echo "Ralat kemaskini servis disyorkan: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
