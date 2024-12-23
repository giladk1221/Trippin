<?php
header('Content-Type: application/json');

include 'db.php';

// Decode the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check for required fields
if (!isset($data['trip_id']) || !isset($data['status'])) {
    echo json_encode(["error" => "Invalid input"]);
    http_response_code(400);
    exit;
}

$tripId = intval($data['trip_id']);
$status = intval($data['status']);


// Update trip status
$stmt = $conn->prepare("UPDATE trip SET status = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare the SQL statement"]);
    http_response_code(500);
    exit;
}

$stmt->bind_param("ii", $status, $tripId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Trip status updated successfully"]);
    } else {
        echo json_encode(["message" => "No rows were updated. Check trip ID."]);
    }
} else {
    echo json_encode(["error" => "Failed to execute the SQL statement"]);
    http_response_code(500);
}

$stmt->close();
$conn->close();
?>
