<?php
session_start();
include '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['id'];
$filter = $_GET['status'] ?? 'pending';

// Set the WHERE clause for status dynamically
$status_condition = "";
if ($filter === 'resolved') {
    $status_condition = "AND r.status = 'resolved'";
} elseif ($filter === 'all') {
    $status_condition = ""; // no filter
} else {
    $status_condition = "AND r.status = 'pending'";
}

$query = "SELECT r.*, u.full_name AS reporter_name, d.name AS department_name
          FROM reports r
          JOIN users u ON r.user_id = u.id
          JOIN departments d ON r.report_to = d.id
          WHERE r.report_to = ? $status_condition
          ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Reports</title>
  <link rel="stylesheet" href="report.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="content-body">
  <h1>📋 Manage Reports</h1>

  <div class="filters">
    <a href="?status=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending</a> |
    <a href="?status=resolved" class="<?= $filter === 'resolved' ? 'active' : '' ?>">Resolved</a> |
    <a href="?status=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <div class="report-list">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="report-item <?= $row['status'] ?>">
          <h3><?= htmlspecialchars($row['report_what'] === 'other' ? $row['other_report'] : $row['report_what']) ?></h3>
          <p><strong>From:</strong> <?= htmlspecialchars($row['reporter_name']) ?></p>
          <p><strong>Remarks:</strong> <?= nl2br(htmlspecialchars($row['remarks'])) ?></p>
          <?php if ($row['file_path']): ?>
            <p><strong>Attachment:</strong> 
              <a href="../uploads/reports/<?= htmlspecialchars($row['file_path']) ?>" target="_blank">View File</a>
            </p>
          <?php endif; ?>
          <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
          <p><strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($row['submitted_at'])) ?></p>

          <?php if ($row['status'] === 'pending'): ?>
            <form method="POST" action="update_report_status.php" class="inline-form">
              <input type="hidden" name="report_id" value="<?= $row['id'] ?>">
              <button type="submit" name="action" value="resolve" class="btn-success">✅ Mark as Resolved</button>
              <button type="submit" name="action" value="delete" class="btn-danger" onclick="return confirm('Are you sure to delete this report?')">🗑 Delete</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>No reports to show for this filter.</p>
  <?php endif; ?>
</div>

</body>
</html>
