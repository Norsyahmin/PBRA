<?php
header('Content-Type: application/json');

include '../mypbra_connect.php';
$user_id = $_SESSION['id'];

// Handle Requests
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch emails by folder
    $folder = $_GET['folder'] ?? 'inbox';
    $stmt = $conn->prepare("SELECT * FROM mails WHERE folder = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $folder);
    $stmt->execute();
    $result = $stmt->get_result();
    $mails = [];
    while ($row = $result->fetch_assoc()) {
        $mails[] = $row;
    }
    echo json_encode(["success" => true, "mails" => $mails]);
    exit();
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send') {
        $to = $_POST['to'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];

        $stmt = $conn->prepare("INSERT INTO mails (sender, receiver, subject, body, folder, unread) VALUES ('me@example.com', ?, ?, ?, 'sent', 0)");
        $stmt->bind_param("sss", $to, $subject, $body);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Email sent."]);
        exit();
    }

    if ($action === 'draft') {
        $subject = $_POST['subject'];
        $body = $_POST['body'];

        $stmt = $conn->prepare("INSERT INTO mails (sender, receiver, subject, body, folder, unread) VALUES ('me@example.com', '', ?, ?, 'drafts', 1)");
        $stmt->bind_param("ss", $subject, $body);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Draft saved."]);
        exit();
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE mails SET folder = 'trash', unread = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Email moved to trash."]);
        exit();
    }

    if ($action === 'reply') {
        $to = $_POST['to'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];

        $stmt = $conn->prepare("INSERT INTO mails (sender, receiver, subject, body, folder, unread) VALUES ('me@example.com', ?, ?, ?, 'sent', 0)");
        $stmt->bind_param("sss", $to, $subject, $body);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Reply sent."]);
        exit();
    }
}

// Default response
echo json_encode(["success" => false, "message" => "Invalid request."]);
?>
