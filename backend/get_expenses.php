<?php
header('Content-Type: application/json');

// Database connection
include 'db.php'; // Assumes `db.php` contains your database connection logic

// Get parameters from the query string
$username = $_GET['username'] ?? null;
$apiKey = $_GET['api_key'] ?? null; // Fetch API key from query parameter
$month = $_GET['month'] ?? null;   // Optional parameter for filtering month
$year = $_GET['year'] ?? null;     // Optional parameter for filtering year

// Validate API key
if (!$apiKey) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Missing API key"]);
    exit();
}

try {
    // Validate the API key in the database
    $apiKeyQuery = "SELECT id FROM api_key WHERE id = ? ";
    $stmt = $conn->prepare($apiKeyQuery);
    $stmt->bind_param("s", $apiKey);
    $stmt->execute();
    $apiKeyResult = $stmt->get_result();

    if ($apiKeyResult->num_rows === 0) {
        http_response_code(401); // Unauthorized
        echo json_encode(["error" => "Invalid API key"]);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "An error occurred during API key validation", "details" => $e->getMessage()]);
    exit();
}

// Validate username
if (!$username) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Missing username"]);
    exit();
}

try {
    // Base SQL query
    $sql = "
        SELECT 
            expense.reason,
            expense.amount,
            expense.currency,
            expense.date,
            CONCAT(user.fname, ' ', user.lname) AS full_name
        FROM 
            expense
        INNER JOIN 
            trip ON expense.trip_id = trip.id
        INNER JOIN 
            user ON trip.user_id = user.id
        WHERE 
            user.username = ?
    ";

    // Add filtering for month and year if provided
    if ($month && $year) {
        $sql .= " AND MONTH(expense.date) = ? AND YEAR(expense.date) = ?";
    } elseif ($month) {
        $sql .= " AND MONTH(expense.date) = ?";
    } elseif ($year) {
        $sql .= " AND YEAR(expense.date) = ?";
    }

    // Prepare the query
    $stmt = $conn->prepare($sql);

    // Bind parameters dynamically
    if ($month && $year) {
        $stmt->bind_param("sii", $username, $month, $year);
    } elseif ($month) {
        $stmt->bind_param("si", $username, $month);
    } elseif ($year) {
        $stmt->bind_param("si", $username, $year);
    } else {
        $stmt->bind_param("s", $username);
    }

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }

    // Return the results as JSON
    echo json_encode(["status" => "success", "data" => $expenses]);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "An error occurred", "details" => $e->getMessage()]);
}