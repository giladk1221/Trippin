<?php
session_start();
header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

// Include the database connection
include 'db.php';

// Validate the input `trip_id`
$trip_id = $_GET['trip_id'] ?? null;
if (!$trip_id || !is_numeric($trip_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Missing or invalid trip_id"]);
    exit();
}

// Fetch flights for the given trip_id
$sql = "SELECT flight_number, airline, flight_date, origin_airport, origin_terminal, gate, 
        destination_airport, destination_terminal, scheduled_departure_time, scheduled_arrival 
        FROM flight WHERE trip_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Failed to prepare SQL statement"]);
    exit();
}

$stmt->bind_param("i", $trip_id);
$stmt->execute();
$result = $stmt->get_result();

$flights = [];
while ($row = $result->fetch_assoc()) {
    $flights[] = $row;
}

echo json_encode($flights);

$stmt->close();
$conn->close();
?>