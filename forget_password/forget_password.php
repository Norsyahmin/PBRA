<?php
session_start();
include '../languages/language_setup.php';

// Get messages from session or query parameters
$error_message = $_SESSION['forgot_error'] ?? ($_GET['error'] ?? '');
unset($_SESSION['forgot_error']); // Clear after displaying

// Update success message to include the 5-minute expiration
$success_type = $_GET['success'] ?? '';
if ($success_type === 'email_sent') {
    $_SESSION['forgot_success'] = 'A password reset link has been sent to your email. The link will expire in 5 minutes.';
} else if ($success_type === 'email_not_found') {
    // Handle the case where email is not found more gracefully
    $_SESSION['forgot_error'] = get_text('forgot_email_not_found_error', 'Email address not found. Please check the email and try again.');
} else if ($success_type === 'email_send_failed') {
    $_SESSION['forgot_error'] = get_text('forgot_email_send_failed_error', 'Failed to send password reset email. Please try again later.');
} else if ($success_type === 'no_email_provided') {
    $_SESSION['forgot_error'] = get_text('forgot_no_email_provided_error', 'Please enter your email address.');
} else if ($success_type === 'database_error') {
    $_SESSION['forgot_error'] = get_text('forgot_database_error', 'A system error occurred. Please try again later.');
}

$success_message = $_SESSION['forgot_success'] ?? '';
unset($_SESSION['forgot_success']); // Clear after displaying
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('forgot_password_title', 'Forgot Password'); ?></title>
    <link rel="stylesheet" href="../languages/language_switcher.css">
    <link rel="stylesheet" href="../login/login.css">
    <!-- Include Font Awesome CSS for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- Language Switcher Container (Copied from login.php) -->
    <div class="language-switcher-container">
        <div class="language-trigger" id="languageTrigger">
            <span><?= htmlspecialchars($supported_languages[$current_language]['name']); ?></span>
            <i class="fas fa-chevron-down arrow-icon"></i>
        </div>
        <div class="language-dropdown" id="languageDropdown">
            <?php foreach ($supported_languages as $code => $data) : ?>
                <a href="?lang=<?= htmlspecialchars($code); ?>"
                    class="<?= ($code === $current_language) ? 'active' : ''; ?>">
                    <?= htmlspecialchars($data['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main container matching login.php's structure -->
    <div class="login-container">
        <div class="login-header">
            <!-- Image path adjusted to be relative to the common 'images' directory -->
            <img src="../login/images/pbralogo.png" alt="PbRa Logo" class="college-logo">
            <h2><?= get_text('page_title', 'Role Appointment'); ?></h2>
            <p><?= get_text('forgot_password_sub_heading', 'Enter your email to receive a reset password link'); ?></p>

            <!-- Success Message Display -->
            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$success_message): /* Only show the form if there's no success message */ ?>
            <form action="send_password_reset.php" method="post" class="login-form">
                <div class="form-group">
                    <label for="recovery_email"><?= get_text('forgot_email_label', 'Enter Recovery Email:'); ?></label>
                    <input type="email" id="recovery_email" name="recovery_email"
                        placeholder="<?= get_text('forgot_email_placeholder', 'e.g., muhamad.ali@pb.edu.bn'); ?>" required>
                </div>

                <!-- Error Message Display -->
                <?php if ($error_message): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="login-button"><?= get_text('forgot_submit', 'Send Reset Link'); ?></button>
            </form>
        <?php endif; ?>

        <div class="login-links">
            <p>
                <a href="../login/login.php" class="forgot-password">
                    <?= get_text('back_to_login', 'Back to Login'); ?>
                </a>
            </p>
        </div>

        <div class="login-footer">
            <p>&copy; <?php echo date("Y"); ?> Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
        </div>
    </div>

    <!-- JavaScript for Language Switcher (Copied from login.php) -->
    <script src="../languages/language_switcher.js"></script>
    <?php if ($success_message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    window.location.href = '../login/login.php';
                }, 5000); // Redirect after 5 seconds
            });
        </script>
    <?php endif; ?>
</body>

</html>