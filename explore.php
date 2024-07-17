<?php
require 'db.php';

// Fetch categories from the categories table
$categorySql = "SELECT * FROM categories";
$categoryResult = $conn->query($categorySql);
$categories = [];
if ($categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

$selectedLocation = isset($_GET['location']) ? $_GET['location'] : 'All';
$sortOrder = isset($_GET['sort']) && $_GET['sort'] == 'rating' ? 'rating' : 'default';
$selectedCategory = isset($_GET['category']) ? urldecode($_GET['category']) : '';

// Fetch restaurants based on selected category and location
$sql = "SELECT restaurants.*, IFNULL(AVG(ratings.rating), 0) AS avg_rating, COUNT(ratings.id) AS num_ratings
        FROM restaurants
        LEFT JOIN ratings ON restaurants.id = ratings.restaurant_id";

$conditions = [];
$params = [];
$types = '';

if ($selectedLocation !== 'All') {
    $conditions[] = "restaurants.address = ?";
    $params[] = $selectedLocation;
    $types .= 's';
}

if ($selectedCategory !== '') {
    $conditions[] = "restaurants.category = ?";
    $params[] = $selectedCategory;
    $types .= 's';
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " GROUP BY restaurants.id";

if ($sortOrder == 'rating') {
    $sql .= " ORDER BY avg_rating DESC";
}

$stmt = $conn->prepare($sql);

if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Restaurants</title>
    <link rel="stylesheet" href="stylesDiscover.css">
    <link rel="stylesheet" href="stylesDiscover2.css">
    <link rel="stylesheet" href="stylesDiscover3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .footer {
            background-color: red;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        body {
            background-color: white;
        }

        .button-container .btn {
            font-size: 17px;
            background: transparent;
            border: none;
            padding: 1em 1.5em;
            color: black;
            text-transform: uppercase;
            position: relative;
            transition: 0.5s ease;
            cursor: pointer;
        }

        .button-container .btn::before {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            height: 2px;
            width: 0;
            background-color: black;
            transition: 0.5s ease;
        }

        .button-container .btn:hover {
            color: #1e1e2b;
            transition-delay: 0.5s;
        }

        .button-container .btn:hover::before {
            width: 100%;
        }

        .button-container .btn::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            height: 0;
            width: 100%;
            background-color: #ffc506;
            transition: 0.4s ease;
            z-index: -1;
        }

        .button-container .btn:hover::after {
            height: 100%;
            transition-delay: 0.4s;
        }

        .logo {
            display: block;
            margin: 20px auto;
            width: 150px;
            height: auto;
        }

        .categories-container {
    display: flex;
    overflow-x: auto;
    gap: 10px;
    padding: 10px;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch; /* Enables smooth scrolling on mobile */
    justify-content: center;
}

@media (max-width: 768px) {
    .categories-container {
        justify-content: flex-start; /* Align to the left on mobile for scrolling */
    }
}


.category-box {
    width: 100px; /* Smaller width */
    height: 100px; /* Smaller height */
    flex: 0 0 auto; /* Prevents shrinking */
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s, opacity 0.3s;
}
.category-box.active {
    opacity: 0.5;
}
.category-box.inactive {
    transform: scale(0.8);
}
.category-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.category-box:hover {
    transform: translateY(-5px);
}
        .category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 10px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            text-align: center;
            font-size: 18px;
        }

        .restaurant-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    margin: 20px;
}

.restaurant-box {
    width: 100%;
    max-width: 200px; /* Smaller max width for landscape format */
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column; /* Ensure details are below the image */
}

.restaurant-box img {
    width: 100%; /* Make the image take the full width */
    height: auto; /* Maintain aspect ratio */
}

        .restaurant-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 10px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            text-align: center;
            font-size: 18px;
        }

        .restaurant-details {
    padding: 10px;
    background: #f9f9f9;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: space-between; /* Align details next to each other */
    align-items: center;
    flex-wrap: wrap; /* Allow wrapping on smaller screens */
    font-size: 11px; /* Set font size to 11px */
}

.restaurant-name {
    font-size: 11px; /* Set font size to 11px */
    font-weight: normal; /* Lighter font weight */
    margin-bottom: 0; /* Remove bottom margin */
}

.restaurant-info {
    display: flex;
    align-items: center;
    font-size: 11px; /* Set font size to 11px */
    font-weight: normal; /* Lighter font weight */
    margin-top: 0; /* Remove top margin */
}

.restaurant-info img {
    width: 12px; /* Smaller icon size */
    height: 12px; /* Smaller icon size */
    margin-right: 5px;
}


    </style>
</head>
<body>

