<?php
$hasMatch = false;  // ✅ ADD THIS FIRST
session_start();
include '../mypbra_connect.php';

$prefill_to = isset($_GET['to']) ? $_GET['to'] : '';

$page_name = $page_name ?? 'Appoint Role'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$role_id = $_GET['role_id'] ?? null;
if (!$role_id)
    die("No role selected");

$current_user_id = $_SESSION['id'] ?? null;
$current_user_id = intval($current_user_id); // Now it's defined

// Fetch role info with department_id
$role = $conn->query("SELECT r.name, r.department_id, d.name AS dept_name 
                      FROM roles r 
                      JOIN departments d ON r.department_id = d.id 
                      WHERE r.id = $role_id")->fetch_assoc();

$dept_id = $role['department_id'];

// Fetch requirements for department
$requirements = $conn->query("
    SELECT requirement_type, keyword, description 
    FROM department_requirements 
    WHERE department_id = $dept_id
")->fetch_all(MYSQLI_ASSOC);

// Fetch current holders
$holders = $conn->query("
    SELECT u.id, u.full_name, u.profile_pic, u.email, ur.appointed_at
    FROM users u
    JOIN userroles ur ON u.id = ur.user_id
    WHERE ur.role_id = $role_id
");

// Collect all holder IDs
$holder_ids = [];
$current_holders_data = [];
while ($row = $holders->fetch_assoc()) {
    $holder_ids[] = $row['id'];
    $current_holders_data[] = $row;
}
$holders = $current_holders_data; // Reassign so we can display holders later



// Fetch all users
$eligible = $conn->query("
    SELECT id, full_name, email, education, work_experience, profile_pic 
    FROM users 
    WHERE id != $current_user_id
")->fetch_all(MYSQLI_ASSOC);




// Match function (loose matching if 1 keyword matches)
function isCandidate($user, $requirements)
{
    $educationLevels = ['certificate', 'diploma', 'degree', 'bachelor', 'honours', 'master', 'phd'];

    $matchesEducation = false;
    $matchesExperience = false;

    foreach ($requirements as $req) {
        $field = strtolower($req['requirement_type'] === 'education' ? $user['education'] : $user['work_experience']);
        $keyword = strtolower($req['keyword']);

        $field = str_replace(['â€™', '’', '‘', '–', '—'], ["'", "'", "'", '-', '-'], $field);
        $keyword = str_replace(['â€™', '’', '‘', '–', '—'], ["'", "'", "'", '-', '-'], $keyword);

        if ($req['requirement_type'] === 'education') {
            $userLevel = $requiredLevel = -1;
            foreach ($educationLevels as $i => $level) {
                if (stripos($field, $level) !== false)
                    $userLevel = $i;
                if (stripos($keyword, $level) !== false)
                    $requiredLevel = $i;
            }
            if ($userLevel >= $requiredLevel && $userLevel !== -1 && $requiredLevel !== -1) {
                $matchesEducation = true;
            }
        } else {
            if (stripos($field, $keyword) !== false) {
                $matchesExperience = true;
            }
        }
    }

    return $matchesEducation || $matchesExperience;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appoint'])) {
    $selected_user_id = intval($_POST['selected_user_id']);
    $appointed_by = $_SESSION['id'] ?? null;

    // Check if user is already assigned to this role
    $exists = $conn->prepare("SELECT * FROM userroles WHERE user_id = ? AND role_id = ?");
    $exists->bind_param("ii", $selected_user_id, $role_id);
    $exists->execute();
    $existsResult = $exists->get_result();

    if ($existsResult->num_rows > 0) {
        $modal_message = 'This user is already appointed to this role.';
    } else {
        // Insert into userroles
        $stmt = $conn->prepare("INSERT INTO userroles (user_id, role_id, appointed_at, appointed_by) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("iii", $selected_user_id, $role_id, $appointed_by);

        if ($stmt->execute()) {
            // Insert notification for appointed user
            // Correct version ✅
            $notifyMessage = "You have been appointed to the role of " . $role['name'];
            $notificationStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notificationStmt->bind_param("is", $selected_user_id, $notifyMessage);
            $notificationStmt->execute();
            $notificationStmt->close();

            // INSERT into role_history
            $historyStmt = $conn->prepare("INSERT INTO role_history (user_id, role_id, assigned_at) VALUES (?, ?, NOW())");
            $historyStmt->bind_param("ii", $selected_user_id, $role_id);
            $historyStmt->execute();
            $historyStmt->close();

            // UPDATE role_history removed_at
            $updateHistory = $conn->prepare("
        UPDATE role_history 
        SET removed_at = NOW() 
        WHERE user_id = ? AND role_id = ? AND removed_at IS NULL
    ");
            $updateHistory->bind_param("ii", $dismiss_user_id, $role_id);
            $updateHistory->execute();
            $updateHistory->close();


            $modal_message = 'User appointed successfully!';
        } else {
            $modal_message = 'Error occurred while dismissing.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss'])) {
    $dismiss_user_id = intval($_POST['dismiss_user_id']);
    $appointed_by = $_SESSION['id'] ?? null; // Optional, to log who dismissed

    // Remove the user from the role
    $stmt = $conn->prepare("DELETE FROM userroles WHERE user_id = ? AND role_id = ?");
    $stmt->bind_param("ii", $dismiss_user_id, $role_id);

    // ✅ After dismissing, update removed_at in role_history
    $updateHistory = $conn->prepare("
UPDATE role_history 
SET removed_at = NOW() 
WHERE user_id = ? AND role_id = ? AND removed_at IS NULL
");
    $updateHistory->bind_param("ii", $dismiss_user_id, $role_id);
    $updateHistory->execute();
    $updateHistory->close();


    if ($stmt->execute()) {
        // Insert notification for dismissed user
        $dismissMessage = "You have been dismissed from the role of " . $role['name'];
        $dismissNotification = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $dismissNotification->bind_param("is", $dismiss_user_id, $dismissMessage);
        $dismissNotification->execute();
        $dismissNotification->close();

        $modal_message = 'User dismissed successfully!';
    } else {
        $modal_message = 'Error occured during dismissing'; // ✅ CORRECT
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($role['name']) ?></title>
    <link rel="stylesheet" href="appoint.css">
</head>
<header>
    <?php include '../navbar/navbar.php'; ?>
</header>

<body>
    <div class="page-title">
        <h1 style="font-size: 30px;"><?= strtoupper($role['name']) ?></h1>
        <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
            Add to Favorite
        </button>
    </div>


    <div class="breadcrumb">
        <ul id="breadcrumb-list"></ul>
    </div>

    <div class="section-title-requirements">
        <strong>Requirements for the role</strong>
        <ul class="requirements">
            <?php foreach ($requirements as $req): ?>
                <li><?= htmlspecialchars($req['description']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- START Clean Container -->
    <?php foreach ($holders as $row): ?>
        <div class="card" style="display: flex; align-items: center; gap: 20px; cursor: pointer; position: relative; margin-bottom:20px;">
            <a href="../profile/profile.php?id=<?= $row['id'] ?>" style="text-decoration: none; color: inherit; display: flex; align-items: center; flex: 1; gap: 20px;">
                <img src="<?php echo (!empty($row['profile_pic']) && file_exists('../' . $row['profile_pic']))
                                ? '../' . htmlspecialchars($row['profile_pic'])
                                : '../profile/images/default-profile.jpg'; ?>"
                    alt="Profile Picture" style="width:70px;height:70px;border-radius:50%;object-fit:cover;" />

                <div>
                    <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                    <span><?= htmlspecialchars($role['name']) ?></span><br>
                    <small>Appointed on <?= date('d M Y', strtotime($row['appointed_at'])) ?></small>
                </div>
            </a>

            <!-- Email Button -->
            <a href="../mails/mail_page.php?to=<?= urlencode($row['email']) ?>"
                class="email-button"
                onclick="event.stopPropagation();"
                title="Send Email via System">
                &#9993;
            </a>


            <!-- Dismiss Button -->
            <form method="POST" class="dismiss-form" style="margin-left: auto;">
                <input type="hidden" name="dismiss_user_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="dismiss" value="1"> <!-- 🛠️ Added this -->
                <button type="button" class="dismiss-button" onclick="openDismissModal('<?= addslashes($row['full_name']) ?>', this)">Dismiss</button>
            </form>

        </div>
    <?php endforeach; ?>


    </div>

    <!-- Search Button -->
    <button class="search-button" onclick="showSuggested()">Search Suggested Candidates</button>

    <!-- Suggested Candidates -->
    <div id="suggested-container" style="display:none; margin-top: 40px;">
        <h2 style="text-align:center; color: #174080;">Suggested Candidates</h2>

        <?php foreach ($eligible as $user):
            if ((int)$user['id'] === $current_user_id) continue;    // Skip logged-in user
            if (in_array($user['id'], $holder_ids)) continue;       // Skip users already holding the role
            if (!isCandidate($user, $requirements)) continue;

            $hasMatch = true;
        ?>
            <div class="card" style="display: flex; align-items: center; gap: 20px; position: relative;">
                <a href="../profile/profile.php?id=<?= $user['id'] ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?php echo (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic']))
                                    ? '../' . htmlspecialchars($user['profile_pic'])
                                    : '../profile/images/default-profile.jpg'; ?>" alt="Profile Picture"
                        style="width:70px;height:70px;border-radius:50%;object-fit:cover;" />
                </a>

                <div style="flex: 1;">
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong><br>
                    <span><strong>Education:</strong> <?= htmlspecialchars($user['education']) ?></span><br>
                    <span><strong>Experience:</strong> <?= htmlspecialchars($user['work_experience']) ?></sp><br>

                        <form method="POST" class="appoint-form" style="display:inline;">
                            <input type="hidden" name="selected_user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="appoint" value="1">
                            <button type="button" class="appoint-button"
                                onclick="openConfirmModal('<?= addslashes($user['full_name']) ?>', this)">
                                Appoint
                            </button>
                        </form>
                </div>

                <a href="../mails/mail_page.php?to=<?= urlencode($row['email']) ?>"
                    class="email-button"
                    onclick="event.stopPropagation();"
                    title="Send Email via System">
                    &#9993;
                </a>
            </div>
        <?php endforeach; ?>


        <?php if (!$hasMatch): ?>
            <p style="text-align:center; color: grey;">No suggested candidates match any requirement at this time.</p>
        <?php endif; ?>
    </div>

    <!-- Confirm Modal -->
    <div id="confirmModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:30px 20px; border-radius:10px; max-width:400px; width:90%; text-align:center;">
            <h2 id="confirmText" style="margin-bottom:20px;">Confirm Appointment?</h2>
            <div style="margin-top:20px;">
                <button id="confirmYes" style="padding:10px 20px; background-color:#174080; color:white; border:none; border-radius:5px; margin-right:10px;">Confirm</button>
                <button onclick="closeModal()" style="padding:10px 20px; background-color:#ccc; border:none; border-radius:5px;">Cancel</button>
            </div>
        </div>
    </div>

    <div id="successModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:30px 20px; border-radius:10px; max-width:400px; width:90%; text-align:center;">
            <h2 id="successText" style="margin-bottom:20px;"></h2>
            <div style="margin-top:20px;">
                <button onclick="closeSuccessModal()" style="padding:10px 20px; background-color:#174080; color:white; border:none; border-radius:5px;">OK</button>
            </div>
        </div>
    </div>

    <div id="dismissModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:30px 20px; border-radius:10px; max-width:400px; width:90%; text-align:center;">
            <h2 id="dismissText" style="margin-bottom:20px;">Confirm Dismissal?</h2>
            <div style="margin-top:20px;">
                <button id="confirmDismiss" style="padding:10px 20px; background-color:#d9534f; color:white; border:none; border-radius:5px; margin-right:10px;">Confirm</button>
                <button onclick="closeDismissModal()" style="padding:10px 20px; background-color:#ccc; border:none; border-radius:5px;">Cancel</button>
            </div>
        </div>
    </div>


    <?php if (!empty($modal_message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                openSuccessModal(<?= json_encode($modal_message) ?>);
            });
        </script>
    <?php endif; ?>



    </div>

    <script>
        function showSuggested() {
            document.getElementById('suggested-container').style.display = 'block';
        }

        function confirmAppoint(name) {
            return confirm(`Are you sure you want to appoint ${name} to this role?`);
        }

        let currentForm = null;
        let dismissForm = null;

        function openConfirmModal(name, button) {
            currentForm = button.closest('form');
            document.getElementById('confirmText').innerText = `Are you sure you want to appoint ${name}?`;
            document.getElementById('confirmModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        document.getElementById('confirmYes').addEventListener('click', function() {
            if (currentForm) {
                currentForm.submit();
            }
        }); // ✅ CLOSE HERE

        function openDismissModal(name, button) {
            dismissForm = button.closest('form');
            document.getElementById('dismissText').innerText = `Are you sure you want to dismiss ${name}?`;
            document.getElementById('dismissModal').style.display = 'flex';
        }

        function closeDismissModal() {
            document.getElementById('dismissModal').style.display = 'none';
        }

        document.getElementById('confirmDismiss').addEventListener('click', function() {
            if (dismissForm) {
                dismissForm.submit();
            }
        });

        function openSuccessModal(message) {
            document.getElementById('successText').innerText = message;
            document.getElementById('successModal').style.display = 'flex';
        }

        function closeSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
            location.href = "appoint.php?role_id=<?= $role_id ?>";
        }

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