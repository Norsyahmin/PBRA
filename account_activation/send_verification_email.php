<?php
function send_verification_email($user_id, $email, $recovery_email, $conn)
{
    // Load PHPMailer
    $mail = include '../mailer.php';

    // Fetch user's full name
    $full_name = '';
    $name_stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    if ($name_stmt) {
        $name_stmt->bind_param("i", $user_id);
        $name_stmt->execute();
        $name_stmt->bind_result($fetched_name);
        if ($name_stmt->fetch() && !empty($fetched_name)) {
            $full_name = $fetched_name;
        }
        $name_stmt->close();
    }

    // Generate token and hash
    $token = bin2hex(random_bytes(32));
    $token_hash = hash("sha256", $token);
    $expiry_time = date('Y-m-d H:i:s', time() + 60 * 60 * 24); // Token valid for 24 hours

    // Remove any previous tokens for this user
    $del_stmt = $conn->prepare("DELETE FROM email_verifications WHERE user_id = ?");
    $del_stmt->bind_param("i", $user_id);
    $del_stmt->execute();
    $del_stmt->close();

    // Insert new token - store both primary and recovery email
    $ins_stmt = $conn->prepare("INSERT INTO email_verifications (user_id, email, recovery_email, token_hash, expires_at) VALUES (?, ?, ?, ?, ?)");
    $ins_stmt->bind_param("issss", $user_id, $email, $recovery_email, $token_hash, $expiry_time);

    if ($ins_stmt->execute()) {
        // Token inserted, proceed to send email
        // Fix the verification link path - include full path without PBRA in URL
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/account_activation/verify_account.php?token=" . $token;

        // Sanitize name for HTML
        $safe_name = htmlspecialchars($full_name, ENT_QUOTES | ENT_HTML5);

        $mail->isHTML(true);
        $mail->clearAddresses();
        $mail->setFrom('noreply@pb.edu.bn', 'PBRA System');
        $mail->addAddress($recovery_email); // Send to recovery email
        $mail->Subject = "Verify Your PbRA Account";
        $mail->Body = "Dear {$safe_name},<br><br>"
            . "Thank you for registering with PbRA. Please verify your account by clicking on the link below:<br><br>"
            . "<a href='$verification_link'>$verification_link</a><br><br>"
            . "This link will expire in 24 hours. If you did not create this account, please ignore this email.";
        $mail->AltBody = "Dear {$full_name},\n\n"
            . "Thank you for registering with PbRA. Please verify your account by visiting: $verification_link\n\n"
            . "This link will expire in 24 hours. If you did not create this account, please ignore this email.";

        try {
            $mail->send();
            $ins_stmt->close();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer error: " . $mail->ErrorInfo);
            $ins_stmt->close();
            return false;
        }
    } else {
        error_log("Failed to insert verification token: " . $conn->error);
        $ins_stmt->close();
        return false;
    }
}
