<?php
// Dashboard: queries and HTML output
require_once dirname(__DIR__) . '/mypbra_connect.php';

// Helper to run simple count queries
function getCount($conn, $sql)
{
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        return (int)$row[0];
    }
    return 0;
}

// Summary counts
$totalUsers = getCount($conn, "SELECT COUNT(*) FROM users");
$totalRoles = getCount($conn, "SELECT COUNT(*) FROM roles");
$totalDepartments = getCount($conn, "SELECT COUNT(*) FROM departments");
$totalAppointments = getCount($conn, "SELECT COUNT(*) FROM userroles");
$totalAnnouncements = getCount($conn, "SELECT COUNT(*) FROM announcement");
$totalFeedbacks = getCount($conn, "SELECT COUNT(*) FROM feedback");

// Aggregations for charts
// Roles per department (top 20 by count)
$sql = "
    SELECT d.name AS label, COUNT(r.id) AS value
    FROM roles r
    JOIN departments d ON r.department_id = d.id
    GROUP BY d.id
    ORDER BY value DESC
    LIMIT 20
";
$rolesPerDept = [];
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $rolesPerDept[] = $row;
    }
}

// Users per role (top 10)
$sql = "
    SELECT r.name AS label, COUNT(ur.user_id) AS value
    FROM roles r
    LEFT JOIN userroles ur ON r.id = ur.role_id
    GROUP BY r.id
    ORDER BY value DESC
    LIMIT 10
";
$usersPerRole = [];
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $usersPerRole[] = $row;
    }
}

// Tasks by status
$sql = "SELECT status AS label, COUNT(*) AS value FROM tasks GROUP BY status";
$tasksByStatus = [];
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $tasksByStatus[] = $row;
    }
}

// Build dashboard data to pass to JS
$dashboardData = [
    'summary' => [
        'users' => $totalUsers,
        'roles' => $totalRoles,
        'departments' => $totalDepartments,
        'appointments' => $totalAppointments,
        'announcements' => $totalAnnouncements,
        'feedbacks' => $totalFeedbacks,
    ],
    'charts' => [
        'rolesPerDept' => $rolesPerDept,
        'usersPerRole' => $usersPerRole,
        'tasksByStatus' => $tasksByStatus,
    ],
];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Roles Appointment</title>
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="top-bar">
            <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            <div class="logo"><img src="../login/images/pbralogo.png" alt="Logo"></div>
        </div>

        <ul class="menu">
            <li><span class="icon"></span><span class="text">Dashboard</span></li>
            <li><span class="icon"></span><span class="text">Users</span></li>
            <li><span class="icon"></span><span class="text">Roles</span></li>
            <li><span class="icon"></span><span class="text">Settings</span></li>
        </ul>
    </div>

    <!-- Floating Search Bar -->
    <div class="floating-search">
        <input type="text" placeholder="Search">
        <button class="search-btn"></button>
    </div>

    <!-- Profile Picture -->
    <div class="profile-pic" onclick="toggleDropdown()">
        <img src="../profile/images/default-profile.jpg" alt="Profile" />
    </div>

    <!-- Dropdown Menu -->
    <div class="dropdown-menu" id="dropdownMenu">
        <a href="#">Profile</a>
        <a href="logout.php">Log out</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <header>
            <h1>Roles Appointment Dashboard</h1>
            <p>Summary statistics generated from the pbradatabases.</p>
        </header>

        <section class="summary-cards">
            <div class="card">
                <h3>Total Users</h3>
                <p class="big"><?php echo $totalUsers; ?></p>
            </div>
            <div class="card">
                <h3>Total Roles</h3>
                <p class="big"><?php echo $totalRoles; ?></p>
            </div>
            <div class="card">
                <h3>Departments</h3>
                <p class="big"><?php echo $totalDepartments; ?></p>
            </div>
            <div class="card">
                <h3>Appointments</h3>
                <p class="big"><?php echo $totalAppointments; ?></p>
            </div>
            <div class="card">
                <h3>Announcements</h3>
                <p class="big"><?php echo $totalAnnouncements; ?></p>
            </div>
            <div class="card">
                <h3>Feedbacks</h3>
                <p class="big"><?php echo $totalFeedbacks; ?></p>
            </div>
        </section>

        <section class="charts-controls">
            <button id="toggleAllBtn" onclick="toggleAllCards()">Collapse All</button>
        </section>

        <section class="charts-grid">
            <div class="chart-card">
                <div class="card-header" onclick="toggleCard(this)">
                    <h4>Roles per Department</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="card-body">
                    <canvas id="rolesPerDeptChart" height="200"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="card-header" onclick="toggleCard(this)">
                    <h4>Users per Role (Top 10)</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="card-body">
                    <canvas id="usersPerRoleChart" height="200"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="card-header" onclick="toggleCard(this)">
                    <h4>Tasks by Status</h4>
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="card-body">
                    <canvas id="tasksByStatusChart" height="200"></canvas>
                </div>
            </div>
        </section>
    </div>

    <!-- Expose data to client script -->
    <script>
        window.DASHBOARD_DATA = <?php echo json_encode($dashboardData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    </script>

    <script src="script.js"></script>
</body>

</html>