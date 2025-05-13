<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

$bengkelID = $_SESSION['bengkelID'];

if (!isset($_GET['tempahanID'])) {
    echo "Tempahan ID tidak diberikan!";
    exit();
}

$tempahanID = $_GET['tempahanID'];

// DB connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get tempahan details (from your code)
$sql = "SELECT t.*, p.nama_p, p.telefon_p, k.plate_kereta, k.model_kereta, k.jenama_kereta, k.tahun_kereta, b.nama_bengkel 
        FROM tempahan t
        JOIN pelanggan p ON t.pelangganID = p.pelangganID
        JOIN kereta k ON t.keretaID = k.keretaID
        JOIN bengkel b ON t.bengkelID = b.bengkelID
        WHERE t.tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$result = $stmt->get_result();
$tempahan = $result->fetch_assoc();

// Now fetch all servis names for this tempahan
$servis_nama_list = [];
$sql_servis = "SELECT s.nama_servis 
               FROM tempahan_servis ts
               JOIN servis s ON ts.servisID = s.servisID
               WHERE ts.tempahanID = ?";
$stmt_servis = $conn->prepare($sql_servis);
$stmt_servis->bind_param("s", $tempahanID);
$stmt_servis->execute();
$result_servis = $stmt_servis->get_result();

while ($row = $result_servis->fetch_assoc()) {
    $servis_nama_list[] = $row['nama_servis'];
}

$selected_servis_names = implode(", ", $servis_nama_list);


// Get pelanggan servis
$sql = "SELECT ts.servisID, s.nama_servis, ts.harga 
        FROM tempahan_servis ts
        JOIN servis s ON ts.servisID = s.servisID
        WHERE ts.tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$pelanggan_servis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Get disyorkan servis
$sql = "SELECT * FROM disyorkan_servis WHERE tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$disyorkan_servis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!-- Modal Tambah Disyorkan Servis -->
<div class="modal fade" id="modalTambahServis" tabindex="-1" aria-labelledby="modalTambahServisLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="tambah_servis_disyorkan.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahServisLabel">Tambah Disyorkan Servis</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="tempahanID" value="<?= $tempahanID ?>">
          <input type="hidden" name="servisID" value="<?= $newServisID ?>">
          
          <div class="mb-3">
            <label for="nama_servis" class="form-label">Nama Servis</label>
            <input type="text" class="form-control" name="nama_servis" id="nama_servis" required>
          </div>
          <div class="mb-3">
            <label for="harga_servis" class="form-label">Harga</label>
            <input type="number" class="form-control" name="harga_servis" id="harga_servis" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Tambah</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php foreach ($pelanggan_servis as $servis): ?>
<div class="modal fade" id="editPelangganModal<?= $servis['servisID'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="update_servis_pelanggan.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Servis Pelanggan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="tempahanID" value="<?= $tempahanID ?>">
        <input type="hidden" name="servisID" value="<?= $servis['servisID'] ?>">
        <div class="mb-3">
          <label class="form-label">Harga Servis</label>
          <input type="number" name="harga" class="form-control" value="<?= $servis['harga'] ?>" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>


<?php foreach ($disyorkan_servis as $servis): ?>
    <div class="modal fade" id="editDisyorkan<?= $servis['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $servis['id'] ?>" aria-hidden="true">
  <div class="modal-dialog">
    <form action="update_servis_disyorkan.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel<?= $servis['id'] ?>">Edit Disyorkan Servis</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" value="<?= $servis['id'] ?>">
        <input type="hidden" name="tempahanID" value="<?= $tempahanID ?>">

        <div class="mb-3">
          <label for="nama_servis" class="form-label">Nama Servis</label>
          <input type="text" class="form-control" name="nama_servis" value="<?= $servis['nama_servis'] ?>" required>
        </div>
        <div class="mb-3">
          <label for="harga_servis" class="form-label">Harga</label>
          <input type="number" step="0.01" class="form-control" name="harga_servis" value="<?= $servis['harga_servis'] ?>" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<!-- Load Bootstrap JS first -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
<!-- Modal Booking Updated -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">Berjaya</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-0">Tempahan telah berjaya dikemaskini!</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>






