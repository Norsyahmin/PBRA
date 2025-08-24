<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit();
}

// Include the database connection
include '../mypbra_connect.php';

$page_name = $page_name ?? 'Schedule';
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$user_id = $_SESSION['id'];

// Fetch image path from the database
$sql = "SELECT image_path FROM schedule WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Set image path or default image if none exists
$image_path = $row ? $row['image_path'] : 'default.png';

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schedule</title>
  <link rel="stylesheet" href="schedule.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body onload="fetchNotifications()">

  <?php include '../includes/navbar.php'; ?>

  <div class="page-title">
    <h1 style="font-size: 30px;">SCHEDULE</h1>
  </div>

  <div class="breadcrumb">
    <ul id="breadcrumb-list">
      <!-- Breadcrumbs will be dynamically inserted here -->
    </ul>
  </div>

  <div class="image-container">
    <div class="image-frame">
      <img src="<?php echo htmlspecialchars($image_path); ?>" alt="User Schedule">
    </div>
  </div>

  <div class="btn-section">
    <button class="delete-btn" onclick="document.getElementById('deleteModal').style.display='flex'">
      <i class="fa fa-trash" aria-hidden="true"></i> Delete Schedule
    </button>
    <button class="upload-btn" onclick="document.getElementById('uploadModal').style.display='flex'">
      <i class="fa fa-upload" aria-hidden="true"></i> Upload New Schedule
    </button>
  </div>

  <!-- Upload Modal -->
  <div id="uploadModal" class="modal">
    <div class="modal-content">
      <button class="close-modal" onclick="document.getElementById('uploadModal').style.display='none'">&times;</button>
      <h3>Upload Your Schedule</h3>
      <form action="upload_schedule_image.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="schedule_image" accept="image/*" required><br><br>
        <button type="submit" class="upload-btn">Submit</button>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <h3>Delete Schedule</h3>
      <div class="delete-warning">
        <p>Are you sure you want to delete your schedule?</p>
      </div>
      <div class="modal-buttons">
        <button type="button" class="cancel-btn" onclick="document.getElementById('deleteModal').style.display='none'">
          <i class="fa fa-times" aria-hidden="true"></i> Cancel
        </button>
        <button type="button" class="confirm-delete-btn" onclick="confirmDelete()">
          <i class="fa fa-trash" aria-hidden="true"></i> Delete Schedule
        </button>
      </div>
    </div>
  </div>
  
  <footer>
    <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
  </footer>

  <script>
    // Delete schedule function
    function confirmDelete() {
      // Close the modal
      document.getElementById('deleteModal').style.display='none';
      // Redirect to delete script
      window.location.href = 'delete_schedule.php';
    }

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
  </script>

</body>

</html>