<?php
session_start();
include '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    echo "<h2>Not logged in.</h2>";
    exit();
}
$page_name = $page_name ?? 'Role Resources Center'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];
$user_id = $_SESSION['id'];
$role_id = $_GET['role_id'] ?? $_POST['role_id'] ?? null;

// 🔁 Redirect if no role selected
if (!$role_id) {
    header("Location: ../resourcescenter/user_role_list.php");

    exit();
}

$role_id = intval($role_id);

// 🛡 Check if user is admin
$is_admin = false;
$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_type);
if ($stmt->fetch() && $user_type === 'admin') {
    $is_admin = true;
}
$stmt->close();

// 🛡 If not admin, validate user-role access
if (!$is_admin) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM userroles WHERE user_id = ? AND role_id = ?");
    $stmt->bind_param("ii", $user_id, $role_id);
    $stmt->execute();
    $stmt->bind_result($has_access);
    $stmt->fetch();
    $stmt->close();

    if ($has_access == 0) {
        echo "<h2 style='color:red;'>Access Denied: You do not have permission to view this role's resources.</h2>";
        echo "<p><a href='../resourcescenter/user_role_list.php'>Return to Role Selection</a></p>";
        exit();
    }
}

// ✅ Role info for header
$role_info = $conn->query("
    SELECT roles.name AS role_name, departments.name AS dept_name
    FROM roles 
    JOIN departments ON roles.department_id = departments.id 
    WHERE roles.id = $role_id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Role Resources</title>
    <link rel="stylesheet" href="role_resources.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill-table-ui@1.2.2/dist/index.css">

</head>
<body>

<header>
    <?php include '../includes/navbar.php'; ?>
</header>

<div class="page-title" style="padding: 10px 5% 0;">
    <h1>📚 Role Resources Center</h1>
    <?php
$role_info = $conn->query("SELECT roles.name AS role_name, departments.name AS dept_name 
                           FROM roles 
                           JOIN departments ON roles.department_id = departments.id 
                           WHERE roles.id = $role_id")->fetch_assoc();
?>
<h2 class="role-subtitle">
    📌 <?= htmlspecialchars($role_info['role_name']) ?> 
    (<?= htmlspecialchars($role_info['dept_name']) ?>)
</h2>
<button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
    Add to Favorite
</button>

</div>

<div class="breadcrumb" style="padding: 10px 5% 0;">
    <ul id="breadcrumb-list"></ul>
</div>

<div class="content-body">
    <input type="text" id="searchInput" placeholder="Search resources..." />

    <?php if ($is_admin): ?>
        <button class="toggle-btn" id="toggleCustomize">🔨 Customize Page</button>
    <?php endif; ?>

    <div id="resourcesWrapper">
        <?php
$section_query = $conn->query("SELECT * FROM resource_sections WHERE role_id = $role_id ORDER BY created_at ASC");

        while ($section = $section_query->fetch_assoc()):
            $files = $conn->query("SELECT * FROM resource_files WHERE section_id = {$section['id']} ORDER BY id DESC");
        ?>
        <div class="section">
            <h2><?= htmlspecialchars($section['title']) ?></h2>

            <?php if ($is_admin): ?>
                <div class="customize-tools">
  <form method="POST" action="delete_section.php" class="delete-section-form">
      <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
      <button type="button" class="btn-danger open-delete-modal">🗑 Delete Section</button>
  </form>
</div>



                </div>
            <?php endif; ?>

            <?php while ($file = $files->fetch_assoc()):
                $icon = 'fa-file';
                $badge = '';
                $now = new DateTime();
                $uploaded = new DateTime($file['uploaded_at']);
                $recent = $now->diff($uploaded)->days < 7;

                if (!empty($file['is_link']) && $file['is_link']) {
                    $icon = 'fa-link';
                    $badge = '<span class="badge badge-link">LINK</span>';
                } else {
                    $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                    $icons = [
                        'pdf' => 'fa-file-pdf',
                        'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
                        'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
                        'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
                        'zip' => 'fa-file-archive', 'rar' => 'fa-file-archive',
                        'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'png' => 'fa-file-image', 'gif' => 'fa-file-image'
                    ];
                    $icon = $icons[$ext] ?? 'fa-file';
                    $badge = '<span class="badge badge-file">FILE</span>';
                }

            ?>
            <div class="file-item" data-title="<?= strtolower($file['title']) ?>" data-desc="<?= strtolower($file['description']) ?>">
                <i class="fas <?= $icon ?>"></i>
                <span class="file-title"><?= htmlspecialchars($file['title']) ?></span>
                <?= $badge ?>
                <div><?= $file['description'] ?></div>


                <a href="<?= $file['is_link'] ? htmlspecialchars($file['link_url']) : '../uploads/resources/' . htmlspecialchars($file['file_path']) ?>" target="_blank">
                    📂 <?= $file['is_link'] ? 'Open Link' : 'View / Download' ?>
                </a>

                <?php if ($is_admin): ?>
                    <div class="customize-tools">
                        <form method="POST" action="delete_resources.php">
                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                            <button type="submit" class="btn-danger">🗑 Remove</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>

            <?php if ($is_admin): ?>
    <?php $editorId = "quillEditor_" . $section['id']; ?>
    <div class="customize-tools">
    <form method="POST" action="add_resources.php" enctype="multipart/form-data" onsubmit="return prepareEditor('<?= $editorId ?>', <?= $section['id'] ?>);">
    <input type="hidden" name="role_id" value="<?= $role_id ?>">
    <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
    <input type="text" name="title" placeholder="File Title" required>

    <label>Content:</label>
    <div id="<?= $editorId ?>" class="quill-editor" style="height: 200px;"></div>
    <textarea name="description" id="hiddenDescription_<?= $section['id'] ?>" style="display: none;"></textarea>

    <input type="file" name="file">
    <button type="submit">➕ Add Resource</button>
</form>


    </div>
<?php endif; ?>

        </div>
        <?php endwhile; ?>
        <?php if ($is_admin): ?>
  <div class="customize-tools" style="margin-top:30px;">
  <form method="POST" action="add_section.php?role_id=<?= $role_id ?>">
      <input type="text" name="section_title" placeholder="New Section Title" required>
      <button type="submit">➕ Add Section</button>
    </form>
  </div>
<?php endif; ?>
    </div>
</div>

<!-- Modal HTML -->
<div id="deleteModal" class="modal-overlay">
  <div class="modal-box">
    <h3>Are you sure you want to delete this section and all its files?</h3>
    <div class="modal-actions">
      <button onclick="confirmDeleteModal()">✅ Yes, Delete</button>
      <button onclick="closeDeleteModal()">❌ Cancel</button>
    </div>
  </div>
</div>


<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill-table-ui@1.2.2/dist/index.min.js"></script>

<script>

let formToDelete = null;

// Use event delegation to support multiple delete buttons
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('open-delete-modal')) {
        formToDelete = e.target.closest('form');
        document.getElementById('deleteModal').style.display = 'flex';
    }
});

