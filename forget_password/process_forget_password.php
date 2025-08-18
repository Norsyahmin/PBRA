<?php
session_start();
require_once '../mypbra_connect.php';

$email = $_POST['email'] ?? '';
if (!$email) {
    $_SESSION['forgot_error'] = 'Please enter your email address.';
    header('Location: forget_password.php');
    exit;
}

// Example: Check if email exists in users table
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Here you would generate a token and send an email
    $_SESSION['forgot_success'] = 'A password reset link has been sent to your email (demo only).';
} else {
    $_SESSION['forgot_error'] = 'Email address not found.';
}
$stmt->close();
header('Location: forget_password.php');
exit;
