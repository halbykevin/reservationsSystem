<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];

        // Delete related records in liked_restaurants
        $deleteLikedSql = "DELETE FROM liked_restaurants WHERE restaurant_id = ?";
        $deleteLikedStmt = $conn->prepare($deleteLikedSql);
        $deleteLikedStmt->bind_param("i", $id);
        $deleteLikedStmt->execute();

        // Delete related records in restaurant_images
        $deleteImagesSql = "DELETE FROM restaurant_images WHERE restaurant_id = ?";
        $deleteImagesStmt = $conn->prepare($deleteImagesSql);
        $deleteImagesStmt->bind_param("i", $id);
        $deleteImagesStmt->execute();

        // Delete related records in ratings
        $deleteRatingsSql = "DELETE FROM ratings WHERE restaurant_id = ?";
        $deleteRatingsStmt = $conn->prepare($deleteRatingsSql);
        $deleteRatingsStmt->bind_param("i", $id);
        $deleteRatingsStmt->execute();

        // Delete related records in reservations
        $deleteReservationsSql = "DELETE FROM reservations WHERE restaurant_id = ?";
        $deleteReservationsStmt = $conn->prepare($deleteReservationsSql);
        $deleteReservationsStmt->bind_param("i", $id);
        $deleteReservationsStmt->execute();

        // Delete the restaurant
        $sql = "DELETE FROM restaurants WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $bio = $_POST['bio'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $location = $_POST['location'];
        $features = $_POST['features'];
        $open_hours = $_POST['open_hours'];

        $sql = "UPDATE restaurants SET name=?, bio=?, address=?, phone=?, location=?, features=?, open_hours=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $name, $bio, $address, $phone, $location, $features, $open_hours, $id);
        $stmt->execute();
    }
}



$sql = "SELECT * FROM restaurants";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Restaurants</title>
    <link rel="stylesheet" href="stylesAdminIndex.css">
    <link rel="stylesheet" href="stylesEditRestaurants.css">
</head>
<body>
    <h1>Edit Restaurants</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Bio</th>
            <th>Address</th>
            <th>Phone</th>
            <th>Location</th>
            <th>Features</th>
            <th>Open Hours</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <form method="POST">
                <td><input type="text" name="id" value="<?php echo htmlspecialchars($row['id']); ?>" readonly></td>
                <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>"></td>
                <td><input type="text" name="bio" value="<?php echo htmlspecialchars($row['bio']); ?>"></td>
                <td><input type="text" name="address" value="<?php echo htmlspecialchars($row['address']); ?>"></td>
                <td><input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>"></td>
                <td><input type="text" name="location" value="<?php echo htmlspecialchars($row['location']); ?>"></td>
                <td><input type="text" name="features" value="<?php echo htmlspecialchars($row['features']); ?>"></td>
                <td><input type="text" name="open_hours" value="<?php echo htmlspecialchars($row['open_hours']); ?>"></td>
                <td>
                    <button type="submit" name="edit">Edit</button>
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this restaurant?')">Delete</button>
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>
    <button onclick="location.href='adminIndex.html'">Back</button>
</body>
</html>
