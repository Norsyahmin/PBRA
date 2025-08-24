<?php
include '../mypbra_connect.php';
session_start();

$meetingId = isset($_GET['meeting_id']) ? intval($_GET['meeting_id']) : 0;

if ($meetingId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid meeting ID"]);
    exit;
}

$sql = "SELECT mc.message, mc.created_at, u.full_name 
        FROM meeting_chats mc
        JOIN users u ON mc.user_id = u.id
        WHERE mc.meeting_id = ?
        ORDER BY mc.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meetingId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(["success" => true, "messages" => $messages]);
