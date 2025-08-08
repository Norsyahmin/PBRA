<?php
session_start();
include '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $message = $_POST['message'];
    $rating = isset($_POST['rating']) ? $_POST['rating'] : null;
    $userId = $_SESSION['id'];

    $uploadDir = "../feedback/uploads/";
    $filePath = null;

    if (!empty($_FILES['attached_files']['name'])) {
        $fileName = basename($_FILES['attached_files']['name']);
        $fileTmp = $_FILES['attached_files']['tmp_name'];
        $fileSize = $_FILES['attached_files']['size'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'docx'];

        if (!in_array($fileType, $allowedTypes)) {
            die("Invalid file type. Allowed: JPG, PNG, PDF, DOCX.");
        }

        if ($fileSize > 5 * 1024 * 1024) {
            die("File size exceeds 5MB.");
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . uniqid("feedback_", true) . "." . $fileType;

        if (!move_uploaded_file($fileTmp, $filePath)) {
            die("File upload failed.");
        }
    }

    $stmt = $conn->prepare("INSERT INTO feedback (user_id, category, message, rating, attachment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $category, $message, $rating, $filePath);

    if ($stmt->execute()) {
        // Get user's email from DB
        $userEmail = "";
        $userQuery = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $userQuery->bind_param("i", $userId);
        $userQuery->execute();
        $userQuery->bind_result($userEmail);
        $userQuery->fetch();
        $userQuery->close();
    
        // Email details
        $to = "pbra.feedback@gmail.com";
        $subject = "PBRA Website Feedback Received";
    
        $body = "You have received new feedback:\n\n";
        $body .= "User ID: $userId\n";
        $body .= "Category: $category\n";
        $body .= "Rating: " . ($rating ?: 'N/A') . "\n";
        $body .= "Message:\n$message\n\n";
    
        if ($filePath) {
            $body .= "File was attached and saved at: $filePath\n";
        }
    
        $headers = "From: $userEmail\r\n";
        $headers .= "Reply-To: $userEmail\r\n";
    
        mail($to, $subject, $body, $headers);
    
        header("Location: feedback.php?success=1");
        exit();
    }
    
    } else {
        die("Database error: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
?>
