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
</head>

<body onload="fetchNotifications()">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <div class="content-body">

    <?php include '../includes/announcement.php'; ?>


    <div class="favorites-container">
    <div class="favorite-tabs" id="favoriteTabs">
        <span class="favorite-title">Favorite:</span>
        <!-- Favorites will be inserted here by JavaScript -->
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
        reader.onload = function () {
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

            xhr.onload = function () {
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
            breadcrumbs.push({ name: currentPageName, url: currentPageUrl });
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

    </script>

</body>

</html>