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

$query = "SELECT destination.country, destination.daily_budget, CAST(destination.last_update_time AS DATE) AS last_update_time, 
user.fname, user.lname 
FROM destination 
INNER JOIN user ON destination.last_updated_by = user.id
ORDER BY 1";
$result = $conn->query($query);

$destinations = [];
while ($row = $result->fetch_assoc()) {
    $destinations[] = $row;
}

echo json_encode($destinations);
?>