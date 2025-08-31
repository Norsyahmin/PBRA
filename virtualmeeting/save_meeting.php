<?php
include '../mypbra_connect.php'; // your DB connection
session_start(); // always at the top
$created_by = $_SESSION['id']; // ID of the logged-in user

if (!$created_by) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

header("Content-Type: application/json");

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "No input data"]);
    exit;
}

// Extract values
$title = $conn->real_escape_string($data['title']);
$agenda = $conn->real_escape_string($data['agenda']);
$meeting_date = $conn->real_escape_string($data['meeting_date']);
$start_time = $conn->real_escape_string($data['start_time']);
$end_time = $conn->real_escape_string($data['end_time']);
$invited = $data['invited'];

// Insert meeting
$sql = "INSERT INTO meetings (title, agenda, meeting_date, start_time, end_time, created_by)
        VALUES ('$title', '$agenda', '$meeting_date', '$start_time', '$end_time', '$created_by')";

if ($conn->query($sql)) {
    $meeting_id = $conn->insert_id;

    // Insert participants
    if (!empty($invited)) {
        foreach ($invited as $user_id) {
            $user_id = (int)$user_id;
            $conn->query("INSERT INTO meeting_participants (meeting_id, user_id) VALUES ($meeting_id, $user_id)");
        }
    }

    echo json_encode(["success" => true, "message" => "Meeting saved successfully"]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
?>
