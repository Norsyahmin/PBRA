<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include '../mypbra_connect.php';

if (isset($_GET['success'])) {
    echo "Debug: Success flag is set to " . $_GET['success'];
} else {
    echo "Debug: No success flag detected.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="feedback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Feedback</title>
</head>

<body onload="fetchNotifications()">

    <?php include '../navbar/navbar.php'; ?>

    <!-- Page Title -->
    <div class="page-title">
        <h1 style="font-size: 30px;">FEEDBACK</h1>
    </div>

    <div class="breadcrumb">
        <ul id="breadcrumb-list">
            <!-- Breadcrumbs will be dynamically inserted here -->
        </ul>
    </div>

    <!-- Feedback Form -->
    <div class="feedback-container">
        <form action="process_feedback.php" method="POST" enctype="multipart/form-data">
            <!-- Category Selection -->
            <label for="category">Category: </label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <option value="bug_report">Bug Report</option>
                <option value="feature_request">Feature Request</option>
                <option value="general_feedback">General Feedback</option>
                <option value="other">Other</option>
            </select>

            <!-- Message Input -->
            <div class="message">
                <label for="message">Message: </label>
                <textarea class="text-box" name="message" placeholder="Type here..." required></textarea>
            </div>

            <!-- File Attachment with Drag & Drop -->
            <label>Attach files:</label>
            <div class="attach-files" id="drop-area">
                <input type="file" name="attached_files" id="attached-files" hidden>
                <label for="attached-files" id="file-label">
                    <i class="fa fa-cloud-upload-alt"></i>
                    <p>Drag & Drop or Click to Attach Files</p>
                </label>
                <div id="file-info"></div>
            </div>

            <!-- Rating System -->
            <label>Rate Us:</label>
            <div class="rating">
                <i class="fa fa-star" data-value="1"></i>
                <i class="fa fa-star" data-value="2"></i>
                <i class="fa fa-star" data-value="3"></i>
                <i class="fa fa-star" data-value="4"></i>
                <i class="fa fa-star" data-value="5"></i>
                <input type="hidden" name="rating" id="rating-value">
            </div>

            <button class="submit-button" type="submit">Submit</button>
        </form>

        <!-- Success Message -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div id='successPopup' class='popup-overlay'>
                <div class='popup-content'>
                    <h2>Feedback Submitted Successfully! ✅</h2>
                    <button onclick='closePopup()'>OK</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const stars = document.querySelectorAll(".rating i");
            const ratingInput = document.getElementById("rating-value");

            stars.forEach((star, index) => {
                star.addEventListener("click", function() {
                    let value = index + 1; // Get the star value (1-5)
                    ratingInput.value = value;

                    // Reset all stars
                    stars.forEach(s => s.classList.remove("selected"));

                    // Highlight the clicked star and all previous ones
                    for (let i = 0; i < value; i++) {
                        stars[i].classList.add("selected");
                    }

                    // Force reflow to apply color change
                    this.offsetHeight;
                });
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const dropArea = document.getElementById("drop-area");
            const fileInput = document.getElementById("attached-files");
            const fileInfo = document.getElementById("file-info");
            const fileLabel = document.getElementById("file-label");

            // Prevent default behavior for drag & drop
            ["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
                dropArea.addEventListener(eventName, (e) => e.preventDefault(), false);
                document.body.addEventListener(eventName, (e) => e.preventDefault(), false);
            });

            dropArea.addEventListener("dragover", () => dropArea.classList.add("drag-over"));
            dropArea.addEventListener("dragleave", () => dropArea.classList.remove("drag-over"));

            dropArea.addEventListener("drop", (e) => {
                dropArea.classList.remove("drag-over");

                if (e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    updateFileInfo(e.dataTransfer.files);
                }
            });

            fileInput.addEventListener("change", function() {
                if (this.files.length > 0) {
                    updateFileInfo(this.files);
                }
            });

            function updateFileInfo(files) {
                fileInfo.innerHTML = ""; // Clear previous info
                for (let i = 0; i < files.length; i++) {
                    let file = files[i];
                    fileInfo.innerHTML += `<p><i class="fa fa-file"></i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</p>`;
                }
                fileLabel.innerHTML = `<i class="fa fa-check-circle"></i> Files Attached`;
            }
        });


        function closePopup() {
            document.getElementById("successPopup").style.display = "none";
        }


        // Breadcrumbs
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