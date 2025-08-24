<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

include '../mypbra_connect.php';

$id = $_SESSION['id'];
$favorites = [];

$sql = "SELECT favorite_id, page_name, page_url FROM user_favorites WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $favorites[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($favorites);
