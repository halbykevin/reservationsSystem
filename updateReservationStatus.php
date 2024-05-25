<?php
session_start();
require 'db.php';

// Set the header for JSON response
header('Content-Type: application/json');

// Turn off error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'errors.log'); // Ensure this file is writable

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['reservationId']) || !isset($input['status'])) {
        $response['error'] = 'Invalid input';
        echo json_encode($response);
        exit;
    }

    $reservationId = $input['reservationId'];
    $status = $input['status'];

    // Get the number of people for the reservation
    $sql = "SELECT user_id, num_people FROM reservations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response['error'] = 'Prepare statement failed: ' . $conn->error;
        echo json_encode($response);
        exit;
    }
    $stmt->bind_param("i", $reservationId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $response['error'] = 'Reservation not found';
        echo json_encode($response);
        exit;
    }
    $reservation = $result->fetch_assoc();
    $userId = $reservation['user_id'];
    $numPeople = $reservation['num_people'];

    // Update the reservation status
    $sql = "UPDATE reservations SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response['error'] = 'Prepare statement failed: ' . $conn->error;
        echo json_encode($response);
        exit;
    }
    $stmt->bind_param("si", $status, $reservationId);

    if ($stmt->execute()) {
        if ($status == 'accepted') {
            // Calculate points
            $points = 5 * $numPeople;
            $sql = "UPDATE users SET points = points + ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $response['error'] = 'Prepare statement failed: ' . $conn->error;
                echo json_encode($response);
                exit;
            }
            $stmt->bind_param("ii", $points, $userId);
            $stmt->execute();
        }
        // Move the reservation to history
        $sql = "INSERT INTO reservations_history SELECT * FROM reservations WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $response['error'] = 'Prepare statement failed: ' . $conn->error;
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("i", $reservationId);
        $stmt->execute();

        $sql = "DELETE FROM reservations WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $response['error'] = 'Prepare statement failed: ' . $conn->error;
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("i", $reservationId);
        $stmt->execute();

        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to update reservation status: ' . $stmt->error;
    }
}

echo json_encode($response);
?>
