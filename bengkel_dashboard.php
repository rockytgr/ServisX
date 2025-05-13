<<<<<<< HEAD
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


// Fetch Servis data associated with the logged-in bengkel
$bengkelID = $_SESSION['bengkelID']; // Get the bengkelID from session
$sql = "SELECT servisID, nama_servis, harga_servis FROM servis WHERE bengkelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mengurus Servis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="styles.css"> <!-- Make sure the path is correct -->
    <style>
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
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
    <div class="sidebar p-4" style="width: 300px;">
        <a href="bengkel_dashboard.php">
            <!-- Logo for SERVIS-X -->
            <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
        </a>
        <ul class="list-unstyled">
    <li><a href="bengkel_dashboard.php" class="text-success">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Mengurus Servis
    </a></li>
    <li><a href="maklumat_bengkel.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Maklumat Tempahan
    </a></li>
    <li><a href="report_bengkel.php">
        <img src="images/Admin Laporan.png" alt="icon" style="width: 20px; margin-right: 10px;"> Laporan
    </a></li>
    <li><a href="profil_bengkel.php">
        <img src="images/tatapan profile.png" alt="icon" style="width: 20px; margin-right: 10px;"> Tatapan Profil
    </a></li>
    <li><a href="logout.php">
        <img src="images/logout.png" alt="icon" style="width: 20px; margin-right: 10px;"> Log Keluar
    </a></li>
</ul>
    </div>

    <!-- Main Content -->
    <div class="container-fluid p-4">
        <h3 class="mb-4">Senarai Servis</h3>

        <!-- Bengkel Count -->
        <div class="mb-4">
            <p>Jumlah Servis: <?php echo $result->num_rows; ?></p>
        </div>

        <!-- Search Bar -->
        <input type="text" id="search" class="form-control mb-4" placeholder="Search Servis">

        <!-- Button to Add New Servis -->
        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addServisModal">Tambah Servis</button>

        <!-- Table for Servis -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Servis ID</th>
                    <th>Nama Servis</th>
                    <th>Harga Servis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $counter = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$counter}</td>
                                <td>{$row['servisID']}</td>
                                <td>{$row['nama_servis']}</td>
                                <td>{$row['harga_servis']}</td>
                                <td>
                                    <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editServisModal{$row['servisID']}'>Edit</button>
                                    <a href='delete_servis.php?delete={$row['servisID']}' class='btn btn-danger btn-sm'>Delete</a>
                                </td>
                            </tr>";

                        // Edit Modal
                        echo "<div class='modal fade' id='editServisModal{$row['servisID']}' tabindex='-1' aria-labelledby='editServisModalLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editServisModalLabel'>Edit Servis</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form method='POST' action='update_servis.php'>
                                                <input type='hidden' name='servisID' value='{$row['servisID']}'>
                                                <div class='mb-3'>
                                                    <label for='nama_servis' class='form-label'>Nama Servis</label>
                                                    <input type='text' class='form-control' id='nama_servis' name='nama_servis' value='{$row['nama_servis']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='harga_servis' class='form-label'>Harga Servis</label>
                                                    <input type='number' class='form-control' id='harga_servis' name='harga_servis' value='{$row['harga_servis']}' required>
                                                </div>
                                                <button type='submit' name='edit_servis' class='btn btn-primary'>Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>";

                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Servis Modal -->
<div class="modal fade" id="addServisModal" tabindex="-1" aria-labelledby="addServisModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServisModalLabel">Tambah Servis Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="add_servis.php">
                    <div class="mb-3">
                        <label for="nama_servis" class="form-label">Nama Servis</label>
                        <input type="text" class="form-control" id="nama_servis" name="nama_servis" required>
                    </div>
                    <div class="mb-3">
                        <label for="harga_servis" class="form-label">Harga Servis</label>
                        <input type="number" class="form-control" id="harga_servis" name="harga_servis" required>
                    </div>
                    <button type="submit" name="add_servis" class="btn btn-success">Add Servis</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Search functionality
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

</body>
</html>

<?php
$conn->close();
?>
=======
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


