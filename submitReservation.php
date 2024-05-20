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

    $sql = "INSERT INTO reservations (user_id, restaurant_id, full_name, birthdate, reservation_date, reservation_time, seating, special_requests, phone, num_people, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssssi", $userId, $restaurantId, $fullName, $birthdate, $reservationDate, $reservationTime, $seating, $specialRequests, $phone, $numPeople);

    if ($stmt->execute()) {
        header("Location: index.html?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
