<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

include 'db.php';

// Query to fetch unique destinations
$sql = "SELECT DISTINCT country FROM destination"; 
$result = $conn->query($sql);

$destinations = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $destinations[] = $row['country']; 
    }
}

echo json_encode($destinations);
?>