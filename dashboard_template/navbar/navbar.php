<!-- Remove this part if you dont want to include session handling aka login/login with verification -->
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
?>

<!-- Start of the main page -->
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
            <!-- Right: Placeholder -->
            <div class="topbar-right"></div>
        </div>
        <div>
            <div class="user-info">
                <!-- replaced simple profile link with a user-menu + dropdown -->
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
<!-- ensure navbar script is loaded; defer to avoid blocking and to ensure DOM exists -->
<script src="../dashboard_template/navbar/navbar.js" defer></script>
<!-- End Navigation Bar Component -->