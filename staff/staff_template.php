<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php'; // Include database connection

$departmentName = $_GET['department'] ?? null;
if (!$departmentName) {
    die("Department not specified.");
}

$sql = "
    SELECT 
        u.id,
        u.profile_pic,
        u.full_name,
        u.email,
        u.office,
        COALESCE(
            GROUP_CONCAT(CONCAT(r.name, ' (', d.name, ')') SEPARATOR ', '),
            'No role assigned'
        ) AS role_with_department
    FROM users u
    LEFT JOIN userroles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN departments d ON r.department_id = d.id
    WHERE d.name = ?
    GROUP BY u.id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $departmentName);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

$hodUsers = [];
$ahosAcademicUsers = [];
$ahosAdminUsers = [];
$groupedByRole = [];

foreach ($users as $user) {
    $roleText = strtolower($user['role_with_department']);

    // Match Head of School or Head of Department (but exclude assistants)
    if (
        (str_contains($roleText, 'head of school') || str_contains($roleText, 'head of department')) &&
        !str_contains($roleText, 'assistant')
    ) {
        $hodUsers[] = $user;
    }

    // Assistant of Head of School Academic
    elseif (
        str_contains($roleText, 'assistant') &&
        str_contains($roleText, 'head of school') &&
        str_contains($roleText, 'academic')
    ) {
        $ahosAcademicUsers[] = $user;
    }

    // Assistant of Head of School Administration
    elseif (
        str_contains($roleText, 'assistant') &&
        str_contains($roleText, 'head of school') &&
        str_contains($roleText, 'administration')
    ) {
        $ahosAdminUsers[] = $user;
    }

    // Others
    else {
        preg_match('/^(.*?)\s*\(/', $user['role_with_department'], $match);
        $roleTitle = $match[1] ?? $user['role_with_department'];
        $groupedByRole[$roleTitle][] = $user;
    }
}



$stmt->close();
$conn->close();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff List</title>
    <link rel="stylesheet" href="staff.css">

</head>

<body>
    <?php include '../navbar/navbar.php'; ?>

    <div class="page-title">
        <h1><?php echo htmlspecialchars(strtoupper($departmentName)); ?></h1>
    </div>

    <div class="breadcrumb">
        <ul id="breadcrumb-list"></ul>
    </div>


    <div class="staff-container">

        <!-- 🥇 Head of Department -->
        <?php if (!empty($hodUsers)): ?>
            <h2 class="role-header">Head of Department</h2>
            <?php foreach ($hodUsers as $user): ?>
                <?php
                $profilePicPath = $user['profile_pic'];
                if (!str_starts_with($profilePicPath, "../")) {
                    $profilePicPath = "../" . $profilePicPath;
                }
                if (!file_exists($profilePicPath) || empty($user['profile_pic'])) {
                    $profilePicPath = "../profile/images/default-profile.jpg";
                }
                ?>
                <div class="staff-card" onclick="location.href='../profile/profile.php?id=<?php echo htmlspecialchars($user['id']); ?>'">
                    <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role_with_department']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Office:</strong> <?php echo htmlspecialchars($user['office'] ?? 'Not Set'); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- 🥈 Assistant of Head of School Academic -->
        <?php if (!empty($ahosAcademicUsers)): ?>
            <h2 class="role-header">Assistant of Head of School Academic</h2>
            <?php foreach ($ahosAcademicUsers as $user): ?>
                <?php
                $profilePicPath = $user['profile_pic'];
                if (!str_starts_with($profilePicPath, "../")) {
                    $profilePicPath = "../" . $profilePicPath;
                }
                if (!file_exists($profilePicPath) || empty($user['profile_pic'])) {
                    $profilePicPath = "../profile/images/default-profile.jpg";
                }
                ?>
                <div class="staff-card" onclick="location.href='../profile/profile.php?id=<?php echo htmlspecialchars($user['id']); ?>'">
                    <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role_with_department']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Office:</strong> <?php echo htmlspecialchars($user['office'] ?? 'Not Set'); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- 🥉 Assistant of Head of School Administration -->
        <?php if (!empty($ahosAdminUsers)): ?>
            <h2 class="role-header">Assistant of Head of School Administration</h2>
            <?php foreach ($ahosAdminUsers as $user): ?>
                <?php
                $profilePicPath = $user['profile_pic'];
                if (!str_starts_with($profilePicPath, "../")) {
                    $profilePicPath = "../" . $profilePicPath;
                }
                if (!file_exists($profilePicPath) || empty($user['profile_pic'])) {
                    $profilePicPath = "../profile/images/default-profile.jpg";
                }
                ?>
                <div class="staff-card" onclick="location.href='../profile/profile.php?id=<?php echo htmlspecialchars($user['id']); ?>'">
                    <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role_with_department']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Office:</strong> <?php echo htmlspecialchars($user['office'] ?? 'Not Set'); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- 👥 All Other Roles -->
        <?php foreach ($groupedByRole as $role => $usersInRole): ?>
            <h2 class="role-header"><?php echo htmlspecialchars($role); ?></h2>
            <?php foreach ($usersInRole as $user): ?>
                <?php
                $profilePicPath = $user['profile_pic'];
                if (!str_starts_with($profilePicPath, "../")) {
                    $profilePicPath = "../" . $profilePicPath;
                }
                if (!file_exists($profilePicPath) || empty($user['profile_pic'])) {
                    $profilePicPath = "../profile/images/default-profile.jpg";
                }
                ?>
                <div class="staff-card" onclick="location.href='../profile/profile.php?id=<?php echo htmlspecialchars($user['id']); ?>'">
                    <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role_with_department']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Office:</strong> <?php echo htmlspecialchars($user['office'] ?? 'Not Set'); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

    </div>




    <script>
        function openProfile(userId) {
            window.location.href = "../profile/profile.php?id=" + userId;
        }

        let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
        let currentPageUrl = window.location.pathname + window.location.search;
        let currentPageName = new URLSearchParams(window.location.search).get('department') || 'Staff';



        let pageExists = breadcrumbs.some(breadcrumb => breadcrumb.url === currentPageUrl);
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
            let breadcrumbItem = document.createElement('li');
            let link = document.createElement('a');
            link.href = breadcrumb.url;
            link.textContent = breadcrumb.name;

            link.addEventListener('click', function(event) {
                event.preventDefault();
                breadcrumbs = breadcrumbs.slice(0, index + 1);
                sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
                window.location.href = breadcrumb.url;
            });

            breadcrumbItem.appendChild(link);
            breadcrumbList.appendChild(breadcrumbItem);

            if (index < breadcrumbs.length - 1) {
                let separator = document.createElement('span');
                separator.textContent = ' > ';
                breadcrumbList.appendChild(separator);
            }
        });
    </script>

</body>

</html>