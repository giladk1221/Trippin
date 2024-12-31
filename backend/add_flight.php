<?php
session_start();
header('Content-Type: application/json');

// Include the database connection
include 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

// Fetch flight data from the API
function fetch_flight_data($iata_code, $flight_number, $date) {
    $api_url = "https://api.aviationstack.com/v1/flightsFuture";
    $access_key = "6cb56e840641ee6de62858e45399d04d";

    // Construct the API request URL
    $params = [
        'access_key' => $access_key,
        'iataCode' => $iata_code,
        'type' => 'departure',
        'date' => $date
    ];
    $query_string = http_build_query($params);
    $full_url = $api_url . '?' . $query_string;

    // Fetch data using file_get_contents
    $response = file_get_contents($full_url);

    if ($response === FALSE) {
        return null; // Error fetching data
    }

    // Decode the JSON response
    $data = json_decode($response, true);

    if (!$data || empty($data['data'])) {
        return null; // No data found
    }

    return $data['data'];
}

// Extract relevant details for the flight
function get_flight_details($flight_data, $flight_number) {
    foreach ($flight_data as $flight) {
        if ($flight['flight']['number'] == $flight_number) {
            return [
                'airline' => $flight['airline']['name'] ?? '',
                'departure' => [
                    'terminal' => $flight['departure']['terminal'] ?? null,
                    'gate' => $flight['departure']['gate'] ?? null,
                    'scheduledTime' => $flight['departure']['scheduledTime'] ?? null
                ],
                'arrival' => [
                    'iataCode' => $flight['arrival']['iataCode'] ?? null,
                    'terminal' => $flight['arrival']['terminal'] ?? null,
                    'scheduledTime' => $flight['arrival']['scheduledTime'] ?? null
                ]
            ];
        }
    }
    return null; // No matching flight found
}

// Process POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate POST inputs
    $trip_id = $_POST['trip_id'] ?? null;
    $iata_code = $_POST['iata_code'] ?? null;
    $flight_number = $_POST['flight_number'] ?? null;
    $date = $_POST['date'] ?? null;

    if (!$trip_id || !$iata_code || !$flight_number || !$date) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Missing required fields"]);
        exit();
    }

    // Fetch flight data
    $flight_data = fetch_flight_data($iata_code, $flight_number, $date);

    if (!$flight_data) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch flight data from API"]);
        exit();
    }

    // Get flight details
    $flight_details = get_flight_details($flight_data, $flight_number);

    if (!$flight_details) {
        http_response_code(404);
        echo json_encode(["error" => "No matching flight found"]);
        exit();
    }

    // Debug the fetched flight details
    error_log("Flight Details: " . print_r($flight_details, true));

    // Insert flight into the database
    $sql = "INSERT INTO flight (trip_id, flight_number, airline, flight_date, origin_airport, origin_terminal, gate, destination_airport, destination_terminal, scheduled_departure_time, scheduled_arrival) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to prepare SQL statement",
            "debug_sql_error" => $conn->error, // Log database error
        ]);
        exit();
    }

    // Extract details from the API response
    $airline = $flight_details['airline'];
    $origin_terminal = $flight_details['departure']['terminal'];
    $gate = $flight_details['departure']['gate'];
    $scheduled_departure_time = $flight_details['departure']['scheduledTime'];
    $destination_airport = $flight_details['arrival']['iataCode'];
    $destination_terminal = $flight_details['arrival']['terminal'];
    $scheduled_arrival = $flight_details['arrival']['scheduledTime'];

    // Debug the values being inserted
    error_log("Inserting values: trip_id=$trip_id, flight_number=$flight_number, airline=$airline, flight_date=$date, origin_airport=$iata_code, origin_terminal=$origin_terminal, gate=$gate, destination_airport=$destination_airport, destination_terminal=$destination_terminal, scheduled_departure_time=$scheduled_departure_time, scheduled_arrival=$scheduled_arrival");

    // Bind parameters
    $stmt->bind_param(
        "issssssssss",
        $trip_id,
        $flight_number,
        $airline,
        $date,
        $iata_code,
        $origin_terminal,
        $gate,
        $destination_airport,
        $destination_terminal,
        $scheduled_departure_time,
        $scheduled_arrival
    );

    // Execute query and handle errors
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Flight added successfully"]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to execute SQL statement",
            "debug_stmt_error" => $stmt->error, // Log error details
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Invalid request method"]);
}