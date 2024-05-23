<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM reservations WHERE restaurant_id IN (SELECT id FROM restaurants WHERE user_id = ?)";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations History</title>
    <link rel="stylesheet" href="stylesIndexR.css">
    <style>
        .reservation-box {
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 10px;
            padding: 15px;
            margin: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .reservation-box p {
            margin: 5px 0;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="button-container">
        <button onclick="location.href='indexR.html'">Home</button>
        <button onclick="location.href='myRestaurants.php'">My Restaurants</button>
        <button onclick="location.href='viewReservations.php'">Reservations</button>
        <button onclick="logout()">Logout</button>
    </div>
    <div class="container">
        <h1>Reservations History</h1>
        <?php foreach ($reservations as $reservation): ?>
        <div class="reservation-box">
            <p><strong>Full Name:</strong> <?php echo $reservation['full_name']; ?></p>
            <p><strong>Birthdate:</strong> <?php echo $reservation['birthdate']; ?></p>
            <p><strong>Reservation Date:</strong> <?php echo $reservation['reservation_date']; ?></p>
            <p><strong>Reservation Time:</strong> <?php echo $reservation['reservation_time']; ?></p>
            <p><strong>Seating:</strong> <?php echo $reservation['seating']; ?></p>
            <p><strong>Special Requests:</strong> <?php echo $reservation['special_requests']; ?></p>
            <p><strong>Phone:</strong> <?php echo $reservation['phone']; ?></p>
            <p><strong>Number of People:</strong> <?php echo $reservation['num_people']; ?></p>
            <p><strong>Status:</strong> <?php echo $reservation['status']; ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        function logout() {
            location.href = "logout.php";
        }
    </script>
</body>
</html>
