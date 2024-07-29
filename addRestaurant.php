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
    $capacity = $_POST['capacity'];
    $category = $_POST['category'];
    $user_id = $_SESSION['user_id'];

    // Handle file uploads for logo and images
    $logo = $_FILES['logo'];
    $logoPath = null;
    if ($logo['error'] == 0) {
        $logoPath = 'uploads/' . basename($logo['name']);
        move_uploaded_file($logo['tmp_name'], $logoPath);
    }

    $stmt = $conn->prepare("INSERT INTO restaurants (user_id, name, bio, address, phone, location, features, open_hours, capacity, category, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssisis", $user_id, $name, $bio, $address, $phone, $location, $features, $open_hours, $capacity, $category, $logoPath);
    $stmt->execute();
    $restaurant_id = $stmt->insert_id;

    // Handle multiple images upload
    foreach ($_FILES['restaurant_images']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['restaurant_images']['name'][$key];
        $file_tmp = $_FILES['restaurant_images']['tmp_name'][$key];
        $file_path = 'uploads/' . basename($file_name);
        move_uploaded_file($file_tmp, $file_path);

        $stmt = $conn->prepare("INSERT INTO restaurant_images (restaurant_id, image_path) VALUES (?, ?)");
        $stmt->bind_param("is", $restaurant_id, $file_path);
        $stmt->execute();
    }

    header('Location: myRestaurants.php');
}
?>
