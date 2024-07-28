<?php
session_start();
require 'db.php';

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
$is_logged_in = isset($_SESSION['user_id']);

// Fetch user notifications
$userId = $_SESSION['user_id'];
$notificationSql = "SELECT reservations.*, restaurants.name AS restaurant_name FROM reservations JOIN restaurants ON reservations.restaurant_id = restaurants.id WHERE reservations.user_id = ?";
$notificationStmt = $conn->prepare($notificationSql);
$notificationStmt->bind_param("i", $userId);
$notificationStmt->execute();
$notificationResult = $notificationStmt->get_result();
$notifications = [];
if ($notificationResult->num_rows > 0) {
    while ($row = $notificationResult->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Fetch unique addresses for the dropdown
$addressSql = "SELECT DISTINCT address FROM restaurants";
$addressResult = $conn->query($addressSql);
$addresses = [];
if ($addressResult->num_rows > 0) {
    while($row = $addressResult->fetch_assoc()) {
        $addresses[] = $row['address'];
    }
}

$sql = "SELECT reservations.*, restaurants.name AS restaurant_name FROM reservations JOIN restaurants ON reservations.restaurant_id = restaurants.id WHERE reservations.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$reservations = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
}


$selectedLocation = isset($_GET['location']) ? $_GET['location'] : 'All';
$sortOrder = isset($_GET['sort']) && $_GET['sort'] == 'rating' ? 'rating' : 'default';

// Fetch promotional restaurants
$sql = "SELECT restaurants.*, IFNULL(AVG(ratings.rating), 0) AS avg_rating, COUNT(ratings.id) AS num_ratings
        FROM restaurants
        LEFT JOIN ratings ON restaurants.id = ratings.restaurant_id";

if ($selectedLocation !== 'All') {
    $sql .= " WHERE restaurants.address = ?";
}

$sql .= " GROUP BY restaurants.id";

if ($sortOrder == 'rating') {
    $sql .= " ORDER BY avg_rating DESC";
}

$stmt = $conn->prepare($sql);

if ($selectedLocation !== 'All') {
    $stmt->bind_param("s", $selectedLocation);
}

$stmt->execute();
$result = $stmt->get_result();


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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link rel="stylesheet" href="prof-noti.css" />
    <link rel="stylesheet" href="main-logo.css" />
    <link rel="stylesheet" href="search-index.css" />
    <link rel="stylesheet" href="gree-reco.css" />
    <link rel="stylesheet" href="he-bo-fo.css" />
    <link rel="stylesheet" href="main-buttons.css" />
</head>

<body>

    <img src="uploads/logo.png" class="logo" alt="logo" />

    <div class="button-container">
    <button class="btn" onclick="location.href='index.php'">Home</button>
    <button class="btn" onclick="checkLogin('discover.php')">Discover</button>
    <button class="btn" onclick="checkLogin('explore.php')">Explore</button>
    <button class="btn" onclick="checkLogin('liked.php')">Liked</button>
    <button class="btn" onclick="checkLogin('myPoints.php')">My Points</button>
    <?php if ($is_logged_in): ?>
        <button class="btn" onclick="logout()">Logout</button>
    <?php else: ?>
        <button class="btn" onclick="location.href='login.php'">Login</button>
        <button class="btn" onclick="location.href='register.html'">Register</button>
    <?php endif; ?>
</div>

<script>
   function checkLogin(page) {
        <?php if ($is_logged_in): ?>
            location.href = page;
        <?php else: ?>
            location.href = 'login.php';
        <?php endif; ?>
    }
</script>

    <img src="images/icons/user-avatar.png" class="profile-icon" alt="Profile Icon" onclick="openProfileModal()" />
    <img src="uploads/noti.jpg" class="notification-icon" alt="Notification Icon" onclick="openNotificationModal()" />

    <div id="notificationModal" class="notification-modal">
    <div class="notification-modal-content">
        <span class="notification-close" onclick="closeNotificationModal()">&times;</span>
        <h2>Notifications</h2>
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                <p><strong>Restaurant:</strong> <?php echo htmlspecialchars($notification['restaurant_name']); ?></p>
<p><strong>Status:</strong> <?php echo htmlspecialchars($notification['status']); ?></p>
                    <?php if ($notification['status'] == 'declined'): ?>
                        <p><strong>Reason:</strong> <?php echo htmlspecialchars($notification['decline_reason']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No notifications.</p>
        <?php endif; ?>
    </div>
</div>

<button class="openbtn" onclick="openNav()">☰</button>

<div id="mySidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
    <a href="index.php">Home</a>
    <a href="javascript:void(0)" onclick="checkLogin('discover.php')">Discover</a>
    <a href="javascript:void(0)" onclick="checkLogin('explore.php')">Explore</a>
    <a href="javascript:void(0)" onclick="checkLogin('liked.php')">Liked</a>
    <a href="javascript:void(0)" onclick="checkLogin('myPoints.php')">My Points</a>
    <?php if ($is_logged_in): ?>
        <a href="javascript:logout()">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.html">Register</a>
    <?php endif; ?>
</div>


    <div id="profileModal" class="profile-modal">
        <div class="profile-modal-content">
            <span class="close-profile-modal" onclick="closeProfileModal()">&times;</span>
            <h2>Edit Profile</h2>
            <form action="updateProfile.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required />
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" required />
                </div>
                <div class="form-group">
                    <label for="oldPassword">Old Password:</label>
                    <input type="password" id="oldPassword" name="oldPassword" required />
                </div>
                <button type="submit">Update Profile</button>
            </form>
            <br />
            <button onclick="openResetPassword()">Reset Password</button>
        </div>
    </div>

    <br />

    <div id="resetPasswordModal" class="profile-modal">
        <div class="profile-modal-content">
            <span class="close-profile-modal" onclick="closeResetPasswordModal()">&times;</span>
            <h2>Reset Password</h2>
            <form action="resetPassword.php" method="post">
                <div class="form-group">
                    <label for="resetOldPassword">Old Password:</label>
                    <input type="password" id="resetOldPassword" name="oldPassword" required />
                </div>
                <div class="form-group">
                    <label for="resetNewPassword">New Password:</label>
                    <input type="password" id="resetNewPassword" name="newPassword" required />
                </div>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>

    <div class="greeting-container">
    <div>
        <h1>Hello, <?php echo htmlspecialchars($user_name); ?></h1>
        <p class="small-text">Let's reserve a table for you</p>
    </div>
</div>


    
<div class="search-container">
    <input type="text" id="searchBar" onkeyup="filterRestaurants()" placeholder="Search for restaurants...">
</div>



    <div id="recommendedContainer">
    <div class="recommended-container" id="recommendedSection">
        <h2>Recommended for you</h2>
        <div class="container">
            <?php foreach ($restaurants as $restaurant): ?>
            <div class="restaurant-box-landscape" data-name="<?php echo strtolower($restaurant['name']); ?>" onclick="openModal('restaurant<?php echo $restaurant['id']; ?>')">
                <img src="<?php echo htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>">
                <div class="overlay">
                    <span class="restaurant-name"><?php echo $restaurant['name']; ?></span>
                </div>
            </div>
        </div>

            <div id="restaurant<?php echo $restaurant['id']; ?>" class="modal" style="display: none;">
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
                    <button class="reserve-now" onclick="reserveRestaurant(<?php echo $restaurant['id']; ?>)">Reserve Now</button>
                    <span class="heart-button" onclick="likeRestaurant(<?php echo $restaurant['id']; ?>)"></span>
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
    </div>
    <div id="searchResultsContainer" class="recommended-container" style="display: none;">
    <div class="container">
        <!-- Search results will be injected here dynamically -->
    </div>
</div>


    <script>

function openNotificationModal() {
    document.getElementById('notificationModal').style.display = 'block';
}

function closeNotificationModal() {
    document.getElementById('notificationModal').style.display = 'none';
}
window.onclick = function(event) {
    let modal = document.getElementById('notificationModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
};
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
    fetch('logout.php', {
        method: 'POST'
    }).then(() => {
        location.href = "login.php";
    });
}


        function openNav() {
            document.getElementById("mySidebar").style.left = "0";
        }

        function closeNav() {
            document.getElementById("mySidebar").style.left = "-250px";
        }

        function openModal(id) {
    document.getElementById(id).style.display = "block";
    const restaurantId = id.replace('restaurant', '');
    slideIndices[restaurantId] = 0;
    showSlides(restaurantId);
    
    <?php if ($is_logged_in): ?>
        document.querySelector(`#${id} .reserve-now`).style.display = "block"; // Show Reserve Now button
    <?php endif; ?>
}


        function closeModal(id) {
            document.getElementById(id).style.display = "none";
            const restaurantId = id.replace('restaurant', '');
            clearTimeout(slideTimers[restaurantId]); // Stop the slideshow when the modal is closed
        }

        let currentRating = {};
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
        
        function filterRestaurants() {
    let input = document.getElementById('searchBar').value.toLowerCase();
    let restaurantBoxes = document.querySelectorAll('.restaurant-box-landscape');
    let searchResultsContainer = document.getElementById('searchResultsContainer');
    let searchResultsContainerInner = searchResultsContainer.querySelector('.container');
    searchResultsContainerInner.innerHTML = ''; // Clear previous search results
    let found = false;

    if (input === "") {
        document.getElementById('recommendedContainer').style.display = 'block';
        searchResultsContainer.style.display = 'none';
        return;
    }

    let addedRestaurants = new Set();

    restaurantBoxes.forEach(box => {
        let name = box.getAttribute('data-name');
        if (name.includes(input) && !addedRestaurants.has(name)) {
            let clone = box.cloneNode(true); // Clone the restaurant box
            searchResultsContainerInner.appendChild(clone); // Add clone to search results
            addedRestaurants.add(name); // Add name to the set
            found = true;
        }
    });

    if (found) {
        document.getElementById('recommendedContainer').style.display = 'none';
        searchResultsContainer.style.display = 'block';
    } else {
        document.getElementById('recommendedContainer').style.display = 'block';
        searchResultsContainer.style.display = 'none';
    }
}

document.addEventListener('click', function(event) {
    let searchBar = document.getElementById('searchBar');
    let searchResultsContainer = document.getElementById('searchResultsContainer');
    let recommendedContainer = document.getElementById('recommendedContainer');
    
    if (!searchBar.contains(event.target) && event.target !== searchBar) {
        searchResultsContainer.style.display = 'none';
        recommendedContainer.style.display = 'block';
    }
});

document.getElementById('searchBar').addEventListener('input', function() {
    if (this.value === '') {
        document.getElementById('recommendedContainer').style.display = 'block';
        document.getElementById('searchResultsContainer').style.display = 'none';
    }
});



function likeRestaurant(restaurantId) {
    <?php if (!$is_logged_in): ?>
        location.href = 'login.php';
        return;
    <?php endif; ?>
    
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
            document.querySelector(`#restaurant${restaurantId} .heart-button`).classList.toggle('liked');
        } else {
            alert('Error liking restaurant.');
        }
    });
}

function reserveRestaurant(restaurantId) {
    <?php if (!$is_logged_in): ?>
        location.href = 'login.php';
        return;
    <?php endif; ?>
    
    location.href = `reserveForm.html?restaurantId=${restaurantId}`;
}



function rateRestaurant(restaurantId, rating) {
    <?php if (!$is_logged_in): ?>
        location.href = 'login.php';
        return;
    <?php endif; ?>
    
    currentRating[restaurantId] = rating;
    const stars = document.querySelectorAll(`#restaurant${restaurantId} .star`);
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('empty');
        } else {
            star.classList.add('empty');
        }
    });
    document.getElementById(`submit-rating-${restaurantId}`).style.display = "block"; // Show Submit Rating button
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
            slideTimers[restaurantId] = setTimeout(() => showSlides(restaurantId), 1000); // Change image every 1 second
        }
    </script>
    <footer class="footer">
        © All rights reserved
    </footer>
</body>
</html>
