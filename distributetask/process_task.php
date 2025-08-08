<?php
session_start();
include '../mypbra_connect.php'; // Ensure DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $persons = $_POST['person']; // Array of selected user IDs
    $task = $_POST['task'];
    $custom_task = isset($_POST['custom_task']) ? trim($_POST['custom_task']) : '';
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Get the user ID of the person assigning the task
    $created_by = $_SESSION['id']; 

    // Use custom task if selected
    if ($task === "other" && !empty($custom_task)) {
        $task = $custom_task;
    }

    // Store assigned task details for confirmation message
    $assigned_tasks = [];

    // Loop through each selected person and insert task
    foreach ($persons as $person_id) {
        // Insert into `tasks` table with `created_by`
        $stmt = $conn->prepare("INSERT INTO tasks (assigned_to, task_name, task_description, task_date, task_time, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $person_id, $task, $description, $date, $time, $created_by);
        $stmt->execute();
        $stmt->close();

        // Store task details for display
        $assigned_tasks[] = [
            'person_id' => $person_id,
            'task_name' => $task,
            'task_date' => $date,
            'task_time' => $time
        ];

        // Insert notification with task name
        $message = "[New Task] " . $task;
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, status) VALUES (?, ?, FALSE, 'unread')");
        $stmt->bind_param("is", $person_id, $message);
        if (!$stmt->execute()) {
            error_log("Notification insert error: " . $stmt->error);
        }
        $stmt->close();
        
    }

    // Store assigned tasks in session to display after redirection
    $_SESSION['assigned_tasks'] = $assigned_tasks;

    // Redirect with success flag
    header("Location: distributetask.php?success=1");
    exit();
} else {
    // Redirect if accessed without POST request
    header("Location: distributetask.php");
    exit();
}
?>