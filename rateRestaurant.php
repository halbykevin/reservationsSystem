<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $_SESSION['user_id'];
    $restaurantId = $data['restaurantId'];
    $rating = $data['rating'];

    // Check if the user has already rated this restaurant
    $sql = "SELECT * FROM ratings WHERE user_id = ? AND restaurant_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $restaurantId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing rating
        $sql = "UPDATE ratings SET rating = ? WHERE user_id = ? AND restaurant_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $rating, $userId, $restaurantId);
    } else {
        // Insert new rating
        $sql = "INSERT INTO ratings (user_id, restaurant_id, rating) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $userId, $restaurantId, $rating);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
