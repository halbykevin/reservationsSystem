<?php
require 'db.php';

// Fetch categories from the database
$categorySql = "SELECT id, name FROM categories";
$categoryResult = $conn->query($categorySql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restaurant Dashboard</title>
    <link rel="stylesheet" href="stylesIndexR.css" />
</head>
<body>
    <div class="button-container">
        <button onclick="location.href='indexR.html'">Home</button>
        <button onclick="location.href='myRestaurants.php'">My Restaurants</button>
        <button onclick="location.href='viewReservations.php'">Reservations</button>
        <button onclick="location.href='reservationsHistory.php'">Reservations History</button>
        <button onclick="location.href='addReservations.php'">Add Reservations</button>
        <button onclick="logout()">Logout</button>
    </div>

    <div class="container">
        <h1>Add a New Restaurant</h1>
        <form action="addRestaurant.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Restaurant Name:</label>
                <input type="text" id="name" name="name" required />
            </div>
            <div class="form-group">
                <label for="bio">Bio:</label>
                <textarea id="bio" name="bio" required></textarea>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required />
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" required />
            </div>
            <div class="form-group">
                <label for="location">Google Maps Embed URL:</label>
                <input type="text" id="location" name="location" required />
            </div>
            <div class="form-group">
                <label for="features">Features:</label>
                <input type="text" id="features" name="features" required />
            </div>
            <div class="form-group">
                <label for="open_hours">Open Hours:</label>
                <textarea id="open_hours" name="open_hours" required></textarea>
            </div>
            <div class="form-group">
                <label for="capacity">Capacity:</label>
                <input type="number" id="capacity" name="capacity" required min="1" />
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <?php while($row = $categoryResult->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="logo">Logo (optional):</label>
                <input type="file" id="logo" name="logo" accept="image/*" />
            </div>
            <div class="form-group">
                <label for="restaurant_images">Restaurant Images:</label>
                <input type="file" id="restaurant_images" name="restaurant_images[]" accept="image/*" multiple />
            </div>
            <button type="submit">Add Restaurant</button>
        </form>
    </div>

    <script>
        function logout() {
            location.href = "logout.php";
        }
    </script>
</body>
</html>
