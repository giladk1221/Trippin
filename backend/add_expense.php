<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trip_id = $_POST['trip_id'];
    $reason = $_POST['reason'];
    $amount = $_POST['amount'];
    $currency = $_POST['currency'];
    $date = $_POST['date'];

    // Insert new expense
    $stmt = $conn->prepare("INSERT INTO expense (trip_id, reason, amount, currency, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $trip_id, $reason, $amount, $currency, $date);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'expense_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add expense']);
    }
    $stmt->close();
}
$conn->close();
?>
