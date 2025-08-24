<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit();
}

include '../mypbra_connect.php';


$page_name = $page_name ?? 'Roles'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

// Fetch user_type
$user_id = $_SESSION['id'];
$user_type = 'regular'; // default fallback

$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_type_result);
$stmt->fetch();
$user_type = $user_type_result ?? 'regular';
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="roles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <title>Roles</title>
</head>

<header>
  <?php include '../includes/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">
  <div class="page-title">
    <h1 style="font-size: 30px;">ROLES</h1>
    <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
      Add to Favorite
    </button>
  </div>

  <div class="breadcrumb">
    <ul id="breadcrumb-list"></ul>
  </div>

  <div class="content">
    <ul>
      <li>
        <div class="container" onclick="window.location.href='../myrole/myrole.php';" style="cursor: pointer;">
          <a href="#">
            <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
            <div class="text">
              <h1>My Role</h1>
              <p>This page enables you to monitor your recent activities and provides a brief overview of your role.</p>
            </div>
          </a>
        </div>
      </li>

      <?php if ($user_type !== 'regular'): ?>
        <li>
          <div class="container" onclick="window.location.href='../appoint_roles/approle.php';" style="cursor: pointer;">
            <a href="#">
              <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
              <div class="text">
                <h1>Appoint Roles</h1>
                <p>You can view available roles at Politeknik Brunei, see current assignments, search for candidates, and assign them to roles.</p>
              </div>
            </a>
          </div>
        </li>

        <li>
          <div class="container" onclick="window.location.href='../distributetask/distributetask.php';" style="cursor: pointer;">
            <a href="#">
              <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
              <div class="text">
                <h1>Distribute Task</h1>
                <p>Capable of assigning any task to others and automatically updating it in their calendars.</p>
              </div>
            </a>
          </div>
        </li>
      <?php endif; ?>

      <li>
        <div class="container" onclick="window.location.href='../rolehistory/role_history.php';" style="cursor: pointer;">
          <a href="#">
            <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
            <div class="text">
              <h1>Role History</h1>
              <p>Show all your past role in one place</p>
            </div>
          </a>
        </div>
      </li>

      <li>
        <div class="container" onclick="window.location.href='<?= $user_type === 'admin' ? '../resourcescenter/admin_role_list.php' : '../resourcescenter/role_resources.php' ?>';" style="cursor: pointer;">
          <a href="#">
            <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
            <div class="text">
              <h1>Role Resources</h1>
              <p><?= $user_type === 'admin'
                    ? 'View all roles and manage teaching resources by department.'
                    : 'Where you can find all your role training resources' ?>
              </p>
            </div>
          </a>
        </div>
      </li>
    </ul>
  </div>

  <script>
    // Fetch breadcrumbs from sessionStorage
    let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];

    // Get the current page name dynamically based on the current URL path
    let currentPageUrl = window.location.pathname;

    // Define page names based on the URL path
    let currentPageName = '';
    if (currentPageUrl.includes('homepage.php')) {
      currentPageName = 'Homepage';
    } else if (currentPageUrl.includes('calendar.php')) {
      currentPageName = 'Calendar';
    } else if (currentPageUrl.includes('distributetask.php')) {
      currentPageName = 'Distribute Task';
    } else if (currentPageUrl.includes('events.php')) {
      currentPageName = ' Events';
    } else if (currentPageUrl.includes('feedback.php')) {
      currentPageName = 'Feedback ';
    } else if (currentPageUrl.includes('mail.php')) {
      currentPageName = ' Mail';
    } else if (currentPageUrl.includes('myrole.php')) {
      currentPageName = 'My Role ';
    } else if (currentPageUrl.includes('profile.php')) {
      currentPageName = 'Profile ';
    } else if (currentPageUrl.includes('report.php')) {
      currentPageName = 'Report ';
    } else if (currentPageUrl.includes('roles.php')) {
      currentPageName = 'Roles';
    } else if (currentPageUrl.includes('schedule.php')) {
      currentPageName = 'Schedule';
    } else if (currentPageUrl.includes('staff.php')) {
      currentPageName = 'Staff';
    } else if (currentPageUrl.includes('usersupport.php')) {
      currentPageName = 'usersupport';
    } else {
      currentPageName = 'Unknown Page'; // Default if no match
    }

    // Check if this page is already in the breadcrumb trail
    let pageExists = breadcrumbs.some(breadcrumb => breadcrumb.url === currentPageUrl);

    // If the page isn't already in the breadcrumb trail, add it
    if (!pageExists) {
      breadcrumbs.push({
        name: currentPageName,
        url: currentPageUrl
      });
      sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
    }

    // Render the breadcrumb list
    let breadcrumbList = document.getElementById('breadcrumb-list');
    breadcrumbList.innerHTML = ''; // Clear any existing breadcrumbs

    // Loop through the breadcrumbs and render them with separators
    breadcrumbs.forEach((breadcrumb, index) => {
      let breadcrumbItem = document.createElement('li');
      let link = document.createElement('a');

      link.href = breadcrumb.url;
      link.textContent = breadcrumb.name;

      // When a breadcrumb is clicked, we go back to that page and remove all breadcrumbs after it
      link.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default navigation
        let clickedIndex = index;

        // Update the breadcrumb trail by trimming after the clicked breadcrumb
        breadcrumbs = breadcrumbs.slice(0, clickedIndex + 1);
        sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));

        // Reload the page to reflect the updated breadcrumbs
        window.location.href = breadcrumb.url;
      });

      breadcrumbItem.appendChild(link);
      breadcrumbList.appendChild(breadcrumbItem);

      // Only add the separator if it's not the last breadcrumb item
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