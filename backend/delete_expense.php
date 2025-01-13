<?php
// delete_expense.php

header('Content-Type: application/json');

// Database connection (update with your credentials)
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if "id" is provided and is numeric
    if (isset($input['id']) && is_numeric($input['id'])) {
        $expense_id = $input['id']; // Use "id" instead of "expense_id"

        // Correct DELETE query with full table and column name
        $query = "DELETE FROM expense WHERE id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("i", $expense_id);

            // Execute the prepared statement
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Expense deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'error' => 'Failed to delete expense. Please try again.']);
            }

            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'error' => 'Failed to prepare the statement.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Invalid expense ID.']);
    }
} else {
    echo json_encode(['status' => 'error', 'error' => 'Invalid request method.']);
}

$conn->close();
?>