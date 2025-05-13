<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Tempahan ID tidak diberikan.";
    exit();
}

$pelangganID = $_SESSION['pelangganID'];
$tempahanID = $_GET['id'];

$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get tempahan details
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

// Get selected servis
$selected_servis = [];
$sql = "SELECT s.servisID, s.nama_servis, ts.harga
        FROM tempahan_servis ts
        JOIN servis s ON ts.servisID = s.servisID
        WHERE ts.tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $selected_servis[] = $row;
}

// Fetch Jenis Servis (concatenated list)
$jenis_servis = "-";
$sql = "SELECT s.nama_servis 
        FROM tempahan_servis ts
        JOIN servis s ON ts.servisID = s.servisID
        WHERE ts.tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$result = $stmt->get_result();

$servis_names = [];
while ($row = $result->fetch_assoc()) {
    $servis_names[] = $row['nama_servis'];
}
if (!empty($servis_names)) {
    $jenis_servis = implode(", ", $servis_names);
}


// Get disyorkan servis
$disyorkan_servis = [];
$sql = "SELECT * FROM disyorkan_servis WHERE tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $disyorkan_servis[] = $row;
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

?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8" />
  <title>Butiran Tempahan</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .container {
      max-width: 1000px;
    }

    .booking-details {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 30px;
      margin-bottom: 30px;
      transition: transform 0.3s ease;
    }

    .booking-details:hover {
      transform: translateY(-5px);
    }

    .booking-details h4 {
      color: #2c3e50;
      font-weight: 700;
      margin-bottom: 25px;
      position: relative;
      padding-bottom: 10px;
    }

    .booking-details h4::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, #1abc9c, #16a085);
      border-radius: 3px;
    }

    .booking-details h5 {
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .booking-details p {
      color: #495057;
      font-size: 15px;
      line-height: 1.6;
      margin-bottom: 15px;
    }

    .booking-details strong {
      color: #2c3e50;
      font-weight: 600;
    }

    .table {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .table thead th {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      font-weight: 600;
      border: none;
      padding: 15px;
    }

    .table tbody td {
      padding: 15px;
      vertical-align: middle;
      border-bottom: 1px solid #f1f1f1;
    }

    .table tbody tr:last-child td {
      border-bottom: none;
    }

    .btn-success {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      font-weight: 600;
      padding: 10px 25px;
      border-radius: 10px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(26, 188, 156, 0.3);
      color: white;
    }

    .btn-secondary {
      background: linear-gradient(45deg, #6c757d, #495057);
      color: white;
      font-weight: 600;
      padding: 10px 25px;
      border-radius: 10px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(73, 80, 87, 0.3);
      color: white;
    }

    .form-control {
      border-radius: 10px;
      border: 2px solid #e9ecef;
      padding: 10px 15px;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: #1abc9c;
      box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
    }

    @media (max-width: 768px) {
      .booking-details {
        padding: 20px;
      }
      
      .table thead th,
      .table tbody td {
        padding: 10px;
      }
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
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="servisin.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Servis
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="maklumat_servis.php">Maklumat Servis</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="hubungin.php">Hubungi Kami</a></li>
        </ul>

        <!-- Profile Dropdown -->
        <div class="d-flex gap-2">
          <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <?php
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

  <div class="container py-5">
    <div class="booking-details">
      <h4 class="fw-bold">Butiran Tempahan</h4>
      <div class="mb-4">
        <strong>Tempahan ID:</strong>
        <input type="text" class="form-control w-25" value="<?= $tempahanID ?>" readonly>
      </div>
      <div class="p-4 border rounded bg-white">
        <h5 class="fw-bold">Butiran Tempahan</h5>
        <div class="row">
          <div class="col-md-6">
            <p><strong>Nama:</strong> <?= $tempahan['nama_p'] ?></p>
            <p><strong>Nombor Telefon:</strong> <?= $tempahan['telefon_p'] ?></p>
            <p><strong>No Plate Kereta:</strong> <?= $tempahan['plate_kereta'] ?></p>
            <p><strong>Model Kereta:</strong> <?= $tempahan['model_kereta'] ?></p>
            <p><strong>Brand Kereta:</strong> <?= $tempahan['jenama_kereta'] ?></p>
            <p><strong>Tahun Kereta:</strong> <?= $tempahan['tahun_kereta'] ?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Tarikh:</strong> <?= $tempahan['tarikh'] ?></p>
            <p><strong>Jenis Servis:</strong> <?= $jenis_servis ?></p>
            <p><strong>Bengkel:</strong> <?= $tempahan['nama_bengkel'] ?></p>
            <p><strong>Komen:</strong> <?= $tempahan['komen'] ?: '-' ?></p>
          </div>
        </div>
      </div>

      <h5 class="mt-4">Servis Pelanggan (<?= count($selected_servis) ?>)</h5>
      <table class="table">
        <thead><tr><th>Id</th><th>Servis ID</th><th>Nama Servis</th><th>Harga</th></tr></thead>
        <tbody>
          <?php foreach ($selected_servis as $i => $s): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= $s['servisID'] ?></td>
            <td><?= $s['nama_servis'] ?></td>
            <td>RM<?= number_format($s['harga'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h5 class="mt-4">Disyorkan Servis (<?= count($disyorkan_servis) ?>)</h5>
      <table class="table">
        <thead><tr><th>Id</th><th>Servis ID</th><th>Nama Servis</th><th>Harga</th></tr></thead>
        <tbody>
          <?php foreach ($disyorkan_servis as $i => $s): ?>
          <tr>
            <td><?= $i + 1 + count($selected_servis) ?></td>
            <td><?= $s['servisID'] ?></td>
            <td><?= $s['nama_servis'] ?></td>
            <td>RM<?= number_format($s['harga_servis'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="d-flex justify-content-between mt-4">
        <form action="terima.php" method="POST" style="display:inline;">
          <input type="hidden" name="tempahanID" value="<?= $tempahanID ?>">
          <input type="hidden" name="choice" value="1">
          <button type="submit" class="btn btn-success">Terima (1)</button>
        </form>

        <form action="terima.php" method="POST" style="display:inline;">
          <input type="hidden" name="tempahanID" value="<?= $tempahanID ?>">
          <input type="hidden" name="choice" value="2">
          <button type="submit" class="btn btn-success">Terima (1&2)</button>
        </form>

        <a href="maklumat_servis.php" class="btn btn-secondary">Tutup</a>
      </div>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
=======
<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Tempahan ID tidak diberikan.";
    exit();
}

$pelangganID = $_SESSION['pelangganID'];
$tempahanID = $_GET['id'];

$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get tempahan details
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

// Get selected servis
$selected_servis = [];
$sql = "SELECT s.servisID, s.nama_servis, ts.harga
        FROM tempahan_servis ts
        JOIN servis s ON ts.servisID = s.servisID
        WHERE ts.tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $selected_servis[] = $row;
}

// Fetch Jenis Servis (concatenated list)
$jenis_servis = "-";
$sql = "SELECT s.nama_servis 
        FROM tempahan_servis ts
        JOIN servis s ON ts.servisID = s.servisID
        WHERE ts.tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$result = $stmt->get_result();

$servis_names = [];
while ($row = $result->fetch_assoc()) {
    $servis_names[] = $row['nama_servis'];
}
if (!empty($servis_names)) {
    $jenis_servis = implode(", ", $servis_names);
}


// Get disyorkan servis
$disyorkan_servis = [];
$sql = "SELECT * FROM disyorkan_servis WHERE tempahanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tempahanID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $disyorkan_servis[] = $row;
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

?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8" />
  <title>Butiran Tempahan</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .container {
      max-width: 1000px;
    }

    .booking-details {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 30px;
      margin-bottom: 30px;
      transition: transform 0.3s ease;
    }

    .booking-details:hover {
      transform: translateY(-5px);
    }

    .booking-details h4 {
      color: #2c3e50;
      font-weight: 700;
      margin-bottom: 25px;
      position: relative;
      padding-bottom: 10px;
    }

    .booking-details h4::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, #1abc9c, #16a085);
      border-radius: 3px;
    }

    .booking-details h5 {
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .booking-details p {
      color: #495057;
      font-size: 15px;
      line-height: 1.6;
      margin-bottom: 15px;
    }

    .booking-details strong {
      color: #2c3e50;
      font-weight: 600;
    }

    .table {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .table thead th {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      font-weight: 600;
      border: none;
      padding: 15px;
    }

    .table tbody td {
      padding: 15px;
      vertical-align: middle;
      border-bottom: 1px solid #f1f1f1;
    }

    .table tbody tr:last-child td {
      border-bottom: none;
    }

    .btn-success {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      font-weight: 600;
      padding: 10px 25px;
      border-radius: 10px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(26, 188, 156, 0.3);
      color: white;
    }

    .btn-secondary {
      background: linear-gradient(45deg, #6c757d, #495057);
      color: white;
      font-weight: 600;
      padding: 10px 25px;
      border-radius: 10px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(73, 80, 87, 0.3);
      color: white;
    }

    .form-control {
      border-radius: 10px;
      border: 2px solid #e9ecef;
      padding: 10px 15px;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: #1abc9c;
      box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
    }

    @media (max-width: 768px) {
      .booking-details {
        padding: 20px;
      }
      
      .table thead th,
      .table tbody td {
        padding: 10px;
      }
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
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="servisin.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Servis
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="maklumat_servis.php">Maklumat Servis</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="hubungin.php">Hubungi Kami</a></li>
        </ul>

        <!-- Profile Dropdown -->
        <div class="d-flex gap-2">
          <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <?php
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

  <div class="container py-5">
    <div class="booking-details">
      <h4 class="fw-bold">Butiran Tempahan</h4>
      <div class="mb-4">
        <strong>Tempahan ID:</strong>
        <input type="text" class="form-control w-25" value="<?= $tempahanID ?>" readonly>
      </div>
      <div class="p-4 border rounded bg-white">
        <h5 class="fw-bold">Butiran Tempahan</h5>
        <div class="row">
          <div class="col-md-6">
            <p><strong>Nama:</strong> <?= $tempahan['nama_p'] ?></p>
            <p><strong>Nombor Telefon:</strong> <?= $tempahan['telefon_p'] ?></p>
            <p><strong>No Plate Kereta:</strong> <?= $tempahan['plate_kereta'] ?></p>
            <p><strong>Model Kereta:</strong> <?= $tempahan['model_kereta'] ?></p>
            <p><strong>Brand Kereta:</strong> <?= $tempahan['jenama_kereta'] ?></p>
            <p><strong>Tahun Kereta:</strong> <?= $tempahan['tahun_kereta'] ?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Tarikh:</strong> <?= $tempahan['tarikh'] ?></p>
            <p><strong>Jenis Servis:</strong> <?= $jenis_servis ?></p>
            <p><strong>Bengkel:</strong> <?= $tempahan['nama_bengkel'] ?></p>
            <p><strong>Komen:</strong> <?= $tempahan['komen'] ?: '-' ?></p>
          </div>
        </div>
      </div>

      <h5 class="mt-4">Servis Pelanggan (<?= count($selected_servis) ?>)</h5>
      <table class="table">
        <thead><tr><th>Id</th><th>Servis ID</th><th>Nama Servis</th><th>Harga</th></tr></thead>
        <tbody>
          <?php foreach ($selected_servis as $i => $s): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= $s['servisID'] ?></td>
            <td><?= $s['nama_servis'] ?></td>
            <td>RM<?= number_format($s['harga'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h5 class="mt-4">Disyorkan Servis (<?= count($disyorkan_servis) ?>)</h5>
      <table class="table">
        <thead><tr><th>Id</th><th>Servis ID</th><th>Nama Servis</th><th>Harga</th></tr></thead>
        <tbody>
          <?php foreach ($disyorkan_servis as $i => $s): ?>
          <tr>
            <td><?= $i + 1 + count($selected_servis) ?></td>
            <td><?= $s['servisID'] ?></td>
            <td><?= $s['nama_servis'] ?></td>
            <td>RM<?= number_format($s['harga_servis'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="d-flex justify-content-between mt-4">
        <form action="terima.php" method="POST" style="display:inline;">
          <input type="hidden" name="tempahanID" value="<?= $tempahanID ?>">
          <input type="hidden" name="choice" value="1">
          <button type="submit" class="btn btn-success">Terima (1)</button>
        </form>

        <form action="terima.php" method="POST" style="display:inline;">
          <input type="hidden" name="tempahanID" value="<?= $tempahanID ?>">
          <input type="hidden" name="choice" value="2">
          <button type="submit" class="btn btn-success">Terima (1&2)</button>
        </form>

        <a href="maklumat_servis.php" class="btn btn-secondary">Tutup</a>
      </div>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
