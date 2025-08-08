<?php
session_start();
include '../mypbra_connect.php';

// Check if the user is admin
$is_admin = false;
if (isset($_SESSION['id'])) {
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($user_type);
    if ($stmt->fetch() && $user_type === 'admin') {
        $is_admin = true;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id']) && $is_admin) {
    $file_id = $_POST['file_id'];

    // Get file path from DB
    $stmt = $conn->prepare("SELECT file_path FROM resource_files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();

    if (!empty($file_path)) {
        $full_path = '../uploads/resources/' . $file_path;

        // Delete file from folder if it exists
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }

    // Remove file record from DB
    $stmt = $conn->prepare("DELETE FROM resource_files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: role_resources.php");
exit();
