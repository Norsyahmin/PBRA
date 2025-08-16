<?php
session_start();
include '../includes/language_setup.php'; // Ensure language setup is included

// Get messages from session or query parameters
$error_message = $_SESSION['forgot_error'] ?? ($_GET['error'] ?? '');
unset($_SESSION['forgot_error']); // Clear after displaying

// Update success message to include the 5-minute expiration
$success_type = $_GET['success'] ?? '';
if ($success_type === 'email_sent') {
    $_SESSION['forgot_success'] = 'A password reset link has been sent to your email. The link will expire in 5 minutes.';
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
    <link rel="stylesheet" href="forget_password.css">
    <!-- Include Font Awesome CSS for the dropdown icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* CSS for the Language Selector with Dropdown (Copied from login.php) */
        .language-switcher-container {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        }

        .language-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 180px;
            padding: 8px 12px;
            border: 1px solid #c0c0c0;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
            color: #333;
            font-size: 0.9em;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        .language-trigger:hover {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }

        .language-trigger .arrow-icon {
            margin-left: 10px;
            font-size: 0.8em;
            color: #555;
        }

        .language-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 5px);
            right: 0;
            background-color: white;
            border: 1px solid #c0c0c0;
            border-radius: 4px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            padding: 0;
            overflow: hidden;
            list-style: none;
            margin: 0;
            box-sizing: border-box;
        }

        .language-dropdown.show {
            display: block;
        }

        .language-dropdown a {
            display: block;
            padding: 8px 15px;
            text-decoration: none;
            color: #333;
            font-size: 0.9em;
            transition: background-color 0.1s ease-in-out;
            white-space: nowrap;
        }

        .language-dropdown a:hover {
            background-color: #e6f7ff;
            color: #007bff;
        }

        .language-dropdown a.active {
            background-color: #e0f2ff;
            color: #007bff;
            font-weight: bold;
        }
    </style>
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

    <img src="../login/images/pbralogo.png" alt="PbRa Logo" width="250" height="100" />
    <h1><?= get_text('forgot_password_heading', 'Forgot Your Password?'); ?></h1>
    <div class="container">
        <div class="login-form">
            <?php if ($success_message): ?>
                <div class="success-message"><?= htmlspecialchars($success_message); ?></div>
            <?php else: /* Only show the form if there's no success message */ ?>
                <form action="send_password_reset.php" method="post">
                    <label for="recovery_email"><?= get_text('forgot_email_label', 'Enter your email:'); ?></label>
                    <input type="email" id="recovery_email" name="recovery_email"
                        placeholder="<?= get_text('forgot_email_placeholder', 'e.g muhamad.ali@pb.edu.bn'); ?>" required>
                    <?php if ($error_message): ?>
                        <div class="error-message"><?= htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    <button type="submit"><?= get_text('forgot_submit', 'Send Reset Link'); ?></button>
                </form>
            <?php endif; ?>
            <div style="margin-top: 10px; text-align: center;">
                <a href="../login/login.php" style="color: #007bff; text-decoration: underline;">
                    <?= get_text('back_to_login', 'Back to Login'); ?>
                </a>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
    </footer>

    <!-- JavaScript for Language Switcher (Copied from login.php) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const languageTrigger = document.getElementById('languageTrigger');
            const languageDropdown = document.getElementById('languageDropdown');

            languageTrigger.addEventListener('click', function(event) {
                languageDropdown.classList.toggle('show');
                event.stopPropagation(); // Prevent click from bubbling up and closing immediately
            });

            // Close dropdown if clicked outside
            document.addEventListener('click', function(event) {
                if (!languageDropdown.contains(event.target) && !languageTrigger.contains(event.target)) {
                    languageDropdown.classList.remove('show');
                }
            });
        });
    </script>
</body>

</html>