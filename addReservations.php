<?php
session_start();
require 'db.php';

$userId = $_SESSION['user_id']; // Assuming the admin's user ID is stored in the session

// Fetch the list of restaurants created by the admin
$sql = "SELECT id, name FROM restaurants WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$restaurants = [];
while ($row = $result->fetch_assoc()) {
    $restaurants[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = $_POST['fullName'];
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : NULL;
    $reservationDate = $_POST['reservationDate'];
    $reservationTime = $_POST['reservationTime'];
    $seating = $_POST['seating'];
    $specialRequests = $_POST['specialRequests'];
    $phone = $_POST['phone'];
    $numPeople = $_POST['numPeople'];
    $restaurantId = $_POST['restaurantId'];

    // Insert reservation into the database
    $sql = "INSERT INTO reservations (restaurant_id, full_name, birthdate, reservation_date, reservation_time, seating, special_requests, phone, num_people) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssi", $restaurantId, $fullName, $birthdate, $reservationDate, $reservationTime, $seating, $specialRequests, $phone, $numPeople);

    if ($stmt->execute()) {
        header("Location: viewReservations.php?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reservation</title>
    <link rel="stylesheet" href="stylesRf.css">
</head>
<body>
    <div class="container">
        <h1>Add Reservation</h1>
        <form action="addReservations.php" method="post" id="reservationForm">
            <div class="form-group">
                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="fullName" required>
            </div>
            <div class="form-group">
                <label for="birthdate">Birthdate:</label>
                <input type="date" id="birthdate" name="birthdate">
            </div>
            <div class="form-group">
                <label for="reservationDate">Reservation Date:</label>
                <input type="date" id="reservationDate" name="reservationDate" required onchange="updateAvailableTimes()">
            </div>
            <div class="form-group">
                <label for="numPeople">Number of People:</label>
                <input type="number" id="numPeople" name="numPeople" required min="1" onchange="updateAvailableTimes()">
            </div>
            <div class="form-group">
                <label for="reservationTime">Reservation Time:</label>
                <select id="reservationTime" name="reservationTime" required>
                    <!-- Time options will be populated by JavaScript -->
                </select>
            </div>
            <div class="form-group">
                <label for="seating">Seating Preference:</label>
                <select id="seating" name="seating" required>
                    <option value="outdoor">Outdoor</option>
                    <option value="indoor">Indoor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="specialRequests">Special Requests:</label>
                <textarea id="specialRequests" name="specialRequests"></textarea>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="restaurantId">Restaurant:</label>
                <select id="restaurantId" name="restaurantId" required onchange="updateAvailableTimes()">
                    <?php foreach ($restaurants as $restaurant): ?>
                        <option value="<?php echo $restaurant['id']; ?>"><?php echo htmlspecialchars($restaurant['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Add Reservation</button>
        </form>
    </div>
    <script>
        function updateAvailableTimes() {
            const restaurantId = document.getElementById('restaurantId').value;
            const reservationDate = document.getElementById('reservationDate').value;
            const numPeople = document.getElementById('numPeople').value;

            if (!restaurantId || !reservationDate || !numPeople) {
                return;
            }

            fetch(`getAvailableTimes.php?restaurantId=${restaurantId}&date=${reservationDate}&numPeople=${numPeople}`)
                .then(response => response.json())
                .then(data => {
                    const reservationTimeSelect = document.getElementById('reservationTime');
                    reservationTimeSelect.innerHTML = '';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.time;
                        option.textContent = item.time;
                        if (!item.available) {
                            option.style.color = 'red';
                            option.disabled = true;
                        }
                        reservationTimeSelect.appendChild(option);
                    });
                });
        }
    </script>
</body>
</html>
