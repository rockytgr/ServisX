<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: login.html");
    exit();
}
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Filters
$where = "WHERE 1";
$today = date('Y-m-d');
$start = $end = '';

if (!empty($_GET['bengkel'])) {
    $bID = $conn->real_escape_string($_GET['bengkel']);
    $where .= " AND t.bengkelID = '$bID'";
}

// Handle time range
$range = $_GET['range'] ?? '';
switch ($range) {
    case 'weekly':
        $start = date('Y-m-d', strtotime('-7 days'));
        $end = $today;
        break;
    case 'monthly':
        $start = date('Y-m-01');
        $end = $today;
        break;
    case 'custom':
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        break;
}
if ($start && $end) {
    $where .= " AND t.tarikh BETWEEN '$start' AND '$end'";
}

// Data queries
$status_sql = "SELECT t.status, COUNT(*) AS jumlah FROM tempahan t $where GROUP BY t.status";
$revenue_sql = "SELECT p.tarikh_pembayaran AS tarikh, SUM(p.jumlah_harga) AS total
    FROM pembayaran p
    JOIN tempahan t ON p.tempahanID = t.tempahanID
    GROUP BY p.tarikh_pembayaran
    ORDER BY p.tarikh_pembayaran";
$service_sql = "SELECT s.nama_servis, COUNT(ts.servisID) AS total FROM tempahan_servis ts JOIN servis s ON s.servisID = ts.servisID JOIN tempahan t ON t.tempahanID = ts.tempahanID $where GROUP BY s.nama_servis";
$rating_sql = "SELECT t.bengkelID, AVG(u.rating) AS avg_rating FROM ulasan u JOIN tempahan t ON u.tempahanID = t.tempahanID GROUP BY t.bengkelID";

$status_data = $conn->query($status_sql);
$revenue_data = $conn->query($revenue_sql);
$service_data = $conn->query($service_sql);
$rating_data = $conn->query($rating_sql);

