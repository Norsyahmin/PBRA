<?php
session_start();
require '../mypbra_connect.php';

$results = [
    "users" => [],
    "features" => []
];

// --- Get user_type and user_id ---
$logged_in_user_id = $_SESSION['id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;

if (!$user_type && $logged_in_user_id) {
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $logged_in_user_id);
    $stmt->execute();
    $stmt->bind_result($user_type);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['user_type'] = $user_type;
}

// --- Fetch users (exclude current logged-in user) ---
$user_query = "SELECT id, full_name, profile_pic FROM users WHERE id != ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $logged_in_user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $results["users"][] = [
        "type" => "user",
        "id" => $row["id"],
        "name" => $row["full_name"],
        "profile_pic" => (!empty($row["profile_pic"]) && file_exists("../" . $row["profile_pic"]))
            ? "../" . $row["profile_pic"]
            : "../profile/images/default-profile.jpg"
    ];
}
$stmt->close();

// --- Full feature list with access control ---
$features = [
    ["name" => "Homepage", "icon" => "house", "url" => "../homepage/homepage.php", "access" => "all"],
    ["name" => "Role", "icon" => "user-tag", "url" => "../roles/roles.php", "access" => "all"],
    ["name" => "My Role", "icon" => "briefcase", "url" => "../myrole/myrole.php", "access" => "all"],
    ["name" => "Mail", "icon" => "envelope", "url" => "../mails/mail_page.php", "access" => "all"],
    ["name" => "Profile", "icon" => "user", "url" => "../profile/profile.php", "access" => "all"],
    ["name" => "Distribute Task", "icon" => "tasks", "url" => "../distributetask/distributetask.php", "access" => "admin"],
    ["name" => "Events", "icon" => "calendar-days", "url" => "../eventss/event.php", "access" => "all"],
    ["name" => "Calendar", "icon" => "calendar", "url" => "../calendar/calendar.php", "access" => "all"],
    ["name" => "Schedule", "icon" => "calendar-check", "url" => "../schedule/schedule.php", "access" => "all"],
    ["name" => "Report", "icon" => "file-alt", "url" => "../report/report.php", "access" => "all"],
    ["name" => "Feedback", "icon" => "comment-dots", "url" => "../feedback/feedback.php", "access" => "all"],
    ["name" => "Support", "icon" => "headset", "url" => "../usersupport/usersupport.php", "access" => "all"],
    ["name" => "Appoint Role", "icon" => "user-plus", "url" => "../appointroles/appointroles.php", "access" => "admin"],
    ["name" => "Staff", "icon" => "users", "url" => "../staff/staffsch.php", "access" => "all"],
    ["name" => "Role History", "icon" => "clock-rotate-left", "url" => "../rolehistory/role_history.php", "access" => "all"],
    ["name" => "Announcement Center", "icon" => "bullhorn", "url" => "../homepage/homepage.php", "access" => "all"]
];

// --- Filter features based on user_type ---
foreach ($features as $feature) {
    if ($feature["access"] === "all" || $user_type === "admin") {
        $results["features"][] = [
            "type" => "feature",
            "name" => $feature["name"],
            "icon" => $feature["icon"],
            "url" => $feature["url"]
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($results);
$conn->close();
?>