<?php
session_start();
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}
$conn = new mysqli("localhost", "root", "", "servisx");
$bengkelID = $_SESSION['bengkelID'];

// Date filter
$range = $_GET['range'] ?? 'all';
$where = "WHERE t.bengkelID = '$bengkelID'";
if ($range == 'weekly') {
    $where .= " AND t.tarikh >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($range == 'monthly') {
    $where .= " AND MONTH(t.tarikh) = MONTH(CURDATE()) AND YEAR(t.tarikh) = YEAR(CURDATE())";
} elseif ($range == 'custom' && $_GET['start'] && $_GET['end']) {
    $start = $conn->real_escape_string($_GET['start']);
    $end = $conn->real_escape_string($_GET['end']);
    $where .= " AND t.tarikh BETWEEN '$start' AND '$end'";
}

// Chart 1: Booking status
$status_sql = "SELECT t.status, COUNT(*) AS jumlah FROM tempahan t $where GROUP BY t.status";
$status_data = $conn->query($status_sql);

// Chart 2: Revenue
$revenue_sql = "SELECT p.tarikh_pembayaran AS tarikh, SUM(p.jumlah_harga) AS total
    FROM pembayaran p
    JOIN tempahan t ON p.tempahanID = t.tempahanID
    WHERE t.bengkelID = '$bengkelID'
    GROUP BY p.tarikh_pembayaran
    ORDER BY p.tarikh_pembayaran";
$revenue_data = $conn->query($revenue_sql);

// Chart 3: Services
$service_sql = "SELECT s.nama_servis, COUNT(ts.servisID) AS total 
    FROM tempahan_servis ts 
    JOIN servis s ON s.servisID = ts.servisID 
    JOIN tempahan t ON t.tempahanID = ts.tempahanID 
    $where GROUP BY s.nama_servis";
$service_data = $conn->query($service_sql);

// Chart 4: Ratings
$rating_sql = "SELECT AVG(u.rating) as avg_rating FROM ulasan u 
    JOIN tempahan t ON u.tempahanID = t.tempahanID 
    WHERE t.bengkelID = '$bengkelID'";
