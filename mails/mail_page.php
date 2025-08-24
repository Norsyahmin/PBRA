<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}
include '../mypbra_connect.php';
$user_id = $_SESSION['id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mail</title>
    <link rel="stylesheet" href="mail.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<header><?php include '../includes/navbar.php'; ?></header>

< onload="fetchNotifications()">

    <div class="page-title">
        <h1>Mail</h1>
    </div>

    <div class="breadcrumb">
        <ul id="breadcrumb-list"></ul>
    </div>

    <div class="mail-container">
        <div class="mail-sidebar">
            <button id="compose-btn" class="btn">+ Compose</button>
            <div class="folder active" data-folder="inbox">All Mail</div>
            <div class="folder" data-folder="drafts">Drafts</div>
            <div class="folder" data-folder="trash">Trash</div>
        </div>

        <div class="main-content">
            <div class="email-list" id="email-list"></div>

            <div id="email-view" class="email-view" style="display:none;">
                <div style="text-align:right; margin-bottom:10px;">
                    <button class="btn" onclick="closeEmailView()">Close</button>
                </div>

                <div class="email-header">
                    <h2 id="email-subject"></h2>
                    <div id="email-from"></div>
                    <div id="email-date"></div>
                </div>
                <div id="email-thread" style="margin-top:15px;"></div>

                <div id="reply-form" style="display:none; margin-top:20px;">
                    <h3>Reply</h3>
                    <form id="reply-email-form">
                        <textarea id="reply-body" name="body" required placeholder="Write your reply..." style="width:100%; height:120px; margin-bottom:10px;"></textarea>
                        <input type="hidden" id="reply-to" name="to">
                        <input type="hidden" id="reply-subject" name="subject">
                        <input type="hidden" id="reply-thread-id" name="thread_id">
                        <button type="submit" class="btn">Send Reply</button>
                    </form>

                </div>

                <div style="margin-top:15px;">
                    <button class="btn" id="reply-btn">Reply</button>
                    <button class="btn" id="delete-btn">Delete</button>
                </div>
            </div>

            <div id="compose-form" style="display:none; margin-top:20px;">
                <h2>Compose New Email</h2>
                <form id="compose-email-form">
                    <input type="email" id="compose-to" name="to" placeholder="To" style="width:100%; padding:10px; margin-bottom:10px;">
                    <input type="text" id="compose-subject" name="subject" required placeholder="Subject" style="width:100%; padding:10px; margin-bottom:10px;">
                    <textarea id="compose-body" name="body" required placeholder="Body" style="width:100%; height:150px; padding:10px; margin-bottom:10px;"></textarea>
                    <input type="hidden" id="draft-id" name="draft_id" value="">
                    <button type="submit" class="btn">Send</button>
                    <button type="button" class="btn" id="discard-btn" style="margin-left:10px;">Draft</button>
                    <button type="button" class="btn" id="delete-draft-btn" style="margin-left:10px; display:none; background-color: #dc3545;">🗑️ Delete</button>

                </form>

            </div>
        </div>
    </div>
    <!-- Modal Structure -->
    <div id="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:9999;">
        <div style="background:white; padding:20px; border-radius:8px; width:90%; max-width:400px; text-align:center; position:relative;">
            <div id="modal-message" style="margin-bottom:20px; font-size:16px;"></div>
            <div id="modal-buttons">
                <button id="modal-ok" class="btn">OK</button>
                <button id="modal-yes" class="btn" style="margin-right:10px; display:none;">Yes</button>
                <button id="modal-no" class="btn" style="display:none;">No</button>
            </div>
        </div>
    </div>


    <script>
        const emailListContainer = document.getElementById('email-list');
        const composeForm = document.getElementById('compose-form');
        const emailView = document.getElementById('email-view');
        const replyForm = document.getElementById('reply-form');
        let currentFolder = 'inbox';
        let currentOpenedEmailId = null;

        function fetchData(url, method = 'GET', body = null) {
            return fetch(url, {
                method,
                body
            }).then(res => res.json());
        }

        function loadEmails(folder) {
            currentFolder = folder;
            fetchData(`mail.php?folder=${folder}`).then(data => {
                emailListContainer.innerHTML = '';
                if (folder === 'trash') {
                    const delAllBtn = document.createElement('button');
                    delAllBtn.textContent = 'Delete All Permanently';
                    delAllBtn.className = 'btn';
                    delAllBtn.onclick = deleteAllTrash;
                    emailListContainer.appendChild(delAllBtn);
                }
                if (data.length === 0) {
                    emailListContainer.innerHTML += '<p style="text-align:center;">No messages found.</p>';
                    return;
                }
                data.forEach(email => {
                    const div = document.createElement('div');
                    div.className = 'email-item';
                    div.innerHTML = `<strong>${email.subject || '(No Subject)'}</strong><br><small>${email.created_at}</small>`;
                    div.onclick = () => viewEmail(email);
                    emailListContainer.appendChild(div);
                });
            });
        }

        function viewEmail(email) {
            emailListContainer.style.display = 'none';
            emailView.style.display = 'block';
            composeForm.style.display = 'none';
            replyForm.style.display = 'none';

            if (currentFolder === 'drafts') {
                document.getElementById('compose-to').value = email.recipient_email || '';
                if (!email.recipient_email) {
                    document.getElementById('compose-to').value = '';
                }

                document.getElementById('compose-subject').value = email.subject || '';
                document.getElementById('compose-body').value = email.body || '';
                document.getElementById('draft-id').value = email.id; // <-- Set draft id
                document.getElementById('delete-draft-btn').style.display = 'inline-block';

                composeForm.style.display = 'block';
                emailView.style.display = 'none';
                return; // important, to stop and not load thread view
            }


            document.getElementById('email-subject').textContent = email.subject || '(No Subject)';
            currentOpenedEmailId = email.id;

            fetchData(`mail.php?view_thread=${email.id}`).then(data => {
                const thread = document.getElementById('email-thread');
                thread.innerHTML = '';
                let shown = new Set();
                if (data.length) {
                    document.getElementById('email-from').innerHTML = `<b>From:</b> ${data[0].sender_name}`;
                    document.getElementById('email-date').textContent = `Date: ${data[0].created_at}`;
                }
                data.forEach(msg => {
                    if (!shown.has(msg.id)) {
                        const msgContainer = document.createElement('div');
                        msgContainer.style.display = 'flex';
                        msgContainer.style.justifyContent = (msg.sender_id == <?php echo json_encode($user_id); ?>) ? 'flex-end' : 'flex-start';
                        msgContainer.style.marginBottom = '10px';

                        const msgBubble = document.createElement('div');
                        msgBubble.className = 'msg-bubble';
                        msgBubble.style.background = (msg.sender_id == <?php echo json_encode($user_id); ?>) ? '#d2e3fc' : '#f0f0f0';
                        msgBubble.innerHTML = `
                    <div style="font-size:14px; font-weight:bold;">${msg.sender_name}</div>
                    <div style="font-size:12px; color:#666;">${msg.created_at}</div>
                    <div style="margin-top:5px;">${msg.body.replace(/\n/g, '<br>')}</div>
                `;
                        msgContainer.appendChild(msgBubble);
                        thread.appendChild(msgContainer);
                        shown.add(msg.id);
                    }
                });
                thread.scrollTop = thread.scrollHeight;
            });

            document.getElementById('reply-to').value = email.recipient_email || '';
            document.getElementById('reply-subject').value = email.subject || '';
            document.getElementById('reply-thread-id').value = email.thread_id || email.id;

        }

        function closeEmailView() {
            emailView.style.display = 'none';
            emailListContainer.style.display = 'block';
        }

        document.getElementById('compose-btn').onclick = () => {
            composeForm.style.display = 'block';
            emailView.style.display = 'none';
            document.getElementById('delete-draft-btn').style.display = 'none'; // hide delete button for new compose
        };


        // Compose Form Submit
        document.getElementById('compose-email-form').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const draftId = document.getElementById('draft-id').value.trim();
            const toEmail = document.getElementById('compose-to').value.trim();

            if (toEmail === '') {
                notifySuccess('Recipient email is required.');
                return;
            }

            if (draftId) {
                formData.append('action', 'send_draft');
                formData.append('draft_id', draftId);
            } else {
                formData.append('action', 'send');
            }

            fetchData('mail.php', 'POST', formData).then(data => {
                if (data.success) {
                    notifySuccess('Message Sent Successfully!');
                    document.getElementById('draft-id').value = '';
                    composeForm.style.display = 'none';
                    loadEmails('inbox');
                } else {
                    notifySuccess('Failed: ' + (data.error || 'Unknown error.'));
                }
            });
        };


        document.getElementById('reply-btn').onclick = () => {
            replyForm.style.display = 'block';
            replyForm.scrollIntoView({
                behavior: 'smooth'
            });
        };

        // Reply Form Submit
        document.getElementById('reply-email-form').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'reply');

            fetchData('mail.php', 'POST', formData).then(data => {
                if (data.success) {
                    notifySuccess('Reply Sent Successfully!');
                    replyForm.style.display = 'none';
                    viewEmail({
                        id: currentOpenedEmailId
                    });
                } else {
                    notifySuccess('Failed to send reply.');
                }
            });
        };

        document.getElementById('discard-btn').onclick = function() {
            const draftId = document.getElementById('draft-id').value.trim();
            const subject = document.getElementById('compose-subject').value.trim();
            const body = document.getElementById('compose-body').value.trim();

            if (subject === '' && body === '') {
                composeForm.style.display = 'none';
                document.getElementById('compose-email-form').reset();
                return;
            }

            const formData = new FormData();
            formData.append('action', 'save_draft');
            formData.append('subject', subject);
            formData.append('body', body);
            if (draftId) {
                formData.append('draft_id', draftId);
            }

            fetch('mail.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        notifySuccess('Draft Saved!');
                        document.getElementById('draft-id').value = '';
                        composeForm.style.display = 'none';
                        loadEmails('drafts');
                    } else {
                        notifySuccess('Failed to save draft.');
                    }
                });
        };

        // Delete Draft Button
        document.getElementById('delete-draft-btn').onclick = function() {
            const draftId = document.getElementById('draft-id').value.trim();

            if (!draftId) {
                notifySuccess('No draft selected.');
                return;
            }

            askConfirmation('Are you sure to delete this draft?', function(confirm) {
                if (confirm) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', draftId);

                    fetch('mail.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                notifySuccess('Draft Moved to Trash.');
                                document.getElementById('draft-id').value = '';
                                document.getElementById('compose-email-form').reset();
                                composeForm.style.display = 'none';
                                loadEmails('drafts');
                            } else {
                                notifySuccess('Failed to delete draft.');
                            }
                        });
                }
            });
        };


        document.getElementById('delete-btn').onclick = function() {
            askConfirmation('Are you sure you want to delete this email?', function(confirm) {
                if (confirm) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', currentOpenedEmailId);

                    fetchData('mail.php', 'POST', formData).then(data => {
                        if (data.success) {
                            notifySuccess('Moved to Trash!');
                            closeEmailView();
                            loadEmails(currentFolder);
                        }
                    });
                }
            });
        };

        function deleteEmail(id) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            fetchData('mail.php', 'POST', formData).then(data => {
                if (data.success) {
                    alert('Moved to Trash!');
                    closeEmailView();
                    loadEmails(currentFolder);
                }
            });
        }

        // Delete All Trash
        function deleteAllTrash() {
            askConfirmation('Delete all Trash Permanently?', function(confirm) {
                if (confirm) {
                    const formData = new FormData();
                    formData.append('action', 'delete_all_trash');

                    fetchData('mail.php', 'POST', formData).then(data => {
                        if (data.success) {
                            notifySuccess('All Trash Deleted!');
                            loadEmails('trash');
                        }
                    });
                }
            });
        }

        // Helpers
        function notifySuccess(message) {
            showModal(message, 'info');
        }

        function askConfirmation(message, onConfirm) {
            showModal(message, 'confirm', onConfirm);
        }

        document.querySelectorAll('.folder').forEach(folder => {
            folder.onclick = () => {
                document.querySelectorAll('.folder').forEach(f => f.classList.remove('active'));
                folder.classList.add('active');
                loadEmails(folder.dataset.folder);
            };
        });

        loadEmails(currentFolder);



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

        // Modal Functions
        function showModal(message, type = 'info', callback = null) {
            const modal = document.getElementById('modal');
            const modalMessage = document.getElementById('modal-message');
            const okBtn = document.getElementById('modal-ok');
            const yesBtn = document.getElementById('modal-yes');
            const noBtn = document.getElementById('modal-no');

            modalMessage.innerHTML = message;
            modal.style.display = 'flex';

            if (type === 'info') {
                okBtn.style.display = 'inline-block';
                yesBtn.style.display = 'none';
                noBtn.style.display = 'none';
            } else if (type === 'confirm') {
                okBtn.style.display = 'none';
                yesBtn.style.display = 'inline-block';
                noBtn.style.display = 'inline-block';
            }

            okBtn.onclick = () => {
                modal.style.display = 'none';
            };

            yesBtn.onclick = () => {
                modal.style.display = 'none';
                if (callback) callback(true);
            };

            noBtn.onclick = () => {
                modal.style.display = 'none';
                if (callback) callback(false);
            };
        }
    </script>

    </body>

</html>