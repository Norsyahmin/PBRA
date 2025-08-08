<?php
session_start();
include '../mypbra_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

    // Check admin
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user['user_type'] === 'admin') {
        $title = $_POST['title'];
        $role_id = $_POST['role_id'];
        $upload_dir = '../uploads/';
        $file_name = basename($_FILES['resource_file']['name']);
        $target_file = $upload_dir . time() . '_' . $file_name;

        if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO role_resources (title, file_path, role_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $title, $target_file, $role_id);
            $stmt->execute();
        }
    }
}
header("Location: role_resources.php");
exit();
