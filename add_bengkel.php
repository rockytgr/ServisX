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

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $nama_bengkel = $_POST['nama_bengkel'];
    $emel_bengkel = $_POST['emel_bengkel'];
    
    // Fetch the last bengkelID from the database
    $sql = "SELECT bengkelID FROM bengkel ORDER BY bengkelID DESC LIMIT 1";
    $result = $conn->query($sql);
    $last_bengkelID = 'B000';  // Default value to handle the first record

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_bengkelID = $row['bengkelID'];
    }

    // Increment the last bengkelID to generate a new ID
    $last_bengkelID_number = substr($last_bengkelID, 1); // Remove the 'b' prefix
    $new_bengkelID_number = str_pad($last_bengkelID_number + 1, 3, "0", STR_PAD_LEFT); // Increment and pad with zeros
    $new_bengkelID = 'B' . $new_bengkelID_number; // New bengkelID with 'b' prefix

    // Default value for status_pengesahan is set to 'Pending' when adding a new bengkel
    $status_pengesahan = 'Confirmed';

    // Insert the bengkel into the database with the generated bengkelID
    $sql_insert = "INSERT INTO bengkel (bengkelID, nama_bengkel, emel_bengkel, status_pengesahan) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql_insert)) {
        // Bind the parameters
        $stmt->bind_param("ssss", $new_bengkelID, $nama_bengkel, $emel_bengkel, $status_pengesahan);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Insert the corresponding record into 'pengesahan' table with 'Pending' status
            $new_pengesahanID = "S" . str_pad(get_next_pengesahan_id($conn), 3, "0", STR_PAD_LEFT);  // Generate new pengesahanID
            
            $adminID = $_SESSION['adminID']; // Assume the admin is logged in
            $tarikh_pengesahan = date('Y-m-d H:i:s'); // Get the current timestamp for confirmation/rejection date
            
            $sql_insert_pengesahan = "INSERT INTO pengesahan (pengesahanID, bengkelID, adminID, status, tarikh_pengesahan) VALUES (?, ?, ?, ?, ?)";
            
            $stmt_pengesahan = $conn->prepare($sql_insert_pengesahan);
            $stmt_pengesahan->bind_param("sssss", $new_pengesahanID, $new_bengkelID, $adminID, $status_pengesahan, $tarikh_pengesahan);
            
            if ($stmt_pengesahan->execute()) {
                // Redirect back to the "Mengurus Bengkel" page after successful insertion
                header("Location: admin.php");
                exit();
            } else {
                // If there's an error with inserting the pengesahan
                echo "Error: " . $stmt_pengesahan->error;
            }
            $stmt_pengesahan->close();
        } else {
            // If there's an error with the bengkel insert
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // If there is an issue preparing the statement
        echo "Error preparing statement: " . $conn->error;
    }
}

// Function to get the next pengesahanID based on the latest entry in the database
function get_next_pengesahan_id($conn) {
    // Query to fetch the latest pengesahanID
    $sql = "SELECT MAX(CAST(SUBSTRING(pengesahanID, 2) AS UNSIGNED)) AS last_id FROM pengesahan";
    $result = $conn->query($sql);
    $last_id = 0;

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['last_id'];
    }
    
    return $last_id + 1;
}

// Close the connection
$conn->close();
?>
