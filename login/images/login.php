<?php
session_start();
include '../mypbra_connect.php'; // Your database connection
include_once '../languages/language_setup.php'; // Include the language setup

$error_message = "";
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

$success_message = $_SESSION['reset_success'] ?? $_SESSION['login_success'] ?? '';
unset($_SESSION['reset_success']); // Clear after displaying
unset($_SESSION['login_success']); // Clear after displaying

// Additionally, check for success parameter from URL
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'password_reset' && !$success_message) {
        $success_message = 'Your password has been successfully updated. You can now login with your new password.';
    } else if ($_GET['success'] === 'verified' && !$success_message) {
        $success_message = 'Your account has been successfully verified. You can now log in.';
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('login_title', 'Login'); ?></title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../languages/language_switcher.css">
</head>

<body>
    <?php include '../languages/language_switcher.php'; ?>
    <img src="images/pbralogo.png" alt="PbRa Logo" width="250" height="100" />
    <h1><?= get_text('page_title', 'Politeknik Brunei <br> Role Appointment'); ?></h1>

    <div class="container">
        <div class="login-form">
            <!-- Success Message Display -->
            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form action="process_login.php" method="post">
                <label for="email"><?= get_text('email_label', 'Email:'); ?> </label>
                <input type="email" id="email" name="email" placeholder="<?= get_text('email_placeholder', 'e.g muhamad.ali@pb.edu.bn'); ?>" required>

                <label for="password"><?= get_text('password_label', 'Password:'); ?> </label>
                <input type="password" id="password" name="password" placeholder="<?= get_text('password_placeholder', 'Enter your password'); ?>" required>

                <!-- Error Message Display -->
                <?php if (!empty($error_message)) : ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <button type="submit"><?= get_text('login_button', 'Login'); ?></button>
            </form>

            <!-- Links Section -->
            <div style="margin-top: 20px; text-align: center;">
                <!-- Forgot Password Link -->
                <a href="../forget_password/forget_password.php" style="color: #007bff; text-decoration: underline; font-size: 0.95em; display: block; margin-bottom: 10px;">
                    <?= get_text('forgot_password_link', 'Forgot Password?'); ?>
                </a>

                <!-- Resend Verification Email Link -->
                <a href="../account_activation/resend_verification.php" style="color: #007bff; text-decoration: underline; font-size: 0.95em;">
                    <?= get_text('resend_verification_link', 'Need to verify your account?'); ?>
                </a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
    </footer>

    <!-- Language-related JS -->
    <script src="../languages/language_switcher.js"></script>
</body>

</html>