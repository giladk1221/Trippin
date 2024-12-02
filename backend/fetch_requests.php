
<?php
include 'db_connection.php';
session_start();

$manager_id = $_SESSION['user_id'];

$query = "
SELECT trip.id, trip.destination, trip.start_date, trip.end_date, user.fname, user.lname 
FROM trip 
INNER JOIN user ON trip.user_id = user.id 
WHERE user.manager_id = ? AND trip.status = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
echo json_encode($requests);
?>
