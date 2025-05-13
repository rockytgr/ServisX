<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

// DB connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch bengkel with status_pengesahan = 'confirmed'
$bengkel_data = [];
$sql = "SELECT bengkelID, nama_bengkel FROM bengkel WHERE status_pengesahan = 'confirmed'";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $bengkel_data[] = $row;
}

$sql = "
  SELECT 
    b.bengkelID,
    b.nama_bengkel,
    b.lokasi,
    ROUND(AVG(u.rating), 1) AS avg_rating
  FROM bengkel b
  LEFT JOIN tempahan t ON b.bengkelID = t.bengkelID
  LEFT JOIN ulasan u ON t.tempahanID = u.tempahanID
  GROUP BY b.bengkelID
";


$result = $conn->query($sql);
$bengkel_data = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $bengkel_data[] = $row;
  }
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
  <title>Tempahan Servis - Langkah 1</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
      background-color: #f8f9fa;
    }

    main.flex-grow-1 {
      flex: 1;
    }

    .booking-container {
      max-width: 500px;
      margin: 60px auto;
      padding: 40px 30px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .booking-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    }

    .booking-header {
      margin-bottom: 2rem;
      position: relative;
    }

    .booking-header h5 {
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 1rem;
      position: relative;
      display: inline-block;
    }

    .booking-header h5::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 40px;
      height: 3px;
      background: linear-gradient(90deg, #1abc9c, #16a085);
      border-radius: 3px;
    }

    .form-label {
      font-weight: 500;
      color: #495057;
      margin-bottom: 0.5rem;
      text-align: left;
    }

    .form-select {
      border-radius: 10px;
      padding: 0.75rem 1rem;
      border: 2px solid #e9ecef;
      transition: all 0.3s ease;
      text-align: left;
    }

    .form-select:focus {
      border-color: #1abc9c;
      box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.15);
    }

    .btn-next {
      width: 100%;
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 10px;
      font-weight: 500;
      transition: all 0.3s ease;
      margin-top: 2rem;
    }

    .btn-next:hover {
      background: linear-gradient(45deg, #16a085, #1abc9c);
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(26, 188, 156, 0.3);
    }

    @media (max-width: 576px) {
      .booking-container {
        margin: 30px auto;
        padding: 30px 20px;
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
         <!-- Servis Link -->
         <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="servisin.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" onclick="toggleDropdown(event)">
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
<div class="booking-container">
  <div class="booking-header">
    <h5 class="mb-4 fw-bold">Tempahan Servis</h5>
  </div>
  <form method="POST" action="booking_2.php">
    <div class="text-start mb-2">
      <label for="bengkelID" class="form-label">Pilih Bengkel yang Didaftar</label>
      <select class="form-select" name="bengkelID" id="bengkelID" required>
        <option value="">-- Sila Pilih Bengkel --</option>
        <?php foreach ($bengkel_data as $b): ?>
          <option value="<?= $b['bengkelID'] ?>">
  <?= $b['nama_bengkel'] ?>
  <?= isset($b['avg_rating']) && $b['avg_rating'] !== null ? ' - ★ ' . $b['avg_rating'] : '' ?>
</option>

        <?php endforeach; ?>
      </select>

      <div id="bengkelInfo" class="mt-3 p-3 bg-light border rounded" style="display: none;">
  <h6 id="bengkelNama"></h6>
  <p id="bengkelLokasi" class="mb-2 text-muted"></p>
  <div id="mapPreview" style="height: 250px;"></div>
</div>

    </div>

    <button type="submit" class="btn btn-next">Sambung</button>
    <script>
const bengkelData = <?= json_encode($bengkel_data) ?>;
</script>

  </form>
</div>
        </main>

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
  <script>
let map;
let marker;
let geocoder;

function initMapAPI() {
  geocoder = new google.maps.Geocoder();

  document.getElementById("bengkelID").addEventListener("change", function () {
    const selectedID = this.value;
    const infoBox = document.getElementById("bengkelInfo");
    const namaEl = document.getElementById("bengkelNama");
    const lokasiEl = document.getElementById("bengkelLokasi");
    const mapEl = document.getElementById("mapPreview");

    const selected = bengkelData.find(b => b.bengkelID === selectedID);

    if (selected && selected.lokasi && selected.lokasi.includes(',')) {
      const [lat, lng] = selected.lokasi.split(',').map(Number);
      const latLng = { lat, lng };

      // Show box
      infoBox.style.display = "block";
      namaEl.textContent = selected.nama_bengkel;
      lokasiEl.textContent = "Memuatkan alamat...";

      // Reverse Geocode
      geocoder.geocode({ location: latLng }, function (results, status) {
        if (status === "OK" && results[0]) {
          lokasiEl.textContent = results[0].formatted_address;
        } else {
          lokasiEl.textContent = `Lokasi: ${selected.lokasi}`;
        }
      });

      // Map setup
      if (!map) {
        map = new google.maps.Map(mapEl, {
          center: latLng,
          zoom: 15
        });
        marker = new google.maps.Marker({
          position: latLng,
          map: map
        });
      } else {
        map.setCenter(latLng);
        marker.setPosition(latLng);
      }
    } else {
      infoBox.style.display = "none";
    }
  });
}
</script>


<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCi64pLUbzxTnGHYRMWhwz4jshvcpcNdNo&callback=initMapAPI">
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

