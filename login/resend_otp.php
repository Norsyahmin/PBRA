<?php
session_start();
include '../mypbra_connect.php';
include '../languages/language_setup.php';

$error_message = $_SESSION['resend_otp_error'] ?? '';
unset($_SESSION['resend_otp_error']);

$success_message = $_SESSION['resend_otp_success'] ?? '';
unset($_SESSION['resend_otp_success']);

// Handle POST: resend OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $lang = $_POST['lang'] ?? $current_language;

    // Basic validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['resend_otp_error'] = get_text('invalid_email', 'Please enter a valid email address.');
        header("Location: resend_otp.php?lang=" . urlencode($lang));
        exit();
    }

    // Look up user and ensure there is a recovery email
    // Use primary email lookup; remove stray comma in SELECT
    $stmt = $conn->prepare("SELECT id, email, full_name FROM users WHERE email = ?");
    if (!$stmt) {
        $_SESSION['resend_otp_error'] = get_text('db_error', 'Database error. Please try again later.');
        header("Location: resend_otp.php?lang=" . urlencode($lang));
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $_SESSION['resend_otp_error'] = get_text('email_not_found', 'No account found with that email address.');
        $stmt->close();
        header("Location: resend_otp.php?lang=" . urlencode($lang));
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Primary email exists (we searched by it). OTP will be sent to primary email.

    // Send OTP using the centralized function
    require_once '../login/otp_notification.php';
    // mailer.php is expected to return a PHPMailer instance
    $mail = require '../mailer.php';

    if (showOtpNotification($user, $mail)) {
        $_SESSION['resend_otp_success'] = get_text('otp_resent_success', 'OTP has been resent to your email. Please check your inbox.');
    } else {
        $_SESSION['resend_otp_error'] = get_text('otp_send_failed', 'Failed to send OTP. Please try again later or contact support.');
    }

    header("Location: resend_otp.php?lang=" . urlencode($lang));
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language ?? 'en'); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('resend_otp_title', 'Role Appointment'); ?></title>
    <link rel="stylesheet" href="verify_otp.css"> <!-- use verify_otp styles for consistent UI -->
    <link rel="stylesheet" href="../languages/language_switcher.css"> <!-- language switcher styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- include the shared language switcher markup -->
    <?php include_once __DIR__ . '/../languages/language_switcher.php'; ?>

    <div class="otp-container">
        <div class="otp-header">
            <img src="images/pbralogo.png" alt="PbRa Logo" class="college-logo">
            <h2><?= get_text('resend_otp_heading', 'Role Appointment'); ?></h2>
            <p><?= get_text('resend_otp_sub_heading', 'Enter your email to resend the OTP.'); ?></p>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$success_message): ?>
            <form action="resend_otp.php" method="post" class="otp-form">
                <div class="form-group">
                    <label for="email"><?= get_text('email_label', 'Enter your email:'); ?></label>
                    <input type="email" id="email" name="email"
                        placeholder="<?= get_text('email_placeholder', 'e.g., muhamad.ali@pb.edu.bn'); ?>" required>
                </div>

                <!-- preserve language on POST -->
                <input type="hidden" name="lang" value="<?= htmlspecialchars($current_language); ?>">

                <?php if ($error_message): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="otp-button"><?= get_text('resend_otp_submit', 'Resend OTP'); ?></button>
            </form>
        <?php endif; ?>

        <div class="otp-links">
            <p>
                <a href="verify_otp.php?lang=<?= htmlspecialchars($current_language); ?>" class="verify-link"><?= get_text('back_to_verify_otp', 'Back to OTP Verification'); ?></a>
            </p>
        </div>

        <div class="otp-footer">
            <p>&copy; <?= date("Y"); ?> Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
        </div>
    </div>

    <script src="../languages/language_switcher.js"></script>
</body>

</html>