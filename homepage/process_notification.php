<?php
session_start();
require_once '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $update = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ?");
    $update->bind_param("i", $user_id);
    $update->execute();
    echo json_encode(['success' => true]);
    exit;
}

// Get unread count
$countStmt = $conn->prepare("SELECT COUNT(*) AS unreadCount FROM notifications WHERE user_id = ? AND status = 'unread'");
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$unreadCount = $countResult['unreadCount'] ?? 0;

// Get last 10 notifications
$dataStmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$dataStmt->bind_param("i", $user_id);
$dataStmt->execute();
$result = $dataStmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'message' => $row['message'],
        'time' => date("Y-m-d H:i", strtotime($row['created_at']))
    ];
}

echo json_encode([
    'unreadCount' => $unreadCount,
    'notifications' => $notifications
]);
exit;
