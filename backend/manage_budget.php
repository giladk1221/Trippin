<?php
include 'db.php';

// Start a session
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country = $_POST['destination'];
    $daily_budget = $_POST['budgetAmount'];
    $last_updated_by = $_SESSION['user_id'];

    // Update or insert budget for the destination
    $stmt = $conn->prepare("INSERT INTO destination (country, daily_budget, last_updated_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE daily_budget = ?, last_updated_by = ?");
    $stmt->bind_param("sisis", $country, $daily_budget, $last_updated_by, $daily_budget, $last_updated_by);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update budget']);
    }
    $stmt->close();
}
$conn->close();
?>
