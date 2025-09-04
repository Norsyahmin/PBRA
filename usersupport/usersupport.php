<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="usersupport.css" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <title>User Support</title>
</head>

<header>
  <?php include '../navbar/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">
  <div class="page-title">
    <h1>USER SUPPORT</h1>
  </div>

  <div class="breadcrumb">
    <ul id="breadcrumb-list">
      <!-- Breadcrumbs will be dynamically inserted here -->
    </ul>
  </div>

  <div class="usersupport-container">
    <div class="chat-box">
      <h2><i class="fas fa-comments"></i> Live Chat</h2>
      <div class="chat-messages" id="chat-messages">
        <div class="message bot-message">Hello! How may I assist you today?</div>
      </div>
      <form id="chat-form" class="chat-input">
        <input type="file" id="file-upload" hidden />
        <button type="button" class="attach-btn" onclick="document.getElementById('file-upload').click()">
          <i class="fas fa-paperclip"></i>
        </button>
        <input type="text" id="user-input" placeholder="Type your message here..." autocomplete="off" />
        <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
      </form>
    </div>

    <div class="right-side">
      <div class="box history-box">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> History</h2>
        <ul class="chat-history-list">
          <!-- Populated by JS -->
        </ul>
      </div>


      <div class="box contact-box">
        <h2><i class="fas fa-envelope"></i> Contact Us</h2>
        <p><i class="fas fa-at"></i> Email: <a href="mailto:pbrausersupp@gmail.com">pbrausersupp@gmail.com</a></p>
        <p><i class="fas fa-phone"></i> 891 5691 | 723 9834</p>
      </div>
    </div>
  </div>

  <script>
    window.currentUserId = <?= json_encode($_SESSION['id']) ?>;
  </script>
  <script defer src="usersupport.js"></script>
</body>

</html>