// Fetch Servis data associated with the logged-in bengkel
$bengkelID = $_SESSION['bengkelID']; // Get the bengkelID from session
$sql = "SELECT servisID, nama_servis, harga_servis FROM servis WHERE bengkelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bengkelID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mengurus Servis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="styles.css"> <!-- Make sure the path is correct -->
    <style>
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
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
    <div class="sidebar p-4" style="width: 300px;">
        <a href="bengkel_dashboard.php">
            <!-- Logo for SERVIS-X -->
            <img src="images/LogoW.png" alt="SERVIS-X Logo" style="width: 150px; margin-bottom: 20px;" />
        </a>
        <ul class="list-unstyled">
    <li><a href="bengkel_dashboard.php" class="text-success">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Mengurus Servis
    </a></li>
    <li><a href="maklumat_bengkel.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Maklumat Tempahan
    </a></li>
    <li><a href="report_bengkel.php">
        <img src="images/Admin Laporan.png" alt="icon" style="width: 20px; margin-right: 10px;"> Laporan
    </a></li>
    <li><a href="profil_bengkel.php">
        <img src="images/tatapan profile.png" alt="icon" style="width: 20px; margin-right: 10px;"> Tatapan Profil
    </a></li>
    <li><a href="logout.php">
        <img src="images/logout.png" alt="icon" style="width: 20px; margin-right: 10px;"> Log Keluar
    </a></li>
</ul>
    </div>

    <!-- Main Content -->
    <div class="container-fluid p-4">
        <h3 class="mb-4">Senarai Servis</h3>

        <!-- Bengkel Count -->
        <div class="mb-4">
            <p>Jumlah Servis: <?php echo $result->num_rows; ?></p>
        </div>

        <!-- Search Bar -->
        <input type="text" id="search" class="form-control mb-4" placeholder="Search Servis">

        <!-- Button to Add New Servis -->
        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addServisModal">Tambah Servis</button>

        <!-- Table for Servis -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Servis ID</th>
                    <th>Nama Servis</th>
                    <th>Harga Servis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $counter = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$counter}</td>
                                <td>{$row['servisID']}</td>
                                <td>{$row['nama_servis']}</td>
                                <td>{$row['harga_servis']}</td>
                                <td>
                                    <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editServisModal{$row['servisID']}'>Edit</button>
                                    <a href='delete_servis.php?delete={$row['servisID']}' class='btn btn-danger btn-sm'>Delete</a>
                                </td>
                            </tr>";

                        // Edit Modal
                        echo "<div class='modal fade' id='editServisModal{$row['servisID']}' tabindex='-1' aria-labelledby='editServisModalLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editServisModalLabel'>Edit Servis</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form method='POST' action='update_servis.php'>
                                                <input type='hidden' name='servisID' value='{$row['servisID']}'>
                                                <div class='mb-3'>
                                                    <label for='nama_servis' class='form-label'>Nama Servis</label>
                                                    <input type='text' class='form-control' id='nama_servis' name='nama_servis' value='{$row['nama_servis']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='harga_servis' class='form-label'>Harga Servis</label>
                                                    <input type='number' class='form-control' id='harga_servis' name='harga_servis' value='{$row['harga_servis']}' required>
                                                </div>
                                                <button type='submit' name='edit_servis' class='btn btn-primary'>Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>";

                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Servis Modal -->
<div class="modal fade" id="addServisModal" tabindex="-1" aria-labelledby="addServisModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServisModalLabel">Tambah Servis Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="add_servis.php">
                    <div class="mb-3">
                        <label for="nama_servis" class="form-label">Nama Servis</label>
                        <input type="text" class="form-control" id="nama_servis" name="nama_servis" required>
                    </div>
                    <div class="mb-3">
                        <label for="harga_servis" class="form-label">Harga Servis</label>
                        <input type="number" class="form-control" id="harga_servis" name="harga_servis" required>
                    </div>
                    <button type="submit" name="add_servis" class="btn btn-success">Add Servis</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Search functionality
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

</body>
</html>

<?php
$conn->close();
?>
>>>>>>> e4a824728d4fe1de902abaa2650ec4192d8f606a
