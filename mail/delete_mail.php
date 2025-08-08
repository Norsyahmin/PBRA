<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include '../mypbra_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $message_id = intval($_POST['id']);
    $user_id = $_SESSION['id'];
    
    // Verify the user has permission to delete this message
    $stmt = $conn->prepare("SELECT id FROM messages WHERE id = ? AND (sender_id = ? OR recipient_id = ?)");
    $stmt->bind_param("iii", $message_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Delete the message (attachments will be deleted via foreign key cascade)
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        header("HTTP/1.1 403 Forbidden");
    }
    
    $stmt->close();
} else {
    header("HTTP/1.1 400 Bad Request");
}
?>