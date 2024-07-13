<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $restaurant_id = $_POST['restaurant_id'];
    $is_promotional = $_POST['is_promotional'];

    $sql = "UPDATE restaurants SET is_promotional = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_promotional, $restaurant_id);

    if ($stmt->execute()) {
        echo "Promotion status updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch all restaurants
$sql = "SELECT * FROM restaurants";
$result = $conn->query($sql);
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
    <title>Promotion Rest</title>
    <link rel="stylesheet" href="stylesAdminIndex.css">
</head>
<body><button onclick="location.href='adminIndex.html'">Back</button>
    <h1>Promotional Restaurants</h1>
    <form method="post" action="promotionRest.php">
        <label for="restaurant_id">Select Restaurant:</label>
        <select id="restaurant_id" name="restaurant_id">
            <?php foreach ($restaurants as $restaurant): ?>
                <option value="<?php echo $restaurant['id']; ?>"><?php echo htmlspecialchars($restaurant['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="is_promotional">Set as Promotional:</label>
        <select id="is_promotional" name="is_promotional">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
        <button type="submit">Update Promotion Status</button>
    </form>
</body>
</html>
