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

// Fetch tempahan details
$sql = "SELECT t.*, p.nama_p, p.telefon_p, p.emel_p, k.plate_kereta, k.model_kereta, k.jenama_kereta, k.tahun_kereta, b.nama_bengkel
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bayar - SERVIS-X</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    .payment-container {
      max-width: 600px;
      margin: 40px auto;
      padding: 30px;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .payment-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .payment-header h2 {
      color: #333;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .payment-header p {
      color: #6c757d;
    }

    .payment-method {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .payment-method:hover {
      background: #e9ecef;
    }

    .payment-method.selected {
      background: #e3fcef;
      border: 2px solid #28a745;
    }

    .payment-method i {
      font-size: 24px;
      margin-right: 10px;
      color: #28a745;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px 15px;
      border: 1px solid #dee2e6;
    }

    .form-control:focus {
      border-color: #28a745;
      box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .card-number {
      position: relative;
    }

    .card-number i {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }

    .expiry-cvv {
      display: flex;
      gap: 15px;
    }

    .expiry-cvv .form-group {
      flex: 1;
    }

    .btn-pay {
      background: linear-gradient(45deg, #28a745, #198754);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-pay:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    .amount-box {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
    }

    .amount-box h4 {
      color: #333;
      font-weight: 600;
    }

    .amount-box h2 {
      color: #28a745;
      font-weight: 700;
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
<body class="bg-light">

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
          <a class="nav-link dropdown-toggle" href="servisin.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
  <div class="payment-container animate__animated animate__fadeInUp">
    <div class="payment-header">
      <h2>Pembayaran</h2>
      <p>Tempahan ID: <?php echo $tempahanID; ?></p>
    </div>

    <div class="amount-box">
      <h4>Jumlah Bayaran</h4>
      <h2>RM <?php echo number_format($tempahan['jumlah_harga'], 2); ?></h2>
    </div>

    <div class="mb-4">
      <label class="form-label">Pilih Kaedah Pembayaran:</label><br>
      <input type="radio" id="pay_online" name="payment_method" value="online" checked>
      <label for="pay_online">Online Banking (ToyyibPay)</label>
      &nbsp;&nbsp;
      <input type="radio" id="pay_card" name="payment_method" value="credit">
      <label for="pay_card">Kad Kredit</label>
    </div>

    <!-- Online Banking Form (ToyyibPay) -->
    <form id="onlineForm" action="payment/proses_toyyibpay.php" method="POST">
      <input type="hidden" name="tempahanID" value="<?php echo $tempahanID; ?>">
      <input type="hidden" name="amount" value="<?php echo $tempahan['jumlah_harga']; ?>">
      <input type="hidden" name="name" value="<?php echo $tempahan['nama_p']; ?>">
      <input type="hidden" name="email" value="<?php echo $tempahan['emel_p']; ?>">
      <input type="hidden" name="phone" value="<?php echo $tempahan['telefon_p']; ?>">
      <button type="submit" class="btn btn-pay w-100">Bayar Sekarang (Online Banking)</button>
    </form>

    <!-- Credit Card Form -->
    <form id="creditForm" action="proses_bayar.php" method="POST" style="display:none;">
      <input type="hidden" name="tempahanID" value="<?php echo $tempahanID; ?>">
      <input type="hidden" name="amount" value="<?php echo $tempahan['jumlah_harga']; ?>">
      <input type="hidden" name="name" value="<?php echo $tempahan['nama_p']; ?>">
      <input type="hidden" name="email" value="<?php echo $tempahan['emel_p']; ?>">
      <input type="hidden" name="phone" value="<?php echo $tempahan['telefon_p']; ?>">
      <div class="mb-3">
        <label for="cardName" class="form-label">Nama pada Kad</label>
        <input type="text" class="form-control" id="cardName" name="cardName" required>
      </div>
      <div class="mb-3 card-number">
        <label for="cardNumber" class="form-label">Nombor Kad</label>
        <input type="text" class="form-control" id="cardNumber" name="cardNumber" maxlength="19" required>
        <i class="bi bi-credit-card"></i>
      </div>
      <div class="expiry-cvv mb-3">
        <div class="form-group">
          <label for="expiry" class="form-label">Tamat Tempoh</label>
          <input type="text" class="form-control" id="expiry" name="expiry" placeholder="MM/YY" maxlength="5" required>
        </div>
        <div class="form-group">
          <label for="cvv" class="form-label">CVV</label>
          <input type="text" class="form-control" id="cvv" name="cvv" maxlength="3" pattern="\d{3}" required>
        </div>
      </div>
      <button type="submit" class="btn btn-pay w-100">Bayar Sekarang (Kad Kredit)</button>
    </form>

    <style>
      .payment-methods {
        margin-bottom: 20px;
      }
      
      .payment-method {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
      }

      .payment-method:hover {
        background: #e9ecef;
      }

      .payment-method.selected {
        background: #e3fcef;
        border: 2px solid #28a745;
      }

      .payment-method i {
        font-size: 24px;
        margin-right: 10px;
        color: #28a745;
      }

      .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
      }

      .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
      }

      .card-number {
        position: relative;
      }

      .card-number i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
      }

      .expiry-cvv {
        display: flex;
        gap: 15px;
      }

      .expiry-cvv .form-group {
        flex: 1;
      }
    </style>

    <script>
      // Show/hide forms based on payment method selection
      const onlineForm = document.getElementById('onlineForm');
      const creditForm = document.getElementById('creditForm');
      document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
          if (this.value === 'online') {
            onlineForm.style.display = 'block';
            creditForm.style.display = 'none';
          } else {
            onlineForm.style.display = 'none';
            creditForm.style.display = 'block';
          }
        });
      });

      // Format card number input
      document.getElementById('cardNumber').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = value;
      });

      // Format expiry date input
      document.getElementById('expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
          value = value.slice(0,2) + '/' + value.slice(2);
        }
        e.target.value = value;
      });

      // Format CVV input
      document.getElementById('cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
      });
    </script>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Format card number input
  document.getElementById('cardNumber').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})/g, '$1 ').trim();
    e.target.value = value;
  });

  // Format expiry date input
  document.getElementById('expiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
      value = value.slice(0,2) + '/' + value.slice(2);
    }
    e.target.value = value;
  });

  // Format CVV input
  document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
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