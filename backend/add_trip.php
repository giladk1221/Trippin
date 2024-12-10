<?php
include 'db.php';
session_start(); // Start the session to access session variables

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user is logged in by verifying the session
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }

    // Debugging: Log incoming data
    file_put_contents('debug_log.txt', print_r($_POST, true));

    $user_id = $_SESSION['user_id']; // Retrieve user_id from session
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = 0; // Default value for new trips

    // Validate input data
    if (!isset($destination, $start_date, $end_date) || 
        empty(trim($destination)) || 
        empty(trim($start_date)) || 
        empty(trim($end_date))) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    // Insert new trip into the database
    $stmt = $conn->prepare("INSERT INTO trip (destination, start_date, end_date, user_id, status) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssii", $destination, $start_date, $end_date, $user_id, $status);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'trip_id' => $stmt->insert_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add trip']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database query failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>