<?php
header('Content-Type: application/json');

// Include the database connection
include 'db.php';

// Validate the input `user_id`
$user_id = $_GET['user_id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Missing or invalid user_id"]);
    exit();
}

// Fetch trips for the given user_id with status = 1 or 2
$sql = "SELECT id, destination, start_date, end_date, status 
        FROM trip 
        WHERE user_id = ? AND status IN (1, 2)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Failed to prepare SQL statement"]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$trips = [];
while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}

echo json_encode($trips);

$stmt->close();
$conn->close();
?>