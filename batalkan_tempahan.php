<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "servisx");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $tempahanID = $_POST['tempahanID'];
    $stmt = $conn->prepare("UPDATE tempahan SET status = 'Batal' WHERE tempahanID = ?");
    $stmt->bind_param("s", $tempahanID);
    $stmt->execute();

    $stmt->close();
    $conn->close();
    header("Location: maklumat_servis.php");
    exit();
}
?>