$customers = $conn->query("SELECT COUNT(*) as total FROM pelanggan")->fetch_assoc()['total'];
$bengkels = $conn->query("SELECT COUNT(*) as total FROM bengkel")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Laporan Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure the path is correct -->
    <style>
        .main-content {
            padding: 32px 18px;
            background-color: #f4f6fb;
            min-height: 100vh;
        }
        .stat-card {
            background: linear-gradient(135deg, #e0f7fa 0%, #f8fafc 100%);
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(52,152,219,0.07);
            padding: 28px 18px 18px 18px;
            margin-bottom: 24px;
            text-align: center;
            transition: box-shadow 0.2s, transform 0.2s;
            border: 1px solid #e3e9f7;
        }
        .stat-card:hover {
            box-shadow: 0 6px 24px rgba(52,152,219,0.13);
            transform: translateY(-2px) scale(1.02);
        }
        .stat-title {
            font-size: 1.08rem;
            color: #6c757d;
            margin-bottom: 7px;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #198754;
            letter-spacing: 1px;
        }
        .filter-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 22px 24px 12px 24px;
            margin-bottom: 28px;
            border: 1px solid #e3e9f7;
        }
        .filter-section label {
            font-weight: 500;
            color: #198754;
        }
        .chart-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 24px 18px 18px 18px;
            margin-bottom: 28px;
            border: 1px solid #e3e9f7;
        }
        .chart-container h5 {
            color: #198754;
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e3e9f7;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #f2f6fa;
        }
        .table-hover tbody tr:hover {
            background-color: #eaf3fb;
        }
        .table th {
            color: #198754;
            font-weight: 600;
            background: #e0f7fa;
            border-bottom: 2px solid #b2dfdb;
        }
        .btn-primary, .btn-success {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .btn-primary {
            background-color: #198754;
            border: none;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-primary:hover, .btn-success:hover {
            filter: brightness(0.95);
            font-size: 2.1rem;
            font-weight: bold;
            color: #3498db;
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
        .admin-box {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 200px;
            margin-left: auto;
        }

        .admin-box button {
            border: none;
            background-color: transparent;
            cursor: pointer;
            color: #007bff;
            font-size: 14px;
        }
        /* Modal Styles */
        .modal-body {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar p-4" style="width: 310px;">
        <a href="admin.php">
            <!-- Logo for SERVIS-X -->
            <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
        </a>
        <ul class="list-unstyled">
    <li><a href="admin.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Mengurus Bengkel
    </a></li>
    <li><a href="pengesahan_bengkel.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Pengesahan Bengkel
    </a></li>
    <li><a href="report_admin.php" class="text-success">
        <img src="images/Admin Laporan.png" alt="icon" style="width: 20px; margin-right: 10px;"> Laporan
    </a></li>
    <li><a href="logout.php">
        <img src="images/logout.png" alt="icon" style="width: 20px; margin-right: 10px;"> Log Keluar
    </a></li>
</ul>
    </div>
    <div class="main-content w-100">
        <h2 class="mb-4 fw-bold">Laporan Pentadbir</h2>
        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-title">Jumlah Pelanggan</div>
                    <div class="stat-value"><?php echo $customers; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-title">Jumlah Bengkel</div>
                    <div class="stat-value"><?php echo $bengkels; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-title">Jumlah Tempahan</div>
                    <div class="stat-value"><?php echo $conn->query("SELECT COUNT(*) as total FROM tempahan")->fetch_assoc()['total']; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-title">Jumlah Pendapatan</div>
                    <div class="stat-value">RM <?php echo number_format($conn->query("SELECT SUM(jumlah_harga) as total FROM pembayaran")->fetch_assoc()['total'] ?? 0,2); ?></div>
                </div>
            </div>
        </div>
        <!-- Filter Section -->
        <div class="filter-section mb-4">
            <form class="row g-3 align-items-end" method="GET">
                <div class="col-md-4">
                    <label>Bengkel:</label>
                    <select name="bengkel" class="form-select">
                        <option value="">Semua</option>
                        <?php
                        $b = $conn->query("SELECT bengkelID, nama_bengkel FROM bengkel");
                        while ($r = $b->fetch_assoc()) {
                            $sel = ($_GET['bengkel'] ?? '') == $r['bengkelID'] ? 'selected' : '';
                            echo "<option value='{$r['bengkelID']}' $sel>{$r['nama_bengkel']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Julat Tarikh:</label>
                    <select name="range" class="form-select" onchange="toggleCustomRange(this.value)">
                        <option value="">Semua</option>
                        <option value="weekly" <?= ($range == 'weekly') ? 'selected' : '' ?>>Mingguan</option>
                        <option value="monthly" <?= ($range == 'monthly') ? 'selected' : '' ?>>Bulanan</option>
                        <option value="custom" <?= ($range == 'custom') ? 'selected' : '' ?>>Pilih Tarikh</option>
                    </select>
                </div>
                <div class="col-md-2" id="customStart" style="display: none;">
                    <label>Dari:</label>
                    <input type="date" name="start" class="form-control" value="<?= $start ?>">
                </div>
                <div class="col-md-2" id="customEnd" style="display: none;">
                    <label>Hingga:</label>
                    <input type="date" name="end" class="form-control" value="<?= $end ?>">
                </div>
                <div class="col-md-12 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Tapis</button>
                    <button type="button" onclick="exportExcel()" class="btn btn-success ms-2"><i class="bi bi-download"></i> Export Excel</button>
                </div>
            </form>
        </div>
        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">Status Tempahan</h5>
                    <canvas id="bookingStatusChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">Trend Pendapatan</h5>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">Servis Paling Popular</h5>
                    <canvas id="serviceChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">Purata Rating Bengkel</h5>
                    <canvas id="ratingChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Table Section -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama Bengkel</th>
                        <th>Jumlah Tempahan</th>
                        <th>Pendapatan</th>
                        <th>Purata Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $workshop_stats = $conn->query("
                        SELECT 
                            b.nama_bengkel, 
                            COUNT(t.tempahanID) AS jumlah_tempahan, 
                            SUM(p.jumlah_harga) AS pendapatan, 
                            AVG(u.rating) AS purata_rating 
                        FROM bengkel b 
                        LEFT JOIN tempahan t ON b.bengkelID = t.bengkelID 
                        LEFT JOIN pembayaran p ON t.tempahanID = p.tempahanID
                        LEFT JOIN ulasan u ON t.tempahanID = u.tempahanID 
                        GROUP BY b.bengkelID
                    ");
                    while($row = $workshop_stats->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['nama_bengkel']) ?></td>
                        <td><?= $row['jumlah_tempahan'] ?></td>
                        <td>RM <?= number_format($row['pendapatan'],2) ?></td>
                        <td><?= number_format($row['purata_rating'],1) ?> / 5</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportExcel() {
    let wb = XLSX.utils.table_to_book(document.getElementById('chartArea'), {sheet:"Laporan"});
    XLSX.writeFile(wb, 'LaporanAdmin.xlsx');
}
function toggleCustomRange(val) {
    document.getElementById('customStart').style.display = val === 'custom' ? 'block' : 'none';
    document.getElementById('customEnd').style.display = val === 'custom' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', () => toggleCustomRange("<?= $range ?>"));
</script>

<script>
// Excel Export
function exportExcel() {
    let wb = XLSX.utils.book_new();
    wb.SheetNames.push("Laporan");
    let ws_data = [
        ["Statistik"],
        ["Jumlah Pelanggan", <?= $customers ?>],
        ["Jumlah Bengkel", <?= $bengkels ?>],
        [],
        ["NOTA: Carta tidak boleh dieksport sebagai grafik ke Excel secara terus"]
    ];
    let ws = XLSX.utils.aoa_to_sheet(ws_data);
    wb.Sheets["Laporan"] = ws;
    XLSX.writeFile(wb, "LaporanAdmin.xlsx");
}

// Show/hide custom range fields
function toggleCustomRange(val) {
    document.getElementById('customStart').style.display = val === 'custom' ? 'block' : 'none';
    document.getElementById('customEnd').style.display = val === 'custom' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', () => toggleCustomRange("<?= $range ?>"));
</script>

<script>
// CHART 1: Booking Status
new Chart(document.getElementById('bookingStatusChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($conn->query($status_sql), MYSQLI_ASSOC), 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($status_sql), MYSQLI_ASSOC), 'jumlah')) ?>,
            backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0']
        }]
    }
});

// CHART 2: Revenue
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($conn->query($revenue_sql), MYSQLI_ASSOC), 'tarikh')) ?>,
        datasets: [{
            label: 'Pendapatan',
            data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($revenue_sql), MYSQLI_ASSOC), 'total')) ?>,
            backgroundColor: '#4BC0C0'
        }]
    }
});

