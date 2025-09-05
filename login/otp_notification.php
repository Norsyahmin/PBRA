<?php
function showOtpNotification($user, $mail)
{
    // Ensure session is active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        // Generate OTP and store expiry (5 minutes)
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes

        // Clear previous recipients/attachments to avoid accidental reuse
        if (method_exists($mail, 'clearAllRecipients')) {
            $mail->clearAllRecipients();
        } else {
            $mail->clearAddresses();
            $mail->clearReplyTos();
        }
        if (method_exists($mail, 'clearAttachments')) {
            $mail->clearAttachments();
        }

        $mail->setFrom('noreply@pb.edu.bn', 'Politeknik Brunei Role Appointment System');
        // Send OTP to primary email (explicit)
        $mail->addAddress($user['email']);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Your PBRA Admin Login OTP";
        $mail->Body = "Welcome to PBRA System, " . htmlspecialchars($user['full_name']) . "! <br><br>Enter this code to continue logging in as admin: <b>$otp</b><br>This code will expire in 5 minutes. <br>If you didn't attempt to log in, you can safely ignore this email.";
        $mail->AltBody = "Your OTP for admin login is: $otp\nThis code will expire in 5 minutes.";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("Error sending OTP email to " . $user['email'] . ": " . $e->getMessage());
        return false;
    }
}
