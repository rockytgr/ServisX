<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$tempahanID = $_GET['id'] ?? '';
$conn = new mysqli("localhost", "root", "", "servisx");

// Get main data
$sql = "SELECT t.*, p.nama_p, p.emel_p, p.telefon_p, 
               k.plate_kereta, k.model_kereta, k.jenama_kereta, k.tahun_kereta,
               b.nama_bengkel,
               pb.pembayaranID
        FROM tempahan t
        JOIN pelanggan p ON t.pelangganID = p.pelangganID
        JOIN kereta k ON t.keretaID = k.keretaID
        JOIN bengkel b ON t.bengkelID = b.bengkelID
        LEFT JOIN pembayaran pb ON pb.tempahanID = t.tempahanID
        WHERE t.tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$logoPath = 'http://localhost/fyp/images/LogoW.png';



// Get services
$sqlServis = "SELECT s.servisID, s.nama_servis, ts.harga 
              FROM tempahan_servis ts
              JOIN servis s ON ts.servisID = s.servisID
              WHERE ts.tempahanID = ?";
$stmt = $conn->prepare($sqlServis);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$servis = $stmt->get_result();

// Get disyorkan_servis
$sqlDisyorkan = "SELECT servisID, nama_servis, harga_servis FROM disyorkan_servis WHERE tempahanID = ?";
$stmt = $conn->prepare($sqlDisyorkan);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$disyorkan = $stmt->get_result();


$html = "
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; }
  .header { text-align: center; font-weight: bold; font-size: 18px; }
  .section-title { font-weight: bold; margin-top: 15px; }
  .info-table td { padding: 2px 5px; vertical-align: top; }
  .service-table, .service-table th, .service-table td {
    border: 1px solid black; border-collapse: collapse;
  }
  .service-table th, .service-table td {
    padding: 6px; text-align: center;
  }
  .total-row td {
    font-weight: bold;
  }
</style>

<div style='text-align:center; margin-bottom: 20px;'>
  <img src='$logoPath' style='height:60px; image-rendering: auto;'><br>
  <strong style='font-size:18px;'>INVOIS PEMBAYARAN</strong>
</div>

<hr />

<table width='100%' class='info-table'>
  <tr>
    <td width='50%'>
      <div class='section-title'>Maklumat Pelanggan</div>
      Nama: {$data['nama_p']}<br>
      Emel: {$data['emel_p']}<br>
      Telefon: {$data['telefon_p']}<br><br>
      <div class='section-title'>Maklumat Kenderaan</div>
      No Plate: {$data['plate_kereta']}<br>
      Jenama: {$data['jenama_kereta']}<br>
      Model: {$data['model_kereta']}<br>
      Tahun: {$data['tahun_kereta']}<br>
    </td>
    <td width='50%'>
      <div class='section-title'>Maklumat Bengkel</div>
      Bengkel: {$data['nama_bengkel']}<br>
      No Invois: {$data['pembayaranID']}<br>
      Tarikh: " . date("d/m/Y") . "<br>
    </td>
  </tr>
</table>

<br>
<table width='100%' class='service-table'>
  <thead>
    <tr>
      <th>No</th>
      <th>Servis ID</th>
      <th>Nama Servis</th>
      <th>Harga (RM)</th>
      <th>Jumlah (RM)</th>
    </tr>
  </thead>
  <tbody>";

  $no = 1;

  // SERVIS DIPILIH
  while ($row = $servis->fetch_assoc()) {
    $html .= "
      <tr>
        <td>{$no}</td>
        <td>{$row['servisID']}</td>
        <td>{$row['nama_servis']} </span></td>
        <td>" . number_format($row['harga'], 2) . "</td>
        <td>" . number_format($row['harga'], 2) . "</td>
      </tr>";
    $no++;
  }
  
  // SERVIS DISYORKAN
  while ($row = $disyorkan->fetch_assoc()) {
    $html .= "
      <tr>
        <td>{$no}</td>
        <td>{$row['servisID']}</td>
        <td>{$row['nama_servis']} </span></td>
        <td>" . number_format($row['harga_servis'], 2) . "</td>
        <td>" . number_format($row['harga_servis'], 2) . "</td>
      </tr>";
    $no++;
  }
  

$html .= "
    <tr class='total-row'>
      <td colspan='4' style='text-align:right;'>Total (RM)</td>
      <td>" . number_format($data['jumlah_harga'], 2) . "</td>
    </tr>
  </tbody>
</table>

<br><br>
<p style='font-size: 10px;'>Nota: Barang yang dijual tidak boleh dipulangkan. Terima kasih kerana menggunakan SERVIS-X.</p>
<p style='text-align:right;'>Tandatangan:</p>
";

$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true); // 🔥 Required for image paths
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Invois_{$tempahanID}.pdf", ["Attachment" => false]);

