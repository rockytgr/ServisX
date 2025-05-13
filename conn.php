<?php
$servername = "localhost";
$username = "root";      // or your DB user
$password = "";          // or your DB password
$dbname = "servisx";     // your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
