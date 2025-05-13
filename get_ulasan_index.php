<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit();
}

// Join with pelanggan to get name + profile picture
$sql = "
  SELECT 
    p.nama_p, 
    p.foto_profil, 
    u.rating, 
    u.ulasan 
  FROM ulasan u
  JOIN pelanggan p ON u.pelangganID = p.pelangganID
  WHERE u.rating >= 4
  ORDER BY RAND()
  LIMIT 4
";

$result = $conn->query($sql);
$testimonials = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

echo json_encode($testimonials);
?>
