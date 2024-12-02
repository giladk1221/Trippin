
<?php
$servername = "localhost:3306";
$username = "benzd_benzd";
$password = "cHOvGAZhnExW";
$dbname = "benzd_trippin";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
