<?php
session_start();
include '../languages/language_setup.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_text('contact_support_title', 'Contact Support'); ?></title>
    <link rel="stylesheet" href="../login/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <img src="../login/images/pbralogo.png" alt="PbRa Logo" class="college-logo">
            <h2><?= get_text('contact_support_heading', 'Contact Support'); ?></h2>
            <p><?= get_text('contact_support_sub_heading', 'Please reach out to IT support for assistance.'); ?></p>
        </div>

        <div class="login-links">
            <p><?= get_text('contact_support_email', 'Email: support@pb.edu.bn'); ?></p>
            <p><?= get_text('contact_support_phone', 'Phone: +673-1234567'); ?></p>
        </div>

        <div class="login-footer">
            <p>&copy; <?= date("Y"); ?> Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
        </div>
    </div>
</body>

</html>