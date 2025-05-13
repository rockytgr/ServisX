<?php
// Start session to store user information if login is successful
session_start();

// Redirect to login if not logged in as an admin
if (!isset($_SESSION['adminID'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "servisx"; // your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Bengkel data where status is 'Confirmed' or null
$sql = "SELECT b.bengkelID, b.nama_bengkel, b.emel_bengkel, b.lokasi, IFNULL(p.status, 'Not Confirmed') AS status
        FROM bengkel b
        LEFT JOIN pengesahan p ON b.bengkelID = p.bengkelID
        WHERE p.status = 'Confirmed' OR p.status IS NULL"; // Fetch only confirmed bengkels
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mengurus Bengkel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure the path is correct -->
    <style>
        /* Custom styles for the table and page */
        .table th, .table td {
            text-align: center;
        }
        .sidebar {
            background-color: #2c3e50;
            color: white;
            height: 100vh;
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


<!-- Sidebar -->
<div class="d-flex">
    <div class="sidebar p-4" style="width: 310px;">
        <a href="admin.php">
            <!-- Logo for SERVIS-X -->
            <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
        </a>
        <ul class="list-unstyled">
    <li><a href="admin.php" class="text-success">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Mengurus Bengkel
    </a></li>
    <li><a href="pengesahan_bengkel.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Pengesahan Bengkel
    </a></li>
    <li><a href="report_admin.php">
        <img src="images/Admin Laporan.png" alt="icon" style="width: 20px; margin-right: 10px;"> Laporan
    </a></li>
    <li><a href="logout.php">
        <img src="images/logout.png" alt="icon" style="width: 20px; margin-right: 10px;"> Log Keluar
    </a></li>
</ul>

    </div>

    <!-- Main Content -->
    <div class="container-fluid p-4">
        <h3 class="mb-4">Mengurus Bengkel</h3>

        <!-- Bengkel Count -->
        <div class="mb-4">
            <?php
            // Count how many bengkel are registered and confirmed
            $count_sql = "SELECT COUNT(bengkelID) AS bengkel_count FROM bengkel WHERE bengkelID IN (SELECT bengkelID FROM pengesahan WHERE status = 'Confirmed')";
            $count_result = $conn->query($count_sql);
            $count_row = $count_result->fetch_assoc();
            echo "<p>Jumlah bengkel telah daftar: {$count_row['bengkel_count']}</p>";
            ?>
        </div>

        <!-- Search Bar -->
        <div>
                <input type="text" id="search" class="form-control" placeholder="Search Bengkel">
            </div>

            <!-- Button to Add New Bengkel -->
            <div>
                <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addBengkelModal">Tambah Bengkel Baru</button>
            </div>

        <!-- Table for Bengkel -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Bengkel ID</th>
                    <th scope="col">Nama Bengkel</th>
                    <th scope="col">Emel</th>
                    <th scope="col">Lokasi</th>
                    <th scope="col">Status Pengesahan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $counter = 1; // Start the counter at 1 for the first row
                    while ($row = $result->fetch_assoc()) {
                        $lokasi = explode(',', $row['lokasi']);
                        $lat = isset($lokasi[0]) ? floatval($lokasi[0]) : 3.139;
                        $lng = isset($lokasi[1]) ? floatval($lokasi[1]) : 101.6869;
                        echo "<tr>
                                <td>{$counter}</td>
                                <td>{$row['bengkelID']}</td>
                                <td>{$row['nama_bengkel']}</td>
                                <td>{$row['emel_bengkel']}</td>
                                <td>
    <button class='btn btn-outline-primary btn-sm' data-bs-toggle='modal' data-bs-target='#mapModal{$row['bengkelID']}'>
        <i class='bi bi-geo-alt'></i> Lihat Peta
    </button>
</td>

                                <td>{$row['status']}</td>
                                <td>
                                    <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editModal{$row['bengkelID']}'>Edit</button>
                                    <a href='delete_bengkel.php?id={$row['bengkelID']}' class='btn btn-danger btn-sm'>Delete</a>
                                </td>
                              </tr>";

                              echo "<div class='modal fade' id='mapModal{$row['bengkelID']}' tabindex='-1'>
                                <div class='modal-dialog modal-lg modal-dialog-centered'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'>Lokasi Bengkel</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <div id='map{$row['bengkelID']}' style='height: 400px;'></div>
                                        </div>
                                    </div>
                                </div>
                              </div>
                              <script>
                              document.addEventListener('DOMContentLoaded', function () {
                                const modal = document.getElementById('mapModal{$row['bengkelID']}');
                                modal.addEventListener('shown.bs.modal', function () {
                                  const map = new google.maps.Map(document.getElementById('map{$row['bengkelID']}'), {
                                    center: { lat: {$lat}, lng: {$lng} },
                                    zoom: 15
                                  });
                                  new google.maps.Marker({ position: { lat: {$lat}, lng: {$lng} }, map: map });
                                });
                              });
                              </script>";

                        // Edit Modal
                        echo "<div class='modal fade' id='editModal{$row['bengkelID']}' tabindex='-1' aria-labelledby='editModalLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editModalLabel'>Edit Bengkel Details</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form method='POST' action='update_bengkel.php'>
                                                <input type='hidden' name='bengkelID' value='{$row['bengkelID']}'>
                                                <div class='mb-3'>
                                                    <label for='nama_bengkel' class='form-label'>Nama Bengkel</label>
                                                    <input type='text' class='form-control' id='nama_bengkel' name='nama_bengkel' value='{$row['nama_bengkel']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='emel_bengkel' class='form-label'>Emel Bengkel</label>
                                                    <input type='email' class='form-control' id='emel_bengkel' name='emel_bengkel' value='{$row['emel_bengkel']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='status_pengesahan' class='form-label'>Status Pengesahan</label>
                                                    <input type='text' class='form-control' id='status_pengesahan' name='status_pengesahan' value='{$row['status']}' required>
                                                </div>
                                                <button type='submit' class='btn btn-primary'>Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                
                              </div>";

                              

                        $counter++; // Increment the counter for the next row
                    }
                } else {
                    echo "<tr><td colspan='7'>No records found</td></tr>";
                }
                ?>

            </tbody>
        </table>
    </div>
</div>

<!-- Add Bengkel Modal -->
<div class="modal fade" id="addBengkelModal" tabindex="-1" aria-labelledby="addBengkelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBengkelModalLabel">Tambah Bengkel Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="add_bengkel.php">
                    <div class="mb-3">
                        <label for="nama_bengkel" class="form-label">Nama Bengkel</label>
                        <input type="text" class="form-control" id="nama_bengkel" name="nama_bengkel" required>
                    </div>
                    <div class="mb-3">
                        <label for="emel_bengkel" class="form-label">Emel Bengkel</label>
                        <input type="email" class="form-control" id="emel_bengkel" name="emel_bengkel" required>
                    </div>
                    <!-- Lokasi field removed as per your request -->
                    <button type="submit" class="btn btn-primary">Add Bengkel</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Add search functionality
    const searchInput = document.getElementById("search");
    searchInput.addEventListener("input", function() {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll("table tbody tr");
        rows.forEach(row => {
            const cells = row.querySelectorAll("td");
            let match = false;
            
            // Loop through each cell in the row
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(filter)) {
                    match = true;
                }
            });

            // If any column matches the filter, show the row
            if (match) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
</script>

<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCi64pLUbzxTnGHYRMWhwz4jshvcpcNdNo">
</script>

</body>
</html>

<?php
$conn->close();
?>