<script>
  // Auto show modal if URL has ?success=1
  var successModal = new bootstrap.Modal(document.getElementById('successModal'));
  window.addEventListener('load', () => {
    successModal.show();
  });
</script>
<?php endif; ?>


<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kemaskini Tempahan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
  <style>
    .info-box {
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
    }
    .section-title {
      font-weight: bold;
      margin-top: 30px;
    }
    .sidebar {
      background-color: #2c3e50;
      color: white;
      padding-top: 20px;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      padding: 10px;
      display: block;
    }
    .sidebar a:hover {
      background-color: #34495e;
    }
    .print-card {
  font-family: 'Arial', sans-serif;
  border: 2px solid #000;
  padding: 30px;
  background: #fff;
}
@media print {
  body * {
    visibility: hidden;
  }
  #printArea, #printArea * {
    visibility: visible;
  }
  #printArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
  </style>
</head>
<body>



<!-- Sidebar -->
<div class="d-flex">
    <div class="sidebar p-4" style="width: 261px;">
        <a href="bengkel_dashboard.php">
            <!-- Logo for SERVIS-X -->
            <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
        </a>
        <ul class="list-unstyled">
    <li><a href="bengkel_dashboard.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Mengurus Servis
    </a></li>
    <li><a href="maklumat_bengkel.php" class="text-success">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Maklumat Tempahan
    </a></li>
    <li><a href="report_bengkel.php">
        <img src="images/Admin Laporan.png" alt="icon" style="width: 20px; margin-right: 10px;"> Laporan
    </a></li>
    <li><a href="profil_bengkel.php">
        <img src="images/tatapan profile.png" alt="icon" style="width: 20px; margin-right: 10px;"> Tatapan Profil
    </a></li>
    <li><a href="logout.php">
        <img src="images/logout.png" alt="icon" style="width: 20px; margin-right: 10px;"> Log Keluar
    </a></li>
</ul>
    </div>


<div class="container py-4">
  <h4 class="fw-bold">Kemaskini Tempahan</h4>

  <!-- Info Box -->
  <div id="butiranTempahan" class="print-card p-4" style="background:#fff;">
  <div class="text-center mb-4">
    <img src="images/logo.svg" alt="SERVIS-X Logo" style="width: 120px; margin-bottom: 10px;">
    <h4 class="fw-bold text-uppercase mt-3">BUTIRAN TEMPAHAN SERVIS</h4>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <p><strong>Nama Pelanggan:</strong> <?= $tempahan['nama_p'] ?></p>
      <p><strong>No Telefon:</strong> <?= $tempahan['telefon_p'] ?></p>
      <p><strong>No Plate Kereta:</strong> <?= $tempahan['plate_kereta'] ?></p>
      <p><strong>Model:</strong> <?= $tempahan['model_kereta'] ?></p>
      <p><strong>Jenama:</strong> <?= $tempahan['jenama_kereta'] ?></p>
      <p><strong>Tahun:</strong> <?= $tempahan['tahun_kereta'] ?></p>
    </div>
    <div class="col-md-6">
      <p><strong>Tarikh Servis:</strong> <?= $tempahan['tarikh'] ?></p>
      <p><strong>Masa:</strong> <?= $tempahan['masa'] ?></p>
      <p><strong>Jenis Servis:</strong> <?= $selected_servis_names ?></p>
      <p><strong>Bengkel:</strong> <?= $tempahan['nama_bengkel'] ?></p>
      <p><strong>Komen:</strong> <?= $tempahan['komen'] ?: '-' ?></p>
    </div>
  </div>
</div>
<br>

<p><strong>Jenis Servis Diterima:</strong> 
  <?= $tempahan['diterima_jenis'] == 'pelanggan' ? 'Servis Pelanggan Sahaja' : ($tempahan['diterima_jenis'] == 'semua' ? 'Servis Pelanggan + Disyorkan' : 'Belum Diterima') ?>
