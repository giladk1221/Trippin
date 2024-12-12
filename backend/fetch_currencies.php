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

// Query to fetch currencies
$sql = "SELECT currency FROM currency_rate";
$result = $conn->query($sql);

$currencies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $currencies[] = $row['currency'];
    }
}

echo json_encode($currencies);
?>