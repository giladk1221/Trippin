<?php
// fetch_expense_details.php

header('Content-Type: application/json');

// Database connection (update with your credentials)
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $expense_id = $_GET['id'];

        // Query to fetch expense details
        $query = "SELECT id, reason, amount, date, currency FROM expense WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $expense_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $expense = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $expense]);
            } else {
                echo json_encode(['status' => 'error', 'error' => 'Expense not found.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'error' => 'Failed to fetch expense details.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Invalid expense ID.']);
    }
} else {
    echo json_encode(['status' => 'error', 'error' => 'Invalid request method.']);
}

$conn->close();
?>