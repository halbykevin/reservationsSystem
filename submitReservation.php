<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = $_POST['fullName'];
    $birthdate = $_POST['birthdate'];
    $reservationDate = $_POST['reservationDate'];
    $reservationTime = $_POST['reservationTime'];
    $seating = $_POST['seating'];
    $specialRequests = $_POST['specialRequests'];
    $phone = $_POST['phone'];
    $numPeople = $_POST['numPeople'];
    $restaurantId = $_POST['restaurantId'];
    $userId = $_SESSION['user_id'];

    // Check if the restaurant has capacity at the given time
    $endTime = date("H:i", strtotime($reservationTime) + 75 * 60);

    $stmt = $conn->prepare("SELECT capacity FROM restaurants WHERE id = ?");
    $stmt->bind_param("i", $restaurantId);
    $stmt->execute();
    $result = $stmt->get_result();
    $restaurant = $result->fetch_assoc();
    $capacity = $restaurant['capacity'];

    $stmt = $conn->prepare("SELECT SUM(num_people) AS total_people FROM reservations WHERE restaurant_id = ? AND reservation_date = ? AND ((reservation_time <= ? AND ? < ADDTIME(reservation_time, '01:15:00')) OR (reservation_time <= ? AND ? < ADDTIME(reservation_time, '01:15:00')))");
    $stmt->bind_param("isssss", $restaurantId, $reservationDate, $reservationTime, $reservationTime, $endTime, $endTime);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalPeople = $row['total_people'] ?? 0;

    if (($totalPeople + $numPeople) <= $capacity) {
        $sql = "INSERT INTO reservations (user_id, restaurant_id, full_name, birthdate, reservation_date, reservation_time, seating, special_requests, phone, num_people, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssssi", $userId, $restaurantId, $fullName, $birthdate, $reservationDate, $reservationTime, $seating, $specialRequests, $phone, $numPeople);

        if ($stmt->execute()) {
            // Increment user's points after successful reservation
            $sql = "UPDATE users SET points = points + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            header("Location: index.php?success=1");
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        header("Location: reserveForm.html?error=full_capacity&restaurantId=$restaurantId&reservationDate=$reservationDate");
    }
}
?>
