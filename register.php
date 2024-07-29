<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $birthdate = $_POST['birthdate'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Plain text password
    $accountType = $_POST['account_type']; // assuming you have a field for account type in the form

    // Check if email already exists
    $checkEmailSql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkEmailSql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email already exists
        $_SESSION['error'] = 'This email is already registered.';
        header("Location: register.html");
        exit();
    } else {
        // Email does not exist, proceed to insert
        $sql = "INSERT INTO users (name, birthdate, phone, email, password, account_type) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $birthdate, $phone, $email, $password, $accountType);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Registration successful. You can now login.';
            header("Location: login.php");
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>
