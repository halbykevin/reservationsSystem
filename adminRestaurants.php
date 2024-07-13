<?php
session_start();
require 'db.php';

// Correcting the SQL query to match the columns in your table
$sql = "SELECT email, name, phone, id FROM users WHERE account_type = 'restaurant'";
$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

$restaurants = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $restaurants[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurants Accounts</title>
    <link rel="stylesheet" href="stylesAdminIndex.css">
    <style>
        .restaurant-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .restaurant-box p {
            margin: 5px 0;
        }
        .restaurant-box button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .restaurant-box button:hover {
            background-color: #0056b3;
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
    <h1>Restaurants Accounts</h1>
    <button class="back-button" onclick="window.history.back();">Back</button>
    <div class="container">
        <?php foreach ($restaurants as $restaurant): ?>
        <div class="restaurant-box">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($restaurant['email']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($restaurant['name']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($restaurant['phone']); ?></p>
            <button onclick="location.href='restaurantDetails.php?user_id=<?php echo $restaurant['id']; ?>'">Their Restaurants</button>
        </div>
        <?php endforeach; ?>
    </div>
    <button onclick="location.href='adminIndex.html'">Back</button>
</body>
</html>
