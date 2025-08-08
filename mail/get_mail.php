<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include '../mypbra_connect.php';

if (isset($_GET['id'])) {
    $message_id = intval($_GET['id']);
    $user_id = $_SESSION['id'];
    
    // Get message details
    $stmt = $conn->prepare("SELECT m.*, 
                           u1.full_name as sender_name, 
                           u2.full_name as recipient_name 
                           FROM messages m
                           JOIN users u1 ON m.sender_id = u1.id
                           JOIN users u2 ON m.recipient_id = u2.id
                           WHERE m.id = ? AND (m.sender_id = ? OR m.recipient_id = ?)");
    $stmt->bind_param("iii", $message_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = $result->fetch_assoc();
        
        // Get attachments
        $attachments = [];
        $stmt = $conn->prepare("SELECT * FROM message_attachments WHERE message_id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $attachments[] = $row;
        }
        
        $message['attachments'] = $attachments;
        
        echo json_encode($message);
    } else {
        header("HTTP/1.1 404 Not Found");
    }
    
    $stmt->close();
} else {
    header("HTTP/1.1 400 Bad Request");
}
?>