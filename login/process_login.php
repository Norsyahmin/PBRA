<?php
session_start();
require_once '../mypbra_connect.php'; // Ensure this path is correct!
require_once '../mailer.php'; // For sending OTP email

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Email and password are required.";
        header("Location: login.php");
        exit();
    }

    // First check if the is_verified column exists
    $columnExists = false;
    $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
    if ($checkColumn->num_rows > 0) {
        $columnExists = true;
    }

    // Prepare and execute SQL query with or without is_verified column
    if ($columnExists) {
        $stmt = $conn->prepare("SELECT id, email, password, full_name, profile_pic, is_verified, user_type, recovery_email FROM users WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, email, password, full_name, profile_pic, user_type, recovery_email FROM users WHERE email = ?");
    }

    if (!$stmt) {
        $_SESSION['login_error'] = "Database error: Unable to prepare statement.";
        header("Location: ../login/login.php");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verify credentials
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if user is verified (if column exists)
            if (!$columnExists || $user['is_verified'] == 1) {
                // Store user info in session
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['profile_pic'] = !empty($user['profile_pic']) ? $user['profile_pic'] : 'profile/images/default-profile.jpg';
                $_SESSION['user_type'] = $user['user_type'];

                if ($user['user_type'] === 'admin') {
                    // Generate OTP
                    $otp = rand(100000, 999999);
                    $_SESSION['otp'] = $otp;
                    $_SESSION['otp_expiry'] = time() + 300; // 5 minutes

                    // Send OTP to admin's recovery email
                    $mail = require '../mailer.php';
                    $mail->clearAddresses();
                    $mail->setFrom('noreply@pb.edu.bn', 'Politeknik Brunei Role Appointment System');
                    $mail->addAddress($user['recovery_email']);
                    $mail->Subject = "Your PbRA Admin Login OTP";
                    $mail->Body = "Your OTP for admin login is: <b>$otp</b><br>This code will expire in 5 minutes.";
                    $mail->AltBody = "Your OTP for admin login is: $otp\nThis code will expire in 5 minutes.";
                    try {
                        $mail->send();
                        $stmt->close();
                        header("Location: ../login/verify_otp.php");
                        exit();
                    } catch (Exception $e) {
                        $_SESSION['login_error'] = "Failed to send OTP email. Please contact IT support.";
                        $stmt->close();
                        header("Location: ../login/login.php");
                        exit();
                    }
                } else {
                    // Regular user: direct to homepage
                    $stmt->close();
                    header("Location: ../homepage/homepage.php");
                    exit();
                }
            } else {
                // User is not verified
                $_SESSION['login_error'] = "Please verify your email address before logging in. Check your inbox for the verification link.";
                header("Location: ../login/login.php");
                exit();
            }
        } else {
            // Password does not match
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: ../login/login.php");
            exit();
        }
    } else {
        // No user found with that email
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: ../login/login.php");
        exit();
    }

    $stmt->close();
} else {
    // If someone tries to access process_login.php directly without POST request
    header("Location: ../login/login.php");
    exit();
}
