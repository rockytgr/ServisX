<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['pelangganID'])) {
    header("Location: login.html");
    exit();
}

$pelangganID = $_SESSION['pelangganID'];
$keretaID = $_POST['keretaID'];
$_SESSION['keretaID'] = $keretaID;
$tarikh = $_SESSION['tarikh'];
$masa = $_SESSION['masa'];



// Make sure session values exist
if (!isset($_SESSION['bengkelID'])) {
    echo "Bengkel tidak dipilih!";
    exit();
}
$bengkelID = $_SESSION['bengkelID'];

// Connect to DB
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch service options
$sql = "SELECT servisID, nama_servis FROM servis WHERE bengkelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$result = $stmt->get_result();
$servis_list = [];
while ($row = $result->fetch_assoc()) {
    $servis_list[] = $row;
}
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
  <title>Tempahan Servis - Jenis Servis</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .step-container {
      max-width: 600px;
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
      width: 75%; /* This covers up to step 3 (3/4 steps) */
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

    .service-section {
      background: #f8f9fa;
      padding: 25px;
      border-radius: 15px;
      margin-bottom: 30px;
    }

    .service-title {
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 15px;
      position: relative;
      padding-bottom: 10px;
    }

    .service-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 3px;
      background: linear-gradient(90deg, #1abc9c, #16a085);
      border-radius: 3px;
    }

    .service-subtitle {
      color: #6c757d;
      font-size: 14px;
      margin-bottom: 20px;
    }

    .service-button {
      border: 2px solid #e9ecef;
      padding: 15px;
      margin: 8px;
      border-radius: 12px;
      text-align: center;
      cursor: pointer;
      background-color: #fff;
      flex: 1 1 calc(50% - 20px);
      transition: all 0.3s ease;
      font-weight: 500;
      color: #495057;
    }

    .service-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      border-color: #1abc9c;
    }

    .service-button.active {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      border: none;
      box-shadow: 0 5px 15px rgba(26, 188, 156, 0.3);
    }

    .comment-section {
      background: #f8f9fa;
      padding: 25px;
      border-radius: 15px;
      margin-bottom: 20px;
    }

    .form-control {
      border-radius: 12px;
      padding: 15px;
      border: 2px solid #e9ecef;
      transition: all 0.3s ease;
      font-size: 14px;
    }

    .form-control:focus {
      border-color: #1abc9c;
      box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.15);
    }

    .btn-next {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      font-weight: 600;
      width: 100%;
      border-radius: 12px;
      padding: 15px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-next:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(26, 188, 156, 0.3);
    }

    .btn-next:disabled {
      background: #e9ecef;
      color: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    @media (max-width: 768px) {
      .step-container {
        margin: 30px auto;
        padding: 30px 20px;
      }
      
      .service-button {
        flex: 1 1 100%;
        margin: 5px 0;
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

  <div class="container">
    <div class="step-container">
      <h5 class="text-center fw-bold mb-4">Tempahan Servis</h5>

      <div class="stepper">
        <div class="step completed">
          <div class="circle">✓</div>
          <span class="text-success">Tempah</span>
        </div>
        <div class="step completed">
          <div class="circle">✓</div>
          <span class="text-success">Butiran Kereta</span>
        </div>
        <div class="step active">
          <div class="circle">3</div>
          <span>Jenis Servis</span>
        </div>
        <div class="step">
          <div class="circle">4</div>
          <span>Pengesahan</span>
        </div>
      </div>

      <form action="booking_5.php" method="POST" onsubmit="return collectServiceSelection()">
        <div class="service-section">
          <h6 class="service-title">Saya sedang mencari…</h6>
          <p class="service-subtitle">Anda boleh pilih lebih dari satu</p>

          <div class="d-flex flex-wrap">
            <?php foreach ($servis_list as $servis): ?>
              <div class="service-button" onclick="toggleService(this)" data-id="<?= $servis['servisID'] ?>" data-name="<?= $servis['nama_servis'] ?>">
                <?= $servis['nama_servis'] ?>
              </div>
            <?php endforeach; ?>
            <div class="service-button" onclick="toggleService(this)" data-id="LAIN" data-name="Lain-Lain">Lain-Lain</div>
          </div>
        </div>

        <div class="comment-section">
          <label for="komen" class="service-title">Komen (Pilihan)</label>
          <textarea class="form-control" name="komen" id="komen" rows="4" placeholder="Jika ada kerosakan yang ingin diberitahu, boleh komen di sini..."></textarea>
        </div>

        <input type="hidden" name="selected_servis_ids" id="selected_servis_ids">
        <input type="hidden" name="selected_servis_names" id="selected_servis_names">

        <button type="submit" class="btn btn-next mt-3" id="btnContinue" disabled>Continue</button>
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

  <script>
    const selectedIDs = new Set();
    const selectedNames = new Set();

    function toggleService(el) {
      const id = el.dataset.id;
      const name = el.dataset.name;

      if (el.classList.contains("active")) {
        el.classList.remove("active");
        selectedIDs.delete(id);
        selectedNames.delete(name);
      } else {
        el.classList.add("active");
        selectedIDs.add(id);
        selectedNames.add(name);
      }

      document.getElementById("btnContinue").disabled = selectedIDs.size === 0;
    }

    function collectServiceSelection() {
      document.getElementById('selected_servis_ids').value = Array.from(selectedIDs).join(',');
      document.getElementById('selected_servis_names').value = Array.from(selectedNames).join(',');
      return true;
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="scripts.js"></script>
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

$pelangganID = $_SESSION['pelangganID'];
$keretaID = $_POST['keretaID'];
$_SESSION['keretaID'] = $keretaID;
$tarikh = $_SESSION['tarikh'];
$masa = $_SESSION['masa'];



// Make sure session values exist
if (!isset($_SESSION['bengkelID'])) {
    echo "Bengkel tidak dipilih!";
    exit();
}
$bengkelID = $_SESSION['bengkelID'];

// Connect to DB
$conn = new mysqli("localhost", "root", "", "servisx");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch service options
$sql = "SELECT servisID, nama_servis FROM servis WHERE bengkelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$result = $stmt->get_result();
$servis_list = [];
while ($row = $result->fetch_assoc()) {
    $servis_list[] = $row;
}
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
  <title>Tempahan Servis - Jenis Servis</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .step-container {
      max-width: 600px;
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
      width: 75%; /* This covers up to step 3 (3/4 steps) */
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

    .service-section {
      background: #f8f9fa;
      padding: 25px;
      border-radius: 15px;
      margin-bottom: 30px;
    }

    .service-title {
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 15px;
      position: relative;
      padding-bottom: 10px;
    }

    .service-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 3px;
      background: linear-gradient(90deg, #1abc9c, #16a085);
      border-radius: 3px;
    }

    .service-subtitle {
      color: #6c757d;
      font-size: 14px;
      margin-bottom: 20px;
    }

    .service-button {
      border: 2px solid #e9ecef;
      padding: 15px;
      margin: 8px;
      border-radius: 12px;
      text-align: center;
      cursor: pointer;
      background-color: #fff;
      flex: 1 1 calc(50% - 20px);
      transition: all 0.3s ease;
      font-weight: 500;
      color: #495057;
    }

    .service-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      border-color: #1abc9c;
    }

    .service-button.active {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      border: none;
      box-shadow: 0 5px 15px rgba(26, 188, 156, 0.3);
    }

    .comment-section {
      background: #f8f9fa;
      padding: 25px;
      border-radius: 15px;
      margin-bottom: 20px;
    }

    .form-control {
      border-radius: 12px;
      padding: 15px;
      border: 2px solid #e9ecef;
      transition: all 0.3s ease;
      font-size: 14px;
    }

    .form-control:focus {
      border-color: #1abc9c;
      box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.15);
    }

    .btn-next {
      background: linear-gradient(45deg, #1abc9c, #16a085);
      color: white;
      font-weight: 600;
      width: 100%;
      border-radius: 12px;
      padding: 15px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-next:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(26, 188, 156, 0.3);
    }

    .btn-next:disabled {
      background: #e9ecef;
      color: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    @media (max-width: 768px) {
      .step-container {
        margin: 30px auto;
        padding: 30px 20px;
      }
      
      .service-button {
        flex: 1 1 100%;
        margin: 5px 0;
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

  <div class="container">
    <div class="step-container">
      <h5 class="text-center fw-bold mb-4">Tempahan Servis</h5>

      <div class="stepper">
        <div class="step completed">
          <div class="circle">✓</div>
          <span class="text-success">Tempah</span>
        </div>
        <div class="step completed">
          <div class="circle">✓</div>
          <span class="text-success">Butiran Kereta</span>
        </div>
        <div class="step active">
          <div class="circle">3</div>
          <span>Jenis Servis</span>
        </div>
        <div class="step">
          <div class="circle">4</div>
          <span>Pengesahan</span>
        </div>
      </div>

      <form action="booking_5.php" method="POST" onsubmit="return collectServiceSelection()">
        <div class="service-section">
          <h6 class="service-title">Saya sedang mencari…</h6>
          <p class="service-subtitle">Anda boleh pilih lebih dari satu</p>

          <div class="d-flex flex-wrap">
            <?php foreach ($servis_list as $servis): ?>
              <div class="service-button" onclick="toggleService(this)" data-id="<?= $servis['servisID'] ?>" data-name="<?= $servis['nama_servis'] ?>">
                <?= $servis['nama_servis'] ?>
              </div>
            <?php endforeach; ?>
            <div class="service-button" onclick="toggleService(this)" data-id="LAIN" data-name="Lain-Lain">Lain-Lain</div>
          </div>
        </div>

        <div class="comment-section">
          <label for="komen" class="service-title">Komen (Pilihan)</label>
          <textarea class="form-control" name="komen" id="komen" rows="4" placeholder="Jika ada kerosakan yang ingin diberitahu, boleh komen di sini..."></textarea>
        </div>

        <input type="hidden" name="selected_servis_ids" id="selected_servis_ids">
        <input type="hidden" name="selected_servis_names" id="selected_servis_names">

        <button type="submit" class="btn btn-next mt-3" id="btnContinue" disabled>Continue</button>
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

  <script>
    const selectedIDs = new Set();
    const selectedNames = new Set();

    function toggleService(el) {
      const id = el.dataset.id;
      const name = el.dataset.name;

      if (el.classList.contains("active")) {
        el.classList.remove("active");
        selectedIDs.delete(id);
        selectedNames.delete(name);
      } else {
        el.classList.add("active");
        selectedIDs.add(id);
        selectedNames.add(name);
      }

      document.getElementById("btnContinue").disabled = selectedIDs.size === 0;
    }

    function collectServiceSelection() {
      document.getElementById('selected_servis_ids').value = Array.from(selectedIDs).join(',');
      document.getElementById('selected_servis_names').value = Array.from(selectedNames).join(',');
      return true;
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="scripts.js"></script>
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
