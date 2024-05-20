<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$restaurantId = $data['restaurantId'];
$userId = $_SESSION['user_id'];

$sql = "INSERT INTO liked_restaurants (user_id, restaurant_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $restaurantId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
