<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT points FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$points = $user['points'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Points</title>
    <link rel="stylesheet" href="stylesIndex.css">
</head>
<body>
    <style>
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
display: none; /* Hide the open button initially */
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
        <button onclick="logout()">Logout</button>
    </div>

    <button class="openbtn" onclick="openNav()">☰</button>

<div id="mySidebar" class="sidebar">
      <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
      <a href="index.html">Home</a>
      <a href="discover.php">Discover</a>
      <a href="liked.php">Liked</a>
      <a href="myPoints.php">My Points</a>
      <a href="javascript:logout()">Logout</a>
    </div>

    <div class="container">
        <h1>My Points</h1>
        <p>You have <strong><?php echo $points; ?></strong> points.</p>
    </div>
    <script>
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
