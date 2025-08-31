<?php
include '../mypbra_connect.php';
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['id'];
$data = json_decode(file_get_contents("php://input"), true);

$meetingId = $data['meeting_id'] ?? null;
$message = $data['message'] ?? null;

if (!$meetingId || !$message) {
    echo json_encode(["success" => false, "message" => "Missing data"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO meeting_chats (meeting_id, user_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $meetingId, $userId, $message);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
?>
