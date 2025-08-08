<?php
session_start();
require_once '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_POST['report_to']) || !isset($_POST['report_what'])) {
    die("Error: Missing required fields.");
}

$user_id = $_SESSION['id'];
$report_to = $_POST['report_to']; // array of user IDs
$report_what = $_POST['report_what'];
$other_report = !empty($_POST['other_report']) ? trim($_POST['other_report']) : NULL;
$remarks = !empty($_POST['remarks']) ? trim($_POST['remarks']) : NULL;

$upload_dir = realpath("../uploads/reports") . "/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_path = NULL;
if (!empty($_FILES['report_file']['name']) && is_uploaded_file($_FILES['report_file']['tmp_name'])) {
    $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
    $file_type = mime_content_type($_FILES['report_file']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        die("Error: Invalid file type.");
    }

    if ($_FILES['report_file']['size'] > 5 * 1024 * 1024) {
        die("Error: File size exceeds 5MB limit.");
    }

    $file_ext = pathinfo($_FILES['report_file']['name'], PATHINFO_EXTENSION);
    $file_name = time() . "_" . uniqid() . "." . $file_ext;
    $file_path = "uploads/reports/" . $file_name;

    if (!move_uploaded_file($_FILES['report_file']['tmp_name'], $upload_dir . $file_name)) {
        die("Error: File upload failed.");
    }
}

// Insert report record
$query = "INSERT INTO reports (user_id, report_to, report_what, other_report, remarks, file_path, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, NOW())";

// Convert array to comma-separated string
$reportToStr = intval($report_to[0]); // just pick the first selected person

$stmt = $conn->prepare($query);
$stmt->bind_param("isssss", $user_id, $reportToStr, $report_what, $other_report, $remarks, $file_path);
if (!$stmt->execute()) {
    die("Error: Could not submit report.");
}
$report_id = $stmt->insert_id;
$stmt->close();

// Optional: Insert notifications for each person reported to
$notif_query = "INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())";
$notif_stmt = $conn->prepare($notif_query);
$notif_message = "You have been assigned a new report (ID: $report_id)";
foreach ($report_to as $person_id) {
    $person_id = (int)$person_id;
    $notif_stmt->bind_param("is", $person_id, $notif_message);
    $notif_stmt->execute();
}
$notif_stmt->close();

$conn->close();

$_SESSION['report_success'] = true;
session_write_close();
header("Location: report.php");
exit();
?>
