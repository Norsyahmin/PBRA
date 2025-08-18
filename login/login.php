<?php
session_start();
include '../mypbra_connect.php';
include_once '../includes/language_setup.php';

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
    <!-- Include Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* CSS for the Language Selector with Dropdown (Mimicking the image) */
        .language-switcher-container {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            /* Ensure consistent font */
        }

        /* Styling for the dropdown trigger (the visible select box) */
        .language-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* Distribute space between text and arrow */
            width: 180px;
            /* Fixed width for the dropdown trigger */
            padding: 8px 12px;
            /* Padding inside the trigger */
            border: 1px solid #c0c0c0;
            /* Border from the image */
            border-radius: 4px;
            /* Rounded corners */
            background-color: white;
            cursor: pointer;
            color: #333;
            font-size: 0.9em;
            /* Font size for the displayed text */
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
            /* Include padding and border in width */
        }

        .language-trigger:hover {
            border-color: #007bff;
            /* Blue border on hover */
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
            /* Light blue shadow on hover */
        }

        .language-trigger .arrow-icon {
            margin-left: 10px;
            /* Space between text and arrow */
            font-size: 0.8em;
            /* Smaller arrow icon */
            color: #555;
            /* Darker arrow color */
        }

        /* Styles to mimic the dropdown list from the image */
        .language-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 5px);
            /* Position below the trigger with a small gap */
            right: 0;
            /* Align to the right of the trigger */
            background-color: white;
            border: 1px solid #c0c0c0;
            border-radius: 4px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            /* Match trigger width */
            padding: 0;
            overflow: hidden;
            list-style: none;
            margin: 0;
            box-sizing: border-box;
            /* Include padding and border in width */
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

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            font-size: 0.9em;
            border-left: 4px solid #28a745;
            /* Green border for success */
        }

        .success-message i {
            margin-right: 8px;
            /* Space between icon and text */
        }
    </style>
</head>

<body>
    <div class="language-switcher-container">
        <div class="language-trigger" id="languageTrigger">
            <span><?= htmlspecialchars($supported_languages[$current_language]['name']); ?></span>
            <i class="fas fa-chevron-down arrow-icon"></i>
            <!-- Changed icon to a down arrow -->
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
            <!-- Registration link removed as only admins can add users -->
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
    </footer>

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