</p>


  <div class="d-flex gap-3 mb-4">
  <button class="btn btn-outline-primary" onclick="printButiranTempahan()">
    🖨️ Cetak Butiran Tempahan
  </button>
  <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#printRecommendationForm">
    📝 Borang Cadangan Mekanik
  </button>
</div>


  <!-- Servis Pelanggan -->
  <h5 class="section-title">Servis Pelanggan</h5>
  <table class="table table-bordered">
    <thead>
      <tr><th>Servis ID</th><th>Nama Servis</th><th>Harga</th><th>Aksi</th></tr>
    </thead>
    <tbody>
      <?php foreach ($pelanggan_servis as $servis): ?>
        <tr>
          <td><?= $servis['servisID'] ?></td>
          <td><?= $servis['nama_servis'] ?></td>
          <td class="harga-servis">RM<?= number_format($servis['harga'], 2) ?></td>
          <td>
  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPelangganModal<?= $servis['servisID'] ?>">Edit</button>
  <a href="delete_servis_pelanggan.php?tempahanID=<?= $tempahanID ?>&servisID=<?= $servis['servisID'] ?>" class="btn btn-danger btn-sm">Delete</a>
</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<!-- Disyorkan Servis -->
<h5 class="section-title">Disyorkan Servis</h5>
<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahServis">
  + Tambah Disyorkan Servis
</button>

<table class="table table-bordered">
  <thead>
    <tr>
      <!-- Hidden Servis ID column -->
      <th style="display:none;">Servis ID</th>
      <th>Nama Servis</th>
      <th>Harga</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($disyorkan_servis as $servis): ?>
      <tr>
        <!-- Hidden Servis ID cell -->
        <td style="display:none;"><?= $servis['servisID'] ?></td>
        <td><?= $servis['nama_servis'] ?></td>
        <td class="harga-servis">RM<?= number_format($servis['harga_servis'], 2) ?></td>
<td>
  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editDisyorkan<?= $servis['id'] ?>">Edit</button>
  <a href="delete_servis_disyorkan.php?id=<?= $servis['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
</td>


      </tr>
    <?php endforeach; ?>
  </tbody>
</table>


<!-- Add Disyorkan Servis -->
<?php
// Auto-generate servisID (DS01, DS02, etc.)
$result = $conn->query("SELECT MAX(servisID) AS lastID FROM disyorkan_servis WHERE servisID LIKE 'DS%'");
$row = $result->fetch_assoc();
$lastID = $row['lastID'];

