<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';
include 'update_expired_task.php';


$page_name = $page_name ?? 'My Role'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$user_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : $_SESSION['id'];
$is_own_profile = ($_SESSION['id'] == $user_id);
$current_datetime = date("Y-m-d H:i:s");

// Get user info
$sql = "
    SELECT u.full_name, u.email, u.start_date, u.profile_pic, u.user_type,
           r.name AS role_name, d.name AS department_name
    FROM users u
    LEFT JOIN userroles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN departments d ON r.department_id = d.id
    WHERE u.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc() ?? [];

$profile_pic = (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic']))
    ? '../' . htmlspecialchars($user['profile_pic'])
    : '../profile/images/default-profile.jpg';

$viewed_user_type = $user['user_type'] ?? '';
$isAdmin = ($_SESSION['user_type'] === 'admin');

$roles = [];
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    if (!empty($row['role_name']) && !in_array($row['role_name'], $roles)) {
        $roles[] = $row['role_name'] . " (" . ($row['department_name'] ?? 'No Department') . ")";
    }
}
$stmt->close();

$sql_tasks = "
    SELECT task_id, task_name, task_date, task_time, task_description, status, reason, proof_path, last_updated, created_at
    FROM tasks
    WHERE assigned_to = ?
ORDER BY task_date DESC, task_time DESC

";

$stmt_tasks = $conn->prepare($sql_tasks);
$stmt_tasks->bind_param("i", $user_id);
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();

