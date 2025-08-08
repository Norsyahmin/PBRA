<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}
include '../mypbra_connect.php';
$user_id = $_SESSION['id'];

// ✅ Handle all POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];

    // --- SAVE DRAFT ---
    if ($action === 'save_draft') {
        $subject = $_POST['subject'] ?? '';
        $body = $_POST['body'] ?? '';
        $draft_id = isset($_POST['draft_id']) && is_numeric($_POST['draft_id']) ? intval($_POST['draft_id']) : null;

        if ($draft_id) {
            $stmt = $conn->prepare("UPDATE messages SET subject = ?, body = ? WHERE id = ? AND sender_id = ? AND folder = 'drafts'");
            $stmt->bind_param("ssii", $subject, $body, $draft_id, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, subject, body, folder) VALUES (?, ?, ?, 'drafts')");
            $stmt->bind_param("iss", $user_id, $subject, $body);
        }

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        exit();
    }

    // --- SEND NEW EMAIL ---
    if ($action === 'send') {
        $toEmail = $_POST['to'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];

        $getRecipient = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $getRecipient->bind_param("s", $toEmail);
        $getRecipient->execute();
        $recipientResult = $getRecipient->get_result();

        if ($recipientRow = $recipientResult->fetch_assoc()) {
            $recipient_id = $recipientRow['id'];

            $insertInbox = $conn->prepare("
                INSERT INTO messages (sender_id, recipient_id, subject, body, folder, thread_id)
                VALUES (?, ?, ?, ?, 'inbox', NULL)
            ");
            $insertInbox->bind_param("iiss", $user_id, $recipient_id, $subject, $body);
            $insertInbox->execute();

            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Recipient not found."]);
        }
        exit();
    }

    // --- REPLY Email (CORRECTED: Insert only 1 record to recipient inbox) ---
    if ($action === 'reply') {
        $toEmail = $_POST['to'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];
        $thread_id = intval($_POST['thread_id']);
        $attachment_path = null;

        if (!empty($_FILES['attachment']['name'])) {
            $targetDir = "../uploads/mail_attachments/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $fileName = time() . "_" . basename($_FILES["attachment"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetFilePath);
            $attachment_path = $targetFilePath;
        }

        $getRecipient = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $getRecipient->bind_param("s", $toEmail);
        $getRecipient->execute();
        $recipientResult = $getRecipient->get_result();

        if ($recipientRow = $recipientResult->fetch_assoc()) {
            $recipient_id = $recipientRow['id'];

            if ($user_id == 1) {
                $lawanan = 2;
            } else {
                $lawanan = 1;
            }

            $insertInbox = $conn->prepare("
                INSERT INTO messages (sender_id, recipient_id, subject, body, folder, thread_id, attachment_path)
                VALUES (?, ?, ?, ?, 'inbox', ?, ?)
            ");
            $insertInbox->bind_param("iissis", $user_id, $lawanan, $subject, $body, $thread_id, $attachment_path);
            $insertInbox->execute();

            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Recipient not found."]);
        }
        exit();
    }

    // --- SEND DRAFT ---
    if ($action === 'send_draft') {
        $draft_id = intval($_POST['draft_id']);
        $toEmail = $_POST['to'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];

        $getRecipient = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $getRecipient->bind_param("s", $toEmail);
        $getRecipient->execute();
        $recipientResult = $getRecipient->get_result();

        if ($recipientRow = $recipientResult->fetch_assoc()) {
            $recipient_id = $recipientRow['id'];

            $stmt = $conn->prepare("
                UPDATE messages
                SET recipient_id = ?, subject = ?, body = ?, folder = 'inbox'
                WHERE id = ? AND sender_id = ?
            ");
            $stmt->bind_param("issii", $recipient_id, $subject, $body, $draft_id, $user_id);
            $stmt->execute();

            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Recipient not found."]);
        }
        exit();
    }

    // --- MOVE EMAIL TO TRASH ---
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE messages SET folder = 'trash' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["success" => true]);
        exit();
    }

    // --- DELETE ALL EMAILS FROM TRASH ---
    if ($action === 'delete_all_trash') {
        $stmt = $conn->prepare("DELETE FROM messages WHERE folder = 'trash' AND (sender_id = ? OR recipient_id = ?)");
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        echo json_encode(["success" => true]);
        exit();
    }

    // --- DELETE DRAFT ---
    if ($action === 'delete_draft') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ? AND folder = 'drafts'");
        $stmt->bind_param("ii", $id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        exit();
    }
}

// ✅ Handle all GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    // --- FETCH EMAIL LIST (Inbox / Drafts / Trash) ---
    if (isset($_GET['folder'])) {
        $folder = $_GET['folder'];

        if (in_array($folder, ['inbox', 'drafts', 'trash'])) {
            $query = "
                SELECT m.id, m.subject, m.body, m.created_at, m.is_read,
                       s.full_name AS sender_name,
                       u.email AS recipient_email,
                       m.folder
                FROM messages m
                LEFT JOIN users u ON m.recipient_id = u.id
                LEFT JOIN users s ON m.sender_id = s.id
                WHERE (m.sender_id = ? OR m.recipient_id = ?)
                AND m.folder = ?
                AND m.thread_id IS NULL
                ORDER BY m.created_at DESC
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iis", $user_id, $user_id, $folder);
            $stmt->execute();
            $result = $stmt->get_result();

            $emails = [];
            while ($row = $result->fetch_assoc()) {
                $emails[] = $row;
            }

            echo json_encode($emails);
        } else {
            echo json_encode([]);
        }
        exit();
    }

    // --- FETCH EMAIL THREAD ---
    if (isset($_GET['view_thread'])) {
        $msg_id = intval($_GET['view_thread']);

        $rootQuery = "
            SELECT CASE
                WHEN thread_id IS NULL THEN id
                ELSE thread_id
            END AS root_id
            FROM messages
            WHERE id = ?
        ";
        $rootStmt = $conn->prepare($rootQuery);
        $rootStmt->bind_param("i", $msg_id);
        $rootStmt->execute();
        $rootResult = $rootStmt->get_result();
        $rootRow = $rootResult->fetch_assoc();
        $root_id = $rootRow['root_id'];

        $query = "
            SELECT m.*, u.full_name AS sender_name
            FROM messages m
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE (m.id = ? OR m.thread_id = ?)
            AND (m.sender_id = ? OR m.recipient_id = ?)
            ORDER BY m.created_at ASC
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $root_id, $root_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $thread = [];
        while ($row = $result->fetch_assoc()) {
            $thread[] = $row;
        }

        echo json_encode($thread);
        exit();
    }
}
?>
