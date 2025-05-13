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
$username = "root"; 
$password = "";
$dbname = "servisx"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// Handling Approve and Reject actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $bengkelID = $_GET['id'];
    $action = $_GET['action'];
    $current_date = date('Y-m-d H:i:s'); // Get the current timestamp for confirmation/rejection date

    // Generate the next pengesahanID
    $sql_select = "SELECT MAX(CAST(SUBSTRING(pengesahanID, 2) AS UNSIGNED)) AS last_id FROM pengesahan";
    $result_select = $conn->query($sql_select);
    $last_id = 0;

    if ($result_select->num_rows > 0) {
        $row = $result_select->fetch_assoc();
        $last_id = $row['last_id'];
    }

    // Generate the new pengesahanID with 'S' prefix and incremented number
    $new_pengesahanID = "S" . str_pad($last_id + 1, 3, "0", STR_PAD_LEFT);

    // Get the adminID from session
    $adminID = $_SESSION['adminID']; 

    if ($action == 'approve') {
        // Approve action
        $sql_check = "SELECT * FROM pengesahan WHERE bengkelID = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $bengkelID);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $sql_update = "UPDATE pengesahan SET tarikh_pengesahan = ?, status = 'Confirmed', adminID = ? WHERE bengkelID = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sss", $current_date, $adminID, $bengkelID);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $sql_insert = "INSERT INTO pengesahan (pengesahanID, bengkelID, tarikh_pengesahan, status, adminID) VALUES (?, ?, ?, 'Confirmed', ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $new_pengesahanID, $bengkelID, $current_date, $adminID);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        // Update the status in 'bengkel' table to 'Confirmed'
        $sql_update_bengkel = "UPDATE bengkel SET status_pengesahan = 'Confirmed' WHERE bengkelID = ?";
        $stmt_update_bengkel = $conn->prepare($sql_update_bengkel);
        $stmt_update_bengkel->bind_param("s", $bengkelID);
        $stmt_update_bengkel->execute();
        $stmt_update_bengkel->close();

    } elseif ($action == 'reject') {
        // Reject action
        $sql_check = "SELECT * FROM pengesahan WHERE bengkelID = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $bengkelID);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $sql_update = "UPDATE pengesahan SET tarikh_pengesahan = ?, status = 'Rejected', adminID = ? WHERE bengkelID = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sss", $current_date, $adminID, $bengkelID);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $sql_insert = "INSERT INTO pengesahan (pengesahanID, bengkelID, tarikh_pengesahan, status, adminID) VALUES (?, ?, ?, 'Rejected', ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $new_pengesahanID, $bengkelID, $current_date, $adminID);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        // Update the status in 'bengkel' table to 'Rejected'
        $sql_update_bengkel = "UPDATE bengkel SET status_pengesahan = 'Rejected' WHERE bengkelID = ?";
        $stmt_update_bengkel = $conn->prepare($sql_update_bengkel);
        $stmt_update_bengkel->bind_param("s", $bengkelID);
        $stmt_update_bengkel->execute();
        $stmt_update_bengkel->close();
    }

    // Redirect back to the page after action
    header("Location: pengesahan_bengkel.php");
    exit();
}

// Fetch Bengkel data where status is 'Pending'
$sql = "SELECT b.bengkelID, b.nama_bengkel, b.emel_bengkel, b.lokasi, IFNULL(b.status_pengesahan, 'Pending') AS status
        FROM bengkel b
        LEFT JOIN pengesahan p ON b.bengkelID = p.bengkelID
        WHERE b.status_pengesahan = 'Pending'";  
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pengesahan Bengkel</title>
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
        .admin-box {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 250px;
            margin-left: auto;
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
    <li><a href="admin.php">
        <img src="images/AdminTask.png" alt="icon" style="width: 20px; margin-right: 10px;"> Mengurus Bengkel
    </a></li>
    <li><a href="pengesahan_bengkel.php" class="text-success">
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
        <h3 class="mb-4">Pengesahan Bengkel</h3>

        <!-- Bengkel Count -->
        <div class="mb-4">
            <?php
            // Count how many bengkel are registered and have status 'Pending'
            $count_sql = "SELECT COUNT(bengkelID) AS bengkel_count FROM bengkel WHERE bengkelID IN (SELECT bengkelID FROM pengesahan WHERE status = 'Pending')";
            $count_result = $conn->query($count_sql);
            $count_row = $count_result->fetch_assoc();
            echo "<p>Jumlah menunggu kelulusan Bengkel: {$count_row['bengkel_count']}</p>";
            ?>
        </div>
        <!-- Search Bar -->
        <div>
                <input type="text" id="search" class="form-control" placeholder="Search Bengkel">
            </div>

        <!-- Table for Bengkel -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">Id</th>
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
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['bengkelID']}</td>
                                <td>{$row['bengkelID']}</td>
                                <td>{$row['nama_bengkel']}</td>
                                <td>{$row['emel_bengkel']}</td>
                                <td>{$row['lokasi']}</td>
                                <td>{$row['status']}</td>
                                <td>
                                    <a href='?action=approve&id={$row['bengkelID']}' class='btn btn-success btn-sm'>Approve</a>
                                    <a href='?action=reject&id={$row['bengkelID']}' class='btn btn-danger btn-sm'>Reject</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
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
</body>
</html>

<?php
$conn->close();
?>