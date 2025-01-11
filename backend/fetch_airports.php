<?php
// Aviationstack API URL and Access Key
$apiUrl = "https://api.aviationstack.com/v1/airports?access_key=6a37eaee7512abd3d4be0f17821a5a47&offset=7000";

// Use file_get_contents to fetch the API response
$response = file_get_contents($apiUrl);

if ($response === false) {
    // If fetching fails, return an error response
    http_response_code(500);
    echo json_encode(['error' => 'Unable to fetch airport data']);
    exit;
}

// Send the API response back to the frontend as JSON
header('Content-Type: application/json');
echo $response;
?>