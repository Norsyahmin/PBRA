<?php
function send_user_registration_notification($user_id, $full_name, $email, $password, $recovery_email, $department_id, $role_id, $office, $user_type, $conn)
{
    try {
        // Get the mailer instance
        $mail = require_once __DIR__ . '/../mailer.php';

        // Set sender and recipient
        $mail->setFrom('noreply@pb.edu.bn', 'PBRA System');
        // Send to primary email
        $mail->addAddress($email);

        // Generate token and hash for verification
        $token = bin2hex(random_bytes(32));
        $token_hash = hash("sha256", $token);
        $expiry_time = date('Y-m-d H:i:s', time() + 60 * 60 * 24); // Token valid for 24 hours

        // Remove any previous tokens for this user
        $del_stmt = $conn->prepare("DELETE FROM email_verifications WHERE user_id = ?");
        $del_stmt->bind_param("i", $user_id);
        $del_stmt->execute();
        $del_stmt->close();

        // Insert new token
        // store recovery_email column as primary email for backward compatibility
        $ins_stmt = $conn->prepare("INSERT INTO email_verifications (user_id, email, recovery_email, token_hash, expires_at) VALUES (?, ?, ?, ?, ?)");
        $ins_stmt->bind_param("issss", $user_id, $email, $email, $token_hash, $expiry_time);
        $ins_stmt->execute();
        $ins_stmt->close();

        // Create verification link
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/account_activation/verify_account.php?token=" . $token;

        // Get department and role names
        $dept_query = "SELECT name FROM departments WHERE id = ?";
        $dept_stmt = $conn->prepare($dept_query);
        $dept_stmt->bind_param("i", $department_id);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        $dept_name = ($dept_result->num_rows > 0) ? $dept_result->fetch_assoc()['name'] : 'Unknown';
        $dept_stmt->close();

        $role_query = "SELECT name FROM roles WHERE id = ?";
        $role_stmt = $conn->prepare($role_query);
        $role_stmt->bind_param("i", $role_id);
        $role_stmt->execute();
        $role_result = $role_stmt->get_result();
        $role_name = ($role_result->num_rows > 0) ? $role_result->fetch_assoc()['name'] : 'Unknown';
        $role_stmt->close();

        // Set subject and body
        $mail->Subject = 'Your New PBRA Account Details';

        // Format the email body
        $body = "
        <h2>Welcome to PBRA System, {$full_name}!</h2>
        <p>An account has been created for you with the following details:</p>
        <table border='0' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>
            <tr>
                <td><strong>Email:</strong></td>
                <td>{$email}</td>
            </tr>
            <tr>
                <td><strong>Password:</strong></td>
                <td>{$password}</td>
            </tr>
            <tr>
                <td><strong>Department:</strong></td>
                <td>{$dept_name}</td>
            </tr>
            <tr>
                <td><strong>Role:</strong></td>
                <td>{$role_name}</td>
            </tr>
            <tr>
                <td><strong>Office:</strong></td>
                <td>{$office}</td>
            </tr>
            <tr>
                <td><strong>User Type:</strong></td>
                <td>{$user_type}</td>
            </tr>
        </table>
        <p><strong>Important:</strong> Please verify your account by clicking the link below:</p>
        <p><a href='{$verification_link}'>{$verification_link}</a></p>
        <p>This link will expire in 24 hours. If you did not create this account, please ignore this email.</p>
        <p>We recommend changing your password after your first login for security reasons.</p>
        <p>You can do this through the account settings page.</p>
        <p>If you have any questions, please contact the system administrator.</p>
        ";

        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        // Send the email
        return $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send user registration notification email: " . $e->getMessage());
        return false;
    }
}
