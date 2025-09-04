<?php
session_start();
include '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    echo "<h2>Not logged in.</h2>";
    exit();
}

$user_id = $_SESSION['id'];
$is_admin = false; // ✅ Fix: define variable before use

// Fetch user roles
$roles = [];
$stmt = $conn->prepare("
    SELECT roles.id, roles.name AS role_name, COALESCE(departments.name, 'No Department') AS dept_name
    FROM userroles
    INNER JOIN roles ON userroles.role_id = roles.id
    LEFT JOIN departments ON roles.department_id = departments.id
    WHERE userroles.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Role Resources List</title>
    <link rel="stylesheet" href="user_role_list.css">
</head>

<body>

    <header>
        <?php include '../navbar/navbar.php'; ?>
    </header>


    <div class="page-title" style="padding: 20px 5%;">
        <h1>Your Role Resources</h1>
    </div>

    <div class="breadcrumb">
        <ul id="breadcrumb-list"></ul>
    </div>

    <div class="feature-description">
        <p>To view the resources you will have to choose which one you have to access to. Each role has different and unique resources</p>
    </div>


    <div class="content-body" style="padding: 0 5%;">
        <?php if (empty($roles)): ?>
            <p>You have no assigned roles. Please contact the administrator.</p>
        <?php else: ?>
            <?php foreach ($roles as $row): ?>
                <div class="role-box">
                    <h3><?= htmlspecialchars($row['role_name']) ?></h3>
                    <p>Department: <?= htmlspecialchars($row['dept_name']) ?></p>
                    <form action="/pbra_website/resourcescenter/role_resources.php" method="get" style="margin-top: 10px;">
                        <input type="hidden" name="role_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <button type="submit" class="role-btn">🔍 View Resources</button>
                    </form>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        //Breadcrumb
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

</body>

</html>