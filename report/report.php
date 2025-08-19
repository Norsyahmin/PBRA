<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit();
}

include '../mypbra_connect.php';
$user_id = $_SESSION['id'];

$page_name = $page_name ?? 'Report'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

// Fetch all other users for Select2
$stmt_users = $conn->prepare("SELECT id, full_name FROM users WHERE id != ?");
$stmt_users->bind_param("i", $user_id);
$stmt_users->execute();
$users_result = $stmt_users->get_result();

// Fetch reports sent to this user
$stmt = $conn->prepare("SELECT r.*, u.full_name 
    FROM reports r 
    JOIN users u ON r.user_id = u.id 
    WHERE FIND_IN_SET(?, r.report_to) > 0 
    ORDER BY 
      CASE 
        WHEN r.status = 'pending' THEN 0
        WHEN r.status = 'resolved' THEN 1
        WHEN r.status = 'ignored' THEN 2
        ELSE 3
      END,
      r.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$inbox_reports = $stmt->get_result();

$showPopup = false;
if (isset($_SESSION['report_success']) && $_SESSION['report_success']) {
  $showPopup = true;
  unset($_SESSION['report_success']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Report</title>
  <link rel="stylesheet" href="report.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>



<body onload="fetchNotifications()">

  <header><?php include '../navbar/navbar.php'; ?></header>

  <div class="page-title">
    <h1>REPORT</h1>
    <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
      Add to Favorite
    </button>
  </div>
  <div class="breadcrumb">
    <ul id="breadcrumb-list"></ul>
  </div>

  <div class="report-container" style="display: flex; gap: 20px; padding: 0 5%;">
    <div id="left-panel" style="flex: 1;">
      <div id="create-form">
        <form class="report-form" action="process_report.php" method="POST" enctype="multipart/form-data">
          <label for="report-to">Who are you reporting to?</label>
          <select id="report-to" name="report_to[]" multiple class="select2" required>
            <?php while ($row = $users_result->fetch_assoc()): ?>
              <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['full_name']) ?></option>
            <?php endwhile; ?>
          </select>

          <label for="report-what">What are you reporting?</label>
          <select id="report-what" name="report_what" required onchange="toggleOtherInput()">
            <option value="" disabled selected>Select</option>
            <optgroup label="📌 Facility & Maintenance Issues">
              <option value="broken-furniture">Broken Furniture</option>
              <option value="ac-issues">AC Issues</option>
              <option value="plumbing">Plumbing</option>
              <option value="electrical">Electrical</option>
              <option value="broken-windows">Windows/Doors</option>
              <option value="wifi">Internet</option>
              <option value="equipment">Equipment</option>
            </optgroup>
            <optgroup label="🛑 Safety & Security">
              <option value="unauthorized-person">Unauthorized Person</option>
              <option value="fire-hazard">Fire Hazard</option>
              <option value="slippery-floor">Slippery Floor</option>
              <option value="student-fight">Fights</option>
              <option value="vandalism">Vandalism</option>
              <option value="hazardous-materials">Hazards</option>
              <option value="medical-emergency">Medical</option>
            </optgroup>
            <optgroup label="🚸 Welfare">
              <option value="bullying">Bullying</option>
              <option value="truancy">Truancy</option>
              <option value="mental-health">Mental Health</option>
              <option value="misconduct">Misconduct</option>
              <option value="overcrowded-class">Overcrowded</option>
              <option value="staff-shortage">Staff Shortage</option>
            </optgroup>
            <optgroup label="📅 Administrative">
              <option value="scheduling">Scheduling</option>
              <option value="communication">Communication</option>
              <option value="payroll">Payroll</option>
              <option value="school-policy">Policy</option>
              <option value="transport">Transport</option>
            </optgroup>
            <option value="other">Other</option>
          </select>

          <input type="text" id="other-report-input" name="other_report" placeholder="Please specify..." style="display: none;">
          <label for="remarks">Remarks</label>
          <textarea id="remarks" name="remarks" rows="4"></textarea>
          <label for="report-file">Upload File</label>
          <input type="file" id="report-file" name="report_file" accept="image/*, .pdf, .doc, .docx">
          <button type="submit" class="submit-btn">Submit</button>
        </form>
      </div>

      <div id="view-report" style="display: none;" class="report-form">
        <button onclick="goBackToForm()" class="back-btn"><i class="fas fa-arrow-left"></i> Back</button>
        <h3>📄 Report Details</h3>
        <div id="report-details"></div>
        <div id="resolveActions" style="display: flex; gap: 10px;">
          <form action="resolve_report.php" method="POST">
            <input type="hidden" name="report_id" id="reportIdInput">
            <input type="hidden" name="status" value="resolved">
            <button type="submit" class="resolve-btn">Mark as Resolved</button>
          </form>
          <form action="resolve_report.php" method="POST">
            <input type="hidden" name="report_id" id="ignoreReportId">
            <input type="hidden" name="status" value="ignored">
            <button type="submit" class="ignore-btn">Ignore</button>
          </form>
        </div>
      </div>
    </div>

    <div class="content2" style="flex: 1;">
      <input type="text" id="report-search" placeholder="Search reports..." style="width: 100%; margin-bottom: 20px;">
      <h3 style="color: #174080;">📥 Reports Sent to You</h3>
      <?php if ($inbox_reports->num_rows > 0): ?>
        <?php while ($report = $inbox_reports->fetch_assoc()): ?>
          <?php
          $bgColor = '#f7faff';
          if ($report['status'] === 'resolved') $bgColor = '#d1f3e0';
          elseif ($report['status'] === 'ignored') $bgColor = '#ffe4e4';
          ?>
          <div style="background-color: <?= $bgColor ?>; padding: 10px 15px; margin-bottom: 15px; border-left: 4px solid #174080; border-radius: 6px;">
            <?php if ($report['status'] === 'resolved'): ?>
              <p style="color: green; font-weight: bold;">✅ Resolved</p>
            <?php elseif ($report['status'] === 'ignored'): ?>
              <p style="color: red; font-weight: bold;">❌ Ignored</p>
            <?php endif; ?>
            <p><strong>From:</strong> <?= htmlspecialchars($report['full_name']) ?></p>
            <p><strong>Issue:</strong> <?= htmlspecialchars($report['report_what']) ?></p>
            <p><strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($report['created_at'])) ?></p>
            <button onclick='viewReport(<?= json_encode($report) ?>)' style="background-color: #2a5ba0; color: white; border: none; padding: 6px 12px;">View Report</button>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="color: #888;">No reports assigned to you.</p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($showPopup): ?>
    <div id="successPopup" class="popup-container" style="display: flex;">
      <div class="popup-content">
        <p>Report successfully sent!</p>
        <button onclick="closePopup()">OK</button>
      </div>
    </div>
  <?php endif; ?>

  <script>
    $(document).ready(function() {
      $('#report-to').select2({
        placeholder: "Select one or more people",
        width: '100%'
      });
    });

    function toggleOtherInput() {
      const select = document.getElementById("report-what");
      const otherInput = document.getElementById("other-report-input");
      otherInput.style.display = select.value === "other" ? "block" : "none";
    }

    function closePopup() {
      document.getElementById("successPopup").style.display = "none";
    }

    function goBackToForm() {
      document.getElementById("view-report").style.display = "none";
      document.getElementById("create-form").style.display = "block";
    }

    function viewReport(report) {
      document.getElementById("create-form").style.display = "none";
      document.getElementById("view-report").style.display = "block";

      const details = `
    <p><strong>From:</strong> ${report.full_name}</p>
    <p><strong>Issue:</strong> ${report.report_what}</p>
    ${report.other_report ? `<p><strong>Other:</strong> ${report.other_report}</p>` : ''}
    <p><strong>Remarks:</strong> ${report.remarks}</p>
    ${report.file_path ? `<p><strong>Attachment:</strong> <a href="../${report.file_path}" target="_blank">View File</a></p>` : ''}
    <p><strong>Submitted At:</strong> ${new Date(report.created_at).toLocaleString()}</p>`;

      document.getElementById("report-details").innerHTML = details;
      document.getElementById("reportIdInput").value = report.id;
      document.getElementById("ignoreReportId").value = report.id;

      const resolveActions = document.getElementById("resolveActions");
      resolveActions.style.display = (report.status === 'resolved' || report.status === 'ignored') ? 'none' : 'flex';
    }

    document.getElementById("report-search").addEventListener("input", function() {
      const query = this.value.toLowerCase();
      const cards = document.querySelectorAll(".content2 > div");
      cards.forEach(card => card.style.display = card.textContent.toLowerCase().includes(query) ? "block" : "none");
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