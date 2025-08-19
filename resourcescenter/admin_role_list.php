<?php
session_start();
include '../mypbra_connect.php';

// Restrict to admins
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$page_name = $page_name ?? 'Manage Role Resources'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$is_admin = false;
$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($user_type_result);
if ($stmt->fetch() && $user_type_result === 'admin') {
    $is_admin = true;
}
$stmt->close();

if (!$is_admin) {
    echo "<h2>Access Denied. Admins only.</h2>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Role Resources List</title>
    <link rel="stylesheet" href="admin_role_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <?php include '../navbar/navbar.php'; ?>

    <div class="page-title" style="padding: 10px 5% 0;">
        <h1>Manage Role Resources</h1>
        <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
            Add to Favorite
        </button>

    </div>


    <div class="breadcrumb">
        <ul id="breadcrumb-list"></ul>
    </div>

    <p class="page-description">
        This page allows administrators to manage teaching resources for each role within their respective departments.
        Use the search bar to find a specific role. Click a role to view and manage its assigned resources.
    </p>

    <input type="text" id="roleSearchInput" placeholder="Search by role or department name...">

    <div class="role-list">
        <?php
        $roles = $conn->query("
    SELECT roles.id, roles.name AS role_name, departments.name AS department_name
    FROM roles
    JOIN departments ON roles.department_id = departments.id
    ORDER BY departments.name ASC, roles.name ASC
");

        while ($role = $roles->fetch_assoc()):
        ?>
            <div class="role-item"
                data-role="<?= strtolower($role['role_name']) ?>"
                data-dept="<?= strtolower($role['department_name']) ?>"
                onclick="window.location.href='role_resources.php?role_id=<?= $role['id'] ?>'">
                <h3><?= htmlspecialchars($role['role_name']) ?></h3>
                <p><?= htmlspecialchars($role['department_name']) ?></p>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        document.getElementById('roleSearchInput')?.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('.role-item').forEach(item => {
                const role = item.dataset.role;
                const dept = item.dataset.dept;
                item.style.display = (role.includes(term) || dept.includes(term)) ? '' : 'none';
            });
        });

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