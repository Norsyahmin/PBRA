<?php
session_start();
include '../mypbra_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['proof_file'], $_POST['task_id'])) {
    $taskId = intval($_POST['task_id']);
    $uploadDir = '../uploads/proofs/';

    // Make sure the directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['proof_file']['name']);
    $targetFile = $uploadDir . time() . '_' . $fileName;
    $relativePath = str_replace('../', '', $targetFile); // For database storage

    // Move file
    if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("UPDATE tasks SET status = 'completed', proof_path = ?, last_updated = NOW() WHERE task_id = ?");

        $stmt->bind_param("si", $relativePath, $taskId);
        if ($stmt->execute()) {
            http_response_code(200);
            echo "Proof uploaded successfully.";
        } else {
            http_response_code(500);
            echo "Database update failed.";
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "File upload failed.";
    }
} else {
    http_response_code(400);
    echo "Invalid request.";
}
?>