$tasks = [];
while ($row = $result_tasks->fetch_assoc()) {
    $task_deadline = $row['task_date'] . ' ' . $row['task_time'];
    $tasks[] = $row;
}
$stmt_tasks->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>My Role</title>
    <link rel="stylesheet" href="myrole.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
    <?php include '../navbar/navbar.php'; ?>

    <div class="page-title">
        <h1>MY ROLES</h1>
        <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
            Add to Favorite
        </button>
    </div>


    <div class="breadcrumb">
        <ul id="breadcrumb-list"></ul>
    </div>


    <div class="container">
        <div class="user-profile">
            <img src="<?php echo (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic']))
                            ? '../' . htmlspecialchars($user['profile_pic'])
                            : '../profile/images/default-profile.jpg'; ?>"
                alt="Profile Picture" />
        </div>
        <div class="container-text">
            <h1><?= htmlspecialchars($user['full_name']) ?></h1>
            <p>Roles: <?= implode(', ', $roles) ?></p>
            <p>Start Date: <?= htmlspecialchars($user['start_date']) ?></p>
            <button class="see-profile" onclick="window.location.href='../profile/profile.php?id=<?= $user_id ?>'">
                See Profile <i class="fa fa-id-card"></i>
            </button>
        </div>
    </div>

    <div class="activity-header">
        <h1>Activity Log</h1>
        <hr class="log-line" />
    </div>

    <!-- 🔍 Filter Section -->
    <div class="task-filter-container" style="text-align:center; margin-bottom: 20px;">
        <input type="text" id="taskSearchInput" placeholder="Search tasks..." style="width: 40%; padding: 8px; margin-right: 10px;">

        <select id="statusFilter" style="padding: 8px; margin-right: 10px;">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="not_completed">Not Completed</option>
        </select>
    </div>



    <div class="activity-log">
        <?php if (empty($tasks)): ?>
            <p class="no-task">No tasks assigned</p>
        <?php else: ?>
            <?php
            function getTimeGroupLabel($datetime)
            {
                $taskDate = new DateTime($datetime); // this is created_at
                $today = new DateTime('today');
                $yesterday = new DateTime('yesterday');
                $last7Days = new DateTime('-7 days');

                if ($taskDate >= $today) return 'Today';
                elseif ($taskDate >= $yesterday && $taskDate < $today) return 'Yesterday';
                elseif ($taskDate >= $last7Days) return 'Last 7 Days';
                else return 'Earlier';
            }

            $groupedTasks = [];
            foreach ($tasks as $task) {
                $groupLabel = getTimeGroupLabel($task['created_at']);
                $groupedTasks[$groupLabel][] = $task;
            }

            $groupOrder = ['Today', 'Yesterday', 'Last 7 Days', 'Earlier'];
            foreach ($groupOrder as $label):
                if (!isset($groupedTasks[$label])) continue;
                echo "<div class='task-group' data-group='$label'>";
                echo "<h3 class='task-date-group' style='margin: 30px 10px 10px; font-weight:bold;'>$label</h3>";

                foreach ($groupedTasks[$label] as $task):
                    $createdAt = $task['created_at'] ?? null;
                    $lastUpdated = isset($task['last_updated']) ? strtotime($task['last_updated']) : null;
                    $buttonsVisible = $task['status'] === 'pending';
                    $statusClass = $task['status'] === 'completed'
                        ? 'completed-task'
                        : ($task['status'] === 'not_completed' ? 'not-completed-task' : '');
                    $canViewProof = $isAdmin || $is_own_profile;
            ?>
                    <div class="activity-box <?= $statusClass ?>" data-created="<?= strtotime($task['created_at']) ?>" data-task-id="<?= $task['task_id'] ?>">


                        <div class="activity-container">
                            <?php if ($is_own_profile && $task['status'] === 'pending'): ?>
                                <div class="activity-status-buttons">
                                    <button class="btn-complete" onclick="openProofModal(<?= $task['task_id'] ?>)">Complete</button>
                                    <button class="btn-incomplete" onclick="openReasonModal(<?= $task['task_id'] ?>)">Not Complete</button>
                                </div>
                            <?php endif; ?>
                            <div class="content-left">
                                <div class="task-name"><?= htmlspecialchars($task['task_name']) ?></div>
                                <div class="description"><?= htmlspecialchars($task['task_description']) ?></div>

                                <?php if ($task['status'] === 'not_completed' && !empty($task['reason'])): ?>
                                    <div class="reason"><strong>Reason:</strong> <?= htmlspecialchars($task['reason']) ?></div>
                                <?php endif; ?>

                                <?php if ($task['status'] === 'completed' && $task['proof_path'] && $canViewProof): ?>
                                    <div class="proof-preview" style="margin-top: 10px;"><strong>Proof:</strong><br>
                                        <?php
                                        $ext = pathinfo($task['proof_path'], PATHINFO_EXTENSION);
                                        $proofUrl = "/pbra_website/" . htmlspecialchars($task['proof_path']);
                                        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                                            echo "<a href='$proofUrl' target='_blank'><img src='$proofUrl' style='max-width:200px;border-radius:6px;margin-top:5px;'></a>";
                                        } elseif (in_array($ext, ['mp4', 'webm'])) {
                                            echo "<video controls style='max-width:200px;border-radius:6px;margin-top:5px;'>
                                        <source src='$proofUrl' type='video/$ext'>
                                      </video><br><a href='$proofUrl' target='_blank'>Open Video</a>";
                                        } elseif ($ext === 'pdf') {
                                            echo "<a href='$proofUrl' target='_blank'>📄 View PDF</a>";
                                        } else {
                                            echo "<a href='$proofUrl' target='_blank'>Download Proof</a>";
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="content-right">
                                <div class="date"><?= htmlspecialchars($task['task_date']) ?></div>
                                <div class="time"><?= htmlspecialchars($task['task_time']) ?></div>
                            </div>
                        </div>
                    </div>
            <?php
                endforeach;
                echo "</div>"; // close task-group
            endforeach;

            ?>

        <?php endif; ?>
    </div>

    <!-- Reason Modal -->
    <div id="reasonModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('reasonModal')">&times;</span>
            <h2>Why couldn't you complete this task?</h2>
            <textarea id="reasonText" placeholder="Enter your reason..."></textarea>
            <button onclick="submitReason()">Submit</button>
        </div>
    </div>

    <!-- Proof Modal -->
    <div id="proofModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('proofModal')">&times;</span>
            <h2>Upload Proof</h2>
            <form id="proofForm" enctype="multipart/form-data">
                <input type="file" name="proof_file" id="proof_file" accept="image/*,video/*,application/pdf" required />
                <button type="submit">Upload & Complete</button>
            </form>
        </div>
    </div>
    <script>
        let currentTaskId = null;

        function openReasonModal(taskId) {
            currentTaskId = taskId;
            document.getElementById('reasonModal').style.display = 'flex';
        }

        function openProofModal(taskId) {
            currentTaskId = taskId;
            document.getElementById('proof_file').value = '';
            document.getElementById('proofModal').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function submitReason() {
            const reason = document.getElementById('reasonText').value.trim();
            if (!reason) {
                alert("Please provide a reason.");
                return;
            }
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_task_status.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = () => location.reload();
            xhr.send(`task_id=${currentTaskId}&status=not_completed&reason=${encodeURIComponent(reason)}`);
            closeModal('reasonModal');
        }

        document.getElementById('proofForm').onsubmit = function(e) {
            e.preventDefault();
            const file = document.getElementById('proof_file').files[0];
            if (!file) {
                alert("Upload a proof file.");
                return;
            }
            const formData = new FormData();
            formData.append("task_id", currentTaskId);
            formData.append("proof_file", file);

            fetch("upload_proof.php", {
                method: "POST",
                body: formData
            }).then(() => {
                closeModal('proofModal');
                location.reload();
            }).catch(() => alert("Upload failed."));

            localStorage.setItem(`task_${currentTaskId}_completed_at`, Date.now());
        };

        // ✅ Search + Filter by task name, description, reason, date & status
        document.addEventListener("DOMContentLoaded", () => {
            const searchInput = document.getElementById("taskSearchInput");
            const statusFilter = document.getElementById("statusFilter");

            const filterTasks = () => {
                const query = searchInput.value.toLowerCase().trim();
                const selectedStatus = statusFilter.value;

                document.querySelectorAll(".task-group").forEach(group => {
                    let groupHasVisible = false;

                    group.querySelectorAll(".activity-box").forEach(box => {
                        const taskName = box.querySelector(".task-name")?.innerText.toLowerCase() || "";
                        const taskDesc = box.querySelector(".description")?.innerText.toLowerCase() || "";
                        const date = box.querySelector(".date")?.innerText.toLowerCase() || "";
                        const reasonEl = box.querySelector(".reason");
                        const reason = reasonEl ? reasonEl.innerText.replace("Reason:", "").toLowerCase().trim() : "";

                        const boxStatus = box.classList.contains("completed-task") ?
                            "completed" :
                            box.classList.contains("not-completed-task") ?
                            "not_completed" :
                            "pending";

                        const matchesSearch =
                            taskName.includes(query) ||
                            taskDesc.includes(query) ||
                            reason.includes(query) ||
                            date.includes(query);

                        const matchesStatus =
                            selectedStatus === "" || selectedStatus === boxStatus;

                        const shouldShow = matchesSearch && matchesStatus;

                        box.style.display = shouldShow ? "block" : "none";
                        if (shouldShow) groupHasVisible = true;
                    });

                    group.style.display = groupHasVisible ? "block" : "none";
                });
            };

            searchInput.addEventListener("input", filterTasks);
            statusFilter.addEventListener("change", filterTasks);
        });

        //breadcrumbs
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