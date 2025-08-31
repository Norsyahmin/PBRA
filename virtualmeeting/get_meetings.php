<?php
include '../mypbra_connect.php';
session_start();

header("Content-Type: application/json");

// Get all meetings with creator name
$sql = "SELECT m.meeting_id, m.title, m.agenda, m.meeting_date, m.start_time, m.end_time, 
       u.full_name AS created_by,
       m.created_by AS created_by_id
FROM meetings m
JOIN users u ON m.created_by = u.id
ORDER BY m.meeting_date";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["success" => false, "message" => $conn->error]);
    exit;
}

$meetings = [];
$meetingIds = [];

while ($row = $result->fetch_assoc()) {
    $meetings[$row['meeting_id']] = $row; // use meeting_id as key
    $meetings[$row['meeting_id']]['participants'] = []; // initialize participants
    $meetingIds[] = $row['meeting_id'];
}

// Get all participants in one query
if (count($meetingIds) > 0) {
    $ids = implode(',', $meetingIds);
    $pResult = $conn->query("SELECT mp.meeting_id, mp.user_id, u.full_name 
                             FROM meeting_participants mp
                             JOIN users u ON mp.user_id = u.id
                             WHERE mp.meeting_id IN ($ids)");

    while ($p = $pResult->fetch_assoc()) {
        $meetings[$p['meeting_id']]['participants'][] = [
            'id' => (int)$p['user_id'],
            'name'=>$p['full_name']
        ];
    }
}

// Return JSON as indexed array
echo json_encode([
    "success" => true,
    "data" => array_values($meetings) // remove keys to get numeric array
]);
?>
