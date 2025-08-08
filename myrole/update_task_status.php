<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    $reason = $_POST['reason'] ?? "";

    // Make sure only valid status values are accepted
    $allowed_statuses = ['pending', 'completed', 'not_completed'];
    if (!in_array($status, $allowed_statuses)) {
        echo "Invalid status.";
        exit();
    }

    // Update the task (assuming table is `tasks`)
    $stmt = $conn->prepare("UPDATE tasks SET status = ?, reason = ?, proof_path = NULL, last_updated = NOW() WHERE task_id = ?");
    $stmt->bind_param("ssi", $status, $reason, $task_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
