<?php
// Aviationstack API URL and Access Key
$apiUrl = "https://api.aviationstack.com/v1/airports?access_key=a24b30bc91d5e2b8de2873c3d3fcd50b&offset=5300";

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