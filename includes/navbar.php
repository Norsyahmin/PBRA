<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$logged_in_user_id = $_SESSION['id'];

$profile_pic = (!empty($_SESSION['profile_pic']) && file_exists('../' . $_SESSION['profile_pic'])) 
    ? '../' . htmlspecialchars($_SESSION['profile_pic']) 
    : '../profile/images/default-profile.jpg';
    include '../mypbra_connect.php'; // Ensure DB connection

    $logged_in_user = $_SESSION['full_name'];
    
    // Fetch unread notifications
    $sql = "SELECT message, url FROM notifications WHERE user_id=? AND is_read=FALSE ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $logged_in_user_id);
    $stmt->execute();    
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'message' => htmlspecialchars($row['message']),
            'url' => $row['url']
        ];
    }
    
    
    // Count unread notifications
    $unread_count = count($notifications);
    
    // Mark notifications as read after fetching
    $sql = "UPDATE notifications SET is_read=TRUE WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $logged_in_user_id); // "i" is for integer
    $stmt->execute();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Navbar</title>
</head>


<nav class="navbar">
<div class="nav-left">
<div class="menu-btn">
                <button id="menu-toggle"><i class="material-icons">menu</i></button>
            </div>
        <!-- Search Bar with Results -->
        <div class="search-bar">
            <input type="text" placeholder="   Search..." id="search" onkeyup="liveSearch()" autocomplete="off">
            <div id="search-results" class="search-results"></div>
        </div>
    </div>


    <div class="nav-right">
    <button class="mail-button" onclick="window.location.href='../mails/mail_page.php';" style="cursor: pointer;">
        <i class="fas fa-envelope"></i>  <!-- Mail icon --></button>

        <button class="notification-btn" onclick="toggleNotifications()">
    <i class="fas fa-bell"></i>
    <span class="notification-dot" id="notification-dot" style="display:none;"></span>
</button>

<div class="notification-container" id="notification-container" style="display:none;">
    <div class="notification-header">Notifications</div>
    <ul class="notification-list" id="notification-list">
<?php if (!empty($notifications)) { 
    foreach ($notifications as $note) { ?>
        <li>
            <a href="<?= htmlspecialchars($note['url']) ?>" style="text-decoration:none; color:black;">
                <?= $note['message'] ?>
            </a>
        </li>
<?php } 
} else { ?>
    <li>No new notifications</li>
<?php } ?>
</ul>

</div>

       
        <div class="user-info">
        <div class="full-name">
        <span id="user_full_name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
        </div>
        
        <a href="../profile/profile.php">
            <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" id="profile-pic">
        </a>
    </div>
    
    </div>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <ul>
        <li><a href="../homepage/homepage.php">Home</a></li>
        <li><a href="../feedback/feedback.php">Feedback</a></li>
        <li><a href="../report/report.php">Report</a></li>
        <li><a href="../usersupport/usersupport.php">User Support</a></li>
    </ul>
</div>


</nav>

<script src="../navbar.js"></script> <!-- Load the external JS -->
