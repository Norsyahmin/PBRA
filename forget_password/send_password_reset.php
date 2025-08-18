<?php
include '../mypbra_connect.php'; // Ensure DB connection
$mail = include '../mailer.php';  // Load PHPMailer

$recovery_email = $_POST['recovery_email'] ?? '';

// Basic validation for the recovery email
if (empty($recovery_email)) {
    // Redirect or show an error if no email is provided
    header('Location: forget_password.php?error=no_email_provided');
    exit();
}

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE recovery_email = ?");
$stmt->bind_param("s", $recovery_email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    header('Location: forget_password.php?error=email_not_found');
    exit();
}
$stmt->close();

// Generate token and hash
$token = bin2hex(random_bytes(32)); // Increased token length for better security
$token_hash = hash("sha256", $token);
$expiry_time = date('Y-m-d H:i:s', time() + 60 * 5); // Token valid for 5 minutes

// Remove any previous tokens for this email
$del_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$del_stmt->bind_param("s", $recovery_email);
$del_stmt->execute();
$del_stmt->close();

// Insert new token
$ins_stmt = $conn->prepare("INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)");
$ins_stmt->bind_param("sss", $recovery_email, $token_hash, $expiry_time);
if ($ins_stmt->execute()) {
    // Token inserted, proceed to send email

    // Replace 'example.com' with your actual domain or localhost path
    $reset_link = "http://localhost/forget_password/reset-password.php?token=" . $token;

    $mail->isHTML(true);
    $mail->clearAddresses();

    // Set the sender as noreply email address
    $mail->setFrom('noreply@pb.edu.bn', 'Politeknik Brunei Role Appointment System');

    $mail->addAddress($recovery_email);
    $mail->Subject = "Password Reset Request";
    $mail->Body = "You have requested to reset your password. Click on the following link to reset your password:<br><br>"
        . "<a href='$reset_link'>$reset_link</a><br><br>This link will expire in 5 minutes. If you did not request a password reset, please ignore this email.";
    $mail->AltBody = "You have requested to reset your password. Visit: $reset_link\n\nThis link will expire in 5 minutes. If you did not request a password reset, please ignore this email.";

    try {
        $mail->send();
        header('Location: forget_password.php?success=email_sent');
    } catch (Exception $e) {
        // Email sending failed
        // Log this error for debugging
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        header('Location: forget_password.php?error=email_send_failed');
    }
} else {
    // Error inserting the password reset token
    // Log this error for debugging
    error_log("Failed to insert password reset token: " . $conn->error);
    header('Location: forget_password.php?error=database_error');
}
$ins_stmt->close();
exit(); // Always exit after a header redirect
exit(); // Always exit after a header redirect
