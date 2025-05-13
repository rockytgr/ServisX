<?php
// Start session to store user information if login is successful
session_start();

// Redirect to login if not logged in as a bengkel
if (!isset($_SESSION['bengkelID'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servisx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch current profile data
$bengkelID = $_SESSION['bengkelID']; // Get the bengkelID from session
$sql = "SELECT nama_bengkel, emel_bengkel, telefon_bengkel, lokasi, foto_profil, kata_laluan FROM bengkel WHERE bengkelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($nama_bengkel, $emel_bengkel, $telefon_bengkel, $lokasi, $foto_profil, $kata_laluan);
$stmt->fetch();

// Display the error message if available
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear the error message after displaying
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Profil Bengkel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="styles.css"> <!-- Make sure the path is correct -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
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

        .container-fluid {
            margin-top: 20px;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 8px rgba(26, 188, 156, 0.4);
        }

        .btn-custom {
            background-color: #1abc9c;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            background-color: #16a085;
        }

        .modal-header {
            border-bottom: none;
        }

        .error-message {
            color: red;
        }

        .profile-card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-card h3 {
            color: #34495e;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
    <div class="sidebar p-4" style="width: 300px;">
        <a href="bengkel_dashboard.php">
            <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
        </a>
        <ul class="list-unstyled">
            <li><a href="bengkel_dashboard.php">
                <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Mengurus Servis
            </a></li>
            <li><a href="maklumat_bengkel.php">
                <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Maklumat Tempahan
            </a></li>
            <li><a href="report_bengkel.php">
                <img src="images/Admin Laporan.png" alt="icon" style="width: 20px; margin-right: 10px;"> Laporan
            </a></li>
            <li><a href="profil_bengkel.php" class="text-success">
                <img src="images/tatapan profile.png" alt="icon" style="width: 20px; margin-right: 10px;"> Tatapan Profil
            </a></li>
            <li><a href="logout.php">
                <img src="images/logout.png" alt="icon" style="width: 20px; margin-right: 10px;"> Log Keluar
            </a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="container-fluid p-4">
    <h3 class="mb-4 text-center">Tatapan Profil</h3>

    <!-- Display Error Modal if Password is Incorrect -->
    <?php if ($error_message): ?>
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo $error_message; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="profile-header">
        <?php
        if ($foto_profil) {
            echo "<img src='$foto_profil' alt='Profile Picture' class='profile-pic' />";
        } else {
            echo "<div class='profile-pic default-profile'></div>";
        }
        ?>
    </div>

    <div class="form-container">
        <form method="POST" action="submit_profil_bengkel.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama_bengkel" class="form-label">Nama Bengkel</label>
                <input type="text" class="form-control" id="nama_bengkel" name="nama_bengkel" value="<?php echo $nama_bengkel; ?>" required />
            </div>

            <div class="mb-3">
                <label for="emel" class="form-label">Emel</label>
                <input type="email" class="form-control" id="emel" name="emel" value="<?php echo $emel_bengkel; ?>" required />
            </div>

            <div class="mb-3">
                <label for="telefon_bengkel" class="form-label">Nombor Telefon</label>
                <input type="text" class="form-control" id="telefon_bengkel" name="telefon_bengkel" value="<?php echo $telefon_bengkel; ?>" required />
            </div>

            <div class="mb-3">
  <label for="lokasi" class="form-label">Lokasi (Klik atau cari di peta)</label>

  <!-- Lokasi input: full width -->
  <input type="text" class="form-control mb-2" id="lokasi" name="lokasi" value="<?php echo $lokasi; ?>" readonly required>

  <!-- Force map below with full width and block display -->
  <div id="map" style="height: 300px; width: 100%; display: block; margin-top: 10px;"></div>

  <!-- Address display -->
  <small class="text-muted" id="alamat-display"></small>
</div>



            <div class="mb-3">
                <label for="old_password" class="form-label">Katalaluan lama</label>
                <input type="password" class="form-control" id="old_password" name="old_password"  />
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Katalaluan baru</label>
                <input type="password" class="form-control" id="new_password" name="new_password"  />
            </div>

            <div class="mb-3">
                <label for="foto_profil" class="form-label">Muat naik gambar profil</label>
                <input type="file" class="form-control" id="foto_profil" name="foto_profil" />
            </div>

            <button type="submit" class="btn btn-success submit-btn">Simpan</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Show the modal if there is an error message
    <?php if ($error_message): ?>
        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    <?php endif; ?>
</script>

<?php
  // Split lat,lng for JS use
  $defaultLat = 3.139;
  $defaultLng = 101.6869;
  
  if (!empty($lokasi) && strpos($lokasi, ',') !== false) {
      $parts = explode(',', $lokasi);
      if (count($parts) == 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
          $defaultLat = $parts[0];
          $defaultLng = $parts[1];
      }
  }
  
?>


<script>
  let map, marker, geocoder;

  function initMap() {
    const defaultLocation = { lat: parseFloat("<?php echo $defaultLat; ?>"), lng: parseFloat("<?php echo $defaultLng; ?>") };

    map = new google.maps.Map(document.getElementById("map"), {
      zoom: 12,
      center: defaultLocation,
    });

    marker = new google.maps.Marker({
      position: defaultLocation,
      map: map,
      draggable: true,
    });

    geocoder = new google.maps.Geocoder();

    // Set default address
    updateLocation(defaultLocation);

    // Click on map
    map.addListener("click", function (e) {
      marker.setPosition(e.latLng);
      updateLocation(e.latLng);
    });

    // Drag marker
    marker.addListener("dragend", function () {
      updateLocation(marker.getPosition());
    });

    // Search Box
    const input = document.getElementById("autocomplete");
    const autocomplete = new google.maps.places.Autocomplete(input, {
      componentRestrictions: { country: "my" },
      fields: ["geometry"],
    });

    autocomplete.addListener("place_changed", function () {
      const place = autocomplete.getPlace();
      if (!place.geometry) return;
      const loc = place.geometry.location;
      map.setCenter(loc);
      map.setZoom(15);
      marker.setPosition(loc);
      updateLocation(loc);
    });
  }

  function updateLocation(latlng) {
  const lat = typeof latlng.lat === "function" ? latlng.lat() : latlng.lat;
  const lng = typeof latlng.lng === "function" ? latlng.lng() : latlng.lng;
  document.getElementById("lokasi").value = `${lat},${lng}`;

  geocoder.geocode({ location: { lat, lng } }, function (results, status) {
    if (status === "OK" && results[0]) {
      document.getElementById("alamat-display").innerText = results[0].formatted_address;
    } else {
      document.getElementById("alamat-display").innerText = "Alamat tidak dapat dikenal pasti.";
    }
  });
}

</script>

<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCi64pLUbzxTnGHYRMWhwz4jshvcpcNdNo&libraries=places&callback=initMap">
</script>


</body>
</html>

<?php
$conn->close();
?>
