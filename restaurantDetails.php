<?php
session_start();
require 'db.php';

$userId = $_GET['user_id'];
$sql = "SELECT * FROM restaurants WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

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
    <title>Restaurant Details</title>
    <link rel="stylesheet" href="stylesAdminIndex.css">
    <style>
        .restaurant-details-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .restaurant-details-box p {
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
    <h1>Restaurant Details</h1>
    <button class="back-button" onclick="window.history.back();">Back</button>
    <div class="container">
        <?php foreach ($restaurants as $restaurant): ?>
        <div class="restaurant-details-box">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($restaurant['name']); ?></p>
            <p><strong>Bio:</strong> <?php echo htmlspecialchars($restaurant['bio']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($restaurant['address']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($restaurant['phone']); ?></p>
            <p><strong>Location:</strong> <a href="<?php echo htmlspecialchars($restaurant['location']); ?>" target="_blank">View on Google Maps</a></p>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
