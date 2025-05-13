<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['pelangganID'])) {
    // Redirect to login page if not logged in
    header("Location: login.html");
    exit();
}

// Get pelangganID from session
$pelangganID = $_SESSION['pelangganID'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil tempahan pelanggan
$sql = "SELECT t.tempahanID, t.tarikh_tempahan, t.status, 
               k.model_kereta, 
               GROUP_CONCAT(s.nama_servis SEPARATOR ', ') AS jenis_servis,
               EXISTS(SELECT 1 FROM pembayaran p WHERE p.tempahanID = t.tempahanID) AS is_paid
        FROM tempahan t
        JOIN kereta k ON t.keretaID = k.keretaID
        LEFT JOIN tempahan_servis ts ON t.tempahanID = ts.tempahanID
        LEFT JOIN servis s ON ts.servisID = s.servisID
        WHERE t.pelangganID = ?
        GROUP BY t.tempahanID";


$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pelangganID);
$stmt->execute();
$result = $stmt->get_result();

$tempahanList = [];
while ($row = $result->fetch_assoc()) {
    $tempahanList[] = $row;
}

// Fetch foto_profil from pelanggan table
$profile_img = "images/profile-icon.png"; // default fallback image
$sql = "SELECT foto_profil FROM pelanggan WHERE pelangganID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pelangganID);
$stmt->execute();
$stmt->bind_result($foto_profil);
if ($stmt->fetch() && !empty($foto_profil) && file_exists($foto_profil)) {
    $profile_img = $foto_profil;
}
$stmt->close();
?>


