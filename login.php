<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurant_reservations";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_name = $_POST['email_or_name'];
    $password = $_POST['password'];
    
    if ($email_or_name == 'halbykevin@hotmail.com' && $password == 'admin') {
        $_SESSION['user_id'] = 'admin';
        $_SESSION['user_name'] = 'Admin';
        header("Location: adminIndex.php");
        exit();
    }
    
    // Check if the input is an email or a name
    if (filter_var($email_or_name, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email = ?";
    } else {
        $sql = "SELECT * FROM users WHERE name = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email_or_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); // Only call get_result() once
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['account_type'] = $row['account_type'];
            if ($row['account_type'] == 'restaurant') {
                header("Location: indexR.html");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "No user found with that email or name.";
    }
    
    if (isset($error)) {
        header("Location: login.html?error=" . urlencode($error));
        exit();
    }
}

$conn->close();
?>
