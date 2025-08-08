
<?php
session_start();
include '../mypbra_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $status = isset($_POST['status']) && $_POST['status'] === 'unresolved' ? 'unresolved' : 'resolved';

    $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $report_id);
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $reportId = $_POST['report_id'];
    $status = $_POST['status'] ?? 'resolved';

    $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $reportId);

    if ($stmt->execute()) {
        header("Location: report.php");
        exit();
    } else {
        echo "❌ Failed to update status.";
    }
}

header("Location: report.php");
exit();
?>
