<?php
// submit_trip.php

header('Content-Type: application/json');

// Database connection (update with your credentials)
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['trip_id']) && is_numeric($input['trip_id'])) {
        $trip_id = $input['trip_id'];

        // Mark the trip as submitted (update the query based on your database structure)
        $query = "UPDATE trip SET status = 1 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $trip_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Trip submitted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'error' => 'Failed to submit trip. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Invalid trip ID.']);
    }
} else {
    echo json_encode(['status' => 'error', 'error' => 'Invalid request method.']);
}

$conn->close();
?>