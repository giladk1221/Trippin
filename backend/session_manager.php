<?php
session_start();

// Check if session variables are set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    http_response_code(401); // Unauthorized
    exit();
}

// Ensure no extra output is sent before JSON
header('Content-Type: application/json');
echo json_encode([
    "user_id" => $_SESSION['user_id'],
    "username" => $_SESSION['username'],
    "fname" => $_SESSION['fname'],
    "manager" => $_SESSION['manager']
]);
?>