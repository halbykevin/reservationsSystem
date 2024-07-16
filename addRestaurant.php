<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $features = $_POST['features'];
    $open_hours = $_POST['open_hours'];
    $category = $_POST['category']; // Get the category from the form
    $userId = $_SESSION['user_id'];

    $logo = $_FILES['logo']['name'];
    $target_file = null;

    if (!empty($logo)) {
        $target_dir = "uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $target_file = $target_dir . basename($logo);
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            echo "Error uploading file.";
            exit();
        }
    }

    // Ensure the logo path is correctly stored
    $logoPath = $target_file ? $target_file : null;
    $sql = "INSERT INTO restaurants (user_id, name, bio, address, phone, location, features, open_hours, logo, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssss", $userId, $name, $bio, $address, $phone, $location, $features, $open_hours, $logoPath, $category);

    if ($stmt->execute()) {
        $restaurantId = $stmt->insert_id;

        // Handle multiple images upload
        $imageCount = count($_FILES['restaurant_images']['name']);
        for ($i = 0; $i < $imageCount; $i++) {
            $imagePath = $_FILES['restaurant_images']['name'][$i];
            $target_file = $target_dir . basename($imagePath);
            if (move_uploaded_file($_FILES['restaurant_images']['tmp_name'][$i], $target_file)) {
                $sql = "INSERT INTO restaurant_images (restaurant_id, image_path) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $restaurantId, $target_file);
                $stmt->execute();
            } else {
                echo "Error uploading image: " . $_FILES['restaurant_images']['name'][$i];
            }
        }

        header("Location: indexR.html");
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    header("Location: indexR.html");
}
?>
