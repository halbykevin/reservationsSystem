<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurant_reservations";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_name = $_POST['email_or_name'];
    $password = $_POST['password'];
    
    if ($email_or_name == 'halbykevin@hotmail.com' && $password == 'admin') {
        $_SESSION['user_id'] = 'admin';
        $_SESSION['user_name'] = 'Admin';
        header("Location: adminIndex.html");
        exit();
    }
    
    // Check if the input is an email or a name
    if (filter_var($email_or_name, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email = ?";
    } else {
        $sql = "SELECT * FROM users WHERE name = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email_or_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Compare plain text passwords
        if ($password == $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['account_type'] = $row['account_type'];
            if ($row['account_type'] == 'restaurant') {
                header("Location: indexR.html");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "No user found with that email or name.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="stylesLogin.css" />
    <style>
        .error-message {
            color: red;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="uploads/logo.png" alt="Logo" class="logo">
        <?php if (!empty($error)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email_or_name"></label>
                <input
                    type="text"
                    id="email_or_name"
                    placeholder="Email or username"
                    name="email_or_name"
                    required
                />
            </div>
            <div class="form-group">
                <label for="password"></label>
                <input
                    type="password"
                    id="password"
                    placeholder="Password"
                    name="password"
                    required
                />
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.html">Register here</a></p>
        <br />
        <a href="forgotPassword.html">Forgot Password?</a>
    </div>
</body>
</html>