// CHART 3: Top Services
new Chart(document.getElementById('serviceChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($conn->query($service_sql), MYSQLI_ASSOC), 'nama_servis')) ?>,
        datasets: [{
            label: 'Jumlah Servis',
            data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($service_sql), MYSQLI_ASSOC), 'total')) ?>,
            backgroundColor: '#36A2EB'
        }]
    }
});

// CHART 4: Average Ratings
new Chart(document.getElementById('ratingChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($conn->query($rating_sql), MYSQLI_ASSOC), 'bengkelID')) ?>,
        datasets: [{
            label: 'Purata Rating',
            data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($rating_sql), MYSQLI_ASSOC), 'avg_rating')) ?>,
            backgroundColor: '#FF9F40'
        }]
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>
=======
<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: login.html");
    exit();
}
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Filters
$where = "WHERE 1";
$today = date('Y-m-d');
$start = $end = '';

if (!empty($_GET['bengkel'])) {
    $bID = $conn->real_escape_string($_GET['bengkel']);
    $where .= " AND t.bengkelID = '$bID'";
}

// Handle time range
$range = $_GET['range'] ?? '';
switch ($range) {
    case 'weekly':
        $start = date('Y-m-d', strtotime('-7 days'));
        $end = $today;
        break;
    case 'monthly':
        $start = date('Y-m-01');
        $end = $today;
        break;
    case 'custom':
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        break;
}
if ($start && $end) {
    $where .= " AND t.tarikh BETWEEN '$start' AND '$end'";
}

// Data queries
$status_sql = "SELECT t.status, COUNT(*) AS jumlah FROM tempahan t $where GROUP BY t.status";
$revenue_sql = "SELECT p.tarikh_pembayaran AS tarikh, SUM(p.jumlah_harga) AS total
    FROM pembayaran p
    JOIN tempahan t ON p.tempahanID = t.tempahanID
    GROUP BY p.tarikh_pembayaran
    ORDER BY p.tarikh_pembayaran";
$service_sql = "SELECT s.nama_servis, COUNT(ts.servisID) AS total FROM tempahan_servis ts JOIN servis s ON s.servisID = ts.servisID JOIN tempahan t ON t.tempahanID = ts.tempahanID $where GROUP BY s.nama_servis";
$rating_sql = "SELECT t.bengkelID, AVG(u.rating) AS avg_rating FROM ulasan u JOIN tempahan t ON u.tempahanID = t.tempahanID GROUP BY t.bengkelID";

$status_data = $conn->query($status_sql);
$revenue_data = $conn->query($revenue_sql);
$service_data = $conn->query($service_sql);
$rating_data = $conn->query($rating_sql);

