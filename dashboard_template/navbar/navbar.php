<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// Get logged-in user ID
$logged_in_user_id = $_SESSION['id'];

$profile_pic = (!empty($_SESSION['profile_pic']) && file_exists('../' . $_SESSION['profile_pic']))
    ? '../' . htmlspecialchars($_SESSION['profile_pic'])
    : '../profile/images/default-profile.jpg';
include '../mypbra_connect.php'; // Ensure DB connection

// Get logged-in user information
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
?>

<!-- Start of the main page -->
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<link rel="stylesheet" href="../dashboard_template/navbar/style.css" />

<!-- Navigation Bar Component -->
<div id="navBarComponent">
    <!-- Top bar -->
    <div id="topbar" class="topbar">
        <div class="topbar-inner">
            <!-- Left: Hamburger -->
            <div class="topbar-left">
                <div class="hamburger-container" onclick="navBar.toggleSidebar(event)">
                    <span class="hamburger">&#9776;</span>
                </div>
            </div>
            <!-- Center: Search -->
            <div class="topbar-center">
                <input type="text" id="topSearch" class="search-bar" placeholder="Search..." onfocus="navBar.openSearch()" />
            </div>
            <!-- Right: Notification, Mail, Profile -->
            <div class="topbar-right" style="position:relative;">
                <button class="notification-btn" onclick="toggleNotifications()" aria-haspopup="true" aria-expanded="false" aria-label="Notifications">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <?php if ($unread_count > 0) { ?>
                        <span class="notification-dot" id="notification-dot"><?php echo $unread_count; ?></span>
                    <?php } else { ?>
                        <span class="notification-dot" id="notification-dot" style="display:none;"></span>
                    <?php } ?>
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
                <div class="mail-button-container">
                    <button class="mail-button" onclick="window.location.href='../mails/mail_page.php';" style="cursor: pointer;" aria-label="Mail">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- moved user-info inside topbar-right so icons appear directly left of profile -->
                <div class="user-info">
                    <div id="userMenu" class="user-menu" onclick="navBar.toggleUserMenu(event)" tabindex="0" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($profile_pic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture" id="profile-pic">
                        <ul class="user-dropdown" role="menu" aria-label="User menu">
                            <li role="menuitem"><a href="../profile/profile.php">View Profile</a></li>
                            <li role="menuitem"><a href="../logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Component -->
        <div id="sidebar" class="sidebar">
            <span class="closebtn" onclick="navBar.closeSidebar()">&times;</span>
            <a href="../homepage/homepage.php">Home</a>
            <a href="#">Task Management</a>
            <a href="#">Statistics</a>
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') { ?>
                <a href="../registration/registration.php">Register User</a>
            <?php } ?>
            <a href="../feedback/feedback.php">Feedback</a>
            <a href="./report/report.php">Report</a>
            <a href="../usersupport/usersupport.php">User Support</a>
            <a href="../virtualmeeting/virtualmeeting.php">Virtual Meeting</a>
        </div>

        <!-- Overlay background -->
        <div id="sidebarOverlay" class="overlay" onclick="navBar.closeSidebar()"></div>

        <!-- Search overlay -->
        <div id="searchOverlay" class="search-overlay" onclick="navBar.closeSearch(event)">
            <div class="search-popup" onclick="event.stopPropagation()">
                <input type="text" id="popupSearch" placeholder="Type to search..." />
                <span class="search-close" onclick="navBar.closeSearch(event)">&times;</span>
            </div>
        </div>

    </div>

    <script src="../dashboard_template/navbar/navbar.js" defer></script>