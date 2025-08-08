<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../mypbra_connect.php'; // because it's outside

$user_id = $_SESSION['id'] ?? 0;
$page_name = $_POST['page_name'] ?? '';
$page_url = $_POST['page_url'] ?? '';
$action = $_POST['action'] ?? '';

if (!$user_id || !$page_name) {
    echo "error: missing user or page_name";
    exit();
}

if ($action === 'add') {
    // Check if already exists
    $check = $conn->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND page_name = ?");
    $check->bind_param("is", $user_id, $page_name);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        // Insert
        $insert = $conn->prepare("INSERT INTO user_favorites (user_id, page_name, page_url) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $user_id, $page_name, $page_url);
        $insert->execute();
        $insert->close();
        echo "added";
    } else {
        echo "already_added";
    }
    $check->close();
} elseif ($action === 'remove') {
    // Delete
    $delete = $conn->prepare("DELETE FROM user_favorites WHERE user_id = ? AND page_name = ?");
    $delete->bind_param("is", $user_id, $page_name);
    $delete->execute();
    $delete->close();
    echo "removed";
} else {
    echo "invalid action";
}
?>
