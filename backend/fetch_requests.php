<?php
header('Content-Type: application/json');
include 'db.php';

// Get manager_id from the query string
$manager_id = $_GET['manager_id'] ?? null;

if (!$manager_id) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT t.id, t.destination, t.start_date, t.end_date, u.fname, u.lname 
        FROM trip t 
        INNER JOIN user u ON t.user_id = u.id 
        WHERE u.manager_id = ? AND t.status = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

echo json_encode($requests);
?>
