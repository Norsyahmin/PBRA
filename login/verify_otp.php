<?php
session_start();
// Include the language setup
include_once '../languages/language_setup.php'; // Path relative to verify_otp.php

if (!isset($_SESSION['id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    // Redirect unauthenticated/non-admin users to login, maintaining language if possible
    header("Location: login.php?lang=" . htmlspecialchars($current_language));
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = trim($_POST['otp'] ?? '');
    if (empty($input_otp)) {
        $error = get_text('otp_empty_error', "Please enter the OTP.");
    } elseif (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry']) || time() > $_SESSION['otp_expiry']) {
        $error = get_text('otp_expired_error', "OTP expired. Please login again.");
        session_destroy();
        // Redirect to login after a short delay, carrying language preference
        echo "<script>alert('" . htmlspecialchars(get_text('otp_expired_alert', 'OTP expired. Please login again.')) . "'); window.location.href='login.php?lang=" . htmlspecialchars($current_language) . "';</script>";
        exit();
    } elseif ($input_otp == $_SESSION['otp']) {
        // OTP correct, allow access
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expiry']);
        unset($_SESSION['otp_recovery_email']);
        header("Location: ../homepage/homepage.php"); // No language param needed as session holds it
        exit();
    } else {
        $error = get_text('otp_invalid_error', "Invalid OTP. Please try again.");
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language); ?>"> <!-- Set lang attribute dynamically -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('otp_verification_title', 'Admin OTP Verification'); ?></title>
    <link rel="stylesheet" href="verify_otp.css"> <!-- Ensure this links to your CSS for verify_otp -->
    <link rel="stylesheet" href="../languages/language_switcher.css">
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
                <!-- This link should ideally point back to the current page (verify_otp.php) with the new lang parameter -->
                <a href="?lang=<?= htmlspecialchars($code); ?>"
                    class="<?= ($code === $current_language) ? 'active' : ''; ?>">
                    <?= htmlspecialchars($data['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Changed container class to match CSS: otp-container -->
    <div class="otp-container">
        <!-- Changed header class to match CSS: otp-header -->
        <div class="otp-header">
            <img src="images/pbralogo.png" alt="College Logo" class="college-logo">
            <h2><?= get_text('page_title', 'Politeknik Brunei <br> Role Appointment'); ?></h2>
            <p><?= get_text('otp_sub_heading', 'Admin One-Time Password Verification'); ?></p>
        </div>

        <!-- Changed form class to match CSS: otp-form -->
        <form method="post" class="otp-form">
            <div class="form-group">
                <label for="otp"><?= get_text('enter_otp_label', 'Enter OTP sent to your recovery email:'); ?></label>
                <input
                    type="password"
                    id="otp"
                    name="otp"
                    value=""
                    placeholder="<?= get_text('otp_placeholder', 'Enter 6-digit OTP'); ?>"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    autocomplete="one-time-code"
                    required>
            </div>

            <?php if ($error) : ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="otp-button"><?= get_text('verify_otp_button', 'Verify OTP'); ?></button>
        </form>

        <div class="otp-links" style="margin-top: 20px;">
            <p><?= get_text('otp_did_not_receive', "Didn't receive the OTP?"); ?>
                <a href="resend_otp.php" class="verify-link"><?= get_text('resend_otp_link', 'Resend OTP'); ?></a>
            </p>
            <p><?= get_text('otp_lost_recovery_email', 'Lost access to recovery email?'); ?>
                <a href="contact_support.php" class="verify-link"><?= get_text('contact_support_link', 'Contact Support'); ?></a>
            </p>
        </div>

        <div class="otp-footer">
            <p><?= get_text('footer_text', '&copy; ' . date("Y") . ' Politeknik Brunei Role Appointment (PbRA). All rights reserved.'); ?></p>
        </div>
    </div>

    <script src="../languages/language_switcher.js"></script>
</body>

</html>