<?php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$reservationId = $data['reservationId'];
$reason = $data['reason'];

$sql = "UPDATE reservations SET status = 'declined', decline_reason = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $reason, $reservationId);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
}
echo json_encode($response);
?>
