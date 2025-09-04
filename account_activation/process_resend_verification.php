<?php
session_start();
include '../mypbra_connect.php';
include_once 'send_verification_email.php';

$email = $_POST['email'] ?? '';

if (empty($email)) {
    $_SESSION['resend_error'] = 'Please enter your email address.';
    header('Location: resend_verification.php');
    exit;
}

// Check if user exists and is not verified
$stmt = $conn->prepare("SELECT id, email, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $user_id = $row['id'];
    $is_verified = $row['is_verified'];
    $primary_email = $row['email'];

    if ($is_verified == 1) {
        $_SESSION['resend_error'] = 'This account is already verified. Please log in.';
        header('Location: resend_verification.php');
        exit;
    }

    // Send verification email to primary email (send_verification_email will use primary email as destination)
    if (send_verification_email($user_id, $primary_email, $primary_email, $conn)) {
        $_SESSION['resend_success'] = "A new verification email has been sent to $primary_email. Please check your inbox and click on the verification link.";
    } else {
        $_SESSION['resend_error'] = 'Failed to send verification email. Please try again later.';
    }
} else {
    $_SESSION['resend_error'] = 'No account found with this email address.';
}

$stmt->close();
header('Location: resend_verification.php');
exit;
