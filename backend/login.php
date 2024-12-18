<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start a session
session_start();

// Include the database connection
include 'db.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve posted data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Username and password are required."]);
        exit();
    }

    // Prepare the SQL statement to prevent SQL injection
    $sql = "SELECT id, username, fname, manager FROM user WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, fetch their data
        $user = $result->fetch_assoc();

        // Save user data in the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fname'] = $user['fname'];
        $_SESSION['manager'] = $user['manager'];

        // Redirect the user to home.html
        header("Location: ../frontend/home.html");
        exit();
    } else {
        // Invalid login
        echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
        exit();
    }
} else {
    // Invalid request method
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}
?>