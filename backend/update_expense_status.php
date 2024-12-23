<?php
header('Content-Type: application/json');

include 'db.php';

// Decode the JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['expense_id']) || !isset($data['status'])) {
    echo json_encode(["error" => "Invalid input"]);
    http_response_code(400);
    exit;
}

$expenseId = intval($data['expense_id']);
$status = intval($data['status']);



// Update the expense status
$stmt = $conn->prepare("UPDATE expense SET status = ? WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(["error" => "Database query failed"]);
    http_response_code(500);
    exit;
}

$stmt->bind_param("ii", $status, $expenseId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Expense status updated successfully"]);
    } else {
        error_log("No rows were updated. Expense ID: $expenseId, Status: $status");
        echo json_encode(["message" => "No rows were affected"]);
    }
} else {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(["error" => "Database update failed"]);
    http_response_code(500);
}

$stmt->close();
$conn->close();
?>
