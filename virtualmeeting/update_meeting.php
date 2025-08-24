<?php
include '../mypbra_connect.php';
session_start();

header("Content-Type: application/json");

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['meeting_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$meeting_id = intval($data['meeting_id']);
$title = $conn->real_escape_string($data['title'] ?? '');
$agenda = $conn->real_escape_string($data['agenda'] ?? '');
$date = $conn->real_escape_string($data['date'] ?? '');
$start_time = $conn->real_escape_string($data['start_time'] ?? '');
$end_time = $conn->real_escape_string($data['end_time'] ?? '');
$participants = $data['participants'] ?? [];

// Update the meeting info
$updateSql = "UPDATE meetings 
              SET title='$title', agenda='$agenda', meeting_date='$date', 
                  start_time='$start_time', end_time='$end_time'
              WHERE meeting_id=$meeting_id";
$conn->query($updateSql);

// Clear old participants
$conn->query("DELETE FROM meeting_participants WHERE meeting_id=$meeting_id");

// Re-insert participants safely
if (is_array($participants)) {
    foreach ($participants as $participant) {
        // Ensure participant has 'full_name'
        if (!isset($participant['full_name'])) continue;
        $pname = $conn->real_escape_string($participant['full_name']);

        $userRes = $conn->query("SELECT id FROM users WHERE full_name='$pname' LIMIT 1");
        if ($userRes && $userRes->num_rows > 0) {
            $userId = $userRes->fetch_assoc()['id'];
            $conn->query("INSERT INTO meeting_participants (meeting_id, user_id) VALUES ($meeting_id, $userId)");
        }
    }
}

echo json_encode(["success" => true]);
?>
