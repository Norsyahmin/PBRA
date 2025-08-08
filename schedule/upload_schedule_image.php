<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';

$user_id = $_SESSION['id'];
$upload_dir = 'schedule/uploads/'; // relative to this file
$full_dir = '../' . $upload_dir;

// Create folder if not exists
if (!is_dir($full_dir)) {
    mkdir($full_dir, 0755, true);
}

// Check if file was uploaded
if (isset($_FILES['schedule_image']) && $_FILES['schedule_image']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['schedule_image']['tmp_name'];
    $file_ext = pathinfo($_FILES['schedule_image']['name'], PATHINFO_EXTENSION);
    $file_name = 'schedule_' . $user_id . '.' . strtolower($file_ext);
    $file_path = $full_dir . $file_name;
    $relative_path = '../' . $upload_dir . $file_name;

    // Move uploaded file
    if (move_uploaded_file($file_tmp, $file_path)) {
        // Save or update in database
        $stmt_check = $conn->prepare("SELECT id FROM schedule WHERE user_id = ?");
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Update existing
            $stmt_update = $conn->prepare("UPDATE schedule SET image_path = ?, uploaded_at = NOW() WHERE user_id = ?");
            $stmt_update->bind_param("si", $relative_path, $user_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Insert new
            $stmt_insert = $conn->prepare("INSERT INTO schedule (user_id, image_path, uploaded_at) VALUES (?, ?, NOW())");
            $stmt_insert->bind_param("is", $user_id, $relative_path);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check->close();
        $conn->close();

        // Redirect back to schedule page
        header("Location: schedule.php");
        exit();
    } else {
        die("Failed to move uploaded file.");
    }
} else {
    die("No file uploaded or upload error.");
}
?>
