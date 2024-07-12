<?php
session_start();
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link rel="stylesheet" href="stylesIndex.css" />
    <style>
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
            font-size: 30px;
            margin-bottom: 10px;
        }
        .greeting-container .small-text {
            font-size: 16px;
            color: #666;
        }
        @media screen and (max-width: 768px) {
    .logo {
        width: 100px; /* Smaller width for mobile */
    }
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
    </script>
    <footer class="footer">
        © All rights reserved
    </footer>
</body>
</html>
