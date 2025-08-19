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
        // Use get_text for this message as well
        $success_message = get_text('password_reset_success', 'Your password has been successfully updated. You can now login with your new password.');
    } else if ($_GET['success'] === 'verified' && !$success_message) {
        // Use get_text for this message as well
        $success_message = get_text('account_verified_success', 'Your account has been successfully verified. You can now log in.');
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language); ?>"> <!-- Set lang attribute dynamically -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('login_title', 'Login'); ?></title>
    <link rel="stylesheet" href="../languages/language_switcher.css">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- Language Switcher Container - Placed early in body for positioning -->
    <div class="language-switcher-container">
        <div class="language-trigger" id="languageTrigger">
            <span><?= htmlspecialchars($supported_languages[$current_language]['name']); ?></span>
            <i class="fas fa-chevron-down arrow-icon"></i>
        </div>

        <div class="language-dropdown" id="languageDropdown">
            <?php foreach ($supported_languages as $code => $data) : ?>
                <!-- Ensure the URL includes the current page base for proper redirection -->
                <!-- This link should ideally point back to the current page (login.php) with the new lang parameter -->
                <a href="?lang=<?= htmlspecialchars($code); ?>"
                    class="<?= ($code === $current_language) ? 'active' : ''; ?>">
                    <?= htmlspecialchars($data['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <img src="../login/images/pbralogo.png" alt="College Logo" class="college-logo">
            <h2><?= get_text('page_title', 'Politeknik Brunei <br> Role Appointment'); ?></h2>
            <p><?= get_text('sign_in_account', 'Sign in to your account'); ?></p> <!-- New text key -->

            <!-- Success Message Display -->
            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="../login/process_login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="email"><?= get_text('email_label', 'Email:'); ?></label>
                <input type="email" id="email" name="email" placeholder="<?= get_text('email_placeholder', 'e.g muhamad.ali@pb.edu.bn'); ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><?= get_text('password_label', 'Password'); ?></label>
                <input type="password" id="password" name="password" placeholder="<?= get_text('password_placeholder', 'Enter your password'); ?>" required>
            </div>

            <!-- Error Message Display -->
            <?php if (!empty($error_message)) : ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="form-options">
                <a href="../forget_password/forget_password.php" class="forgot-password"><?= get_text('forgot_password_link', 'Forgot Password?'); ?></a>
            </div>
            <button type="submit" class="login-button"><?= get_text('login_button', 'Login'); ?></button>
        </form>
        <div class="login-links">
            <p><?= get_text('need_to_verify_account', 'Need to verify your account?'); ?> <a href="../account_activation/resend_verification.php" class="verify-link"><?= get_text('resend_verification_link', 'Verify Now'); ?></a></p>
        </div>
        <div class="login-footer">
            <p><?= get_text('footer_text', '&copy; ' . date("Y") . ' Politeknik Brunei Role Appointment (PbRA). All rights reserved.'); ?></p>
        </div>
    </div>

    <script src="../languages/language_switcher.js"></script> <!-- Link to your new JavaScript file -->
</body>

</html>