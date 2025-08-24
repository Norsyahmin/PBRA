<?php
session_start();
include '../mypbra_connect.php';
include '../languages/language_setup.php';

$error_message = $_SESSION['resend_otp_error'] ?? '';
unset($_SESSION['resend_otp_error']);

$success_message = $_SESSION['resend_otp_success'] ?? '';
unset($_SESSION['resend_otp_success']);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('resend_otp_title', 'Resend OTP'); ?></title>
    <link rel="stylesheet" href="resend_otp.css"> <!-- changed: use standalone CSS for this page -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="resend_otp-container">
        <div class="resend_otp-header">
            <img src="../login/images/pbralogo.png" alt="PbRa Logo" class="college-logo">
            <h2><?= get_text('resend_otp_heading', 'Resend OTP'); ?></h2>
            <p><?= get_text('resend_otp_sub_heading', 'Enter your email to resend the OTP.'); ?></p>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$success_message): ?>
            <form action="process_resend_otp.php" method="post" class="resend_otp-form">
                <div class="form-group">
                    <label for="email"><?= get_text('email_label', 'Enter your email:'); ?></label>
                    <input type="email" id="email" name="email"
                        placeholder="<?= get_text('email_placeholder', 'e.g., muhamad.ali@pb.edu.bn'); ?>" required>
                </div>

                <?php if ($error_message): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="resend_otp-button"><?= get_text('resend_otp_submit', 'Resend OTP'); ?></button>
            </form>
        <?php endif; ?>

        <div class="resend_otp-links">
            <p>
                <a href="verify_otp.php" class="forgot-password"><?= get_text('back_to_verify_otp', 'Back to OTP Verification'); ?></a>
            </p>
        </div>

        <div class="resend_otp-footer">
            <p>&copy; <?= date("Y"); ?> Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
        </div>
    </div>
</body>

</html>