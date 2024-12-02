<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $user_id = $_POST['user_id'];

    // Insert new trip
    $stmt = $conn->prepare("INSERT INTO trip (destination, start_date, end_date, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $destination, $start_date, $end_date, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'trip_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add trip']);
    }
    $stmt->close();
}
$conn->close();
?>
