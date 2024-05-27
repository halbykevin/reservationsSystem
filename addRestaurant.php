<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $features = $_POST['features']; // Add this line to capture features
    $open_hours = $_POST['open_hours']; // Add this line to capture open hours
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

    // Insert restaurant data into the database
    $sql = "INSERT INTO restaurants (user_id, name, bio, address, phone, location, features, open_hours, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $userId, $name, $bio, $address, $phone, $location, $features, $open_hours, $logo);

    if ($stmt->execute()) {
        header("Location: indexR.html");
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    header("Location: indexR.html");
}
?>
