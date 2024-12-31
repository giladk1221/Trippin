<?php
// delete_flight.php

header('Content-Type: application/json');

// Database connection (update with your credentials)
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['flight_id']) && is_numeric($input['flight_id'])) {
        $flight_id = $input['flight_id'];

        // Delete the flight (update the query based on your database structure)
        $query = "DELETE FROM flights WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $flight_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Flight deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'error' => 'Failed to delete flight. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Invalid flight ID.']);
    }
} else {
    echo json_encode(['status' => 'error', 'error' => 'Invalid request method.']);
}

$conn->close();
?>