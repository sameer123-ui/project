<?php
// db.php
$servername = "localhost";
$username = "root";          // usually 'root' on localhost/XAMPP/LAMP
$password = "";              // usually empty password on localhost
$dbname = "car_rental";  // your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
