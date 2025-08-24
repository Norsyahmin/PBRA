<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php'; // ✅ Add this line to connect to DB

$departments = [];

$sql = "
    SELECT d.name AS department, u.full_name, r.name AS role
    FROM users u
    LEFT JOIN userroles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN departments d ON r.department_id = d.id
    WHERE d.name IS NOT NULL
";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $dept = $row['department'];
    $info = $row['full_name'] . ', ' . $row['role'];
    $departments[$dept][] = $info;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="staffsch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Staff Department</title>
</head>

<header>
    <?php include '../includes/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">

    <div class="page-title">
        <h1 style="font-size: 30px;">STAFF</h1>
    </div>

    <div class="breadcrumb">
        <ul id="breadcrumb-list">
            <!-- Breadcrumbs will be dynamically inserted here -->
        </ul>
    </div>


    <!--Content-->
    <div class="content">
        <div style="padding: 0 5% 10px;">
            <div style="padding: 0 5% 10px; text-align: center;">
                <input type="text" id="folderSearchInput" placeholder="Search by department, role or staff..."
                    style="width: 60%; padding: 10px 14px; font-size: 15px; border: 1px solid #ccc; border-radius: 8px;">
                <small style="display: block; margin-top: 6px; color: #555; font-style: italic;">
                    🔍 You can search by your name or role — the matching department folder will be shown automatically.
                </small>
            </div>



            <ul id="folderList">
                <?php
                $departments = [];

                $sql = "
    SELECT d.name AS department, u.full_name, r.name AS role
    FROM users u
    LEFT JOIN userroles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN departments d ON r.department_id = d.id
    WHERE d.name IS NOT NULL
";

                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $dept = $row['department'];
                    $info = $row['full_name'] . ', ' . $row['role'];
                    $departments[$dept][] = $info;
                }

                foreach ($departments as $deptName => $staffList):
                    $searchData = htmlspecialchars(implode(', ', $staffList));
                    $deptUrl = urlencode($deptName);
                ?>
                    <li>
                        <div class="container" onclick="window.location.href='../staff/staff_template.php?department=<?php echo $deptUrl; ?>';" style="cursor: pointer;">
                            <a href="#">
                                <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
                                <div class="text">
                                    <h1><?php echo htmlspecialchars($deptName); ?></h1>
                                    <span class="hidden-info" style="display: none;"><?php echo $searchData; ?></span>
                                </div>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>


        </div>


        <script>
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
        </script>

        <script>
            const folderSearchInput = document.getElementById('folderSearchInput');
            const folders = document.querySelectorAll('#folderList li');

            folderSearchInput.addEventListener('input', function() {
                const keyword = this.value.toLowerCase();

                folders.forEach(folder => {
                    const visibleText = folder.innerText.toLowerCase();
                    const hiddenInfo = folder.querySelector('.hidden-info')?.textContent.toLowerCase() || '';

                    const match = visibleText.includes(keyword) || hiddenInfo.includes(keyword);
                    folder.style.display = match ? '' : 'none';
                });
            });
        </script>

</body>

</html>