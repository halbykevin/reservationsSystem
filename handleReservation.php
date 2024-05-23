<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $reservationId = $data['id'];
    $action = $data['action'];
    $email = $data['email'];
    $message = isset($data['message']) ? $data['message'] : '';

    $status = ($action == 'approve') ? 'Accepted' : 'Declined';

    // Update reservation status
    $sql = "UPDATE reservations SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $reservationId);

    if ($stmt->execute()) {
        if ($action == 'approve') {
            $subject = "Reservation Approved";
            $body = "Hello, your reservation is accepted.";
        } else {
            $subject = "Reservation Declined";
            $body = $message;
        }

        // Send email
        $headers = "From: your-email@example.com";
        if (mail($email, $subject, $body, $headers)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send email']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}
?>
