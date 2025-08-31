<?php
session_start();
include '../mypbra_connect.php';
include '../languages/language_setup.php';

$error_message = $_SESSION['resend_error'] ?? '';
unset($_SESSION['resend_error']);

$success_message = $_SESSION['resend_success'] ?? '';
unset($_SESSION['resend_success']);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('resend_verification_title', 'Resend Verification Email'); ?></title>
    <link rel="stylesheet" href="../languages/language_switcher.css">
    <link rel="stylesheet" href="../login/login.css">
    <!-- Include Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- Language Switcher Container -->
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

    <div class="login-container">
        <div class="login-header">
            <img src="../login/images/pbralogo.png" alt="PbRa Logo" class="college-logo">
            <h2><?= get_text('page_title', 'Role Appointment'); ?></h2>
            <p><?= get_text('resend_verification_sub_heading', 'Enter your email to resend the verification link.'); ?></p>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message); ?>
                </div>

                <script>
                    // Redirect to login after 5 seconds without showing a countdown
                    setTimeout(function() {
                        window.location.href = '../login/login.php';
                    }, 10000);
                </script>
            <?php endif; ?>
        </div>

        <?php if (!$success_message): ?>
            <form action="process_resend_verification.php" method="post" class="login-form">
                <div class="form-group">
                    <label for="email"><?= get_text('email_label', 'Enter your primary email:'); ?></label>
                    <input type="email" id="email" name="email"
                        placeholder="<?= get_text('email_placeholder', 'e.g., muhamad.ali@pb.edu.bn'); ?>" required>
                </div>

                <?php if ($error_message): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="login-button"><?= get_text('resend_verification_submit', 'Resend Verification Email'); ?></button>
            </form>
        <?php endif; ?>

        <div class="login-links">
            <p>
                <a href="../login/login.php" class="forgot-password"><?= get_text('back_to_login', 'Back to Login'); ?></a>
            </p>
        </div>

        <div class="login-footer">
            <p>&copy; <?= date("Y"); ?> Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
        </div>
    </div>
    <script src="../languages/language_switcher.js"></script> <!-- Link to your new JavaScript file -->
</body>

</html>