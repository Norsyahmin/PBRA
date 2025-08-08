<?php
session_start();
include '../mypbra_connect.php';

// Check admin
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

$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin && $role_id) {
    $title = trim($_POST['section_title'] ?? '');

    if (!empty($title)) {
        $stmt = $conn->prepare("INSERT INTO resource_sections (title, role_id) VALUES (?, ?)");
        $stmt->bind_param("si", $title, $role_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: role_resources.php?role_id=" . $role_id);
exit();
