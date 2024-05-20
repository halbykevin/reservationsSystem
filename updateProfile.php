<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $oldPassword = $_POST['oldPassword'];
    $profilePicture = null;

    // Check if profile picture is uploaded
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        $profilePicture = 'uploads/' . basename($_FILES['profilePicture']['name']);
        move_uploaded_file($_FILES['profilePicture']['tmp_name'], $profilePicture);
    }

    // Verify old password
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (password_verify($oldPassword, $user['password'])) {
        // Update user information
        $sql = "UPDATE users SET email = ?, phone = ?" . ($profilePicture ? ", profile_picture = ?" : "") . " WHERE id = ?";
        if ($profilePicture) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $email, $phone, $profilePicture, $userId);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $email, $phone, $userId);
        }
        $stmt->execute();

        header("Location: index.html?profile_updated=1");
    } else {
        echo "Invalid old password.";
    }
}
?>
