<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'restaurant') {
    header("Location: login.php");
    exit();
}

require 'db.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM restaurants WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$restaurants = [];
while ($row = $result->fetch_assoc()) {
    $restaurants[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Restaurants</title>
    <link rel="stylesheet" href="stylesIndexR.css">
</head>
<body>
    <div class="button-container">
        <button onclick="location.href='logout.php'">Logout</button>
        <button onclick="location.href='indexR.html'">Home</button>
    </div>
    <div class="container">
        <h1>My Restaurants</h1>
        <?php if (count($restaurants) > 0): ?>
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="restaurant-box">
                    <h2><?php echo htmlspecialchars($restaurant['name']); ?></h2>
                    <p><?php echo htmlspecialchars($restaurant['bio']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($restaurant['address']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($restaurant['phone']); ?></p>
                    <p><strong>Location:</strong> <a href="<?php echo htmlspecialchars($restaurant['location']); ?>" target="_blank">View on Google Maps</a></p>
                    <?php if ($restaurant['logo']): ?>
                        <img src="<?php echo htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" style="width: 100px; height: 100px;">
                    <?php endif; ?>
                    <iframe src="<?php echo htmlspecialchars($restaurant['location']); ?>" width="200" height="150" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You have not added any restaurants yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