<img src="uploads/logo.png" class="logo" alt="logo" />

<div class="button-container">
    <button class="btn" onclick="location.href='index.php'">Home</button>
    <button class="btn" onclick="location.href='discover.php'">Discover</button>
    <button class="btn" onclick="location.href='explore.php'">Explore</button>
    <button class="btn" onclick="location.href='liked.php'">Liked</button>
    <button class="btn" onclick="location.href='myPoints.php'">My Points</button>
    <button class="btn" onclick="logout()">Logout</button>
</div>

<img src="images/icons/user-avatar.png" class="profile-icon" alt="Profile Icon" onclick="openProfileModal()" />

<button class="openbtn" onclick="openNav()">☰</button>

<div id="mySidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
    <a href="index.php">Home</a>
    <a href="discover.php">Discover</a>
    <a href="explore.php">Explore</a>
    <a href="liked.php">Liked</a>
    <a href="myPoints.php">My Points</a>
    <a href="javascript:logout()">Logout</a>
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

<div class="categories-container">
    <?php foreach ($categories as $category): ?>
        <div class="category-box">
            <a href="explore.php?category=<?php echo urlencode($category['name']); ?>">
                <img src="<?php echo htmlspecialchars($category['image_path']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                <!-- Remove the overlay div content -->
            </a>
        </div>
    <?php endforeach; ?>
</div>


<div class="restaurant-container">
    <?php if (!empty($restaurants)): ?>
        <?php foreach ($restaurants as $restaurant): ?>
            <div class="restaurant-box" onclick="openModal('restaurant<?php echo $restaurant['id']; ?>')">
                <img src="<?php echo htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>">
                <div class="restaurant-details">
                    <div class="restaurant-name"><?php echo htmlspecialchars($restaurant['name']); ?></div>
                    <div class="restaurant-info">
                        <img src="uploads/pin.png" alt="Location">
                        <?php echo htmlspecialchars($restaurant['address']); ?>
                    </div>
                    <div class="restaurant-info">
                        (<?php echo htmlspecialchars($restaurant['num_ratings']); ?>)
                        <?php echo number_format($restaurant['avg_rating'], 1); ?>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No restaurants found for this category.</p>
    <?php endif; ?>
</div>

<!-- Modal Structure -->
<?php foreach ($restaurants as $restaurant): ?>
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

<script>
    // Declare currentRating once at the top level
    let currentRating = {};
    let slideTimers = {}; // Store timers for each restaurant
    let slideIndices = {}; // Store slide indices for each restaurant

    function openModal(id) {
        document.getElementById(id).style.display = "block";
        const restaurantId = id.replace('restaurant', '');
        slideIndices[restaurantId] = 0;
        showSlides(restaurantId);
    }

    function closeModal(id) {
        document.getElementById(id).style.display = "none";
        const restaurantId = id.replace('restaurant', '');
        clearTimeout(slideTimers[restaurantId]); // Stop the slideshow when the modal is closed
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
            let features = box.getAttribute('data-features');
            if (name.includes(input) || features.includes(input)) {
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

    function filterByLocation() {
        const selectedLocation = document.getElementById('locationDropdown').value;
        const url = new URL(window.location.href);
        url.searchParams.set('location', selectedLocation);
        window.location.href = url.href;
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
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const categoryBoxes = document.querySelectorAll('.category-box');
    const activeCategory = localStorage.getItem('activeCategory');

    if (activeCategory) {
        categoryBoxes.forEach(box => {
            const category = box.querySelector('a').href.split('category=')[1];
            if (category === activeCategory) {
                box.classList.add('active');
            } else {
                box.classList.add('inactive');
            }
        });
    }

    categoryBoxes.forEach(box => {
        box.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            const category = this.querySelector('a').href.split('category=')[1];

            categoryBoxes.forEach(box => {
                box.classList.add('inactive');
                box.classList.remove('active');
            });

            this.classList.remove('inactive');
            this.classList.add('active');

            localStorage.setItem('activeCategory', category);

            // Redirect to the clicked category
            window.location.href = this.querySelector('a').href;
        });
    });
});

    function openModal(id) {
        document.getElementById(id).style.display = "block";
        const restaurantId = id.replace('restaurant', '');
        slideIndices[restaurantId] = 0;
        showSlides(restaurantId);
    }

    function closeModal(id) {
        document.getElementById(id).style.display = "none";
        const restaurantId = id.replace('restaurant', '');
        clearTimeout(slideTimers[restaurantId]); // Stop the slideshow when the modal is closed
    }

    function logout() {
        location.href = "logout.php";
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
</script>

<footer class="footer">
    © All rights reserved
</footer>

</body>
</html>
