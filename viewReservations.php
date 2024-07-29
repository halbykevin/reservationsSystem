<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM reservations WHERE restaurant_id IN (SELECT id FROM restaurants WHERE user_id = ?) AND reservation_date >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
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
    <title>View Reservations</title>
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
        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .button-container button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="button-container">
        <button onclick="location.href='indexR.html'">Home</button>
        <button onclick="location.href='myRestaurants.php'">My Restaurants</button>
        <button onclick="location.href='reservationsHistory.php'">Reservations History</button>
        <button onclick="logout()">Logout</button>
    </div>
    <div class="container">
        <h1>Reservations</h1>
        <?php foreach ($reservations as $reservation): ?>
        <div class="reservation-box" id="reservation-<?php echo $reservation['id']; ?>">
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($reservation['full_name']); ?></p>
            <p><strong>Birthdate:</strong> <?php echo htmlspecialchars($reservation['birthdate']); ?></p>
            <p><strong>Reservation Date:</strong> <?php echo htmlspecialchars($reservation['reservation_date']); ?></p>
            <p><strong>Reservation Time:</strong> <?php echo htmlspecialchars($reservation['reservation_time']); ?></p>
            <p><strong>Seating:</strong> <?php echo htmlspecialchars($reservation['seating']); ?></p>
            <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($reservation['special_requests']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($reservation['phone']); ?></p>
            <p><strong>Number of People:</strong> <?php echo htmlspecialchars($reservation['num_people']); ?></p>
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
