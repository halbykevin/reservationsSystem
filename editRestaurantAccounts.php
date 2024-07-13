<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];

        // Delete related records in restaurant_images and ratings
        $deleteImagesSql = "DELETE FROM restaurant_images WHERE restaurant_id = ?";
        $deleteImagesStmt = $conn->prepare($deleteImagesSql);
        $deleteImagesStmt->bind_param("i", $id);
        $deleteImagesStmt->execute();

        $deleteRatingsSql = "DELETE FROM ratings WHERE restaurant_id = ?";
        $deleteRatingsStmt = $conn->prepare($deleteRatingsSql);
        $deleteRatingsStmt->bind_param("i", $id);
        $deleteRatingsStmt->execute();

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
    <title>Edit Restaurant Accounts</title>
    <link rel="stylesheet" href="stylesAdminIndex.css">
    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}

button {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 10px 2px;
    cursor: pointer;
    border-radius: 5px;
}

button:hover {
    background-color: #45a049;
}

h1 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    overflow: hidden;
}

th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background-color: #f2f2f2;
    color: #333;
}

tr:hover {
    background-color: #f1f1f1;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

input[type="text"] {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
}</style>

</head>
<body>
    <button onclick="location.href='adminIndex.html'">Back</button>
    <h1>Edit Restaurant Accounts</h1>
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
</body>
</html>