$nextNumber = 1;
if ($lastID) {
    $num = (int)substr($lastID, 2); // Remove "DS"
    $nextNumber = $num + 1;
}
$newServisID = 'DS' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
?>



  <!-- Final Update -->
  <form action="update_tempahan.php" method="POST" class="mt-4">
    <input type="hidden" name="tempahanID" value="<?= $tempahanID ?>">
    <div class="mb-3">
      <label for="jumlah_harga">Jumlah Harga</label>
      <input type="number" class="form-control" name="jumlah_harga" id="jumlah_harga" value="<?= $tempahan['jumlah_harga'] ?>" required>
      <br>
      <button type="button" class="btn btn-success mb-3" onclick="kiraJumlahServis()">Kira Jumlah Servis</button>

    </div>
    <div class="mb-3">
      <label for="status">Status Tempahan</label>
      <select class="form-select" name="status" required>
        <option value="Menunggu Kelulusan" <?= $tempahan['status'] == 'Menunggu Kelulusan' ? 'selected' : '' ?>>Menunggu Kelulusan</option>
        <option value="Sedang Berjalan" <?= $tempahan['status'] == 'Sedang Berjalan' ? 'selected' : '' ?>>Sedang Berjalan</option>
        <option value="Selesai" <?= $tempahan['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
      </select>
    </div>
    <div class="d-flex gap-3">
      <a href="maklumat_bengkel.php" class="btn btn-secondary">Kembali</a>
      <button type="submit" class="btn btn-primary">Kemaskini</button>
    </div>
  </form>
</div>

<!-- Modal for Borang Cadangan Mekanik -->
<div class="modal fade" id="printRecommendationForm" tabindex="-1" aria-labelledby="printRecommendationFormLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    
      <div class="modal-header">
        <h5 class="modal-title">Borang Cadangan Mekanik</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      
      <div class="modal-body">
        <!-- ✅ PRINTABLE CONTENT STARTS HERE -->
        <div id="printArea" class="print-card p-4">
        <div class="text-center mb-4">
  <img src="images/logo.svg" alt="SERVIS-X Logo" style="width: 120px; margin-bottom: 10px;">
  <h4 class="fw-bold text-uppercase mt-3">BORANG CADANGAN SERVIS TAMBAHAN</h4>
  <p><strong>Nama Bengkel:</strong> <?= $tempahan['nama_bengkel'] ?></p>
  <p><strong>Tarikh:</strong> <?= $tempahan['tarikh'] ?></p>
</div>


          <hr>

          <div class="mb-3">
            <p><strong>Nama Pelanggan:</strong> <?= $tempahan['nama_p'] ?></p>
            <p><strong>No Telefon:</strong> <?= $tempahan['telefon_p'] ?></p>
            <p><strong>No Plate:</strong> <?= $tempahan['plate_kereta'] ?></p>
            <p><strong>Model:</strong> <?= $tempahan['model_kereta'] ?> (<?= $tempahan['jenama_kereta'] ?>)</p>
            <p><strong>Tahun:</strong> <?= $tempahan['tahun_kereta'] ?></p>
          </div>

          <div class="mb-3">
            <p><strong>Servis Yang Telah Dipilih:</strong></p>
            <ul>
              <?php foreach ($servis_nama_list as $s): ?>
                <li><?= $s ?></li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="mb-4">
            <p><strong>Cadangan Servis Tambahan:</strong></p>
            <div style="border:1px solid #ccc; height:100px; padding:10px;"></div>
          </div>

          <div class="row mt-5">
            <div class="col-md-6">
              <p><strong>Tandatangan Mekanik:</strong></p>
              <div style="border-bottom: 1px solid #000; width: 100%; height: 40px;"></div>
            </div>
            <div class="col-md-6">
              <p><strong>Nama Mekanik:</strong></p>
              <div style="border-bottom: 1px solid #000; width: 100%; height: 40px;"></div>
            </div>
          </div>
        </div>
        <!-- ✅ PRINTABLE CONTENT ENDS HERE -->
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="printForm()">Cetak Borang Ini</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
      
    </div>
  </div>
</div>





<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function kiraJumlahServis() {
  let total = 0;
  const hargaSel = document.querySelectorAll('.harga-servis');

  hargaSel.forEach(cell => {
    const text = cell.textContent.replace('RM', '').replace(',', '').trim();
    const num = parseFloat(text);
    if (!isNaN(num)) {
      total += num;
    }
  });

  document.getElementById('jumlah_harga').value = total.toFixed(2);
}
</script>



<script>
function printForm() {
  const printContent = document.getElementById('printArea');
  if (!printContent) {
    alert("Print area not found!");
    return;
  }

  const win = window.open('', '', 'height=800,width=1000');
  win.document.write('<html><head><title>Borang Cadangan Servis</title>');
  win.document.write('<style>');
  win.document.write('body{font-family:Arial;padding:40px;} .print-card{border:2px solid #000;padding:30px;} ul{padding-left: 20px;}');
  win.document.write('</style>');
  win.document.write('</head><body>');
  win.document.write(printContent.innerHTML);
  win.document.write('</body></html>');
  win.document.close();
  win.focus();
  win.print();
}
</script>


<script>
function printButiranTempahan() {
  const content = document.getElementById("butiranTempahan").innerHTML;
  const win = window.open('', '', 'height=800,width=1000');
  win.document.write('<html><head><title>Cetak Butiran Tempahan</title>');
  win.document.write('<style>body{font-family:Arial;padding:40px;} .print-card{border:2px solid #000;padding:30px;} p{margin:5px 0;} .row{display:flex;} .col-md-6{width:50%;padding:10px;box-sizing:border-box;}</style>');
  win.document.write('</head><body>');
  win.document.write(content);
  win.document.write('</body></html>');
  win.document.close();
  win.focus();
  win.print();
}
</script>



</body>
</html>
