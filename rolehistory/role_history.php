<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit();
}

include '../mypbra_connect.php';


$page_name = $page_name ?? 'Role History'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$user_id = $_SESSION['id'];
echo "<!-- Debug: Session User ID = $user_id -->";

// Check if user exists
$userCheck = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$userCheck->bind_param("i", $user_id);
$userCheck->execute();
$userResult = $userCheck->get_result();
$userData = $userResult->fetch_assoc();
echo "<!-- Debug: Logged in user is {$userData['full_name']} -->";

// Main role history query
$query = "SELECT r.name AS role_name, d.name AS dept_name, rh.assigned_at, rh.removed_at
FROM role_history rh
JOIN roles r ON rh.role_id = r.id
LEFT JOIN departments d ON r.department_id = d.id
WHERE rh.user_id = ?
ORDER BY rh.assigned_at DESC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
  die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Store result in an array
$rows = [];
while ($row = $result->fetch_assoc()) {
  $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="role_history.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <title>Role History</title>
</head>

<header>
  <?php include '../includes/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">
  <div class="container">
    <h2>Role History</h2>

    <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
    Add to Favorite
</button>

    <div class="breadcrumb">
      <ul id="breadcrumb-list"></ul>
    </div>

    <?php if (count($rows) === 0): ?>
      <p style="text-align:center; color:red;">No role history found for this user.</p>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Role Name</th>
          <th>Assigned At</th>
          <th>Removed At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
          <td><?= htmlspecialchars($row['role_name'] . ' (' . $row['dept_name'] . ')') ?></td>
            <td><?= htmlspecialchars($row['assigned_at']) ?></td>
            <td><?= $row['removed_at'] ? htmlspecialchars($row['removed_at']) : '-' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<script>
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