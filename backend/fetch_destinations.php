
<?php
include 'db_connection.php';

$query = "
SELECT destination.country, destination.daily_budget, destination.last_update_time, 
user.fname, user.lname 
FROM destination 
INNER JOIN user ON destination.last_updated_by = user.id";
$result = $conn->query($query);

$destinations = [];
while ($row = $result->fetch_assoc()) {
    $destinations[] = $row;
}
echo json_encode($destinations);
?>
