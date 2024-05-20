<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT restaurants.* FROM liked_restaurants JOIN restaurants ON liked_restaurants.restaurant_id = restaurants.id WHERE liked_restaurants.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$likedRestaurants = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $likedRestaurants[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liked Restaurants</title>
    <link rel="stylesheet" href="stylesDiscover.css">
</head>
<body>
    <div class="button-container">
        <button onclick="location.href='index.html'">Home</button>
        <button onclick="location.href='discover.php'">Discover</button>
        <button onclick="location.href='liked.php'">Liked</button>
        <button onclick="logout()">Logout</button>
    </div>
    <div class="container">
        <?php foreach ($likedRestaurants as $restaurant): ?>
        <div class="restaurant-box">
            <div class="image-container">
                <img src="<?php echo $restaurant['logo']; ?>" alt="<?php echo $restaurant['name']; ?>">
                <div class="overlay">
                    <span class="restaurant-name"><?php echo $restaurant['name']; ?></span>
                </div>
            </div>
            <p class="bio"><?php echo $restaurant['bio']; ?></p>
            <iframe
                src="<?php echo htmlspecialchars($restaurant['location']); ?>"
                width="100%"
                height="150"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
            </iframe>
            <p><a href="<?php echo htmlspecialchars($restaurant['location']); ?>" target="_blank">View on Google Maps</a></p>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        function logout() {
            location.href = "logout.php";
        }
    </script>
</body>
</html>