$rating = $conn->query($rating_sql)->fetch_assoc()['avg_rating'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bengkel - SERVIS-X</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
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
      background-color: #17a2b8;
      color: white;
    }
    .badge-batal {
      background-color: #e74c3c;
      color: white;
    }

    /* New Styles */
    .main-content {
      margin-left: 250px;
      padding: 20px;
      background-color: #f8f9fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .report-header {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      width: 100%;
      max-width: 1200px;
      text-align: center;
    }

    .filter-section {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      width: 100%;
      max-width: 1200px;
    }

    .chart-container {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      width: 100%;
    }

    .charts-row {
      width: 100%;
      max-width: 1200px;
    }

    .rating-display {
      background: linear-gradient(45deg, #2c3e50, #3498db);
      color: white;
      padding: 15px 25px;
      border-radius: 10px;
      display: inline-block;
      margin-bottom: 20px;
    }

    .rating-stars {
      color: #f1c40f;
      font-size: 1.2rem;
      margin-right: 10px;
    }

    .form-select, .form-control {
      border-radius: 8px;
      border: 1px solid #dee2e6;
      padding: 8px 12px;
    }

    .btn-primary {
      background-color: #3498db;
      border: none;
      padding: 8px 20px;
      border-radius: 8px;
    }

    .btn-success {
      background-color: #2ecc71;
      border: none;
      padding: 8px 20px;
      border-radius: 8px;
    }

    .btn:hover {
      transform: translateY(-2px);
      transition: all 0.3s ease;
    }
    </style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-4" style="width: 264px;">
    <a href="bengkel_dashboard.php">
      <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
    </a>
    <ul class="list-unstyled">
      <li><a href="bengkel_dashboard.php"><img src="images/AdminTask.png" style="width: 20px; margin-right: 10px;"> Mengurus Servis</a></li>
      <li><a href="maklumat_bengkel.php"><img src="images/AdminTask.png" style="width: 20px; margin-right: 10px;"> Maklumat Tempahan</a></li>
      <li><a href="report_bengkel.php"class="text-success"><img src="images/Admin Laporan.png" style="width: 20px; margin-right: 10px;"> Laporan</a></li>
      <li><a href="profil_bengkel.php"><img src="images/tatapan profile.png" style="width: 20px; margin-right: 10px;"> Tatapan Profil</a></li>
      <li><a href="logout.php"><img src="images/logout.png" style="width: 20px; margin-right: 10px;"> Log Keluar</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="report-header">
      <h3 class="mb-3">Laporan Bengkel</h3>
      <div class="rating-display">
        <span class="rating-stars">
          <?php
          $rating = round($rating, 1);
          $full_stars = floor($rating);
          $half_star = $rating - $full_stars >= 0.5;
          for ($i = 0; $i < 5; $i++) {
              if ($i < $full_stars) {
                  echo '<i class="bi bi-star-fill"></i>';
              } elseif ($i == $full_stars && $half_star) {
                  echo '<i class="bi bi-star-half"></i>';
              } else {
                  echo '<i class="bi bi-star"></i>';
              }
          }
          ?>
        </span>
        <span>Purata Rating: <?= number_format($rating, 1) ?> / 5</span>
      </div>
    </div>

    <div class="filter-section">
      <form method="GET" class="row g-3">
        <div class="col-md-4">
          <label for="range" class="form-label">Julat Tarikh:</label>
          <select name="range" class="form-select" onchange="toggleRange(this.value)">
            <option value="all" <?= $range == 'all' ? 'selected' : '' ?>>Semua</option>
            <option value="weekly" <?= $range == 'weekly' ? 'selected' : '' ?>>Minggu Ini</option>
            <option value="monthly" <?= $range == 'monthly' ? 'selected' : '' ?>>Bulan Ini</option>
            <option value="custom" <?= $range == 'custom' ? 'selected' : '' ?>>Pilih Tarikh</option>
          </select>
        </div>
        <div class="col-md-4" id="customStart" style="display:none;">
          <label class="form-label">Dari:</label>
          <input type="date" name="start" class="form-control" value="<?= $_GET['start'] ?? '' ?>">
        </div>
        <div class="col-md-4" id="customEnd" style="display:none;">
          <label class="form-label">Hingga:</label>
          <input type="date" name="end" class="form-control" value="<?= $_GET['end'] ?? '' ?>">
        </div>
        <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary">Tapis</button>
          <button type="button" onclick="exportExcel()" class="btn btn-success ms-2">
            <i class="bi bi-download"></i> Export Excel
          </button>
        </div>
      </form>
    </div>

    <div class="charts-row">
      <div class="row">
        <div class="col-md-6">
          <div class="chart-container">
            <h5 class="mb-3 text-center">Status Tempahan</h5>
            <canvas id="statusChart"></canvas>
          </div>
        </div>
        <div class="col-md-6">
          <div class="chart-container">
            <h5 class="mb-3 text-center">Trend Pendapatan</h5>
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
        <div class="col-md-6 mx-auto">
          <div class="chart-container">
            <h5 class="mb-3 text-center">Servis Paling Popular</h5>
            <canvas id="serviceChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleRange(val) {
    document.getElementById('customStart').style.display = val === 'custom' ? 'block' : 'none';
    document.getElementById('customEnd').style.display = val === 'custom' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    toggleRange("<?= $range ?>");
});

function exportExcel() {
    let wb = XLSX.utils.book_new();
    let ws_data = [
        ["Laporan Bengkel"],
        ["Purata Rating", "<?= round($rating, 2) ?>"],
    ];
    let ws = XLSX.utils.aoa_to_sheet(ws_data);
    wb.SheetNames.push("Laporan");
    wb.Sheets["Laporan"] = ws;
    XLSX.writeFile(wb, "LaporanBengkel.xlsx");
}

// Enhanced Charts
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column(mysqli_fetch_all($status_data, MYSQLI_ASSOC), 'status')) ?>,
            datasets: [{
                data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($status_sql), MYSQLI_ASSOC), 'jumlah')) ?>,
                backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c', '#3498db']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Revenue Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column(mysqli_fetch_all($revenue_data, MYSQLI_ASSOC), 'tarikh')) ?>,
            datasets: [{
                label: 'Pendapatan',
                data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($revenue_sql), MYSQLI_ASSOC), 'total')) ?>,
                borderColor: '#3498db',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Service Chart
    new Chart(document.getElementById('serviceChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column(mysqli_fetch_all($service_data, MYSQLI_ASSOC), 'nama_servis')) ?>,
            datasets: [{
                label: 'Servis Paling Popular',
                data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($service_sql), MYSQLI_ASSOC), 'total')) ?>,
                backgroundColor: '#2ecc71'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
</body>
</html>

<?php $conn->close(); ?>
