<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];

        // Delete related records in ratings table
        $sql = "DELETE FROM ratings WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Now delete the user
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $birthdate = $_POST['birthdate'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $password = $_POST['password']; // Ensure password is in plain text

        $sql = "UPDATE users SET name=?, birthdate=?, phone=?, email=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $birthdate, $phone, $email, $password, $id);
        $stmt->execute();
    }
}


$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Accounts</title>
    <link rel="stylesheet" href="stylesAdminIndex.css">

    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}

button {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 10px 2px;
    cursor: pointer;
    border-radius: 5px;
}

button:hover {
    background-color: #45a049;
}

h1 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    overflow: hidden;
}

th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background-color: #f2f2f2;
    color: #333;
}

tr:hover {
    background-color: #f1f1f1;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

input[type="text"] {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
}
</style>

</head>
<body>
    <button onclick="location.href='adminIndex.html'">Back</button>
    <h1>Edit User Accounts</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Birthdate</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Password</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <form method="POST">
                <td><input type="text" name="id" value="<?php echo htmlspecialchars($row['id']); ?>" readonly></td>
                <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>"></td>
                <td><input type="text" name="birthdate" value="<?php echo htmlspecialchars($row['birthdate']); ?>"></td>
                <td><input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>"></td>
                <td><input type="text" name="email" value="<?php echo htmlspecialchars($row['email']); ?>"></td>
                <td><input type="text" name="password" value="<?php echo htmlspecialchars($row['password']); ?>"></td>
                <td>
                    <button type="submit" name="edit">Edit</button>
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
