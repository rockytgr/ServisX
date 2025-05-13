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

// Fetch all user's car data for the edit modal
$kereta_data = [];
$sql = "SELECT keretaID, plate_kereta, jenama_kereta, model_kereta, tahun_kereta FROM kereta WHERE pelangganID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pelangganID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $kereta_data[] = $row;
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kemaskini Kenderaan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body {
      background-color: #f9f9f9;
      font-family: 'Segoe UI', sans-serif;
    }
    .form-wrapper {
      max-width: 550px;
      margin: 40px auto;
      background: white;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }
    h3.text-center {
      font-weight: bold;
      margin-bottom: 30px;
    }
    .form-control {
      border-radius: 10px;
      padding: 12px;
      font-size: 15px;
      box-shadow: none;
      border: 1px solid #ddd;
    }
    .form-control:focus {
      border-color: #1abc9c;
      box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
    }
    .btn-custom {
      background-color: #198754;
      color: white;
      border: none;
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      transition: background-color 0.3s ease;
    }
    .btn-custom:hover {
      background-color: #16a085;
    }
    .btn-secondary {
      width: 100%;
      border-radius: 10px;
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

<div class="container">
  <div class="form-wrapper">
    <h3 class="text-center">Kemaskini Kenderaan</h3>
    
    <form method="POST" action="submit_kenderaan.php">
      
      <div class="mb-3">
        <label for="no_plate" class="form-label">No Plate Kereta</label>
        <input type="text" class="form-control" name="no_plate" id="no_plate" placeholder="Contoh : MYR1234" required>
      </div>

      <div class="mb-3">
        <label for="jenama" class="form-label">Jenama Kereta</label>
        <select class="form-control" id="jenama" name="jenama" required>
          <option value="" disabled selected>Pilih Jenama Kereta</option>
          <option value="perodua">Perodua</option>
          <option value="proton">Proton</option>
          <option value="honda">Honda</option>
          <option value="toyota">Toyota</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="model" class="form-label">Model Kereta</label>
        <select class="form-control" id="model" name="model" disabled required>
          <option value="" disabled selected>Pilih Model Kereta</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="tahun" class="form-label">Tahun Kereta</label>
        <select class="form-control" id="tahun" name="tahun" disabled required>
          <option value="" disabled selected>Pilih Tahun Kereta</option>
          <?php
            for ($year = 2025; $year >= 2018; $year--) {
                echo "<option value=\"$year\">$year</option>";
            }            
          ?>
        </select>
      </div>

      <div class="d-flex gap-3 mt-4">
        <a href="profil_pelanggan.php" class="btn btn-outline-success">Profil</a>
        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>
        <button type="submit" class="btn btn-custom">Simpan</button>
      </div>
    </form>

    <!-- Modal for Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="update_kenderaan.php">
    <input type="hidden" name="keretaID" id="hidden_keretaID">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Kenderaan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        
        <div class="modal-body">

        <!-- Dropdown to choose car to edit -->
<div class="mb-3">
  <label for="kereta_id_select" class="form-label">Pilih Kenderaan</label>
  <select id="kereta_id_select" class="form-control" required>
    <option value="">-- Pilih Kenderaan --</option>
    <?php foreach ($kereta_data as $car): ?>
      <option value="<?php echo $car['keretaID']; ?>">
        <?php echo $car['keretaID'] . " - " . $car['plate_kereta']; ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

          <div class="mb-3">
            <label for="edit_plate" class="form-label">No Plate</label>
            <input type="text" name="edit_plate" id="edit_plate" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="edit_jenama" class="form-label">Jenama</label>
            <select name="edit_jenama" id="edit_jenama" class="form-control" required>
              <option value="">Pilih Jenama</option>
              <option value="perodua">Perodua</option>
              <option value="proton">Proton</option>
              <option value="honda">Honda</option>
              <option value="toyota">Toyota</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="edit_model" class="form-label">Model</label>
            <select name="edit_model" id="edit_model" class="form-control" required disabled>
              <option value="">Pilih Model</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="edit_tahun" class="form-label">Tahun</label>
            <select name="edit_tahun" id="edit_tahun" class="form-control" required disabled>
              <option value="">Pilih Tahun</option>
              <?php
              for ($year = 2025; $year >= 2018; $year--) {
                  echo "<option value=\"$year\">$year</option>";
              }
              ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
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

<script>
const modelOptions = {
  perodua: ['Axia', 'Bezza', 'Myvi', 'Alza', 'Ativa'],
  proton: ['Saga', 'Persona', 'Iriz', 'X50', 'X70'],
  honda: ['Civic', 'City', 'HR-V', 'CR-V'],
  toyota: ['Vios', 'Yaris', 'Hilux', 'Corolla']
};

document.getElementById('jenama').addEventListener('change', function () {
  const jenama = this.value;
  const modelSelect = document.getElementById('model');
  const tahunSelect = document.getElementById('tahun');

  modelSelect.innerHTML = '<option value="" disabled selected>Pilih Model Kereta</option>';
  tahunSelect.disabled = true;
  tahunSelect.selectedIndex = 0;

  if (modelOptions[jenama]) {
    modelOptions[jenama].forEach(function(model) {
      const option = document.createElement('option');
      option.value = model;
      option.textContent = model;
      modelSelect.appendChild(option);
    });
    modelSelect.disabled = false;
  } else {
    modelSelect.disabled = true;
  }
});

document.getElementById('model').addEventListener('change', function () {
  document.getElementById('tahun').disabled = !this.value;
});
</script>

<script src="scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const editModelOptions = {
  perodua: ['Axia', 'Bezza', 'Myvi', 'Alza', 'Ativa'],
  proton: ['Saga', 'Persona', 'Iriz', 'X50', 'X70'],
  honda: ['Civic', 'City', 'HR-V', 'CR-V'],
  toyota: ['Vios', 'Yaris', 'Hilux', 'Corolla']
};

document.getElementById('edit_jenama').addEventListener('change', function () {
  const jenama = this.value;
  const modelSelect = document.getElementById('edit_model');
  const tahunSelect = document.getElementById('edit_tahun');

  modelSelect.innerHTML = '<option value="">Pilih Model</option>';
  tahunSelect.disabled = true;
  tahunSelect.selectedIndex = 0;

  if (editModelOptions[jenama]) {
    editModelOptions[jenama].forEach(function(model) {
      const option = document.createElement('option');
      option.value = model;
      option.textContent = model;
      modelSelect.appendChild(option);
    });
    modelSelect.disabled = false;
  } else {
    modelSelect.disabled = true;
  }
});

document.getElementById('edit_model').addEventListener('change', function () {
  document.getElementById('edit_tahun').disabled = !this.value;
});
</script>

<script>
const keretaData = <?php echo json_encode($kereta_data); ?>;

document.getElementById('kereta_id_select').addEventListener('change', function () {
  const selectedID = this.value;
  const selected = keretaData.find(k => k.keretaID === selectedID);

  if (selected) {
    document.getElementById('edit_plate').value = selected.plate_kereta;
    document.getElementById('edit_jenama').value = selected.jenama_kereta;
    document.getElementById('hidden_keretaID').value = selected.keretaID;

    const jenamaChangeEvent = new Event('change');
    document.getElementById('edit_jenama').dispatchEvent(jenamaChangeEvent);

    setTimeout(() => {
      document.getElementById('edit_model').value = selected.model_kereta;
      document.getElementById('edit_tahun').disabled = false;
      document.getElementById('edit_tahun').value = selected.tahun_kereta;
    }, 200);
  }
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
