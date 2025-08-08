<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail</title>
    
    <link rel="stylesheet" href="mail.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* (Paste your full CSS here — from your version) */
        /* ✅ You already posted it — no change needed. */
    </style>
</head>
<body>
    <div class="page-title">
        <h1>Email Client</h1>
    </div>

    <div class="container">
        <div class="sidebar">
            <button class="btn" id="compose-btn">Compose</button>
            <div class="folder active" data-folder="inbox">Inbox</div>
            <div class="folder" data-folder="sent">Sent</div>
            <div class="folder" data-folder="drafts">Drafts</div>
            <div class="folder" data-folder="trash">Trash</div>
        </div>
        <div class="main-content">
            <div class="email-list" id="email-list"></div>
            <div class="email-view" id="email-view">
                <div class="email-header">
                    <h2 id="email-subject">Email Subject</h2>
                    <div id="email-from">From: </div>
                    <div id="email-date">Date: </div>
                </div>
                <div class="email-actions">
                    <button class="action-btn" id="reply-btn">Reply</button>
                    <button class="action-btn" id="delete-btn">Delete</button>
                </div>
                <div id="email-body">Hello there?</div>
            </div>
            <div class="compose-form" id="compose-form">
                <h2>Compose Email</h2>
                <form id="compose-email-form">
                    <input type="email" id="compose-to" placeholder="To" required>
                    <input type="text" id="compose-subject" placeholder="Subject" required>
                    <textarea id="compose-body" placeholder="Body" required></textarea>
                    <button type="submit" class="btn">Send</button>
                    <button type="button" class="btn" id="discard-draft-btn">Discard Draft</button>
                </form>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="popup-overlay">
        <div class="popup-content">
            <h2>Success!</h2>
            <p id="popup-message">Your action was successful.</p>
            <button class="btn" id="close-popup-btn">Close</button>
        </div>
    </div>

    <!-- ✅ UPDATED SCRIPT -->
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const emailListContainer = document.getElementById('email-list');
    const emailView = document.getElementById('email-view');
    const composeForm = document.getElementById('compose-form');
    const popupOverlay = document.getElementById('popup-overlay');
    const closePopupBtn = document.getElementById('close-popup-btn');
    const composeBtn = document.getElementById('compose-btn');
    const discardDraftBtn = document.getElementById('discard-draft-btn');
    const composeEmailForm = document.getElementById('compose-email-form');

    let currentFolder = 'inbox';
    let selectedEmail = null;

    function updateFolderView() {
        fetch(`mail_backend.php?folder=${currentFolder}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderEmails(data.mails);
                }
            });
    }

    function renderEmails(emails) {
        emailListContainer.innerHTML = '';
        if (emails.length === 0) {
            emailListContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">No emails here</div>';
        }

        emails.forEach(email => {
            const emailItem = document.createElement('div');
            emailItem.classList.add('email-item');
            if (email.unread == 1) emailItem.classList.add('unread');
            emailItem.dataset.id = email.id;
            emailItem.innerHTML = `
                <strong>${email.subject}</strong><br>
                <span>${currentFolder === 'sent' ? 'To: ' + email.receiver : 'From: ' + email.sender}</span><br>
                <small>${email.created_at.split(' ')[0]}</small>
            `;
            emailItem.addEventListener('click', () => viewEmail(email));
            emailListContainer.appendChild(emailItem);
        });

        emailView.style.display = 'none';
        composeForm.style.display = 'none';
    }

    function viewEmail(email) {
        document.getElementById('email-subject').textContent = email.subject;
        document.getElementById('email-from').textContent = currentFolder === 'sent' ? `To: ${email.receiver}` : `From: ${email.sender}`;
        document.getElementById('email-date').textContent = `Date: ${email.created_at}`;
        document.getElementById('email-body').textContent = email.body;

        selectedEmail = email;
        emailView.style.display = 'block';
        composeForm.style.display = 'none';
    }

    function toggleComposeForm() {
        composeForm.style.display = 'block';
        emailView.style.display = 'none';
        composeEmailForm.reset();
    }

    function showPopup(message) {
        document.getElementById('popup-message').textContent = message;
        popupOverlay.style.display = 'flex';
    }

    composeBtn.addEventListener('click', toggleComposeForm);

    discardDraftBtn.addEventListener('click', () => {
        const subject = document.getElementById('compose-subject').value || '(No Subject)';
        const body = document.getElementById('compose-body').value || '';

        fetch('mail_backend.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=draft&subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPopup(data.message);
                composeForm.style.display = 'none';
                updateFolderView();
            }
        });
    });

    closePopupBtn.addEventListener('click', () => {
        popupOverlay.style.display = 'none';
    });

    composeEmailForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const to = document.getElementById('compose-to').value;
        const subject = document.getElementById('compose-subject').value;
        const body = document.getElementById('compose-body').value;

        fetch('mail_backend.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=send&to=${encodeURIComponent(to)}&subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPopup(data.message);
                composeForm.style.display = 'none';
                updateFolderView();
            }
        });
    });

    document.getElementById('reply-btn').addEventListener('click', () => {
        if (!selectedEmail) return;
        toggleComposeForm();
        document.getElementById('compose-to').value = selectedEmail.sender;
        document.getElementById('compose-subject').value = selectedEmail.subject.startsWith('Re:') ? selectedEmail.subject : `Re: ${selectedEmail.subject}`;
        document.getElementById('compose-body').value = `\n\n--- Original Message ---\n${selectedEmail.body}`;
    });

    document.getElementById('delete-btn').addEventListener('click', () => {
        if (!selectedEmail) return;

        fetch('mail_backend.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=delete&id=${selectedEmail.id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPopup(data.message);
                updateFolderView();
            }
        });
    });

    document.querySelectorAll('.folder').forEach(folderBtn => {
        folderBtn.addEventListener('click', () => {
            document.querySelectorAll('.folder').forEach(f => f.classList.remove('active'));
            folderBtn.classList.add('active');
            currentFolder = folderBtn.dataset.folder;
            updateFolderView();
        });
    });

    updateFolderView();
});
</script>

</body>
</html>
