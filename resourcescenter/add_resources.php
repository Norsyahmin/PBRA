<?php
session_start();
include '../mypbra_connect.php';

// Log file for debugging
$logFile = '../logs/add_resources_debug.txt';
file_put_contents($logFile, "==== ADD RESOURCES HIT ====\n", FILE_APPEND);

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
file_put_contents($logFile, "Admin check: " . ($is_admin ? "yes" : "no") . "\n", FILE_APPEND);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : null;
    $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link_url'] ?? '');

    file_put_contents($logFile, "Data: section_id=$section_id, role_id=$role_id, title=$title\n", FILE_APPEND);

    if (!$section_id || !$role_id || empty($title)) {
        file_put_contents($logFile, "Missing required field\n", FILE_APPEND);
        header("Location: role_resources.php?role_id=" . urlencode($role_id));
        exit();
    }

    $upload_dir = '../uploads/resources/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle file
    if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $originalName = basename($_FILES['file']['name']);
        $uniqueName = uniqid() . '_' . $originalName;
        $savePath = $upload_dir . $uniqueName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $savePath)) {
            $stmt = $conn->prepare("INSERT INTO resource_files (section_id, title, description, file_path, is_link, uploaded_at) VALUES (?, ?, ?, ?, 0, NOW())");
            $stmt->bind_param("isss", $section_id, $title, $description, $uniqueName);
            $stmt->execute();
            $stmt->close();
            file_put_contents($logFile, "File uploaded and inserted\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "Failed to move uploaded file\n", FILE_APPEND);
        }
    }
    // Or if it's a link
    elseif (!empty($link)) {
        $stmt = $conn->prepare("INSERT INTO resource_files (section_id, title, description, is_link, link_url, uploaded_at) VALUES (?, ?, ?, 1, ?, NOW())");
        $stmt->bind_param("isss", $section_id, $title, $description, $link);
        $stmt->execute();
        $stmt->close();
        file_put_contents($logFile, "Link inserted\n", FILE_APPEND);
    } elseif (!empty($description)) {
        $stmt = $conn->prepare("INSERT INTO resource_files (section_id, title, description, is_link, uploaded_at) VALUES (?, ?, ?, 0, NOW())");
        $stmt->bind_param("iss", $section_id, $title, $description);
        $stmt->execute();
        $stmt->close();
        file_put_contents($logFile, "Text-only resource inserted\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "Neither file, link, nor description provided\n", FILE_APPEND);
    }
    

    header("Location: role_resources.php?role_id=" . urlencode($role_id));
    exit();
} else {
    file_put_contents($logFile, "Invalid access\n", FILE_APPEND);
    header("Location: ../index.php");
    exit();
}
?>
