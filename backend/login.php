
<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verify user credentials
    $stmt = $conn->prepare("SELECT id, fname, lname, manager FROM user WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fname'] = $user['fname'];
        $_SESSION['lname'] = $user['lname'];
        $_SESSION['manager'] = $user['manager'];

        // Redirect to the home page based on user role
        if ($user['manager'] == 1) {
            echo json_encode(['status' => 'success', 'redirect' => 'manager_home.html']);
        } else {
            echo json_encode(['status' => 'success', 'redirect' => 'home.html']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
    $stmt->close();
}
$conn->close();
?>