$customers = $conn->query("SELECT COUNT(*) as total FROM pelanggan")->fetch_assoc()['total'];
$bengkels = $conn->query("SELECT COUNT(*) as total FROM bengkel")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Laporan Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure the path is correct -->
    <style>
        .main-content {
            padding: 32px 18px;
            background-color: #f4f6fb;
            min-height: 100vh;
        }
        .stat-card {
            background: linear-gradient(135deg, #e0f7fa 0%, #f8fafc 100%);
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(52,152,219,0.07);
            padding: 28px 18px 18px 18px;
            margin-bottom: 24px;
            text-align: center;
            transition: box-shadow 0.2s, transform 0.2s;
            border: 1px solid #e3e9f7;
        }
        .stat-card:hover {
            box-shadow: 0 6px 24px rgba(52,152,219,0.13);
            transform: translateY(-2px) scale(1.02);
        }
        .stat-title {
            font-size: 1.08rem;
            color: #6c757d;
            margin-bottom: 7px;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #198754;
            letter-spacing: 1px;
        }
        .filter-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 22px 24px 12px 24px;
            margin-bottom: 28px;
            border: 1px solid #e3e9f7;
        }
        .filter-section label {
            font-weight: 500;
            color: #198754;
        }
        .chart-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 24px 18px 18px 18px;
            margin-bottom: 28px;
            border: 1px solid #e3e9f7;
        }
        .chart-container h5 {
            color: #198754;
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e3e9f7;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #f2f6fa;
        }
        .table-hover tbody tr:hover {
            background-color: #eaf3fb;
        }
        .table th {
            color: #198754;
            font-weight: 600;
            background: #e0f7fa;
            border-bottom: 2px solid #b2dfdb;
        }
        .btn-primary, .btn-success {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .btn-primary {
            background-color: #198754;
            border: none;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-primary:hover, .btn-success:hover {
            filter: brightness(0.95);
            font-size: 2.1rem;
            font-weight: bold;
            color: #3498db;
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
        .admin-box {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 200px;
            margin-left: auto;
        }

        .admin-box button {
            border: none;
            background-color: transparent;
            cursor: pointer;
            color: #007bff;
            font-size: 14px;
        }
        /* Modal Styles */
        .modal-body {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar p-4" style="width: 310px;">
        <a href="admin.php">
            <!-- Logo for SERVIS-X -->
            <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
        </a>
        <ul class="list-unstyled">
    <li><a href="admin.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Mengurus Bengkel
    </a></li>
    <li><a href="pengesahan_bengkel.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Pengesahan Bengkel
    </a></li>
    <li><a href="report_admin.php" class="text-success">
        <img src="images/Admin Laporan.png" alt="icon" style="width: 20px; margin-right: 10px;"> Laporan
    </a></li>
    <li><a href="logout.php">
        <img src="images/logout.png" alt="icon" style="width: 20px; margin-right: 10px;"> Log Keluar
    </a></li>
</ul>
    </div>
    <div class="main-content w-100">
        <h2 class="mb-4 fw-bold">Laporan Pentadbir</h2>
        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-title">Jumlah Pelanggan</div>
                    <div class="stat-value"><?php echo $customers; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-title">Jumlah Bengkel</div>
                    <div class="stat-value"><?php echo $bengkels; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-title">Jumlah Tempahan</div>
                    <div class="stat-value"><?php echo $conn->query("SELECT COUNT(*) as total FROM tempahan")->fetch_assoc()['total']; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-title">Jumlah Pendapatan</div>
                    <div class="stat-value">RM <?php echo number_format($conn->query("SELECT SUM(jumlah_harga) as total FROM pembayaran")->fetch_assoc()['total'] ?? 0,2); ?></div>
                </div>
            </div>
        </div>
        <!-- Filter Section -->
        <div class="filter-section mb-4">
            <form class="row g-3 align-items-end" method="GET">
                <div class="col-md-4">
                    <label>Bengkel:</label>
                    <select name="bengkel" class="form-select">
                        <option value="">Semua</option>
                        <?php
                        $b = $conn->query("SELECT bengkelID, nama_bengkel FROM bengkel");
                        while ($r = $b->fetch_assoc()) {
                            $sel = ($_GET['bengkel'] ?? '') == $r['bengkelID'] ? 'selected' : '';
                            echo "<option value='{$r['bengkelID']}' $sel>{$r['nama_bengkel']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Julat Tarikh:</label>
                    <select name="range" class="form-select" onchange="toggleCustomRange(this.value)">
                        <option value="">Semua</option>
                        <option value="weekly" <?= ($range == 'weekly') ? 'selected' : '' ?>>Mingguan</option>
                        <option value="monthly" <?= ($range == 'monthly') ? 'selected' : '' ?>>Bulanan</option>
                        <option value="custom" <?= ($range == 'custom') ? 'selected' : '' ?>>Pilih Tarikh</option>
                    </select>
                </div>
                <div class="col-md-2" id="customStart" style="display: none;">
                    <label>Dari:</label>
                    <input type="date" name="start" class="form-control" value="<?= $start ?>">
                </div>
                <div class="col-md-2" id="customEnd" style="display: none;">
                    <label>Hingga:</label>
                    <input type="date" name="end" class="form-control" value="<?= $end ?>">
                </div>
                <div class="col-md-12 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Tapis</button>
                    <button type="button" onclick="exportExcel()" class="btn btn-success ms-2"><i class="bi bi-download"></i> Export Excel</button>
                </div>
            </form>
        </div>
        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">Status Tempahan</h5>
                    <canvas id="bookingStatusChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">Trend Pendapatan</h5>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">Servis Paling Popular</h5>
                    <canvas id="serviceChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">Purata Rating Bengkel</h5>
                    <canvas id="ratingChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Table Section -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama Bengkel</th>
                        <th>Jumlah Tempahan</th>
                        <th>Pendapatan</th>
                        <th>Purata Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $workshop_stats = $conn->query("
                        SELECT 
                            b.nama_bengkel, 
                            COUNT(t.tempahanID) AS jumlah_tempahan, 
                            SUM(p.jumlah_harga) AS pendapatan, 
                            AVG(u.rating) AS purata_rating 
                        FROM bengkel b 
                        LEFT JOIN tempahan t ON b.bengkelID = t.bengkelID 
                        LEFT JOIN pembayaran p ON t.tempahanID = p.tempahanID
                        LEFT JOIN ulasan u ON t.tempahanID = u.tempahanID 
                        GROUP BY b.bengkelID
                    ");
                    while($row = $workshop_stats->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['nama_bengkel']) ?></td>
                        <td><?= $row['jumlah_tempahan'] ?></td>
                        <td>RM <?= number_format($row['pendapatan'],2) ?></td>
                        <td><?= number_format($row['purata_rating'],1) ?> / 5</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportExcel() {
    let wb = XLSX.utils.table_to_book(document.getElementById('chartArea'), {sheet:"Laporan"});
    XLSX.writeFile(wb, 'LaporanAdmin.xlsx');
}
function toggleCustomRange(val) {
    document.getElementById('customStart').style.display = val === 'custom' ? 'block' : 'none';
    document.getElementById('customEnd').style.display = val === 'custom' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', () => toggleCustomRange("<?= $range ?>"));
</script>

<script>
// Excel Export
function exportExcel() {
    let wb = XLSX.utils.book_new();
    wb.SheetNames.push("Laporan");
    let ws_data = [
        ["Statistik"],
        ["Jumlah Pelanggan", <?= $customers ?>],
        ["Jumlah Bengkel", <?= $bengkels ?>],
        [],
        ["NOTA: Carta tidak boleh dieksport sebagai grafik ke Excel secara terus"]
    ];
    let ws = XLSX.utils.aoa_to_sheet(ws_data);
    wb.Sheets["Laporan"] = ws;
    XLSX.writeFile(wb, "LaporanAdmin.xlsx");
}

// Show/hide custom range fields
function toggleCustomRange(val) {
    document.getElementById('customStart').style.display = val === 'custom' ? 'block' : 'none';
    document.getElementById('customEnd').style.display = val === 'custom' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', () => toggleCustomRange("<?= $range ?>"));
</script>

<script>
// CHART 1: Booking Status
new Chart(document.getElementById('bookingStatusChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($conn->query($status_sql), MYSQLI_ASSOC), 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($status_sql), MYSQLI_ASSOC), 'jumlah')) ?>,
            backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0']
        }]
    }
});

// CHART 2: Revenue
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($conn->query($revenue_sql), MYSQLI_ASSOC), 'tarikh')) ?>,
        datasets: [{
            label: 'Pendapatan',
            data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($revenue_sql), MYSQLI_ASSOC), 'total')) ?>,
            backgroundColor: '#4BC0C0'
        }]
    }
});

// CHART 3: Top Services
new Chart(document.getElementById('serviceChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($conn->query($service_sql), MYSQLI_ASSOC), 'nama_servis')) ?>,
        datasets: [{
            label: 'Jumlah Servis',
            data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($service_sql), MYSQLI_ASSOC), 'total')) ?>,
            backgroundColor: '#36A2EB'
        }]
    }
});

// CHART 4: Average Ratings
new Chart(document.getElementById('ratingChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($conn->query($rating_sql), MYSQLI_ASSOC), 'bengkelID')) ?>,
        datasets: [{
            label: 'Purata Rating',
            data: <?= json_encode(array_column(mysqli_fetch_all($conn->query($rating_sql), MYSQLI_ASSOC), 'avg_rating')) ?>,
            backgroundColor: '#FF9F40'
        }]
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
