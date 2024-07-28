<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];

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

// Handle redeem action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['redeem'])) {
    $sql = "UPDATE users SET points = points - 10 WHERE id = ? AND points >= 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

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
      .profile-icon, .notification-icon {
    display: block;
    margin: 10px auto; /* Center icons horizontally and add margin */
    position: absolute; /* Use absolute positioning */
    right: 10px; /* Position 10px from the right */
}

.notification-icon {
    top: 70px; /* Adjust the top margin as needed to position under the profile icon */
}
.notification-icon {
    width: 30px;
    height: 30px;
    cursor: pointer;
    margin-left: 0; /* Remove auto margin */
}

.notification-modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.notification-modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 10px;
    overflow: auto;
}

.notification-close {
    position: absolute;
    right: 10px;
    top: 10px;
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.notification-close:hover,
.notification-close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.notification-item {
    border-bottom: 1px solid #ddd;
    padding: 10px 0;
}

.notification-item:last-child {
    border-bottom: none;
}

      .points-container {
        display: flex;
        gap: 5px;
        justify-content: center;
        margin: 20px 0;
      }

      .point {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #ccc; /* Gray for empty points */
      }

      .point.filled {
        background-color: #4caf50; /* Green for filled points */
      }

      .footer {
        background-color: grey;
        color: white;
        text-align: center;
        padding: 10px 0;
        position: fixed;
        width: 100%;
        bottom: 0;
      }

      header,
      body {
        background-color: white;
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
      .logo {
        display: block;
        margin: 20px auto;
        width: 300px;
        height: auto;
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
        background-color: black;
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
    </style>
    <img src="uploads/logo.png" class="logo" alt="logo" />

    <div class="button-container">
      <button class="btn" onclick="location.href='index.php'">Home</button>
      <button class="btn" onclick="location.href='discover.php'">Discover</button>
      <button class="btn" onclick="location.href='explore.php'">Explore</button>
      <button class="btn" onclick="location.href='liked.php'">Liked</button>
      <button class="btn" onclick="location.href='myPoints.php'">My Points</button>
      <button class="btn" onclick="logout()">Logout</button>
    </div>

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
</script>

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

    <div class="container">
      <h1>My Points</h1>
      <div class="points-container">
        <?php for ($i = 1; $i <= 10; $i++): ?>
          <div class="point <?php echo ($i <= $points) ? 'filled' : ''; ?>"></div>
        <?php endfor; ?>
      </div>
      <?php if ($points >= 10): ?>
        <form method="post">
          <button type="submit" name="redeem">Redeem</button>
        </form>
      <?php endif; ?>
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

    <footer class="footer">
        © All rights reserved
    </footer>
</body>
</html>
