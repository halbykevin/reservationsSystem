<?php
require 'db.php';

$sql = "SELECT restaurants.*, IFNULL(AVG(ratings.rating), 0) AS avg_rating, COUNT(ratings.id) AS num_ratings
        FROM restaurants
        LEFT JOIN ratings ON restaurants.id = ratings.restaurant_id
        GROUP BY restaurants.id";
$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

$restaurants = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $restaurants[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Restaurants</title>
    <link rel="stylesheet" href="stylesDiscover.css">
    <style>
        .profile-icon {
            position: fixed;
            top: 10px;
            right: 10px;
            cursor: pointer;
            width: 40px;
            height: 40px;
        }
        .profile-modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        .profile-modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
        }
        .close-profile-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-profile-modal:hover,
        .close-profile-modal:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .form-group button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        .search-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .search-container input {
            width: 50%;
            padding: 10px;
            font-size: 16px;
        }
        .heart-button {
            font-size: 24px;
            color: red;
            cursor: pointer;
            user-select: none;
        }
        .stars {
            display: flex;
            gap: 5px;
            cursor: pointer;
            margin-top: 10px;
            justify-content: center;
        }
        .star {
            font-size: 24px;
            color: gold;
        }
        .star.empty {
            color: lightgray;
        }
        .rating-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 10px;
        }
        .submit-rating {
            display: none;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-rating:hover {
            background-color: #0056b3;
        }
        .num-ratings {
            font-size: 14px;
            color: gray;
        }
    </style>
</head>
<body>
<div class="button-container">
        <button onclick="location.href='index.html'">Home</button>
        <button onclick="location.href='discover.php'">Discover</button>
        <button onclick="location.href='liked.php'">Liked</button>
        <button onclick="location.href='myPoints.php'">My Points</button>
        <button onclick="logout()">Logout</button>
    </div>

    <img
        src="images/icons/user-avatar.png"
        class="profile-icon"
        alt="Profile Icon"
        onclick="openProfileModal()"
    />

    <div class="search-container">
        <input type="text" id="searchBar" onkeyup="filterRestaurants()" placeholder="Search for restaurants...">
    </div>
    <div class="container" id="restaurantContainer">
        <?php foreach ($restaurants as $restaurant): ?>
        <div class="restaurant-box" data-name="<?php echo strtolower($restaurant['name']); ?>" onclick="openModal('restaurant<?php echo $restaurant['id']; ?>')">
            <div class="image-container">
                <img src="<?php echo $restaurant['logo']; ?>" alt="<?php echo $restaurant['name']; ?>">
                <div class="overlay">
                    <span class="restaurant-name"><?php echo $restaurant['name']; ?></span>
                </div>
            </div>
        </div>

        <div id="profileModal" class="profile-modal">
        <div class="profile-modal-content">
            <span class="close-profile-modal" onclick="closeProfileModal()">&times;</span>
            <h2>Edit Profile</h2>
            <form action="updateProfile.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profilePicture">Profile Picture:</label>
                    <input type="file" id="profilePicture" name="profilePicture" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="oldPassword">Old Password:</label>
                    <input type="password" id="oldPassword" name="oldPassword" required>
                </div>
                <button type="submit">Update Profile</button>
            </form>
            <br>
            <button onclick="openResetPassword()">Reset Password</button>
        </div>
    </div>

    <div id="resetPasswordModal" class="profile-modal">
        <div class="profile-modal-content">
            <span class="close-profile-modal" onclick="closeResetPasswordModal()">&times;</span>
            <h2>Reset Password</h2>
            <form action="resetPassword.php" method="post">
                <div class="form-group">
                    <label for="resetOldPassword">Old Password:</label>
                    <input type="password" id="resetOldPassword" name="oldPassword" required>
                </div>
                <div class="form-group">
                    <label for="resetNewPassword">New Password:</label>
                    <input type="password" id="resetNewPassword" name="newPassword" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>

        <!-- Modal Structure -->
        <div id="restaurant<?php echo $restaurant['id']; ?>" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('restaurant<?php echo $restaurant['id']; ?>')">X</span>
                <div class="slideshow-container">
                    <div class="slide fade">
                        <img src="<?php echo $restaurant['logo']; ?>" class="main-image" alt="<?php echo $restaurant['name']; ?>">
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
                <button onclick="location.href='reserveForm.html?restaurantId=<?php echo $restaurant['id']; ?>'">Reserve Now</button>
                <span class="heart-button" onclick="likeRestaurant(<?php echo $restaurant['id']; ?>)">❤</span>
                <div class="rating-container">
                    <div class="stars" data-restaurant-id="<?php echo $restaurant['id']; ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= round($restaurant['avg_rating']) ? '' : 'empty'; ?>" onclick="rateRestaurant(<?php echo $restaurant['id']; ?>, <?php echo $i; ?>)">★</span>
                        <?php endfor; ?>
                    </div>
                    <div class="num-ratings">(<?php echo $restaurant['num_ratings']; ?>)</div>
                    <button class="submit-rating" id="submit-rating-<?php echo $restaurant['id']; ?>" onclick="submitRating(<?php echo $restaurant['id']; ?>)">Submit Rating</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        let currentRating = {};

        function openModal(id) {
            document.getElementById(id).style.display = "block";
        }

        function closeModal(id) {
            document.getElementById(id).style.display = "none";
        }

        function logout() {
            location.href = "logout.php";
        }

        // Close the modal if the user clicks outside of the modal content
        window.onclick = function (event) {
            let modals = document.querySelectorAll(".modal");
            modals.forEach((modal) => {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });
        };

        function filterRestaurants() {
            let input = document.getElementById('searchBar').value.toLowerCase();
            let restaurantBoxes = document.querySelectorAll('.restaurant-box');
            restaurantBoxes.forEach(box => {
                let name = box.getAttribute('data-name');
                if (name.includes(input)) {
                    box.style.display = "block";
                } else {
                    box.style.display = "none";
                }
            });
        }

        function likeRestaurant(restaurantId) {
            fetch('likeRestaurant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ restaurantId: restaurantId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Restaurant liked!');
                } else {
                    alert('Error liking restaurant.');
                }
            });
        }

        function rateRestaurant(restaurantId, rating) {
            currentRating[restaurantId] = rating;
            const stars = document.querySelectorAll(`#restaurant${restaurantId} .star`);
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('empty');
                } else {
                    star.classList.add('empty');
                }
            });
            document.getElementById(`submit-rating-${restaurantId}`).style.display = "block";
        }

        function submitRating(restaurantId) {
            const rating = currentRating[restaurantId];
            fetch('rateRestaurant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ restaurantId: restaurantId, rating: rating })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you for your rating!');
                    location.reload(); // Reload the page to update the average rating
                } else {
                    alert('Error rating restaurant.');
                }
            });
        }

        function openProfileModal() {
            document.getElementById("profileModal").style.display = "block";
        }

        function closeProfileModal() {
            document.getElementById("profileModal").style.display = "none";
        }

        function openResetPassword() {
            document.getElementById("profileModal").style.display = "none";
            document.getElementById("resetPasswordModal").style.display = "block";
        }

        function closeResetPasswordModal() {
            document.getElementById("resetPasswordModal").style.display = "none";
        }

        function logout() {
            location.href = "logout.php";
        }
    </script>
</body>
</html>
