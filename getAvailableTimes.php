<?php
require 'db.php';

$restaurantId = $_GET['restaurantId'];
$date = $_GET['date'];

// Fetch the restaurant's capacity
$sql = "SELECT capacity FROM restaurants WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $restaurantId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(['error' => 'Restaurant not found']);
    exit;
}
$restaurant = $result->fetch_assoc();
$capacity = $restaurant['capacity'];

// Fetch reservations for the selected date
$sql = "SELECT reservation_time, num_people FROM reservations WHERE restaurant_id = ? AND reservation_date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $restaurantId, $date);
$stmt->execute();
$result = $stmt->get_result();
$reservations = [];
while ($row = $result->fetch_assoc()) {
    $start_time = strtotime($row['reservation_time']);
    $end_time = strtotime('+75 minutes', $start_time);
    $reservations[] = ['start_time' => $start_time, 'end_time' => $end_time, 'num_people' => $row['num_people']];
}

// Generate available times
$start_time = strtotime('9:00 AM');
$end_time = strtotime('11:59 PM');
$available_times = [];

for ($current_time = $start_time; $current_time <= $end_time; $current_time = strtotime('+15 minutes', $current_time)) {
    $formatted_time = date('g:i A', $current_time); // 12-hour format with AM/PM
    $total_reserved = 0;
    $is_available = true;

    foreach ($reservations as $reservation) {
        if ($current_time >= $reservation['start_time'] && $current_time < $reservation['end_time']) {
            $total_reserved += $reservation['num_people'];
            if ($total_reserved >= $capacity) {
                $is_available = false;
                break;
            }
        }
    }

    $available_times[] = ['time' => $formatted_time, 'available' => $is_available];
}

echo json_encode($available_times);
?>
