<?php
session_start();
require_once '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['id'];
$error_message = '';
$success_message = '';

// Check if user must change password
$query = $conn->prepare("SELECT must_change_password FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$query->bind_result($must_change_password);
$query->fetch();
$query->close();

if ($must_change_password != 1) {
    header("Location: ../homepage/homepage.php");
    exit();
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        if ($stmt->execute()) {
            $success_message = "Password changed successfully. You may now use your new password.";
            echo "<meta http-equiv='refresh' content='2;url=../homepage/homepage.php'>";
        } else {
            $error_message = "Error updating password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Change Temporary Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Use login.css for unified design -->
    <link rel="stylesheet" href="../login/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <img src="../login/images/pbralogo.png" alt="PbRa Logo" class="college-logo">
            <h2>Change Temporary Your Password</h2>
            <p>You must change your temporary password before accessing the system.</p>
            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
        </div>
        <form method="post" autocomplete="off" class="login-form">
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required minlength="8" placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="Confirm new password">
            </div>
            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            <button type="submit" class="login-button">Change Password</button>
        </form>
        <div class="login-links">
            <p>
                <a href="../login/login.php" class="forgot-password">Back to Login</a>
            </p>
        </div>
        <div class="login-footer">
            <p>&copy; <?= date("Y"); ?> Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
        </div>
    </div>
</body>

</html>