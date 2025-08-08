<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['id'];
    $recipients = $_POST['recipient'];
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);

    // Validate inputs
    if (empty($recipients) || empty($subject) || empty($body)) {
        $_SESSION['email_error'] = "All fields are required";
        header("Location: mail.php");
        exit();
    }

    // Process each recipient
    foreach ($recipients as $recipient_id) {
        // Insert message into database
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $sender_id, $recipient_id, $subject, $body);
        $stmt->execute();
        $message_id = $stmt->insert_id;
        $stmt->close();

        // Handle file uploads if any
        if (!empty($_FILES['attachments']['name'][0])) {
            $upload_dir = "../uploads/email_attachments/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                $file_name = basename($_FILES['attachments']['name'][$key]);
                $file_path = $upload_dir . uniqid() . '_' . $file_name;
                $file_size = $_FILES['attachments']['size'][$key];

                if (move_uploaded_file($tmp_name, $file_path)) {
                    $stmt = $conn->prepare("INSERT INTO message_attachments (message_id, file_name, file_path, file_size) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("issi", $message_id, $file_name, $file_path, $file_size);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    $_SESSION['email_success'] = "Message sent successfully";
    header("Location: mail.php?folder=sent");
    exit();
} else {
    header("Location: mail.php");
    exit();
}
?>