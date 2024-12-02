<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trip_id = $_POST['trip_id'];
    $flight_number = $_POST['flight_number'];
    $airline = $_POST['airline'];
    $flight_date = $_POST['flight_date'];
    $origin_airport = $_POST['origin_airport'];
    $destination_airport = $_POST['destination_airport'];

    // Insert new flight
    $stmt = $conn->prepare("INSERT INTO flight (trip_id, flight_number, airline, flight_date, origin_airport, destination_airport) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $trip_id, $flight_number, $airline, $flight_date, $origin_airport, $destination_airport);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'flight_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add flight']);
    }
    $stmt->close();
}
$conn->close();
?>
