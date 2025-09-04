<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit();
}

include '../mypbra_connect.php'; // Database connection
if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['id'];
$users_result = $conn->query("SELECT id, full_name FROM users WHERE id != $user_id");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Distribute Task</title>
  <link rel="stylesheet" href="distributetask.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
</head>

<header>
  <?php include '../includes/navbar.php'; ?>
</header>

<body onload="fetchNotifications(); showSuccessMessage();">
  <div class="page-title">
    <h1 style="font-size: 30px;">DISTRIBUTE TASK</h1>
  </div>

  <div class="breadcrumb">
    <ul id="breadcrumb-list"></ul>
  </div>

  <div class="content">
    <form class="task-form" action="process_task.php" method="POST">
      <div class="form-group">
        <label for="person">Assign To:</label>
        <select name="person[]" id="person" multiple required>
          <?php while ($row = $users_result->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['full_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="task">What task?</label>
        <select id="task" name="task" required onchange="toggleCustomTask()">
          <option value="" disabled selected>Select</option>
          <optgroup label="📚 Class-Related Tasks">
            <option value="Lesson Planning">Lesson Planning</option>
            <option value="Teaching a Class">Teaching a Class</option>
            <option value="Substituting a Teacher">Substituting a Teacher</option>
            <option value="Student Attendance Checking">Student Attendance Checking</option>
            <option value="Student Counseling">Student Counseling</option>
          </optgroup>
          <optgroup label="📝 Exam-Related Tasks">
            <option value="Exam Question Preparation">Exam Question Preparation</option>
            <option value="Exam Supervision">Exam Supervision</option>
            <option value="Paper Marking">Paper Marking</option>
            <option value="Grade Submission">Grade Submission</option>
          </optgroup>
          <optgroup label="📅 Meeting & Administration">
            <option value="Faculty Meeting">Faculty Meeting</option>
            <option value="Department Meeting">Department Meeting</option>
            <option value="Administrative Paperwork">Administrative Paperwork</option>
            <option value="Performance Review">Performance Review</option>
          </optgroup>
          <optgroup label="📖 Student Activities & Events">
            <option value="Student Mentoring">Student Mentoring</option>
            <option value="Club or Society Management">Club or Society Management</option>
            <option value="School Event Coordination">School Event Coordination</option>
            <option value="Parent-Teacher Meeting">Parent-Teacher Meeting</option>
          </optgroup>
          <optgroup label="🔬 Research & Development">
            <option value="Research Paper Review">Research Paper Review</option>
            <option value="Syllabus Development">Syllabus Development</option>
            <option value="Course Material Preparation">Course Material Preparation</option>
          </optgroup>
          <option value="other">Other (Specify Below)</option>
        </select>
        <input type="text" id="custom-task" name="custom_task" placeholder="Enter custom task" style="display: none; margin-top: 10px;">
      </div>

      <div class="form-group">
        <label for="description">Task Description / Remarks:</label>
        <textarea id="description" name="description" rows="4" placeholder="Enter additional details about the task..."></textarea>
      </div>

      <div class="form-group">
        <label for="date">Date & Time:</label>
        <div class="datetime-inputs">
          <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
          <input type="time" id="time" name="time" required>
        </div>
      </div>

      <button type="submit" class="submit-button">
        <span class="material-icons">check</span>
      </button>

      <?php
      if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['assigned_tasks'])) {
        echo "<div id='successPopup' class='popup-overlay'>
                <div class='popup-content'>
                  <h2>Task Assigned Successfully! ✅</h2>
                  <p>The following tasks have been assigned:</p>
                  <ul>";

        foreach ($_SESSION['assigned_tasks'] as $tasks) {
          $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
          $stmt->bind_param("i", $tasks['person_id']);
          $stmt->execute();
          $stmt->bind_result($assigned_person);
          $stmt->fetch();
          $stmt->close();

          echo "<li><strong>{$tasks['task_name']}</strong> assigned to <strong>{$assigned_person}</strong> on <strong>{$tasks['task_date']} at {$tasks['task_time']}</strong></li>";
        }

        echo "</ul><button onclick='closePopup()'>OK</button></div></div>";
        unset($_SESSION['assigned_tasks']);
      }
      ?>
    </form>
  </div>

  <script>
    function toggleCustomTask() {
      const taskDropdown = document.getElementById("task");
      const customTaskInput = document.getElementById("custom-task");
      if (taskDropdown.value === "other") {
        customTaskInput.style.display = "block";
        customTaskInput.setAttribute("required", "true");
      } else {
        customTaskInput.style.display = "none";
        customTaskInput.removeAttribute("required");
      }
    }

    $(document).ready(() => {
      $('#person').select2({
        placeholder: "Select one or more people",
        allowClear: true
      });
    });

    function showSuccessMessage() {
      if (new URLSearchParams(window.location.search).has('success')) {
        document.getElementById("successPopup").style.display = "flex"; // ✅
        window.history.replaceState(null, null, window.location.pathname);
      }
    }

    function closePopup() {
      document.getElementById("successPopup").style.display = "none";
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
  </script>

</body>

</html>