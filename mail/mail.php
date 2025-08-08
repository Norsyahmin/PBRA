<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit();
}

$page_name = $page_name ?? 'Calendar'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

include '../mypbra_connect.php'; // Database connection
if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['id'];

// Check if favorite
$sql_fav = "SELECT * FROM user_favorites WHERE user_id = ? AND page_name = 'Mail'";
$stmt_fav = $conn->prepare($sql_fav);
$stmt_fav->bind_param("i", $user_id);
$stmt_fav->execute();
$result_fav = $stmt_fav->get_result();
$is_favorite = $result_fav->num_rows > 0;
$stmt_fav->close();

// Get unread count
$unread_count = 0;
$unread_stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE recipient_id = ? AND is_read = FALSE");
$unread_stmt->bind_param("i", $user_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
if ($unread_row = $unread_result->fetch_assoc()) {
    $unread_count = $unread_row['count'];
}
$unread_stmt->close();

// Get all users for recipient dropdown
$users_result = $conn->query("SELECT id, full_name FROM users WHERE id != $user_id");

// Get messages
$folder = isset($_GET['folder']) ? $_GET['folder'] : 'inbox';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if ($folder == 'inbox') {
    $messages_stmt = $conn->prepare("SELECT m.*, u.full_name as sender_name 
                                   FROM messages m 
                                   JOIN users u ON m.sender_id = u.id 
                                   WHERE m.recipient_id = ? 
                                   ORDER BY m.created_at DESC 
                                   LIMIT ? OFFSET ?");
    $messages_stmt->bind_param("iii", $user_id, $limit, $offset);
} elseif ($folder == 'sent') {
    $messages_stmt = $conn->prepare("SELECT m.*, u.full_name as recipient_name 
                                   FROM messages m 
                                   JOIN users u ON m.recipient_id = u.id 
                                   WHERE m.sender_id = ? 
                                   ORDER BY m.created_at DESC 
                                   LIMIT ? OFFSET ?");
    $messages_stmt->bind_param("iii", $user_id, $limit, $offset);
}
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$messages = [];
while ($row = $messages_result->fetch_assoc()) {
    $messages[] = $row;
}
$messages_stmt->close();

// Get total count for pagination
if ($folder == 'inbox') {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM messages WHERE recipient_id = ?");
} else {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM messages WHERE sender_id = ?");
}
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_messages = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_messages / $limit);
$count_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mail</title>
  <link rel="stylesheet" href="mail.css">

  <!-- Include jQuery -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

  <!-- Include Select2 CSS & JS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<header>
<?php include '../includes/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">

  <div class="page-title">
    <h1 style="font-size: 30px;">MAIL <?php if ($unread_count > 0) echo "<span class='unread-badge'>$unread_count</span>"; ?></h1>
    <button id="favorite-btn" class="favorite-btn" onclick="toggleFavorite()">
      <i class="fa <?php echo $is_favorite ? 'fa-heart' : 'fa-heart-o'; ?>" id="fav-icon"></i>
    </button>
  </div>

  <div class="breadcrumb">
    <ul id="breadcrumb-list">
    </ul>
  </div>

  <div class="mail-container">
    <div class="mail-sidebar">
      <button class="compose-btn" onclick="showComposeModal()">
        <i class="material-icons">edit</i> Compose
      </button>
      
      <ul class="folder-list">
        <li class="<?= $folder == 'inbox' ? 'active' : '' ?>">
          <a href="mail.php?folder=inbox">
            <i class="material-icons">inbox</i> Inbox
            <?php if ($unread_count > 0): ?>
              <span class="unread-count"><?= $unread_count ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="<?= $folder == 'sent' ? 'active' : '' ?>">
          <a href="mail.php?folder=sent"><i class="material-icons">send</i> Sent</a>
        </li>
      </ul>
    </div>

    <div class="mail-content">
      <div class="mail-toolbar">
        <div class="toolbar-left">
          <button class="toolbar-btn" onclick="refreshMessages()">
            <i class="material-icons">refresh</i>
          </button>
        </div>
        <div class="toolbar-right">
          <span class="pagination-info">
            Showing <?= ($offset + 1) ?>-<?= min($offset + $limit, $total_messages) ?> of <?= $total_messages ?>
          </span>
          <button class="toolbar-btn" <?= $page <= 1 ? 'disabled' : '' ?> onclick="goToPage(<?= $page - 1 ?>)">
            <i class="material-icons">chevron_left</i>
          </button>
          <button class="toolbar-btn" <?= $page >= $total_pages ? 'disabled' : '' ?> onclick="goToPage(<?= $page + 1 ?>)">
            <i class="material-icons">chevron_right</i>
          </button>
        </div>
      </div>

      <div class="mail-list">
        <?php if (empty($messages)): ?>
          <div class="empty-message">
            <i class="material-icons">email</i>
            <p>No messages found in this folder</p>
          </div>
        <?php else: ?>
          <?php foreach ($messages as $message): ?>
            <div class="mail-item <?= $folder == 'inbox' && !$message['is_read'] ? 'unread' : '' ?>" 
                 onclick="viewMessage(<?= $message['id'] ?>, '<?= $folder ?>')">
              <div class="mail-item-checkbox">
                <input type="checkbox" onclick="event.stopPropagation()">
              </div>
              <div class="mail-item-sender">
                <?= $folder == 'inbox' ? htmlspecialchars($message['sender_name']) : htmlspecialchars($message['recipient_name']) ?>
              </div>
              <div class="mail-item-content">
                <div class="mail-item-subject">
                  <?= htmlspecialchars($message['subject']) ?>
                  <?php if ($folder == 'inbox' && !$message['is_read']): ?>
                    <span class="unread-dot"></span>
                  <?php endif; ?>
                </div>
                <div class="mail-item-preview">
                  <?= substr(strip_tags($message['body']), 0, 100) ?>...
                </div>
              </div>
              <div class="mail-item-time">
                <?= date("M j, Y g:i A", strtotime($message['created_at'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Compose Modal -->
  <div id="composeModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>New Message</h2>
        <span class="close-btn" onclick="closeComposeModal()">&times;</span>
      </div>
      <form id="composeForm" action="process_mail.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="recipient">To:</label>
          <select name="recipient[]" id="recipient" multiple required>
            <?php while ($row = $users_result->fetch_assoc()) { ?>
              <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['full_name']) ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label for="subject">Subject:</label>
          <input type="text" id="subject" name="subject" required>
        </div>
        <div class="form-group">
          <label for="body">Message:</label>
          <textarea id="body" name="body" rows="10" required></textarea>
        </div>
        <div class="form-group">
          <label for="attachments">Attachments:</label>
          <input type="file" id="attachments" name="attachments[]" multiple>
        </div>
        <div class="form-actions">
          <button type="button" class="cancel-btn" onclick="closeComposeModal()">Cancel</button>
          <button type="submit" class="send-btn">
            <i class="material-icons">send</i> Send
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- View Message Modal -->
  <div id="viewMessageModal" class="modal">
    <div class="modal-content message-view">
      <div class="modal-header">
        <h2 id="messageSubject"></h2>
        <span class="close-btn" onclick="closeViewModal()">&times;</span>
      </div>
      <div class="message-header">
        <div class="message-sender" id="messageSender"></div>
        <div class="message-recipient" id="messageRecipient"></div>
        <div class="message-date" id="messageDate"></div>
      </div>
      <div class="message-body" id="messageBody"></div>
      <div class="message-attachments" id="messageAttachments"></div>
      <div class="message-actions">
        <button class="reply-btn" onclick="replyToMessage()">
          <i class="material-icons">reply</i> Reply
        </button>
        <button class="forward-btn" onclick="forwardMessage()">
          <i class="material-icons">forward</i> Forward
        </button>
        <button class="delete-btn" onclick="deleteMessage()">
          <i class="material-icons">delete</i> Delete
        </button>
      </div>
    </div>
  </div>

  <script>
    function showComposeModal() {
      document.getElementById('composeModal').style.display = 'block';
      document.getElementById('recipient').focus();
    }

    function closeComposeModal() {
      document.getElementById('composeModal').style.display = 'none';
      document.getElementById('composeForm').reset();
    }

    function closeViewModal() {
      document.getElementById('viewMessageModal').style.display = 'none';
    }

    function refreshMessages() {
      window.location.reload();
    }

    function goToPage(page) {
      const url = new URL(window.location.href);
      url.searchParams.set('page', page);
      window.location.href = url.toString();
    }

    function viewMessage(messageId, folder) {
      // Mark as read if it's an inbox message
      if (folder === 'inbox') {
        $.ajax({
          url: 'mark_as_read.php',
          type: 'POST',
          data: { message_id: messageId },
          success: function() {
            // Update UI to show message as read
            const messageElement = document.querySelector(`.mail-item[onclick*="${messageId}"]`);
            if (messageElement) {
              messageElement.classList.remove('unread');
              const unreadDot = messageElement.querySelector('.unread-dot');
              if (unreadDot) unreadDot.remove();
              
              // Update unread count in sidebar
              const unreadCountElement = document.querySelector('.unread-count');
              if (unreadCountElement) {
                const currentCount = parseInt(unreadCountElement.textContent);
                if (currentCount > 1) {
                  unreadCountElement.textContent = currentCount - 1;
                } else {
                  unreadCountElement.remove();
                }
              }
              
              // Update unread badge in title
              const unreadBadge = document.querySelector('.unread-badge');
              if (unreadBadge) {
                const currentBadgeCount = parseInt(unreadBadge.textContent);
                if (currentBadgeCount > 1) {
                  unreadBadge.textContent = currentBadgeCount - 1;
                } else {
                  unreadBadge.remove();
                }
              }
            }
          }
        });
      }
      
      // Fetch and display message details
      $.ajax({
        url: 'get_message.php',
        type: 'GET',
        data: { id: messageId },
        success: function(response) {
          const message = JSON.parse(response);
          document.getElementById('messageSubject').textContent = message.subject;
          
          if (folder === 'inbox') {
            document.getElementById('messageSender').innerHTML = `<strong>From:</strong> ${message.sender_name}`;
          } else {
            document.getElementById('messageSender').innerHTML = `<strong>To:</strong> ${message.recipient_name}`;
          }
          
          document.getElementById('messageDate').textContent = new Date(message.created_at).toLocaleString();
          document.getElementById('messageBody').innerHTML = message.body.replace(/\n/g, '<br>');
          
          // Display attachments if any
          const attachmentsContainer = document.getElementById('messageAttachments');
          attachmentsContainer.innerHTML = '';
          if (message.attachments && message.attachments.length > 0) {
            attachmentsContainer.innerHTML = '<strong>Attachments:</strong><ul>';
            message.attachments.forEach(attachment => {
              attachmentsContainer.innerHTML += `
                <li>
                  <a href="${attachment.file_path}" target="_blank">${attachment.file_name}</a>
                  (${formatFileSize(attachment.file_size)})
                </li>`;
            });
            attachmentsContainer.innerHTML += '</ul>';
          }
          
          document.getElementById('viewMessageModal').style.display = 'block';
        }
      });
    }

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function replyToMessage() {
      const subject = document.getElementById('messageSubject').textContent;
      const senderId = document.getElementById('messageSender').textContent.split(':')[1].trim();
      
      closeViewModal();
      showComposeModal();
      
      document.getElementById('subject').value = 'Re: ' + subject;
      $('#recipient').val([senderId]).trigger('change');
      document.getElementById('body').focus();
    }

    function forwardMessage() {
      const subject = document.getElementById('messageSubject').textContent;
      const body = document.getElementById('messageBody').textContent;
      
      closeViewModal();
      showComposeModal();
      
      document.getElementById('subject').value = 'Fwd: ' + subject;
      document.getElementById('body').value = '\n\n---------- Forwarded message ----------\n' + body;
      document.getElementById('body').focus();
    }

    function deleteMessage() {
      const messageId = document.querySelector('#viewMessageModal').getAttribute('data-message-id');
      if (confirm('Are you sure you want to delete this message?')) {
        $.ajax({
          url: 'delete_message.php',
          type: 'POST',
          data: { id: messageId },
          success: function() {
            closeViewModal();
            refreshMessages();
          }
        });
      }
    }

    $(document).ready(function() {
      $('#recipient').select2({
        placeholder: "Select recipients",
        allowClear: true
      });
      
      // Close modals when clicking outside
      window.onclick = function(event) {
        if (event.target.className === 'modal') {
          event.target.style.display = 'none';
        }
      };
    });

    function toggleFavorite() {
      var btn = document.getElementById("favorite-btn");
      var icon = document.getElementById("fav-icon");

      if (icon.classList.contains("fa-heart")) {
        icon.classList.remove("fa-heart");
        icon.classList.add("fa-heart-o");
        btn.classList.remove("favorited");
        var isFavorite = 0;
      } else {
        icon.classList.remove("fa-heart-o");
        icon.classList.add("fa-heart");
        btn.classList.add("favorited");
        var isFavorite = 1;
      }

      $.ajax({
        url: 'update_favorite.php',
        type: 'POST',
        data: {
          favorite: isFavorite,
          page_name: 'Mail',
          user_id: <?php echo $_SESSION['id']; ?>
        },
        success: function(response) {
          console.log("Favorite status updated successfully");
        },
        error: function(xhr, status, error) {
          console.error("Error updating favorite status:", error);
        }
      });
    }

    // Breadcrumb functionality (same as in your original code)
    let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
    let currentPageUrl = window.location.pathname;
    let currentPageName = 'Mail';

    let pageExists = breadcrumbs.some(breadcrumb => breadcrumb.url === currentPageUrl);

    if (!pageExists) {
      breadcrumbs.push({ name: currentPageName, url: currentPageUrl });
      sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
    }

    let breadcrumbList = document.getElementById('breadcrumb-list');
    breadcrumbList.innerHTML = '';

    breadcrumbs.forEach((breadcrumb, index) => {
      let breadcrumbItem = document.createElement('li');
      let link = document.createElement('a');

      link.href = breadcrumb.url;
      link.textContent = breadcrumb.name;

      link.addEventListener('click', function(event) {
        event.preventDefault();
        let clickedIndex = index;

        breadcrumbs = breadcrumbs.slice(0, clickedIndex + 1);
        sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));

        window.location.href = breadcrumb.url;
      });

      breadcrumbItem.appendChild(link);
      breadcrumbList.appendChild(breadcrumbItem);

      if (index < breadcrumbs.length - 1) {
        let separator = document.createElement('span');
        separator.textContent = ' > ';
        breadcrumbList.appendChild(separator);
      }
    });
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

  </script>
</body>
</html>