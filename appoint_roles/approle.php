<?php
include '../mypbra_connect.php';

$page_name = $page_name ?? 'Appoint Role Department'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

// Fetch roles with department names
$sql = "
    SELECT r.id, r.name AS role_name, d.name AS dept_name
    FROM roles r
    LEFT JOIN departments d ON r.department_id = d.id
    ORDER BY d.name, r.name
";
$result = $conn->query($sql);

// Group roles by department
$roles_by_dept = [];

while ($row = $result->fetch_assoc()) {
    $dept = $row['dept_name'] ?? 'No Department';
    $roles_by_dept[$dept][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appoint Role Department</title>
    <link rel="stylesheet" href="approle.css">
</head>

<body onload="fetchNotifications(); showSuccessMessage();">
<header>
  <?php include '../includes/navbar.php'; ?>
</header>

<div class="page-title">
    <h1>Choose Department</h1>  <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
    Add to Favorite
</button>

</div>

<div class="breadcrumb">
    <ul id="breadcrumb-list"></ul>
  </div>

<div class="feature-description" >
    <h3>How does this work?</h3>
    <p>
        Below is a list of all departments available in Politeknik Brunei. You can appoint roles for staff members by following these steps:
    </p>
    <ol style="padding-left: 20px; margin-top: 10px;">
        <li>Click on a department name to reveal the roles available under that department. Then, click on a role to proceed.</li>
        <li>You will be shown a requirements page that outlines the qualifications needed to hold that role, along with a list of current role holders.</li>
        <li>To appoint a candidate, simply click the search button to browse and select from available staff.</li>
    </ol>
</div>


<?php foreach ($roles_by_dept as $dept => $roles): ?>
    <div class="department-section">
        <div class="department-header collapsed" onclick="toggleDropdown(this)">
            <?= htmlspecialchars($dept) ?>
            <i class="fas fa-chevron-down"></i>
        </div>
        <ul class="role-list">
            <?php foreach ($roles as $role): ?>
                <li>
                    <a href="appoint.php?role_id=<?= $role['id'] ?>">
                        <div class="container">
                            <div class="folder-icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div class="text">
                                <h1><?= htmlspecialchars($role['role_name']) ?></h1>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>

<!-- Include Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
        function toggleDropdown(header) {
        const list = header.nextElementSibling;
        const isCollapsed = header.classList.contains('collapsed');

        // Toggle icon and visibility
        header.classList.toggle('collapsed');
        list.style.display = isCollapsed ? 'block' : 'none';
    }
// Breadcrumbs
let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
let currentPageUrl = window.location.pathname;

// 🧠 Instead of hardcoding, get <title> automatically
let currentPageName = document.title.trim(); 

let pageExists = breadcrumbs.some(b => b.url === currentPageUrl);

if (!pageExists) {
  breadcrumbs.push({ name: currentPageName, url: currentPageUrl });
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
//favorites

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

//favorites
function toggleFavorite() {
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');

    const index = favorites.findIndex(fav => fav.pageName === pageName);

    if (index === -1) {
        // Not favorited yet, add it
        favorites.push({ pageName: pageName, pageUrl: pageUrl });
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
