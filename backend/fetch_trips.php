
<?php
include 'db_connection.php';
session_start();
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM trips WHERE user_id = ? AND status != 'closed'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$trips = [];
while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}
echo json_encode($trips);
?>
