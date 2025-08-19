<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';

$page_name = $page_name ?? 'Profile'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$user_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : $_SESSION['id'];

$sql = "SELECT full_name, email, start_date, work_experience, education, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Error: No user found.";
    exit();
}

$roles = [];
$role_query = "SELECT r.name AS role_name, d.name AS department_name
               FROM userroles ur
               JOIN roles r ON ur.role_id = r.id
               JOIN departments d ON r.department_id = d.id
               WHERE ur.user_id = ?";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $user_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();

while ($row = $role_result->fetch_assoc()) {
    $roles[] = $row['role_name'] . " (" . $row['department_name'] . ")";
}
$role_stmt->close();

$profile_pic = (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic']))
    ? '../' . htmlspecialchars($user['profile_pic'])
    : '../profile/images/default-profile.jpg';

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="profile.css" />
    <link rel="stylesheet" href="navbar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <title>Profile</title>
</head>

<header>
    <?php include '../navbar/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">

    <div class="page-title">
        <h1 style="font-size: 30px;">PROFILE</h1>
        <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
            Add to Favorite
        </button>
    </div>

    <div class="breadcrumb">
        <ul id="breadcrumb-list"></ul>
    </div>

    <div class="profile-container">
        <div class="user-profile">
            <img src="<?php echo (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic']))
                            ? '../' . htmlspecialchars($user['profile_pic'])
                            : '../profile/images/default-profile.jpg'; ?>"
                alt="Profile Picture" />

        </div>

        <div class="user-details">
            <span id="name"><?php echo htmlspecialchars($user['full_name']); ?></span>
            <span id="email"><?php echo htmlspecialchars($user['email']); ?></span>
            <span id="role"><?php echo !empty($roles) ? implode(', ', $roles) : 'No roles assigned'; ?></span>
            <span id="date-start"><?php echo htmlspecialchars($user['start_date']); ?></span>
        </div>

        <div class="buttons">
            <?php if ($_SESSION['id'] == $user_id): ?>
                <button id="edit-btn">Edit Profile</button>
            <?php endif; ?>

            <button type="button" class="view-log-btn" onclick="window.location.href='../myrole/myrole.php?id=<?php echo $user_id; ?>';">View Activity Log</button>

            <?php if ($_SESSION['id'] == $user_id): ?>
                <button class="logout-btn" onclick="confirmLogout()">Logout</button>
            <?php endif; ?>
        </div>

        <!-- Logout Popup -->
        <div id="logoutConfirmBox" class="logout-popup" style="display: none;">
            <div class="logout-popup-content">
                <h3>Are you sure you want to logout?</h3>
                <div class="popup-actions">
                    <button onclick="proceedLogout()" class="logout-btn">Yes</button>
                    <button onclick="closeLogoutPopup()" class="cancel-btn">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <?php if ($_SESSION['id'] == $user_id): ?>
            <div id="editProfileModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h3>Edit Profile</h3>
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <label>Full Name:</label><br>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>"><br><br>
                        <label>Email:</label><br>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"><br><br>
                        <label>Profile Picture:</label><br>
                        <input type="file" name="profile_pic" class="file-input"><br><br>
                        <label>Work Experience:</label><br>
                        <textarea name="work_experience" rows="4"><?php echo str_replace("\n", "&#10;", htmlspecialchars($user['work_experience'])); ?></textarea><br><br>
                        <label>Education:</label><br>
                        <textarea name="education" rows="4"><?php echo str_replace("\n", "&#10;", htmlspecialchars($user['education'])); ?></textarea><br><br>
                        <button type="submit" class="submit-btn">Save Changes</button>
                    </form>
                    <form id="deleteProfilePicForm" action="delete_profile_pic.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <button type="submit" class="delete-btn">Remove Profile Picture</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="background">
        <div class="work-experience">
            <h2>Work Experience</h2>
            <ul>
                <?php foreach (explode("\n", $user['work_experience']) as $experience): ?>
                    <?php if (!empty(trim($experience))) echo '<li>' . htmlspecialchars($experience) . '</li><br>'; ?>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="education">
            <h2>Education</h2>
            <ul>
                <?php foreach (explode("\n", $user['education']) as $education): ?>
                    <?php if (!empty(trim($education))) echo '<li>' . htmlspecialchars($education) . '</li>'; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Delete Profile Picture Modal -->
    <div id="deletePicModal" class="modal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:white; padding:30px 20px; border-radius:10px; max-width:400px; width:90%; text-align:center;">
            <h3>Remove Profile Picture?</h3>
            <p>Are you sure you want to remove your profile picture?</p>
            <div style="margin-top:20px;">
                <button id="confirmDeletePic" style="padding:10px 20px; background-color:#d9534f; color:white; border:none; border-radius:5px; margin-right:10px;">Yes</button>
                <button onclick="closeDeletePicModal()" style="padding:10px 20px; background-color:#ccc; border:none; border-radius:5px;">Cancel</button>
            </div>
        </div>
    </div>


    <script>
        // Open the delete profile picture modal
        document.querySelector('.delete-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('deletePicModal').style.display = 'flex';
        });

        // Close modal function
        function closeDeletePicModal() {
            document.getElementById('deletePicModal').style.display = 'none';
        }

        // Confirm delete
        document.getElementById('confirmDeletePic')?.addEventListener('click', function() {
            document.getElementById('deleteProfilePicForm').submit();
        });

        document.getElementById("edit-btn")?.addEventListener("click", () => {
            document.getElementById("editProfileModal").style.display = "flex";
        });

        function closeModal() {
            document.getElementById("editProfileModal").style.display = "none";
        }

        function confirmLogout() {
            document.getElementById("logoutConfirmBox").style.display = "flex";
        }

        function closeLogoutPopup() {
            document.getElementById("logoutConfirmBox").style.display = "none";
        }

        function proceedLogout() {
            window.location.href = "logout.php";
        }

        function confirmProfilePicDeletion() {
            return confirm("Are you sure you want to remove your profile picture?");
        }

        // Breadcrumbs
        // Breadcrumbs
        let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
        let currentPageUrl = window.location.pathname;

        // 🧠 Instead of hardcoding, get <title> automatically
        let currentPageName = document.title.trim();

        let pageExists = breadcrumbs.some(b => b.url === currentPageUrl);

        if (!pageExists) {
            breadcrumbs.push({
                name: currentPageName,
                url: currentPageUrl
            });
            sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
        }

        let breadcrumbList = document.getElementById('breadcrumb-list');
        breadcrumbList.innerHTML = '';

        breadcrumbs.forEach((breadcrumb, index) => {
            let item = document.createElement('li');
            let link = document.createElement('a');
            link.href = breadcrumb.url;
            link.textContent = breadcrumb.name;

            link.addEventListener('click', (e) => {
                e.preventDefault();
                breadcrumbs = breadcrumbs.slice(0, index + 1);
                sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
                window.location.href = breadcrumb.url;
            });

            item.appendChild(link);
            breadcrumbList.appendChild(item);

            if (index < breadcrumbs.length - 1) {
                let separator = document.createElement('span');
                separator.textContent = ' > ';
                breadcrumbList.appendChild(separator);
            }
        });

        //favorite
        const pageName = "<?php echo $page_name; ?>";
        const pageUrl = "<?php echo $page_url; ?>";
        const button = document.getElementById('favoriteButton');

        // Check if already favorited when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            const exists = favorites.find(fav => fav.pageName === pageName);
            if (exists) {
                button.classList.add('favorited');
                button.textContent = 'Favorited';
            }
        });

        function toggleFavorite() {
            let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');

            const index = favorites.findIndex(fav => fav.pageName === pageName);

            if (index === -1) {
                // Not favorited yet, add it
                favorites.push({
                    pageName: pageName,
                    pageUrl: pageUrl
                });
                button.classList.add('favorited');
                button.textContent = 'Favorited';
            } else {
                // Already favorited, remove it
                favorites.splice(index, 1);
                button.classList.remove('favorited');
                button.textContent = 'Add to Favorite';
            }

            localStorage.setItem('favorites', JSON.stringify(favorites));
        }
    </script>

</body>

</html>