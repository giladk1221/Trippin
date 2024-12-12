<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

include 'db.php';

$trip_id = $_GET['trip_id'] ?? null;
if (!$trip_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing trip_id"]);
    exit();
}

$sql = "SELECT reason, amount, date, currency FROM expense WHERE trip_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$result = $stmt->get_result();

$expenses = [];
while ($row = $result->fetch_assoc()) {
    $expenses[] = $row;
}

echo json_encode($expenses);
?>