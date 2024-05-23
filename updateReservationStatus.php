<?php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$reservationId = $data['reservationId'];
$status = $data['status'];

$sql = "UPDATE reservations SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $reservationId);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
}
echo json_encode($response);
?>
