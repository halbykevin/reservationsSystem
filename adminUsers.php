<?php
session_start();
require 'db.php';

// Correcting the SQL query to match the columns in your table
$sql = "SELECT email, name, phone FROM users WHERE account_type = 'user'";
$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Accounts</title>
    <link rel="stylesheet" href="stylesAdminIndex.css">
    <style>
        .user-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .user-box p {
            margin: 5px 0;
        }
        .back-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            display: inline-block;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Users Accounts</h1>
    <button class="back-button" onclick="window.history.back();">Back</button>
    <div class="container">
        <?php foreach ($users as $user): ?>
        <div class="user-box">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    
</body>
</html>