<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Maklumat Servis - SERVIS-X</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"/>
  <style>
    /* Footer Sticky Styles */
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }

    main {
      flex: 1 0 auto;
      padding-bottom: 2rem;
    }

    footer {
      flex-shrink: 0;
      margin-top: auto;
    }

    /* Modern Animation Styles */
    .service-card {
      transition: all 0.3s ease;
      border: none;
      border-radius: 15px;
      background: #fff;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      overflow: hidden;
    }
    .service-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .service-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(45deg, #28a745, #198754);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      color: white;
      font-size: 1.5rem;
      transition: all 0.3s ease;
    }
    .service-icon:hover {
      transform: scale(1.1) rotate(360deg);
    }
    
    .service-title {
      font-weight: 600;
      color: #333;
      transition: color 0.3s ease;
    }
    .service-card:hover .service-title {
      color: #28a745;
    }
    
    .service-description {
      color: #6c757d;
      transition: color 0.3s ease;
    }
    
    .service-price {
      font-weight: 600;
      color: #28a745;
      transition: transform 0.3s ease;
    }
    .service-card:hover .service-price {
      transform: scale(1.05);
    }
    
    .booking-btn {
      transition: all 0.3s ease;
      background: linear-gradient(45deg, #28a745, #198754);
      border: none;
      position: relative;
      overflow: hidden;
    }
    .booking-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    .booking-btn::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
      transform: translateX(-100%);
      transition: transform 0.5s ease;
    }
    .booking-btn:hover::after {
      transform: translateX(100%);
    }
    
    .nav-link {
      position: relative;
      color: #333;
      transition: color 0.3s ease;
      padding: 0.5rem 0;
    }
    .nav-link::before,
    .nav-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      background: linear-gradient(90deg, #28a745, #198754);
      transition: width 0.3s ease;
    }
    .nav-link::before {
      left: 50%;
    }
    .nav-link::after {
      right: 50%;
    }
    .nav-link:hover {
      color: #28a745;
    }
    .nav-link:hover::before,
    .nav-link:hover::after {
      width: 50%;
    }
    .nav-link.active {
      color: #28a745;
    }
    .nav-link.active::before,
    .nav-link.active::after {
      width: 50%;
    }

    /* Service Categories */
    .category-tabs {
      border-bottom: 2px solid #e9ecef;
      margin-bottom: 2rem;
    }
    .category-tab {
      padding: 1rem 2rem;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
    }
    .category-tab.active {
      color: #28a745;
      font-weight: 600;
    }
    .category-tab::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, #28a745, #198754);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }
    .category-tab.active::after {
      transform: scaleX(1);
    }
    .category-tab:hover::after {
      transform: scaleX(1);
    }

    /* Service Details Modal */
    .service-modal {
      border-radius: 15px;
      overflow: hidden;
    }
    .service-modal-header {
      background: linear-gradient(45deg, #28a745, #198754);
      color: white;
      border: none;
    }
    .service-modal-body {
      padding: 2rem;
    }
    .service-modal-footer {
      border-top: none;
      background: #f8f9fa;
    }
  </style>
</head>
<body>

  <!-- Navbar for Logged-in User -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3">
  <div class="container">
    <!-- Logo Image -->
    <a class="navbar-brand" href="indexin.php">
      <img src="images/Logo.svg" alt="Logo" style="height: 35px;" />
    </a>

    <!-- Mobile Menu Button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Items -->
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav me-3">
        <li class="nav-item"><a class="nav-link" href="indexin.php">Utama</a></li>
        <li class="nav-item"><a class="nav-link" href="tentangkamin.php">Tentang Kami</a></li>
         <!-- Servis Link -->
         <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle active" href="servisin.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Servis
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="maklumat_servis.php">Maklumat Servis</a></li>
            <!-- Add more items if needed -->
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="hubungin.php">Hubungi Kami</a></li>
      </ul>

<!-- Profile Dropdown -->
<div class="d-flex gap-2">
  <div class="dropdown">
    <button class="btn btn-light dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <?php
      // Fallback image if profile picture not set or missing
      $profile_img = !empty($foto_profil) && file_exists($foto_profil) ? $foto_profil : "images/profile-icon.png";
      ?>
      <img src="<?php echo $profile_img; ?>" alt="Profile" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover; border: 2px solid black;">
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
          <li>
  <a class="dropdown-item" href="profil_pelanggan.php">
    <img src="images/tp.png" alt="Profil" style="width: 18px; margin-right: 8px;">
    Tatapan Profil
  </a>
</li>
<li>
  <a class="dropdown-item" href="maklumat_servis.php">
    <img src="images/ms.png" alt="Servis" style="width: 18px; margin-right: 8px;">
    Maklumat Servis
  </a>
</li>
<li>
  <a class="dropdown-item" href="logout.php">
    <img src="images/lo.png" alt="Logout" style="width: 18px; margin-right: 8px;">
    Logout
  </a>
</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</nav>

<main>
<div class="container py-4">
<div class="table-responsive">
  <table class="table table-bordered text-center" style="width: 100%;">

<h3 class="mb-4">Maklumat Tempahan</h3>
  <!-- Search & Filter -->
<div class="d-flex mb-3 gap-3">
  <input type="text" class="form-control" id="searchInput" placeholder="Cari Tempahan...">
  <select class="form-select" id="statusFilter">
    <option value="">Status</option>
    <option value="Menunggu">Menunggu</option>
    <option value="Menunggu Kelulusan">Menunggu Kelulusan</option>
    <option value="Sedang Berjalan">Sedang Berjalan</option>
    <option value="Selesai">Selesai</option>
  </select>
</div>

<!-- Tempahan Table -->
<table class="table table-bordered">
<thead class="table-light text-center">
    <tr>
      <th>No</th>
      <th style="white-space: nowrap;">Tempahan ID</th>
<th style="white-space: nowrap;">Tarikh Mohon</th>
<th style="white-space: nowrap;">Jenis Servis</th>
<th style="white-space: nowrap;">Model Kereta</th>
<th style="white-space: nowrap;">Status</th>
<th style="white-space: nowrap;">Aksi</th>

    </tr>
  </thead>
  <tbody>
    <?php
    $no = 1;
    foreach ($tempahanList as $tempahan) {
      echo "<tr>";
      echo "<td>{$no}</td>";
      echo "<td>{$tempahan['tempahanID']}</td>";
      echo "<td>{$tempahan['tarikh_tempahan']}</td>";
      echo "<td>{$tempahan['jenis_servis']}</td>";
      echo "<td>{$tempahan['model_kereta']}</td>";
      echo "<td><span class='badge bg-".getStatusColor($tempahan['status'])."'>{$tempahan['status']}</span></td>";
      echo "<td>";
      echo "<a href='butiran_tempahan.php?id={$tempahan['tempahanID']}' class='btn btn-outline-success btn-sm'>Butiran</a> ";

      if ($tempahan['status'] === "Selesai") {
        echo "<a href='ulasan.php?id={$tempahan['tempahanID']}' class='btn btn-outline-warning btn-sm'>Ulasan</a> ";
    
        if ($tempahan['is_paid']) {
            echo "<a href='invoice.php?id={$tempahan['tempahanID']}' class='btn btn-outline-primary btn-sm'>Invois</a>";
        } else {
            echo "<a href='bayar.php?id={$tempahan['tempahanID']}' class='btn btn-outline-success btn-sm'>Bayar</a>";
        }
    
      } elseif ($tempahan['status'] === "Menunggu") {
        echo "<button class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#modalBatalkan{$tempahan['tempahanID']}'>Batalkan Tempahan</button>";
      } elseif ($tempahan['status'] === "Menunggu Kelulusan") {
        echo "<a href='terimapage.php?id={$tempahan['tempahanID']}' class='btn btn-outline-success btn-sm'>Terima</a>";
      }

      echo "</td>";
      echo "</tr>";
      $no++;
    }

    function getStatusColor($status) {
      return match($status) {
        "Selesai" => "success",
        "Menunggu" => "warning",
        "Sedang Berjalan" => "primary",
        "Menunggu Kelulusan" => "info",
        "Batal" => "danger", // 
        default => "secondary",
    };
    
    }
   
    ?>

  </tbody>
</table>
</div>
</div>
</main>

<!-- Modal for Batalkan Tempahan -->
<div class="modal fade" id="modalBatalkan<?= $tempahan['tempahanID'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $tempahan['tempahanID'] ?>" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="batalkan_tempahan.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabel<?= $tempahan['tempahanID'] ?>">Sahkan Pembatalan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          Anda pasti ingin membatalkan tempahan <strong><?= $tempahan['tempahanID'] ?></strong>?
        </div>
        <div class="modal-footer">
          <input type="hidden" name="tempahanID" value="<?= $tempahan['tempahanID'] ?>">
          <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
        </div>
      </div>
    </form>
  </div>
</div>




<!-- Footer -->
<footer id="hubungi" class="bg-dark text-white pt-5">
    <div class="container">
      <div class="row g-4">
        <!-- Left: Heading & Icons -->
        <div class="col-md-6 mb-4 mb-md-0">
          <div class="footer-content">
            <h3 class="fw-bold mb-4">Hubungi kami untuk<br>servis anda.</h3>
            <div class="social-icons d-flex gap-3">
              <a href="#" class="social-icon">
              <img src="images/twitter.png" alt="Twitter" class="social-icon-img">
              </a>
              <a href="#" class="social-icon">
              <img src="images/instagram.png" alt="Instagram" class="social-icon-img">
              </a>
              <a href="#" class="social-icon">
              <img src="images/linkedin.png" alt="Linkedin" class="social-icon-img">
              </a>
            </div>
          </div>
        </div>

        <!-- Right: Contact Details -->
        <div class="col-md-6">
          <div class="contact-details">
            <h6 class="fw-bold mb-3">Hubungi Kami</h6>
            <div class="contact-item mb-3">
            <img src="images/phone.png" alt="Phone" class="contact-icon">
              <span class="fw-bold">017-7024127</span>
            </div>
            <div class="contact-item mb-3">
            <img src="images/gps.png" alt="Location" class="contact-icon">
              <span class="fw-bold">Servis-X, Malaysia</span><br />
            </div>
            <div class="contact-item">
            <img src="images/time.png" alt="Clock" class="contact-icon">
              <span class="fw-bold">8:00 Pagi - 5:00 Petang</span><br />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom bg-black mt-5 py-4">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-4 mb-3 mb-md-0">
            <a href="indexin.php" class="brand-logo">
              <span class="fw-bold">SERVIS-X</span>
            </a>
          </div>
          <div class="col-md-4 mb-3 mb-md-0">
            <ul class="footer-links list-inline mb-0 text-center">
              <li class="list-inline-item"><a href="indexin.php" class="text-white text-decoration-none">Utama</a></li>
              <li class="list-inline-item"><a href="tentangkamin.php" class="text-white text-decoration-none">Tentang Kami</a></li>
              <li class="list-inline-item"><a href="servisin.php" class="text-white text-decoration-none">Servis</a></li>
              <li class="list-inline-item"><a href="hubungin.php" class="text-white text-decoration-none">Hubungi Kami</a></li>
            </ul>
          </div>
          <div class="col-md-4 text-md-end">
            <div class="copyright">&copy; SERVIS-X 2025</div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <script src="scripts.js"></script>
  
<!-- JS Script for Search & Filter -->
<script>
  const searchInput = document.getElementById("searchInput");
  const statusFilter = document.getElementById("statusFilter");
  const tableRows = document.querySelectorAll("tbody tr");

  function filterTable() {
    const search = searchInput.value.toLowerCase();
    const status = statusFilter.value.toLowerCase();

    tableRows.forEach(row => {
      const text = row.textContent.toLowerCase();
      const matchSearch = text.includes(search);
      const matchStatus = !status || text.includes(status);

      row.style.display = (matchSearch && matchStatus) ? "" : "none";
    });
  }

  searchInput.addEventListener("input", filterTable);
  statusFilter.addEventListener("change", filterTable);
</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
  <script>
    // Initialize AOS
    AOS.init({
      duration: 1000,
      once: true
    });

    // Service category filtering
    document.addEventListener('DOMContentLoaded', function() {
      const categoryTabs = document.querySelectorAll('.category-tab');
      const serviceCards = document.querySelectorAll('.service-card').parentElement;

      categoryTabs.forEach(tab => {
        tab.addEventListener('click', function() {
          // Remove active class from all tabs
          categoryTabs.forEach(t => t.classList.remove('active'));
          // Add active class to clicked tab
          this.classList.add('active');

          const category = this.dataset.category;
          
          // Show/hide service cards based on category
          document.querySelectorAll('.service-card').parentElement.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
              card.style.display = 'block';
              card.classList.add('animate__fadeInUp');
            } else {
              card.style.display = 'none';
            }
          });
        });
      });
    });
  </script>
  <style>
    /* Footer Styles */
    footer {
      background: linear-gradient(180deg, #1a1a1a 0%, #000000 100%);
      position: relative;
      overflow: hidden;
    }

    footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(26, 188, 156, 0.5), transparent);
    }

    .footer-content h3 {
      font-size: 2.2rem;
      line-height: 1.2;
      background: linear-gradient(45deg, #ffffff, #e0e0e0);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 1.5rem;
      position: relative;
      display: inline-block;
    }

    .footer-content h3::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 50px;
      height: 3px;
      background: linear-gradient(90deg, #1abc9c, transparent);
      border-radius: 2px;
    }

    .social-icons {
      margin-top: 2rem;
    }

    .social-icon {
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      color: white;
      transition: all 0.3s ease;
      text-decoration: none;
      position: relative;
      overflow: hidden;
      padding: 0;
    }

    .social-icon-img {
      width: 24px;
      height: 24px;
      object-fit: contain;
      transition: all 0.3s ease;
      filter: brightness(1) invert(0);
    }

    .social-icon:hover .social-icon-img {
      transform: scale(1.2);
      filter: brightness(1) invert(0);
    }

    .contact-icon {
      width: 24px;
      height: 24px;
      margin-right: 10px;
      object-fit: contain;
      transition: all 0.3s ease;
      filter: brightness(0) invert(1);
    }

    .contact-item:hover .contact-icon {
      transform: scale(1.1);
    }

    .contact-details {
      padding: 2rem;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 15px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .contact-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 1.2rem;
      padding: 0.5rem;
      border-radius: 8px;
      transition: all 0.3s ease;
      
    }

    .contact-item:hover {
      background: rgba(255, 255, 255, 0.05);
      transform: translateX(5px);
    }

    .footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      position: relative;
    }

    .footer-bottom::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(26, 188, 156, 0.3), transparent);
    }

    .brand-logo {
      color: white;
      text-decoration: none;
      font-size: 1.8rem;
      transition: all 0.3s ease;
      display: inline-block;
      position: relative;
    }

    .brand-logo::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #1abc9c, #16a085);
      transition: width 0.3s ease;
    }

    .brand-logo:hover::after {
      width: 100%;
    }

    .footer-links a {
      position: relative;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
      font-weight: 500;
    }

    .footer-links a::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 2px;
      background: linear-gradient(45deg, #1abc9c, #16a085);
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    .footer-links a:hover::before {
      width: 80%;
    }

    .copyright {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
      font-weight: 500;
    }

    @media (max-width: 768px) {
      .footer-content h3 {
        font-size: 1.8rem;
      }

      .social-icon {
        width: 40px;
        height: 40px;
      }

      .contact-details {
        padding: 1.5rem;
      }

      .footer-links {
        margin: 1rem 0;
      }

      .footer-links a {
        padding: 0.5rem;
        font-size: 0.9rem;
      }
    }
  </style>
</body>
</html>
