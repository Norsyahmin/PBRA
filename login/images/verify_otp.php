<?php
session_start();
if (!isset($_SESSION['id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = trim($_POST['otp'] ?? '');
    if (empty($input_otp)) {
        $error = "Please enter the OTP.";
    } elseif (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry']) || time() > $_SESSION['otp_expiry']) {
        $error = "OTP expired. Please login again.";
        session_destroy();
        header("Refresh:2; url=login.php");
        exit();
    } elseif ($input_otp == $_SESSION['otp']) {
        // OTP correct, allow access
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expiry']);
        unset($_SESSION['otp_recovery_email']);
        header("Location: ../homepage/homepage.php");
        exit();
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin OTP Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="verify_otp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="otp-bg">
        <div class="otp-header">
            <img src="images/pbralogo.png" alt="PbRa Logo" width="250" height="100" />
            <h1>Politeknik Brunei<br>Role Appointment</h1>
        </div>
        <div class="otp-container">
            <div class="otp-form">
                <h2>Admin OTP Verification</h2>
                <form method="post">
                    <label for="otp">Enter OTP sent to your recovery email:</label>
                    <input type="text" name="otp" id="otp" maxlength="6" required placeholder="Enter OTP">
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    <button type="submit">Verify OTP</button>
                </form>
            </div>
        </div>
        <footer>
            <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
        </footer>
    </div>
</body>

</html>