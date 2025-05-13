<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];

// Get all previous data from session
$bengkelID = $_SESSION['bengkelID'];
$tarikh = $_SESSION['tarikh'];
$masa = $_SESSION['masa'];
$keretaID = $_SESSION['keretaID'];
$selected_servis_names = $_POST['selected_servis_names'];
$selected_servis_ids = $_POST['selected_servis_ids'];
$komen = $_POST['komen'];

// DB connection
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get pelanggan data
$stmt = $conn->prepare("SELECT nama_p, telefon_p FROM pelanggan WHERE pelangganID = ?");
$stmt->bind_param("s", $pelangganID);
$stmt->execute();
$stmt->bind_result($nama_p, $telefon_p);
$stmt->fetch();
$stmt->close();

// Get kereta details
$stmt = $conn->prepare("SELECT plate_kereta, model_kereta, jenama_kereta, tahun_kereta FROM kereta WHERE keretaID = ?");
$stmt->bind_param("s", $keretaID);
$stmt->execute();
$stmt->bind_result($plate_kereta, $model_kereta, $jenama_kereta, $tahun_kereta);
$stmt->fetch();
$stmt->close();

// Get bengkel name
$stmt = $conn->prepare("SELECT nama_bengkel FROM bengkel WHERE bengkelID = ?");
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$stmt->bind_result($nama_bengkel);
$stmt->fetch();
$stmt->close();

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
  <title>Pengesahan Tempahan</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .step-container {
      max-width: 800px;
      margin: 60px auto;
      padding: 40px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }

    .step-container:hover {
      transform: translateY(-5px);
    }

    .stepper {
      display: flex;
      justify-content: space-between;
      margin-bottom: 40px;
      position: relative;
    }

    .stepper::before {
      content: '';
      position: absolute;
      top: 12px;
      left: 0;
      right: 0;
      height: 2px;
      background: #e9ecef;
      z-index: 1;
    }

    .stepper::after {
      content: '';
      position: absolute;
      top: 12px;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, #1abc9c, #16a085);
      z-index: 1;
    }

    .stepper .step {
      text-align: center;
      flex: 1;
      position: relative;
      z-index: 2;
    }

    .stepper .step .circle {
      width: 28px;
      height: 28px;
      line-height: 28px;
      border-radius: 50%;
      background: #fff;
      margin: auto;
      font-size: 14px;
      border: 2px solid #e9ecef;
      position: relative;
      z-index: 2;
      transition: all 0.3s ease;
    }

    .stepper .step.active .circle,
    .stepper .step.completed .circle {
      background: #1abc9c;
      color: #fff;
      border-color: #1abc9c;
      box-shadow: 0 0 0 4px rgba(26, 188, 156, 0.2);
    }

    .stepper .step span {
      display: block;
      margin-top: 10px;
      font-size: 14px;
      color: #6c757d;
      font-weight: 500;
    }

    .stepper .step.active span,
    .stepper .step.completed span {
      color: #1abc9c;
      font-weight: 600;
    }

    .info-box {
      background: #f8f9fa;
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .info-box h5 {
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 25px;
      position: relative;
      padding-bottom: 10px;
    }

    .info-box h5::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 3px;
      background: linear-gradient(90deg, #1abc9c, #16a085);
      border-radius: 3px;
    }

    .info-box p {
      margin-bottom: 15px;
      color: #495057;
      font-size: 15px;
      line-height: 1.6;
    }

    .info-box strong {
      color: #2c3e50;
      font-weight: 600;
    }

    .btn-confirm {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      font-weight: 600;
      padding: 12px 30px;
      border-radius: 12px;
      border: none;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-confirm:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(26, 188, 156, 0.3);
      color: white;
    }

    .btn-outline-secondary {
      background: linear-gradient(45deg, #6c757d, #495057);
      color: white;
      font-weight: 600;
      padding: 12px 30px;
      border-radius: 12px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(73, 80, 87, 0.3);
      color: white;
    }

    @media (max-width: 768px) {
      .step-container {
        margin: 30px auto;
        padding: 30px 20px;
      }
      
      .info-box {
        padding: 20px;
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
            <a class="nav-link dropdown-toggle" href="servisin.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" onclick="toggleDropdown(event)">
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

  <div class="step-container">
    <h5 class="text-center fw-bold mb-4">Tempahan Servis</h5>

    <div class="stepper">
      <div class="step completed">
        <div class="circle">✓</div>
        <span class="text-success">Tempah</span>
      </div>
      <div class="step active">
        <div class="circle">✓</div>
        <span class="text-success">Butiran Kereta</span>
      </div>
      <div class="step active">
        <div class="circle">✓</div>
        <span class="text-success">Jenis Servis</span>
      </div>
      <div class="step active">
        <div class="circle">✓</div>
        <span class="text-success">Pengesahan</span>
      </div>
    </div>

    <div class="info-box">
      <h5 class="fw-bold mb-3">Butiran Tempahan</h5>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Nama:</strong> <?= $nama_p ?></p>
          <p><strong>Nombor Telefon:</strong> <?= $telefon_p ?></p>
          <p><strong>No Plate Kereta:</strong> <?= $plate_kereta ?></p>
          <p><strong>Model Kereta:</strong> <?= $model_kereta ?></p>
          <p><strong>Brand Kereta:</strong> <?= $jenama_kereta ?></p>
          <p><strong>Tahun Kereta:</strong> <?= $tahun_kereta ?></p>
        </div>
        <div class="col-md-6">
          <p><strong>Tarikh:</strong> <?= $tarikh ?></p>
          <p><strong>Masa:</strong> <?= $masa ?></p>
          <p><strong>Jenis Servis:</strong> <?= $selected_servis_names ?></p>
          <p><strong>Bengkel:</strong> <?= $nama_bengkel ?></p>
          <p><strong>Komen:</strong> <?= $komen ? $komen : '-' ?></p>
        </div>
      </div>
    </div>

    <h5 class="fw-bold mb-3">Adakah anda pasti?</h5>
    <p class="mb-4"><strong>Jika ya, anda perlu menunggu pengesahan dari staff. Maklumat akan diberi setelah pengesahan.</strong></p>

    <form action="submit_tempahan.php" method="POST">
      <input type="hidden" name="keretaID" value="<?= $keretaID ?>">
      <input type="hidden" name="servis_ids" value="<?= $selected_servis_ids ?>">
      <input type="hidden" name="komen" value="<?= htmlspecialchars($komen) ?>">

      <div class="d-flex gap-3">
        <button type="submit" class="btn btn-confirm">Ya</button>
        <a href="booking_1.php" class="btn btn-outline-secondary">Kembali</a>
      </div>
    </form>
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
