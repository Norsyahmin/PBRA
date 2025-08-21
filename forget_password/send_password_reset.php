<?php
session_start();
require_once '../mypbra_connect.php';

$recovery_email = $_POST['recovery_email'] ?? '';

if (!$recovery_email) {
    $_SESSION['forgot_error'] = 'Please enter your email address.';
    header('Location: forget_password.php?success=no_email_provided'); // Use success param for clearer error indication
    exit;
}

// Example: Check if email exists in users table (assuming 'recovery_email' field)
$stmt = $conn->prepare("SELECT id FROM users WHERE recovery_email = ?");
$stmt->bind_param("s", $recovery_email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
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
        $mail = include '../mailer.php';  // Load PHPMailer

        // Replace 'example.com' with your actual domain or localhost path
        // IMPORTANT: Ensure this URL is correct for your deployment
        $reset_link = "http://localhost/forget_password/reset_password.php?token=" . $token;

        $mail->isHTML(true);
        $mail->clearAddresses();

        // Set the sender as noreply email address
        $mail->setFrom('noreply@pb.edu.bn', 'Politeknik Brunei Role Appointment System');

        $mail->addAddress($recovery_email);
        $mail->Subject = "Password Reset Request";
        // Fetch user's full name from the database
        $name_stmt = $conn->prepare("SELECT full_name FROM users WHERE recovery_email = ?");
        $name_stmt->bind_param("s", $recovery_email);
        $name_stmt->execute();
        $name_stmt->bind_result($full_name);
        $name_stmt->fetch();
        $name_stmt->close();

        $mail->Body = "Dear $full_name,<br><br>"
            . "You have requested to reset your password. Click on the following link to reset your password:<br><br>"
            . "<a href='$reset_link'>$reset_link</a><br><br>This link will expire in 5 minutes. If you did not request a password reset, please ignore this email.";
        $mail->AltBody = "You have requested to reset your password. Visit: $reset_link\n\nThis link will expire in 5 minutes. If you did not request a password reset, please ignore this email.";

        try {
            $mail->send();
            header('Location: forget_password.php?success=email_sent');
        } catch (Exception $e) {
            // Email sending failed
            error_log("PHPMailer error: " . $mail->ErrorInfo); // Log the error
            header('Location: forget_password.php?success=email_send_failed'); // Pass error type
        }
    } else {
        // Error inserting the password reset token
        error_log("Failed to insert password reset token: " . $conn->error); // Log the error
        header('Location: forget_password.php?success=database_error'); // Pass error type
    }
    $ins_stmt->close();
} else {
    // Email address not found. Provide a generic message for security.
    // Instead of saying "email not found", say "If an account exists, a reset link has been sent."
    // For this request, we'll follow the current error message style.
    $_SESSION['forgot_error'] = 'Email address not found.';
    header('Location: forget_password.php?success=email_not_found'); // Pass error type
}
$stmt->close(); // Close statement if it was opened
exit;
