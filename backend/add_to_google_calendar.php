<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function createGoogleCalendarEvent($flight) {
    // Initialize the Google Client
    $client = new Client();
    $client->setAuthConfig('credentials.json'); 
    $client->addScope(Calendar::CALENDAR);

    $service = new Calendar($client);

    // Extract flight details
    $summary = "Flight: " . $flight['flight_number'] . " - " . $flight['airline'];
    $description = "Origin: " . $flight['origin_airport'] . " (Terminal " . ($flight['origin_terminal'] ?? 'N/A') . ")\n" .
                   "Destination: " . $flight['destination_airport'] . " (Terminal " . ($flight['destination_terminal'] ?? 'N/A') . ")\n" .
                   "Gate: " . ($flight['gate'] ?? 'N/A') . "\n" .
                   "Scheduled Time: " . $flight['scheduled_departure_time'];

    $event_date = $flight['flight_date']; // Use flight_date for the event date

    // Create a new Google Calendar event
    $event = new Calendar\Event([
        'summary' => $summary,
        'description' => $description,
        'start' => [
            'date' => $event_date,
            'timeZone' => 'Asia/Jerusalem',
        ],
        'end' => [
            'date' => $event_date,
            'timeZone' => 'Asia/Jerusalem',
        ],
    ]);

    $calendar_id = "20251w89@gmail.com"; 

    try {
        $createdEvent = $service->events->insert($calendar_id, $event);
        return [
            "status" => "success",
            "message" => "Event created successfully",
            "link" => $createdEvent->htmlLink,
        ];
    } catch (Exception $e) {
        return [
            "status" => "error",
            "message" => "Error creating event: " . $e->getMessage(),
        ];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Decode JSON payload sent from the frontend
    $rawData = file_get_contents('php://input');
    $flight = json_decode($rawData, true);

    // Validate required fields
    $requiredFields = ['flight_number', 'airline', 'origin_airport', 'destination_airport', 'flight_date'];
    foreach ($requiredFields as $field) {
        if (empty($flight[$field])) {
            http_response_code(400); // Bad Request
            echo json_encode([
                "status" => "error",
                "message" => "Missing required field: $field",
            ]);
            exit();
        }
    }

    // Call the function to create the Google Calendar event
    $result = createGoogleCalendarEvent($flight);

    // Send response back to the frontend
    echo json_encode($result);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method",
    ]);
}
?>