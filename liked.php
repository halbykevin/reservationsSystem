<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT restaurants.* FROM liked_restaurants JOIN restaurants ON liked_restaurants.restaurant_id = restaurants.id WHERE liked_restaurants.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$likedRestaurants = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $likedRestaurants[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liked Restaurants</title>
    <link rel="stylesheet" href="stylesDiscover.css">
</head>
<body>
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
        .button-container {
        display: flex;
        justify-content: center; /* Center buttons horizontally */
        gap: 10px; /* Space between buttons */
        background-color: #fff;
        padding: 10px 0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        width: 100%;
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
      }
      @media screen and (max-width: 768px) {
        .button-container {
          display: none; /* Hide button container on small screens */
        }
        .openbtn {
          display: block; /* Show the open button on small screens */
        }
      }
    </style>
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
    <div class="container">
        <?php foreach ($likedRestaurants as $restaurant): ?>
        <div class="restaurant-box">
            <div class="image-container">
                <img src="<?php echo $restaurant['logo']; ?>" alt="<?php echo $restaurant['name']; ?>">
                <div class="overlay">
                    <span class="restaurant-name"><?php echo $restaurant['name']; ?></span>
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
        </div>
        <?php endforeach; ?>
    </div>

    <script>
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

        function logout() {
            location.href = "logout.php";
        }

        function openNav() {
        document.getElementById("mySidebar").style.left = "0";
      }

      function closeNav() {
        document.getElementById("mySidebar").style.left = "-250px";
      }
    </script>
</body>
</html>
