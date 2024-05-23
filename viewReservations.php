<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM reservations WHERE restaurant_id IN (SELECT id FROM restaurants WHERE user_id = ?) AND reservation_date >= DATE_SUB(NOW(), INTERVAL 2 DAY) AND status = 'pending'";
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            position: relative;
            text-align: center;
            border-radius: 10px;
        }
        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer; 
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
            <p><strong>Full Name:</strong> <?php echo $reservation['full_name']; ?></p>
            <p><strong>Birthdate:</strong> <?php echo $reservation['birthdate']; ?></p>
            <p><strong>Reservation Date:</strong> <?php echo $reservation['reservation_date']; ?></p>
            <p><strong>Reservation Time:</strong> <?php echo $reservation['reservation_time']; ?></p>
            <p><strong>Seating:</strong> <?php echo $reservation['seating']; ?></p>
            <p><strong>Special Requests:</strong> <?php echo $reservation['special_requests']; ?></p>
            <p><strong>Phone:</strong> <?php echo $reservation['phone']; ?></p>
            <p><strong>Number of People:</strong> <?php echo $reservation['num_people']; ?></p>
            <p><strong>Status:</strong> <?php echo $reservation['status']; ?></p>
            <button onclick="updateReservationStatus(<?php echo $reservation['id']; ?>, 'accepted')">Approve</button>
            <button onclick="showDeclineModal(<?php echo $reservation['id']; ?>)">Decline</button>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal for decline reason -->
    <div id="declineModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDeclineModal()">X</span>
            <h2>Decline Reservation</h2>
            <textarea id="declineReason" placeholder="Enter reason for declining"></textarea>
            <button onclick="declineReservation()">Send</button>
        </div>
    </div>

    <script>
        let currentReservationId = null;

        function updateReservationStatus(reservationId, status) {
            fetch('updateReservationStatus.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ reservationId: reservationId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reservation status updated!');
                    document.getElementById(`reservation-${reservationId}`).remove();
                } else {
                    alert('Error updating reservation status.');
                }
            });
        }

        function showDeclineModal(reservationId) {
            currentReservationId = reservationId;
            document.getElementById('declineModal').style.display = 'block';
        }

        function closeDeclineModal() {
            document.getElementById('declineModal').style.display = 'none';
        }

        function declineReservation() {
            const reason = document.getElementById('declineReason').value;
            fetch('declineReservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ reservationId: currentReservationId, reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reservation declined!');
                    document.getElementById(`reservation-${currentReservationId}`).remove();
                    closeDeclineModal();
                } else {
                    alert('Error declining reservation.');
                }
            });
        }

        function logout() {
            location.href = "logout.php";
        }
    </script>
</body>
</html>
