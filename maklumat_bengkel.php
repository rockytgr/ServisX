<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

$bengkelID = $_SESSION['bengkelID'];

$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch bookings from this bengkel
$sql = "SELECT 
            t.tempahanID, 
            t.tarikh_tempahan, 
            t.status,
            t.diterima_jenis,
            k.model_kereta
        FROM tempahan t
        JOIN kereta k ON t.keretaID = k.keretaID
        WHERE t.bengkelID = ?
        ORDER BY t.tarikh_tempahan DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <title>Maklumat Tempahan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .sidebar {
      background-color: #2c3e50;
      color: white;
      height: 100vh;
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
    .badge-menunggu {
      background-color: #f1c40f;
      color: black;
    }
    .badge-selesai {
      background-color: #2ecc71;
      color: white;
    }
    .badge-berjalan {
      background-color: #3d52d5;
      color: white;
    }
    .badge-menunggu-kelulusan {
  background-color: #17a2b8; /* Bootstrap 'info' color */
  color: white;
}
.badge-batal {
  background-color: #e74c3c; /* red */
  color: white;
}

  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-4" style="width: 300px;">
    <a href="bengkel_dashboard.php">
      <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
    </a>
    <ul class="list-unstyled">
      <li><a href="bengkel_dashboard.php"><img src="images/AdminTask.png" style="width: 20px; margin-right: 10px;"> Mengurus Servis</a></li>
      <li><a href="maklumat_bengkel.php" class="text-success"><img src="images/AdminTask.png" style="width: 20px; margin-right: 10px;"> Maklumat Tempahan</a></li>
      <li><a href="report_bengkel.php"><img src="images/Admin Laporan.png" style="width: 20px; margin-right: 10px;"> Laporan</a></li>
      <li><a href="profil_bengkel.php"><img src="images/tatapan profile.png" style="width: 20px; margin-right: 10px;"> Tatapan Profil</a></li>
      <li><a href="logout.php"><img src="images/logout.png" style="width: 20px; margin-right: 10px;"> Log Keluar</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="container-fluid p-4">
    <h3 class="mb-4">Maklumat Tempahan</h3>

    <!-- Filter -->
    <div class="d-flex mb-4 gap-2">
      <input type="text" id="searchID" class="form-control" placeholder="Search" style="max-width: 300px;">
      <select id="statusFilter" class="form-select" style="max-width: 200px;">
        <option value="">Status</option>
        <option value="Menunggu">Menunggu</option>
        <option value="Menunggu Kelulusan">Menunggu Kelulusan</option>
        <option value="Selesai">Selesai</option>
        <option value="Sedang Berjalan">Sedang Berjalan</option>
      </select>
    </div>

    <!-- Table -->
    <table class="table table-bordered">
      <thead class="table-light text-center">
        <tr>
          <th>No</th>
          <th>Tempahan ID</th>
          <th>Tarikh Mohon</th>
          <th>Model Kereta</th>
          <th>Status</th>
          <th>Status Diterima</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tempahanTable">
        <?php
        $no = 1;
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
          <td class="text-center"><?= $no++ ?></td>
          <td><?= $row['tempahanID'] ?></td>
          <td><?= date("d-m-Y", strtotime($row['tarikh_tempahan'])) ?></td>
          <td><?= $row['model_kereta'] ?></td>
          <td class="text-center">
            <?php
              $status = $row['status'];
              switch ($status) {
                case 'Selesai':
                  echo '<span class="badge badge-selesai">Selesai</span>';
                  break;
                case 'Menunggu':
                  echo '<span class="badge badge-menunggu">Menunggu</span>';
                  break;
                case 'Menunggu Kelulusan':
                  echo '<span class="badge badge-menunggu-kelulusan">Menunggu Kelulusan</span>';
                  break;
                case 'Batal':
                  echo '<span class="badge badge-batal">Batal</span>';
                  break;
                default:
                  echo '<span class="badge badge-berjalan">Sedang Berjalan</span>';
              }
                           
            ?>
          </td>
          <td class="text-center">
  <?php
    $jenis = $row['diterima_jenis'];
    if ($jenis == 'semua') {
        echo '<span class="badge bg-success">Semua</span>';
    } elseif ($jenis == 'pelanggan') {
        echo '<span class="badge bg-primary">Pelanggan</span>';
    } else {
        echo '<span class="badge bg-warning text-dark">Belum Diterima</span>';
    }
  ?>
</td>

          <td class="text-center">
          <?php if ($status != 'Batal'): ?>
  <a href="kemaskini_tempahan.php?tempahanID=<?= $row['tempahanID'] ?>" class="btn btn-outline-success btn-sm">Kemaskini</a>
<?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('searchID').addEventListener('input', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
  const searchVal = document.getElementById('searchID').value.toLowerCase();
  const statusVal = document.getElementById('statusFilter').value.toLowerCase();
  const rows = document.querySelectorAll('#tempahanTable tr');

  rows.forEach(row => {
    const cells = row.querySelectorAll("td");
    let matchSearch = false;
    let matchStatus = true;

    // Loop through each cell (column) to check for any matching content
    cells.forEach((cell, index) => {
      if (cell.textContent.toLowerCase().includes(searchVal)) {
        matchSearch = true;
      }

      // Check status column (assuming it's column index 4)
      if (index === 4 && statusVal !== "") {
        matchStatus = cell.textContent.toLowerCase().includes(statusVal);
      }
    });

    // Show or hide the row based on match
    row.style.display = (matchSearch && matchStatus) ? "" : "none";
  });
}


</script>

</body>
</html>

<?php $conn->close(); ?>
=======
<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

$bengkelID = $_SESSION['bengkelID'];

$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch bookings from this bengkel
$sql = "SELECT 
            t.tempahanID, 
            t.tarikh_tempahan, 
            t.status,
            t.diterima_jenis,
            k.model_kereta
        FROM tempahan t
        JOIN kereta k ON t.keretaID = k.keretaID
        WHERE t.bengkelID = ?
        ORDER BY t.tarikh_tempahan DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <title>Maklumat Tempahan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .sidebar {
      background-color: #2c3e50;
      color: white;
      height: 100vh;
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
    .badge-menunggu {
      background-color: #f1c40f;
      color: black;
    }
    .badge-selesai {
      background-color: #2ecc71;
      color: white;
    }
    .badge-berjalan {
      background-color: #3d52d5;
      color: white;
    }
    .badge-menunggu-kelulusan {
  background-color: #17a2b8; /* Bootstrap 'info' color */
  color: white;
}
.badge-batal {
  background-color: #e74c3c; /* red */
  color: white;
}

  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-4" style="width: 300px;">
    <a href="bengkel_dashboard.php">
      <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
    </a>
    <ul class="list-unstyled">
      <li><a href="bengkel_dashboard.php"><img src="images/AdminTask.png" style="width: 20px; margin-right: 10px;"> Mengurus Servis</a></li>
      <li><a href="maklumat_bengkel.php" class="text-success"><img src="images/AdminTask.png" style="width: 20px; margin-right: 10px;"> Maklumat Tempahan</a></li>
      <li><a href="report_bengkel.php"><img src="images/Admin Laporan.png" style="width: 20px; margin-right: 10px;"> Laporan</a></li>
      <li><a href="profil_bengkel.php"><img src="images/tatapan profile.png" style="width: 20px; margin-right: 10px;"> Tatapan Profil</a></li>
      <li><a href="logout.php"><img src="images/logout.png" style="width: 20px; margin-right: 10px;"> Log Keluar</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="container-fluid p-4">
    <h3 class="mb-4">Maklumat Tempahan</h3>

    <!-- Filter -->
    <div class="d-flex mb-4 gap-2">
      <input type="text" id="searchID" class="form-control" placeholder="Search" style="max-width: 300px;">
      <select id="statusFilter" class="form-select" style="max-width: 200px;">
        <option value="">Status</option>
        <option value="Menunggu">Menunggu</option>
        <option value="Menunggu Kelulusan">Menunggu Kelulusan</option>
        <option value="Selesai">Selesai</option>
        <option value="Sedang Berjalan">Sedang Berjalan</option>
      </select>
    </div>

    <!-- Table -->
    <table class="table table-bordered">
      <thead class="table-light text-center">
        <tr>
          <th>No</th>
          <th>Tempahan ID</th>
          <th>Tarikh Mohon</th>
          <th>Model Kereta</th>
          <th>Status</th>
          <th>Status Diterima</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tempahanTable">
        <?php
        $no = 1;
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
          <td class="text-center"><?= $no++ ?></td>
          <td><?= $row['tempahanID'] ?></td>
          <td><?= date("d-m-Y", strtotime($row['tarikh_tempahan'])) ?></td>
          <td><?= $row['model_kereta'] ?></td>
          <td class="text-center">
            <?php
              $status = $row['status'];
              switch ($status) {
                case 'Selesai':
                  echo '<span class="badge badge-selesai">Selesai</span>';
                  break;
                case 'Menunggu':
                  echo '<span class="badge badge-menunggu">Menunggu</span>';
                  break;
                case 'Menunggu Kelulusan':
                  echo '<span class="badge badge-menunggu-kelulusan">Menunggu Kelulusan</span>';
                  break;
                case 'Batal':
                  echo '<span class="badge badge-batal">Batal</span>';
                  break;
                default:
                  echo '<span class="badge badge-berjalan">Sedang Berjalan</span>';
              }
                           
            ?>
          </td>
          <td class="text-center">
  <?php
    $jenis = $row['diterima_jenis'];
    if ($jenis == 'semua') {
        echo '<span class="badge bg-success">Semua</span>';
    } elseif ($jenis == 'pelanggan') {
        echo '<span class="badge bg-primary">Pelanggan</span>';
    } else {
        echo '<span class="badge bg-warning text-dark">Belum Diterima</span>';
    }
  ?>
</td>

          <td class="text-center">
          <?php if ($status != 'Batal'): ?>
  <a href="kemaskini_tempahan.php?tempahanID=<?= $row['tempahanID'] ?>" class="btn btn-outline-success btn-sm">Kemaskini</a>
<?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('searchID').addEventListener('input', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
  const searchVal = document.getElementById('searchID').value.toLowerCase();
  const statusVal = document.getElementById('statusFilter').value.toLowerCase();
  const rows = document.querySelectorAll('#tempahanTable tr');

  rows.forEach(row => {
    const cells = row.querySelectorAll("td");
    let matchSearch = false;
    let matchStatus = true;

    // Loop through each cell (column) to check for any matching content
    cells.forEach((cell, index) => {
      if (cell.textContent.toLowerCase().includes(searchVal)) {
        matchSearch = true;
      }

      // Check status column (assuming it's column index 4)
      if (index === 4 && statusVal !== "") {
        matchStatus = cell.textContent.toLowerCase().includes(statusVal);
      }
    });

    // Show or hide the row based on match
    row.style.display = (matchSearch && matchStatus) ? "" : "none";
  });
}


</script>

</body>
</html>

<?php $conn->close(); ?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
