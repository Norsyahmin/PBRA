<?php
session_start();
include '../mypbra_connect.php';

$error_message = "";
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

$has_pending_reports = false;
$user_id = $_SESSION['id'] ?? 0;

// FIXED QUERY (remove status if it doesn't exist)
$stmt = $conn->prepare("SELECT COUNT(*) FROM reports WHERE report_to = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($count);
if ($stmt->fetch() && $count > 0) {
    $has_pending_reports = true;
}
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <img src="images/pbralogo.png" alt="PbRa Logo" width="250" height="100"/>
    <h1>Politeknik Brunei <br> Role Appointment</h1>
    
    <div class="container">
        <div class="login-form">
            <form action="process_login.php" method="post">
                <label for="email">Email: </label>
                <input type="email" id="email" name="email" placeholder="e.g muhamad.ali@pb.edu.bn" required>
            
                <label for="password">Password: </label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <!-- Error Message Display -->
                <!-- Error Message Display -->
                <?php if (!empty($error_message)) : ?>
                <div class="error-message">
                <?= htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>


                <button type="submit">Login</button>
            </form>            
        </div>
    </div>
</body>
</html>
