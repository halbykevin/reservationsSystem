<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];

$dateFilter = isset($_GET['date']) ? $_GET['date'] : '';

$sql = "SELECT reservations.*, restaurants.name AS restaurant_name 
        FROM reservations 
        JOIN restaurants ON reservations.restaurant_id = restaurants.id 
        WHERE restaurants.user_id = ?";
if (!empty($dateFilter)) {
    $sql .= " AND reservation_date = ?";
}

$stmt = $conn->prepare($sql);

if (!empty($dateFilter)) {
    $stmt->bind_param("is", $userId, $dateFilter);
} else {
    $stmt->bind_param("i", $userId);
}

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
        .date-filter {
            margin: 20px;
            text-align: center;
        }
        .date-filter input {
            padding: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="button-container">
        <button onclick="location.href='indexR.php'">Home</button>
        <button onclick="location.href='myRestaurants.php'">My Restaurants</button>
        <button onclick="location.href='viewReservations.php'">Reservations</button>
        <button onclick="logout()">Logout</button>
    </div>
    <div class="container">
        <h1>Reservations History</h1>
        <div class="date-filter">
            <label for="reservation-date">Filter by Date:</label>
            <input type="date" id="reservation-date" name="reservation-date" onchange="filterByDate()">
            <button onclick="clearFilter()">Clear Filter</button>
        </div>
        <?php if (empty($reservations)): ?>
            <p>No reservations found for the selected date.</p>
        <?php endif; ?>
        <?php foreach ($reservations as $reservation): ?>
        <div class="reservation-box">
            <p><strong>Restaurant:</strong> <?php echo htmlspecialchars($reservation['restaurant_name']); ?></p>
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
        function filterByDate() {
            const date = document.getElementById('reservation-date').value;
            if (date) {
                window.location.href = `reservationsHistory.php?date=${date}`;
            }
        }

        function clearFilter() {
            window.location.href = 'reservationsHistory.php';
        }

        function logout() {
            location.href = "logout.php";
        }
    </script>
</body>
</html>
