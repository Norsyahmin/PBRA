<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../mypbra_connect.php';

$is_admin = false;
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_type);
    if ($stmt->fetch() && $user_type === 'admin') {
        $is_admin = true;
    }
    $stmt->close();
}

// Handle announcement upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin && isset($_POST['submit_announcement'])) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/announcements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
        $fullPath = $uploadDir . $imageName;
        move_uploaded_file($imageTmp, $fullPath);
        $imagePath = 'uploads/announcements/' . $imageName; // ✅ just this

    }

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO announcement (title, content, image_path, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $title, $content, $imagePath);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin && isset($_POST['delete_id'])) {
    $idToDelete = $_POST['delete_id'];
    $conn->query("DELETE FROM announcement WHERE id = " . intval($idToDelete));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$announcements = [];
$result = $conn->query("SELECT * FROM announcement ORDER BY created_at DESC LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}
?>

<link rel="stylesheet" href="../includes/announcement.css">

<div class="announcement-carousel">
  <div class="announcement-header">
    <h2>📢 Latest Announcements</h2>
    <?php if ($is_admin): ?>
      <button class="add-announcement-toggle" onclick="openModal()">+ Add Announcement</button>
    <?php endif; ?>
  </div>

  <div class="carousel-wrapper">
    <button class="carousel-btn left" onclick="moveSlide(-1)"></button>
    <div class="carousel-track">
      <?php foreach ($announcements as $a): ?>
        <div class="announcement-slide">
          <h3><?= htmlspecialchars($a['title']) ?></h3>
          <?php if (!empty($a['image_path'])): ?>
            <img src="../<?= htmlspecialchars($a['image_path']) ?>" alt="announcement image">
          <?php endif; ?>
          <div class="desc"><?= html_entity_decode($a['content']) ?></div>
          <?php if ($is_admin): ?>
            <form method="POST" class="delete-form" data-id="<?= $a['id'] ?>">
              <button type="button" class="delete-btn" onclick="openDeleteModal(this)">🗑 Delete</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-btn right" onclick="moveSlide(1)"></button>
  </div>
  <div class="carousel-dots">
  <?php foreach ($announcements as $index => $a): ?>
    <span class="dot <?= $index === 0 ? 'active' : '' ?>" onclick="jumpToSlide(<?= $index ?>)"></span>
  <?php endforeach; ?>
</div>

</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
  <div class="delete-modal-content">
    <span class="close-btn" onclick="closeDeleteModal()">&times;</span>
    <h3>Are you sure you want to delete this announcement?</h3>
    <form method="POST" id="confirmDeleteForm">
      <input type="hidden" name="delete_id" id="delete_id">
      <div class="modal-button-group">
    <button type="submit" class="confirm-delete-btn">Yes, Delete</button>
    <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
  </div>
    </form>
  </div>
</div>

<!-- Add Announcement Modal -->
<?php if ($is_admin): ?>
<div id="announcementModal" class="modal-overlay" style="display: none;">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h3>Add New Announcement</h3>
    <form method="POST" enctype="multipart/form-data" class="announcement-form" onsubmit="prepareContent()">
      <input type="text" name="title" placeholder="Title" required><br>

      <!-- Formatting toolbar -->
      <div style="margin-bottom: 10px; text-align: center;">
        <button type="button" onclick="formatText('bold')"><b>B</b></button>
        <button type="button" onclick="formatText('italic')"><i>I</i></button>
        <button type="button" onclick="formatText('underline')"><u>U</u></button>
      </div>

      <!-- Rich text editor -->
      <div id="richContent" contenteditable="true" class="rich-text-box"
           style="border: 1px solid #ccc; padding: 12px; min-height: 100px; border-radius: 6px;">
      </div>

      <!-- Hidden field to store HTML -->
      <input type="hidden" name="content" id="hiddenContent" />

      <input type="file" name="image" accept="image/*"><br>
      <button type="submit" name="submit_announcement">Post</button>
    </form>
  </div>
</div>
<?php endif; ?>


<script>
let currentIndex = 0;
function moveSlide(dir) {
  const slides = document.querySelectorAll('.announcement-slide');
  if (slides.length === 0) return;
  currentIndex += dir;
  if (currentIndex < 0) currentIndex = slides.length - 1;
  if (currentIndex >= slides.length) currentIndex = 0;
  const track = document.querySelector('.carousel-track');
  track.style.transform = `translateX(-${currentIndex * 100}%)`;
}

function openModal() {
  document.getElementById('announcementModal').style.display = 'block';
}
function closeModal() {
  document.getElementById('announcementModal').style.display = 'none';
}

function openDeleteModal(btn) {
  const form = btn.closest('.delete-form');
  const id = form.getAttribute('data-id');
  document.getElementById('delete_id').value = id;
  document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
  document.getElementById('deleteModal').style.display = 'none';
}

window.onclick = function(event) {
  const delModal = document.getElementById('deleteModal');
  const addModal = document.getElementById('announcementModal');
  if (event.target === delModal) closeDeleteModal();
  if (event.target === addModal) closeModal();
}

function formatText(command) {
  document.execCommand(command, false, null);
}

function prepareContent() {
  const richContent = document.getElementById('richContent').innerHTML;
  document.getElementById('hiddenContent').value = richContent;
}
//dot logic
function updateDots() {
  const dots = document.querySelectorAll('.carousel-dots .dot');
  dots.forEach((dot, i) => {
    dot.classList.toggle('active', i === currentIndex);
  });
}

function moveSlide(dir) {
  const slides = document.querySelectorAll('.announcement-slide');
  if (slides.length === 0) return;
  currentIndex += dir;
  if (currentIndex < 0) currentIndex = slides.length - 1;
  if (currentIndex >= slides.length) currentIndex = 0;
  document.querySelector('.carousel-track').style.transform = `translateX(-${currentIndex * 100}%)`;
  updateDots();
}

function jumpToSlide(index) {
  currentIndex = index;
  document.querySelector('.carousel-track').style.transform = `translateX(-${currentIndex * 100}%)`;
  updateDots();
}

</script>
