<?php
session_start();
include '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['id'];
$is_admin = false;

$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($user_type);
if ($stmt->fetch() && $user_type === 'admin') {
    $is_admin = true;
}
$stmt->close();

if ($is_admin && isset($_POST['id'])) {
    $announcementId = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM announcement WHERE id = ?");
    $stmt->bind_param("i", $announcementId);
    $stmt->execute();
    $stmt->close();
}

header("Location: homepage.php");
exit();
?>
