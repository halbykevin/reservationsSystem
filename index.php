<?php
session_start();
require 'db.php';

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';

// Fetch promotional restaurants
$sql = "SELECT restaurants.*, IFNULL(AVG(ratings.rating), 0) AS avg_rating, COUNT(ratings.id) AS num_ratings
        FROM restaurants
        LEFT JOIN ratings ON restaurants.id = ratings.restaurant_id
        WHERE restaurants.is_promotional = 1
        GROUP BY restaurants.id";
$result = $conn->query($sql);

$promotional_restaurants = [];
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
        $promotional_restaurants[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link rel="stylesheet" href="stylesIndex.css" />
    <style>

    /* Ensure modal covers the entire page */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
}

.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%; /* Could be more or less, depending on screen size */
    height: 90%; /* Make the modal content cover most of the screen */
    max-width: 1200px;
    max-height: 800px;
    border-radius: 10px;
    overflow: auto; /* Enable scrolling inside the modal content */
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}


        .recommended-container .container {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start; /* Ensure items are aligned to the left */
}
.recommended-container {
    margin: 20px 20px;
    position: absolute;
    top: 300px; /* Adjust this value as needed to place it correctly */
    left: 20px; /* Adjust this value as needed to place it correctly */
}

        .recommended-container h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .restaurant-box-landscape {
            left: -16px;
            position: relative;
            margin: 15px;
            width: 300px; /* Set the width to make it landscape */
            height: 200px; /* Adjust the height to maintain aspect ratio */
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .restaurant-box-landscape img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }

        .restaurant-box-landscape .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .restaurant-box-landscape:hover img {
            opacity: 0.3;
        }

        .restaurant-box-landscape:hover .overlay {
            opacity: 1;
        }

        header,
        body {
            background-color: white;
        }
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
        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            background-color: #fff;
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 100%;
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
        .main-container {
            flex: 1;
            width: 100%;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-color: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #111;
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
            z-index: 1;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background-color: #575757;
        }
        .sidebar .closebtn {
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 36px;
        }
        .openbtn {
            background-color: transparent;
            font-size: 20px;
            cursor: pointer;
            color: black;
            padding: 10px 15px;
            border: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1;
            display: none;
        }
        @media screen and (max-width: 768px) {
            .button-container {
                display: none;
            }
            .openbtn {
                display: block;
            }
        }
        .slideshow-container {
            max-width: 600px;
            position: relative;
            margin: auto;
            margin-top: 20px;
        }
        .mySlides {
            display: none;
        }
        .fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }
        @keyframes fade {
            from {
                opacity: 0.4;
            }
            to {
                opacity: 1;
            }
        }
        .dot {
            cursor: pointer;
            height: 15px;
            width: 15px;
            margin: 0 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
        }
        .active,
        .dot:hover {
            background-color: #717171;
        }
        .dots-container {
            text-align: center;
            position: relative;
            top: -30px;
            z-index: 1;
        }
        .logo {
            display: block;
            margin: 20px auto;
            width: 150px;
            height: auto;
        }
        .footer {
            background-color: grey;
            color: white;
            text-align: center;
            padding: 30px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
        .greeting-container {
            text-align: left;
            margin: 20px 20px;
            position: absolute;
            top: 200px; /* Adjust the top value as needed */
            left: 20px; /* Adjust the left value as needed */
        }

        .greeting-container h1 {
            font-size: 24px;
            margin-bottom: 5px; /* Reduce the bottom margin */
        }

        .greeting-container .small-text {
            font-size: 16px;
            color: #666;
            margin-top: 0; /* Remove the top margin */
        }
        @media screen and (max-width: 768px) {
            .logo {
                width: 100px; /* Smaller width for mobile */
            }
        }
        .main-image-frame {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
}

.main-image {
    max-width: 60%; /* Smaller size for the main image */
    height: auto;
    border-radius: 10px;
}

.thumbnail-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.thumbnail {
    width: 10%; /* Smaller size for thumbnails */
    height: auto;
    margin-bottom: 10px;
    cursor: pointer;
    border-radius: 5px;
    transition: transform 0.2s;
}

.thumbnail:hover {
    transform: scale(1.05);
}

.thumbnail.active {
    border: 2px solid #007bff;
}

.bio-frame {
    text-align: center; /* Center the bio text */
}

.open-hours,
.location,
button {
    text-align: center; /* Center the open hours and location */
}

.submit-rating {
    display: none; /* Hide the Submit Rating button by default */
}

button.reserve-now {
    display: none; /* Hide the Reserve Now button by default */
}

.features {
    display: flex;
    flex-wrap: wrap;
    justify-content: center; /* Center the feature bubbles */
    gap: 10px;
    margin-top: 20px;
}

.feature-bubble {
    background-color: #f0f0f0;
    border-radius: 50px;
    padding: 10px 20px;
    text-align: center;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.heart-button {
    font-size: 24px;
    cursor: pointer;
    color: transparent; /* Initial state with no color fill */
    border: none; /* Remove border */
    background-color: transparent; /* Transparent background */
    transition: color 0.3s;
}

.heart-button::before {
    content: '❤'; /* Unicode character for heart */
    color: red; /* Red color for the heart outline */
}

.heart-button.liked::before {
    color: red; /* Fill color when liked */
}

    </style>
</head>
<body>
    <img src="uploads/logo.png" class="logo" alt="logo" />

    <div class="button-container">
        <button class="btn" onclick="location.href='index.php'">Home</button>
        <button class="btn" onclick="location.href='discover.php'">Discover</button>
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
        <h1>Hello, <?php echo htmlspecialchars($user_name); ?></h1>
        <p class="small-text">Let's reserve a table for you</p>
    </div>

    <div class="recommended-container">
        <h2>Recommended for you</h2>
        <div class="container">
            <?php foreach ($promotional_restaurants as $restaurant): ?>
            <div class="restaurant-box-landscape" onclick="openModal('restaurant<?php echo $restaurant['id']; ?>')">
                <img src="<?php echo htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>">
                <div class="overlay">
                    <span class="restaurant-name"><?php echo $restaurant['name']; ?></span>
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
                    <button class="reserve-now" onclick="location.href='reserveForm.html?restaurantId=<?php echo $restaurant['id']; ?>'">Reserve Now</button>
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

    <script>
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
    document.querySelector(`#${id} .reserve-now`).style.display = "block"; // Show Reserve Now button
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

        function filterByLocation() {
            const selectedLocation = document.getElementById('locationDropdown').value;
            const url = new URL(window.location.href);
            url.searchParams.set('location', selectedLocation);
            window.location.href = url.href;
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
            document.querySelector(`#restaurant${restaurantId} .heart-button`).classList.toggle('liked');
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
