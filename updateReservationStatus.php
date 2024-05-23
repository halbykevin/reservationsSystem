<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reservationId = $_POST['reservationId'];
    $status = $_POST['status'];

    // Get the number of people for the reservation
    $sql = "SELECT user_id, num_people FROM reservations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();
    $userId = $reservation['user_id'];
    $numPeople = $reservation['num_people'];

    // Update the reservation status
    $sql = "UPDATE reservations SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $reservationId);

    if ($stmt->execute()) {
        if ($status == 'accepted') {
            // Calculate points
            $points = 5 * $numPeople;
            $sql = "UPDATE users SET points = points + ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $points, $userId);
            $stmt->execute();
        }
        // Move the reservation to history
        $sql = "INSERT INTO reservations_history SELECT * FROM reservations WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservationId);
        $stmt->execute();

        $sql = "DELETE FROM reservations WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservationId);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
