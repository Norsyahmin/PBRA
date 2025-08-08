<?php
session_start();
require_once '../mypbra_connect.php'; // Ensure this path is correct!

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT id, email, password, full_name, profile_pic FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verify credentials
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Store user info in session
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name']; // Store full name
            
            // Check if profile picture exists, otherwise use default
            $_SESSION['profile_pic'] = !empty($user['profile_pic']) ? $user['profile_pic'] : 'profile/images/default-profile.jpg';

            $stmt->close();
            header("Location: ../homepage/homepage.php"); // Redirect to homepage.php
            exit();
        }
    }

    // If login fails
    $_SESSION['login_error'] = "Invalid email or password.";
    $stmt->close();
    header("Location: ../login/login.php");
    exit();
}
?>