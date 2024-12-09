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

// Get user_id from query string
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Missing user_id"]);
    exit();
}

// Fetch trips for the user
$sql = "SELECT destination, start_date, end_date FROM trip WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$trips = [];
while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}

echo json_encode($trips);
?>