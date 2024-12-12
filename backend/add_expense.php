<?php
session_start();
header('Content-Type: application/json');

// Initialize error collection
$errors = [];

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

// Fetch trip_id directly from POST
$trip_id = $_POST['trip_id'] ?? null;
if (!$trip_id) {
    $errors[] = "Missing trip ID.";
}

// Fetch other form inputs
$reason = $_POST['reason'] ?? null;
$amount = $_POST['amount'] ?? null;
$date = $_POST['date'] ?? null;
$currency = $_POST['currency'] ?? null;

// Validate inputs
if (!$reason || strlen($reason) > 30) {
    $errors[] = "Invalid reason. Maximum length is 30 characters.";
}

if (!$amount || !is_numeric($amount)) {
    $errors[] = "Invalid amount. It must be a number.";
}

if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $errors[] = "Invalid date format. Use YYYY-MM-DD.";
}

if (!$currency || strlen($currency) > 10) {
    $errors[] = "Invalid currency. Maximum length is 10 characters.";
}

// Validate file upload
$receiptFileContent = null;
if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['receipt']['tmp_name'];
    $receiptFileContent = file_get_contents($fileTmpPath);

    if ($receiptFileContent === false) {
        $errors[] = "Failed to read the receipt file content.";
    }
} else {
    $errors[] = "Receipt file is required.";
}

// If there are validation errors, return them and stop further execution
if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(["errors" => $errors]);
    exit();
}

// Database Connection
include 'db.php';

if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Insert expense into the database
$status = 0; // Hardcoded status
$sql = "INSERT INTO expense (trip_id, reason, amount, currency, date, receipt_file, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "error" => "Failed to prepare SQL statement",
        "debug_sql_error" => $conn->error,
    ]);
    exit();
}

// Bind parameters
$stmt->bind_param("isdsssi", $trip_id, $reason, $amount, $currency, $date, $receiptFileContent, $status);

// Execute the query and handle the response
if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "debug_receipt_size" => strlen($receiptFileContent),
    ]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "error" => "Failed to execute SQL statement",
        "debug_stmt_error" => $stmt->error,
    ]);
}

$stmt->close();
$conn->close();
exit();