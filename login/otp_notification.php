<?php
function showOtpNotification($user, $mail)
{
    try {
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes

        // Clear any previous addresses and set up for the current user
        $mail->clearAddresses();
        $mail->setFrom('noreply@pb.edu.bn', 'Politeknik Brunei Role Appointment System');
        $mail->addAddress($user['recovery_email']);
        $mail->Subject = "Your PBRA Admin Login OTP";
        $mail->Body = "Welcome to PBRA System, {$user['full_name']}! <br><br>Enter this code to continue logging in as admin: <b>$otp</b><br>This code will expire in 5 minutes. <br>If you didn't attempt to log in, you can safely ignore this email.";
        $mail->AltBody = "Your OTP for admin login is: $otp\nThis code will expire in 5 minutes.";

        $mail->send();
        return true; // Indicate success
    } catch (Exception $e) {
        error_log("Error sending OTP email to " . $user['email'] . ": " . $e->getMessage());
        return false; // Indicate failure
    }
}
