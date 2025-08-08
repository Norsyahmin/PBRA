<?php
if (!isset($conn)) {
    include '../mypbra_connect.php';
}

date_default_timezone_set('Asia/Brunei');
$currentDateTime = date('Y-m-d H:i:s');

// Check how many pending tasks are overdue
$sql_check = "SELECT COUNT(*) FROM tasks
              WHERE status = 'pending' 
              AND CONCAT(task_date, ' ', task_time) < ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $currentDateTime);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

// Only update if there are overdue tasks
if ($count > 0) {
    $sql_update = "UPDATE tasks
                   SET status = 'not_completed',
                       reason = 'this person does not complete the task',
                       last_updated = NOW()
                   WHERE status = 'pending'
                   AND CONCAT(task_date, ' ', task_time) < ?";
    $stmt2 = $conn->prepare($sql_update);
    $stmt2->bind_param("s", $currentDateTime);
    $stmt2->execute(); // ✅ This was missing!
    $stmt2->close();
}
?>
