<?php
// edit_expense.php

header('Content-Type: application/json');

// Database connection (update with your credentials)
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['expense_id'], $input['reason'], $input['amount'], $input['date'], $input['currency'])) {
        $expense_id = $input['expense_id'];
        $reason = $input['reason'];
        $amount = $input['amount'];
        $date = $input['date'];
        $currency = $input['currency'];

        // Update the expense (update the query based on your database structure)
        $query = "UPDATE expenses SET reason = ?, amount = ?, date = ?, currency = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdssi", $reason, $amount, $date, $currency, $expense_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Expense updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'error' => 'Failed to update expense. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Invalid or incomplete data.']);
    }
} else {
    echo json_encode(['status' => 'error', 'error' => 'Invalid request method.']);
}

$conn->close();
?>