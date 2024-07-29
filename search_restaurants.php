<?php
require 'db.php';

$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';

if ($searchQuery !== '') {
    $searchQuery = '%' . $searchQuery . '%';
    $stmt = $conn->prepare("SELECT id, name, logo FROM restaurants WHERE name LIKE ?");
    $stmt->bind_param("s", $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    $restaurants = [];
    while ($row = $result->fetch_assoc()) {
        $restaurants[] = $row;
    }

    echo json_encode($restaurants);
} else {
    echo json_encode([]);
}
?>
