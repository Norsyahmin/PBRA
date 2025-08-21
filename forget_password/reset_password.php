<?php
// Start session and basic validation
session_start();
include '../languages/language_setup.php'; // Include language setup

$token = $_GET['token'] ?? '';
if (!$token) {
    header('Location: forget_password.php?error=invalid_token');
    exit;
}

// Check if the token is valid
include '../mypbra_connect.php'; // Your database connection
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

// Get success message if set (e.g., from process_reset_password.php, though usually redirect to login with success)
// For now, only handle error here. Success messages will typically be shown on login page after reset.
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('reset_password_title', 'Set New Password'); ?></title>
    <!-- Use the combined CSS for consistency -->
    <link rel="stylesheet" href="../languages/language_switcher.css">
    <link rel="stylesheet" href="../login/login.css">
    <!-- Font Awesome for icons in messages -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="language-switcher-container">
        <div class="language-trigger" id="languageTrigger">
            <span><?= htmlspecialchars($supported_languages[$current_language]['name']); ?></span>
            <i class="fas fa-chevron-down arrow-icon"></i>
        </div>
        <div class="language-dropdown" id="languageDropdown">
            <?php foreach ($supported_languages as $code => $data) : ?>
                <a href="?lang=<?= htmlspecialchars($code); ?>" class="<?= ($code === $current_language) ? 'active' : ''; ?>">
                    <?= htmlspecialchars($data['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <img src="../login/images/pbralogo.png" alt="PbRa Logo" class="college-logo">
            <!-- Main title consistent with login page -->
            <h2><?= get_text('page_title', 'Politeknik Brunei <br> Role Appointment'); ?></h2>
            <!-- Specific subtitle for this page -->
            <p><?= get_text('reset_password_sub_heading', 'Set Your New Password'); ?></p>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="process_reset_password.php" method="post" class="login-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="new_password"><?= get_text('new_password_label', 'New Password:'); ?></label>
                <input type="password" id="new_password" name="new_password" required placeholder="<?= get_text('new_password_placeholder', 'Enter new password'); ?>">
            </div>
            <div class="form-group">
                <label for="confirm_password"><?= get_text('confirm_password_label', 'Confirm New Password:'); ?></label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="<?= get_text('confirm_password_placeholder', 'Confirm new password'); ?>">
            </div>
            <button type="submit" class="login-button"><?= get_text('reset_password_button', 'Reset Password'); ?></button>
        </form>

        <div class="login-links">
            <p>
                <a href="../login/login.php" class="forgot-password"><?= get_text('back_to_login', 'Back to Login'); ?></a>
            </p>
        </div>

        <div class="login-footer">
            <p>&copy; <?= date("Y"); ?> Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
        </div>
    </div>

    <script src="../languages/language_switcher.js"></script>
</body>

</html>