<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = $_SESSION['id'];
$favorites = [];

// Get user favorites
$sql_fav = "SELECT page_name, page_url FROM user_favorites WHERE id = ?";
$stmt_fav = $conn->prepare($sql_fav);
if ($stmt_fav) {
    $stmt_fav->bind_param("i", $id);
    $stmt_fav->execute();
    $result_fav = $stmt_fav->get_result();
    while ($row = $result_fav->fetch_assoc()) {
        $favorites[] = $row;
    }
    $stmt_fav->close();
}

// Admin check
$is_admin = false;
$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($user_type);
if ($stmt->fetch() && $user_type === 'admin') {
    $is_admin = true;
}
$stmt->close();

// Handle announcement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $imagePath = null;

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = '../uploads/announcements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $uniqueName = uniqid() . '_' . $imageName;
        $fullPath = $uploadDir . $uniqueName;
        move_uploaded_file($imageTmp, $fullPath);
        $imagePath = 'uploads/announcements/' . $uniqueName; // ✅ Use this for HTML src
    }

    // Save to database
    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO announcement (title, content, image_path, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $title, $content, $imagePath);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch announcements (latest first)
$sql = "SELECT * FROM announcement ORDER BY created_at DESC";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Add these new styles inline or in your homepage.css */
        .top-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            padding: 20px;
        }

        .calendar-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .calendar-widget h3 {
            color: #4fc3f7;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .calendar-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            margin-bottom: 15px;
        }

        .day-header {
            text-align: center;
            font-weight: bold;
            padding: 10px 5px;
            color: #666;
            font-size: 12px;
        }

        .day-cell {
            text-align: center;
            padding: 10px 5px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s;
            min-height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .day-cell:hover {
            background: #e3f2fd;
        }

        .day-cell.highlight {
            background: #4fc3f7;
            color: white;
        }

        .day-cell.today {
            background: #2196f3;
            color: white;
            font-weight: bold;
        }

        .announcement-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body onload="fetchNotifications()">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <div class="content-body">
        <!-- Add the new two-column layout here -->
        <div class="top-content">
            <!-- Left Column - Calendar -->
            <div class="calendar-section">
                <div class="calendar-widget">
                    <h3>Calendar</h3>
                    <div class="calendar-header">
                        <div class="calendar-controls">
                            <button class="nav-btn" onclick="prevMonth()"><i class="fas fa-chevron-left"></i></button>
                            <span id="currentMonth">August 2025</span>
                            <button class="nav-btn" onclick="nextMonth()"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <button class="new-event-btn">
                        <i class="fas fa-plus"></i> New Event
                    </button>

                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Calendar will be generated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Right Column - Announcements -->
            <div class="announcement-section">
                <?php include '../includes/announcement.php'; ?>
            </div>
        </div>

        <div class="feature-container">
            <div class="role-container" onclick="window.location.href='../roles/roles.php';">
                <div class="text-role">
                    <h2><strong>ROLE</strong></h2>
                    <p>This section lets you easily keep track of all your roles, including when they start, end, and
                        the latest tasks completed.</p>
                </div>
            </div>

            <div class="events-container" onclick="window.location.href='../eventss/event.php';">
                <div class="text">
                    <h2><strong>EVENTS</strong></h2>
                    <p>This section shows your personalised calendar and schedule.</p>
                </div>
            </div>

            <div class="pbstaff-container" onclick="window.location.href='../staff/staffsch.php';">
                <div class="text">
                    <h2><strong>PB STAFF</strong></h2>
                    <p>Get to know who is in charge of each department and all of its staff.</p>
                </div>
            </div>
        </div>

        <div class="setting-section">
            <div class="feedback" onclick="window.location.href='../feedback/feedback.php';">
                <i class="fas fa-comment"></i> Feedback
            </div>
            <div class="report" onclick="window.location.href='../report/report.php';">
                <i class="fas fa-clipboard"></i> Report
            </div>
            <div class="user-support" onclick="window.location.href='../usersupport/usersupport.php';">
                <i class="fas fa-question-circle"></i> User Support
            </div>

        </div>

    </div>


    <footer>
        <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
    </footer>

    <script>
        function formatText(command) {
            document.execCommand(command, false, null);
        }

        function prepareSubmission() {
            const richContent = document.getElementById('richContent').innerHTML;
            document.getElementById('hiddenContent').value = richContent;
        }

        function openModal() {
            document.getElementById('announcementModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('announcementModal').style.display = 'none';
        }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('openFormBtn')?.addEventListener('click', openModal);
        });

        //FAVORITE
        function toggleFavorite() {
            var btn = document.getElementById("favorite-btn");
            var icon = document.getElementById("fav-icon");

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "toggle_favorite.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        if (response.favorited) {
                            icon.classList.remove("fa-heart-o");
                            icon.classList.add("fa-heart");
                            btn.classList.add("favorited");
                        } else {
                            icon.classList.remove("fa-heart");
                            icon.classList.add("fa-heart-o");
                            btn.classList.remove("favorited");
                        }
                    } else {
                        alert("Failed to update favorite.");
                    }
                }
            };

            xhr.send("toggle_favorite=true");
        }

        // Breadcrumbs
        let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
        let currentPageName = "Homepage";
        let currentPageUrl = window.location.pathname;

        if (!breadcrumbs.some(breadcrumb => breadcrumb.url === currentPageUrl)) {
            breadcrumbs.push({
                name: currentPageName,
                url: currentPageUrl
            });
            sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
        }

        let breadcrumbList = document.getElementById('breadcrumb-list');
        if (breadcrumbList) {
            breadcrumbList.innerHTML = '';
            breadcrumbs.forEach((breadcrumb, index) => {
                let breadcrumbItem = document.createElement('li');
                let link = document.createElement('a');
                link.href = breadcrumb.url;
                link.textContent = breadcrumb.name;
                breadcrumbItem.appendChild(link);
                breadcrumbList.appendChild(breadcrumbItem);

                if (index < breadcrumbs.length - 1) {
                    let separator = document.createElement('span');
                    separator.textContent = ' > ';
                    breadcrumbList.appendChild(separator);
                }
            });
        }

        // Modal Controls
        // Update your modal control functions

        // FAVORITE from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            const favContainer = document.getElementById('favoriteTabs');

            if (favorites.length === 0) {
                const noFav = document.createElement('span');
                noFav.textContent = "No favorites yet.";
                favContainer.appendChild(noFav);
            } else {
                favorites.forEach(fav => {
                    const link = document.createElement('a');
                    link.href = fav.pageUrl;
                    link.className = 'favorite-tab';
                    link.textContent = fav.pageName;
                    favContainer.appendChild(link);
                });
            }
        });

        // Calendar functionality
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        let currentDate = new Date();

        function generateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            document.getElementById('currentMonth').textContent = `${months[month]} ${year}`;

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();

            let calendarHTML = `
                <div class="day-header">Sun</div>
                <div class="day-header">Mon</div>
                <div class="day-header">Tue</div>
                <div class="day-header">Wed</div>
                <div class="day-header">Thu</div>
                <div class="day-header">Fri</div>
                <div class="day-header">Sat</div>
            `;

            for (let i = 0; i < startingDay; i++) {
                calendarHTML += '<div class="day-cell empty"></div>';
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = new Date().toDateString() === new Date(year, month, day).toDateString();
                calendarHTML += `
                    <div class="day-cell ${isToday ? 'today' : ''}">${day}</div>
                `;
            }

            document.getElementById('calendarGrid').innerHTML = calendarHTML;
        }

        function prevMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar();
        }

        // Initialize calendar when page loads
        document.addEventListener('DOMContentLoaded', generateCalendar);
    </script>

</body>

</html>