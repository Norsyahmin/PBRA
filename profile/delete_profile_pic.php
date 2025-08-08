<?php
session_start();
require '../mypbra_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $default_pic = "profile/images/default-profile.jpg";

    $query = "UPDATE users SET profile_pic = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $default_pic, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['profile_pic'] = $default_pic;
    }

    header("Location: profile.php");
    exit();
}
?>
