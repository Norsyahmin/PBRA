<?php
session_start();
include '../mypbra_connect.php';

if (!isset($_SESSION['id']) || !isset($_POST['report_id']) || !isset($_POST['action'])) {
    header("Location: manage_report.php");
    exit();
}

$report_id = intval($_POST['report_id']);
$action = $_POST['action'];

if ($action === 'resolve') {
    $stmt = $conn->prepare("UPDATE reports SET status = 'resolved' WHERE id = ?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
} elseif ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
}

header("Location: manage_report.php");
exit();
