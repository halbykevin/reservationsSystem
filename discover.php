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
        $restaurantId = $row['id'];
        $imageSql = "SELECT image_path FROM restaurant_images WHERE restaurant_id = ?";
        $imageStmt = $conn->prepare($imageSql);
        $imageStmt->bind_param("i", $restaurantId);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $images = [];
        while ($imageRow = $imageResult->fetch_assoc()) {
            $images[] = $imageRow['image_path'];
        }
        $row['images'] = $images;
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
    <link rel="stylesheet" href="stylesDiscover2.css">
    <link rel="stylesheet" href="stylesDiscover3.css">
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

    <button class="openbtn" onclick="openNav()">☰</button>

    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
        <a href="index.html">Home</a>
        <a href="discover.php">Discover</a>
        <a href="liked.php">Liked</a>
        <a href="myPoints.php">My Points</a>
        <a href="javascript:logout()">Logout</a>
    </div>

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
        <div class="main-image-frame">
            <img src="<?php echo $restaurant['images'][0]; ?>" class="main-image" id="mainImage<?php echo $restaurant['id']; ?>" alt="<?php echo $restaurant['name']; ?>">
        </div>
        <div class="thumbnail-container">
            <?php foreach ($restaurant['images'] as $index => $imagePath): ?>
            <img src="<?php echo $imagePath; ?>" class="thumbnail" alt="<?php echo $restaurant['name']; ?>" onclick="currentSlide(<?php echo $index; ?>, '<?php echo $restaurant['id']; ?>')">
            <?php endforeach; ?>
        </div>
        <div class="bio-frame">
  <p class="bio"><?php echo $restaurant['bio']; ?></p>
</div>

            
            <!-- Display features -->
            <div class="features">
                <?php 
                $features = explode(',', $restaurant['features']); 
                foreach ($features as $feature) {
                    echo "<span class='feature-bubble'>" . htmlspecialchars(trim($feature)) . "</span>";
                }
                ?>
            </div>
            
            <!-- Display open hours -->
            <div class="open-hours">
            <h3>Open Hours</h3>
                <?php 
                $open_hours = explode("\n", $restaurant['open_hours']);
                foreach ($open_hours as $hours) {
                    echo "<p>" . htmlspecialchars(trim($hours)) . "</p>";
                }
                ?>
            </div>
            
            <iframe
                src="<?php echo htmlspecialchars($restaurant['location']); ?>"
                width="50%"
                height="600"
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

        function openNav() {
            document.getElementById("mySidebar").style.left = "0";
        }

        function closeNav() {
            document.getElementById("mySidebar").style.left = "-250px";
        }
        
        
        let slideTimers = {}; // Store timers for each restaurant
let slideIndices = {}; // Store slide indices for each restaurant

function showSlides(restaurantId) {
    const thumbnails = document.querySelectorAll(`#restaurant${restaurantId} .thumbnail`);
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.classList.remove('active');
    });
    slideIndices[restaurantId]++;
    if (slideIndices[restaurantId] > thumbnails.length) {
        slideIndices[restaurantId] = 1;
    }
    thumbnails[slideIndices[restaurantId] - 1].classList.add('active');
    document.getElementById(`mainImage${restaurantId}`).src = thumbnails[slideIndices[restaurantId] - 1].src;
    slideTimers[restaurantId] = setTimeout(() => showSlides(restaurantId), 2000); // Change image every 2 seconds
}

function currentSlide(index, restaurantId) {
    clearTimeout(slideTimers[restaurantId]); // Stop the automatic slideshow
    const thumbnails = document.querySelectorAll(`#restaurant${restaurantId} .thumbnail`);
    thumbnails.forEach((thumbnail, idx) => {
        thumbnail.classList.remove('active');
    });
    thumbnails[index].classList.add('active');
    document.getElementById(`mainImage${restaurantId}`).src = thumbnails[index].src;
    slideIndices[restaurantId] = index + 1; // Update slide index
}

function openModal(id) {
    const restaurantId = id.replace('restaurant', '');
    document.getElementById(id).style.display = "block";
    slideIndices[restaurantId] = 0;
    showSlides(restaurantId);
}

function closeModal(id) {
    const restaurantId = id.replace('restaurant', '');
    document.getElementById(id).style.display = "none";
    clearTimeout(slideTimers[restaurantId]); // Stop the slideshow when the modal is closed
}
    </script>
</body>
</html>
