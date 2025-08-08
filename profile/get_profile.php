<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';

$user_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : $_SESSION['id'];

$sql = "SELECT full_name, email, start_date, work_experience, education, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

$profile_pic = (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])) 
    ? '../' . htmlspecialchars($user['profile_pic']) 
    : '../profile/images/default-profile.jpg';

$user['profile_pic'] = $profile_pic;

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($user);
?>
