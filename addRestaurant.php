<?php
session_start();

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $userId = $_SESSION['user_id'];
    
    $logo = $_FILES['logo']['name'];
    $target_file = null;
    
    if (!empty($logo)) {
        $target_dir = "uploads/";
        
        // Check if the directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true); // Create the directory if it doesn't exist
        }

        $target_file = $target_dir . basename($logo);
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            echo "Error uploading file.";
            exit();
        }
    }
    
    if ($target_file) {
        $sql = "INSERT INTO restaurants (name, bio, address, phone, location, logo, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $name, $bio, $address, $phone, $location, $target_file, $userId);
    } else {
        $sql = "INSERT INTO restaurants (name, bio, address, phone, location, user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $bio, $address, $phone, $location, $userId);
    }
    
    if ($stmt->execute()) {
        header("Location: indexR.html?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    header("Location: indexR.html");
}
?>
