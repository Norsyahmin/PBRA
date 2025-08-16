<?php
// Start session and basic validation
session_start();
$token = $_GET['token'] ?? '';
if (!$token) {
    header('Location: forget_password.php?error=invalid_token');
    exit;
}

// Check if the token is valid
include '../mypbra_connect.php';
$token_hash = hash("sha256", $token);
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Token is invalid or expired
    header('Location: forget_password.php?error=expired_token');
    exit;
}

// Get error message if exists
$error_message = $_SESSION['reset_error'] ?? '';
unset($_SESSION['reset_error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Set New Password</title>
    <link rel="stylesheet" href="forget_password.css">
</head>

<body>
    <img src="../login/images/pbralogo.png" alt="PbRa Logo" width="250" height="100" />
    <h1>Set Your New Password</h1>
    <div class="container">
        <div class="login-form">
            <?php if ($error_message): ?>
                <div class="error-message"><?= htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <form action="process_reset_password.php" method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm new password">
                </div>
                <button type="submit">Reset Password</button>
            </form>
            <div style="margin-top: 10px; text-align: center;">
                <a href="../login/login.php" style="color: #007bff; text-decoration: underline;">Back to Login</a>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
    </footer>
</body>

</html>