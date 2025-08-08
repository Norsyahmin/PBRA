<?php
session_start();
include '../mypbra_connect.php';

// Only admin can delete
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_id']) && $is_admin) {
    $section_id = $_POST['section_id'];

    // Delete section (files will be auto-deleted if FK cascade is set)
    $stmt = $conn->prepare("DELETE FROM resource_sections WHERE id = ?");
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: role_resources.php");
exit();
