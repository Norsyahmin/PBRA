<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include '../mypbra_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'])) {
    $message_id = intval($_POST['message_id']);
    $user_id = $_SESSION['id'];
    
    $stmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE id = ? AND recipient_id = ?");
    $stmt->bind_param("ii", $message_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true]);
} else {
    header("HTTP/1.1 400 Bad Request");
}
?>