function closeDeleteModal() {
    formToDelete = null;
    document.getElementById('deleteModal').style.display = 'none';
}

function confirmDeleteModal() {
    if (formToDelete) {
        formToDelete.submit();
    }
}

  
// Breadcrumb system
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


// Initialize all quill editors dynamically
const quillInstances = {};
document.querySelectorAll('.quill-editor').forEach(editor => {
    const id = editor.getAttribute('id');
    quillInstances[id] = new Quill(`#${id}`, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ color: [] }, { background: [] }],
                ['link'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean']
            ]
        }
    });
});

// Prepare editor content before submitting form
function prepareEditor(editorId, sectionId) {
    const html = quillInstances[editorId].root.innerHTML;
    document.getElementById(`hiddenDescription_${sectionId}`).value = html;
    return true; // allow the form to submit
}

const toggleBtn = document.getElementById('toggleCustomize');
const wrapper = document.getElementById('resourcesWrapper');

// Load previous customize state on page load
if (localStorage.getItem('customizeActive') === 'true') {
    wrapper.classList.add('customize-active');
    if (toggleBtn) toggleBtn.textContent = "✅ Done Customizing";
}

// Toggle button listener
toggleBtn?.addEventListener('click', function () {
    wrapper.classList.toggle('customize-active');
    const isActive = wrapper.classList.contains('customize-active');
    localStorage.setItem('customizeActive', isActive); // Save state
    this.textContent = isActive ? "✅ Done Customizing" : "🔨 Customize Page";
});


document.getElementById('searchInput')?.addEventListener('input', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.file-item').forEach(item => {
        const title = item.dataset.title;
        const desc = item.dataset.desc;
        item.style.display = (title.includes(term) || desc.includes(term)) ? '' : 'none';
    });
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