<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT points FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$points = $user['points'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Points</title>
    <link rel="stylesheet" href="stylesIndex.css">
</head>
<body>
    <div class="button-container">
        <button onclick="location.href='index.html'">Home</button>
        <button onclick="location.href='discover.php'">Discover</button>
        <button onclick="location.href='liked.php'">Liked</button>
        <button onclick="logout()">Logout</button>
    </div>
    <div class="container">
        <h1>My Points</h1>
        <p>You have <strong><?php echo $points; ?></strong> points.</p>
    </div>
    <script>
        function logout() {
            location.href = "logout.php";
        }
    </script>
</body>
</html>
