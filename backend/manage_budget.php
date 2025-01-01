<?php
session_start();
include 'db.php'; // Ensure the database connection is included

// Check if the request is an AJAX call
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Handle AJAX JSON requests
if ($isAjax) {
    $requestBody = file_get_contents('php://input');
    $data = json_decode($requestBody, true);

    if (isset($data['action']) && $data['action'] === 'delete') {
        $country = $data['country'] ?? null;

        if (!$country) {
            echo json_encode(["status" => "error", "message" => "Invalid country."]);
            exit();
        }

        // Prepare and execute the delete query
        $query = $conn->prepare("DELETE FROM destination WHERE country = ?");
        $query->bind_param("s", $country);

        if ($query->execute()) {
            echo json_encode(["status" => "success", "message" => "Budget deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete budget."]);
        }

        $query->close();
        $conn->close();
        exit();
    }

    echo json_encode(["status" => "error", "message" => "Invalid AJAX request."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = $_POST['destination'] ?? null;
    $daily_budget = $_POST['budgetAmount'] ?? null;
    $last_updated_by = $_SESSION['user_id'] ?? null;

    if (!$country || !$daily_budget || !is_numeric($daily_budget)) {
        if ($isAjax) {
            echo json_encode(["status" => "error", "message" => "Invalid input."]);
        } else {
            header("Location: ../frontend/manage_budget.html");
        }
        exit();
    }

    $query = $conn->prepare("INSERT INTO destination (country, daily_budget, last_updated_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE daily_budget = ?, last_updated_by = ?, last_update_time = NOW()");
    $query->bind_param("sisis", $country, $daily_budget, $last_updated_by, $daily_budget, $last_updated_by);

    if ($query->execute()) {
        if ($isAjax) {
            echo json_encode(["status" => "success", "message" => "Budget saved successfully."]);
        } else {
            header("Location: ../frontend/manage_budget.html");
        }
    } else {
        if ($isAjax) {
            echo json_encode(["status" => "error", "message" => "Failed to save budget."]);
        } else {
            header("Location: ../frontend/manage_budget.html");
        }
    }

    $query->close();
    $conn->close();
    exit();
}

// Invalid request
if ($isAjax) {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
} else {
    header("Location: ../frontend/manage_budget.html");
}
?>
