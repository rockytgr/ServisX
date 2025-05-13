<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tempahanID = $_POST["tempahanID"];
    $servisID = $_POST["servisID"];
    $harga = $_POST["harga"];

    $conn = new mysqli("localhost", "root", "", "servisx");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE tempahan_servis SET harga = ? WHERE tempahanID = ? AND servisID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dss", $harga, $tempahanID, $servisID);

    if ($stmt->execute()) {
        header("Location: kemaskini_tempahan.php?tempahanID=" . $tempahanID);
        exit();
    } else {
        echo "Ralat kemaskini servis pelanggan: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
