<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// Include the database connection
include '../mypbra_connect.php';

$user_id = $_SESSION['id'];
$message = '';
$messageType = '';

try {
    // First, get the current image path to delete the file
    $sql = "SELECT image_path FROM schedule WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row && $row['image_path'] && $row['image_path'] !== 'default.png') {
        // Delete the physical file if it exists and is not the default image
        $file_path = $row['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete the record from the database
    $delete_sql = "DELETE FROM schedule WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            $message = "Schedule deleted successfully!";
            $messageType = "success";
        } else {
            $message = "No schedule found to delete.";
            $messageType = "info";
        }
    } else {
        $message = "Error deleting schedule: " . $conn->error;
        $messageType = "error";
    }
    
    $delete_stmt->close();
    $stmt->close();
    
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
    $messageType = "error";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Schedule</title>
    <link rel="stylesheet" href="schedule.css">
    <style>
        .message-container {
            max-width: 600px;
            margin: 100px auto 50px;
            padding: 20px;
            text-align: center;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .back-btn {
            background-color: #174080;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        
        .back-btn:hover {
            background-color: #0d2a5d;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="message-container">
        <h1 style="color: #174080; margin-bottom: 30px;">Delete Schedule</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <a href="schedule.php" class="back-btn">
            ← Back to Schedule
        </a>
    </div>
    
    <footer>
        <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
    </footer>
    
    <script>
        // Auto-redirect after 3 seconds for success message
        <?php if ($messageType === 'success'): ?>
            setTimeout(function() {
                window.location.href = 'schedule.php';
            }, 10000);
        <?php endif; ?>
    </script>
</body>
</html>
