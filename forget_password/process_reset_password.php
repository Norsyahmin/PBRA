<?php
session_start();
include '../mypbra_connect.php';

// Get and validate form data
$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate input
if (empty($token) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['reset_error'] = 'All fields are required';
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

// Check password match
if ($new_password !== $confirm_password) {
    $_SESSION['reset_error'] = 'Passwords do not match';
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

// Validate password strength
if (strlen($new_password) < 8) {
    $_SESSION['reset_error'] = 'Password must be at least 8 characters long';
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

// Hash the token to compare with stored hash
$token_hash = hash("sha256", $token);

// Check if token exists and is valid
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $email = $row['email'];

    // Get the user's record using the email
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE recovery_email = ?");
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_row = $user_result->fetch_assoc()) {
        // Hash the new password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the user's password
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $password_hash, $user_row['id']);

        if ($update_stmt->execute()) {
            // Delete the used token
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE token_hash = ?");
            $delete_stmt->bind_param("s", $token_hash);
            $delete_stmt->execute();

            $_SESSION['reset_success'] = 'Your password has been successfully updated. You can now login with your new password.';
            header("Location: ../login/login.php?success=password_reset");
            exit;
        } else {
            $_SESSION['reset_error'] = 'Failed to update password. Please try again.';
        }
        $update_stmt->close();
    } else {
        $_SESSION['reset_error'] = 'User not found.';
    }
    $user_stmt->close();
} else {
    $_SESSION['reset_error'] = 'Invalid or expired reset token. Password reset links expire after 5 minutes. Please request a new password reset.';
}

$stmt->close();
header("Location: reset_password.php?token=" . urlencode($token));
exit;
