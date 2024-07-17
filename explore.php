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
            <div class="restaurant-box">
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

    let currentRating = {};

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
