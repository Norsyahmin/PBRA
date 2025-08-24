<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../mypbra_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = $_SESSION['id'];

// Admin check
$is_admin = false;
$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($user_type);
if ($stmt->fetch() && $user_type === 'admin') {
    $is_admin = true;
}
$stmt->close();

// Handle announcement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $imagePath = null;

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = '../uploads/announcements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $uniqueName = uniqid() . '_' . $imageName;
        $fullPath = $uploadDir . $uniqueName;
        move_uploaded_file($imageTmp, $fullPath);
        $imagePath = 'uploads/announcements/' . $uniqueName; // ✅ Use this for HTML src
    }

    // Save to database
    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO announcement (title, content, image_path, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $title, $content, $imagePath);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch announcements (latest first)
$sql = "SELECT * FROM announcement ORDER BY created_at DESC";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Add these new styles inline or in your homepage.css */
        .top-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            padding: 20px;
        }

        .calendar-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .calendar-widget h3 {
            color: #4fc3f7;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        /* Add these styles for the month navigation */
        .calendar-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: #f5f5f5;
            border-radius: 6px;
            padding: 8px 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .nav-btn {
            background: #174080;
            border: 1px solid #174080;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .nav-btn:hover {
            background: #f0f0f0;
            color: #174080;
        }

        .nav-btn:active {
            transform: translateY(1px);
            box-shadow: none;
        }

        #currentMonth {
            font-weight: 600;
            font-size: 16px;
            color: #333;
            padding: 5px 15px;
            background: white;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
            min-width: 130px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            margin-bottom: 15px;
        }

        .day-header {
            text-align: center;
            font-weight: bold;
            padding: 10px 5px;
            color: #666;
            font-size: 12px;
        }

        .day-cell {
            text-align: center;
            padding: 10px 5px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s;
            min-height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .day-cell:hover {
            background: #e3f2fd;
        }

        .day-cell.highlight {
            background: #4fc3f7;
            color: white;
        }

        .day-cell.today {
            background: #2196f3;
            color: white;
            font-weight: bold;
        }

        .announcement-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Add these styles for the new-event-btn */
        .new-event-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to bottom, #174080, #174080);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 15px;
            margin: 0 0 15px 0;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }

        .new-event-btn:hover {
            background: linear-gradient(to bottom, #174080, #174080);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: translateY(-1px);
        }

        .new-event-btn:active {
            transform: translateY(1px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .new-event-btn i {
            margin-right: 8px;
            font-size: 14px;
        }

        /* Update these styles for better modal centering */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            width: 95%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s;
            margin: 0;
            position: relative;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 15px 20px;
            background: #174080;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .close-modal {
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #f0f0f0;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        /* Force 24-hour format display in time inputs */
        input[type="time"] {
            font-family: monospace;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        
        /* Ensure 24-hour format is maintained */
        input[type="time"]::-webkit-calendar-picker-indicator {
            opacity: 0.7;
        }

        .form-group textarea {
            resize: vertical;
        }

        .submit-btn {
            background: #174080;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #2962ff;
        }

        /* Make day cells clickable to select date */
        .day-cell.highlight {
            background: #4fc3f7;
            color: white;
        }

        /* Add styles for the events container popup */
        .events-popup-container {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 600px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .events-popup-header {
            background: #174080;
            color: white;
            padding: 15px 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .events-popup-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .events-popup-close {
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        .events-popup-body {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .events-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .event-item {
            padding: 10px;
            border-left: 3px solid #4fc3f7;
            background: #f5f9ff;
            margin-bottom: 8px;
            border-radius: 0 4px 4px 0;
        }

        .event-date {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }

        .event-title {
            font-weight: 500;
            color: #333;
        }

        .no-events-message {
            color: #666;
            font-style: italic;
            padding: 10px 0;
        }

        /* Add these styles for the upcoming events section */
        .upcoming-events {
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }

        .upcoming-events h4 {
            color: #174080;
            margin-bottom: 12px;
            font-size: 16px;
            display: flex;
            align-items: center;
        }

        .upcoming-events h4 i {
            margin-right: 8px;
        }

        /* Add this to your existing CSS */
        .day-cell.has-event {
            position: relative;
        }

        .day-cell.has-event::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #4fc3f7;
        }

        .day-cell.has-event.today::after {
            background-color: white;
        }

        .day-cell.has-event:hover::after {
            background-color: #174080;
        }

        /* Add these styles to improve the events popup centering and animation */
        .events-popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .events-popup-container {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) scale(0.95);
            width: 80%;
            max-width: 600px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .events-popup-container.active {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .events-popup-overlay.active {
            opacity: 1;
        }

        /* Add these styles for the event action buttons */
        .event-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            justify-content: flex-end;
        }

        .edit-event-btn,
        .delete-event-btn {
            background: none;
            border: none;
            padding: 5px 8px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .edit-event-btn {
            color: #174080;
        }

        .delete-event-btn {
            color: #e53935;
        }

        .edit-event-btn:hover {
            background-color: #e3f2fd;
        }

        .delete-event-btn:hover {
            background-color: #ffebee;
        }

        .event-item {
            position: relative;
        }

        .upcoming-events {
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }

        .upcoming-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .slider-controls {
            display: flex;
            gap: 8px;
        }

        .slider-btn {
            background: #f0f0f0;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #174080;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .slider-btn:hover:not(:disabled) {
            background: #e3f2fd;
            transform: translateY(-1px);
        }

        .slider-btn:disabled {
            color: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }

        .events-slider-container {
            overflow: hidden;
            position: relative;
        }

        #events-slider {
            display: flex;
            transition: transform 0.3s ease;
        }

        .event-slide {
            min-width: 100%;
            flex-shrink: 0;
            padding: 0 2px;
        }

        .event-slide .event-item {
            height: 100%;
        }
    </style>
</head>

<body onload="fetchNotifications()">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <div class="content-body">
        <!-- Add the new two-column layout here -->
        <div class="top-content">
            <!-- Left Column - Calendar -->
            <div class="calendar-section">
                <div class="calendar-widget">
                    <h3>Calendar</h3>
                    <div class="calendar-header">
                        <div class="calendar-controls">
                            <button class="nav-btn" onclick="prevMonth()"><i class="fas fa-chevron-left"></i></button>
                            <span id="currentMonth">August 2025</span>
                            <button class="nav-btn" onclick="nextMonth()"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <button class="new-event-btn">
                        <i class="fas fa-plus"></i> New Event
                    </button>

                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Calendar will be generated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Right Column - Announcements -->
            <div class="announcement-section">
                <?php include '../includes/announcement.php'; ?>
            </div>
        </div>

        <div class="feature-container">
            <div class="role-container" onclick="window.location.href='../roles/roles.php';">
                <div class="text-role">
                    <h2><strong>ROLE</strong></h2>
                    <p>This section lets you easily keep track of all your roles, including when they start, end, and
                        the latest tasks completed.</p>
                </div>
            </div>

            <div class="pbstaff-container" onclick="window.location.href='../staff/staffsch.php';">
                <div class="text">
                    <h2><strong>PB STAFF</strong></h2>
                    <p>Get to know who is in charge of each department and all of its staff.</p>
                </div>
            </div>
        </div>

        <div class="setting-section">
            <div class="feedback" onclick="window.location.href='../feedback/feedback.php';">
                <i class="fas fa-comment"></i> Feedback
            </div>
            <div class="report" onclick="window.location.href='../report/report.php';">
                <i class="fas fa-clipboard"></i> Report
            </div>
            <div class="user-support" onclick="window.location.href='../usersupport/usersupport.php';">
                <i class="fas fa-question-circle"></i> User Support
            </div>

        </div>

    </div>


    <footer>
        <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
    </footer>

    <script>
        function formatText(command) {
            document.execCommand(command, false, null);
        }

        function prepareSubmission() {
            const richContent = document.getElementById('richContent').innerHTML;
            document.getElementById('hiddenContent').value = richContent;
        }

        function openModal() {
            document.getElementById('announcementModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('announcementModal').style.display = 'none';
        }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('openFormBtn')?.addEventListener('click', openModal);
        });

        // Breadcrumbs
        let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
        let currentPageName = "Homepage";
        let currentPageUrl = window.location.pathname;

        if (!breadcrumbs.some(breadcrumb => breadcrumb.url === currentPageUrl)) {
            breadcrumbs.push({
                name: currentPageName,
                url: currentPageUrl
            });
            sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
        }

        let breadcrumbList = document.getElementById('breadcrumb-list');
        if (breadcrumbList) {
            breadcrumbList.innerHTML = '';
            breadcrumbs.forEach((breadcrumb, index) => {
                let breadcrumbItem = document.createElement('li');
                let link = document.createElement('a');
                link.href = breadcrumb.url;
                link.textContent = breadcrumb.name;
                breadcrumbItem.appendChild(link);
                breadcrumbList.appendChild(breadcrumbItem);

                if (index < breadcrumbs.length - 1) {
                    let separator = document.createElement('span');
                    separator.textContent = ' > ';
                    breadcrumbList.appendChild(separator);
                }
            });
        }

        // Modal Controls
        // Update your modal control functions

        // Calendar functionality
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        let currentDate = new Date();

        // Enhanced calendar generation with event highlighting
        function generateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            document.getElementById('currentMonth').textContent = `${months[month]} ${year}`;

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();

            // Get saved events to check for highlights
            const savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

            // Create array of dates that have events for this month
            const datesWithEvents = savedEvents.filter(event => {
                const eventDate = new Date(event.date);
                return eventDate.getMonth() === month && eventDate.getFullYear() === year;
            }).map(event => new Date(event.date).getDate());

            let calendarHTML = `
                <div class="day-header">Sun</div>
                <div class="day-header">Mon</div>
                <div class="day-header">Tue</div>
                <div class="day-header">Wed</div>
                <div class="day-header">Thu</div>
                <div class="day-header">Fri</div>
                <div class="day-header">Sat</div>
            `;

            for (let i = 0; i < startingDay; i++) {
                calendarHTML += '<div class="day-cell empty"></div>';
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = new Date().toDateString() === new Date(year, month, day).toDateString();
                const hasEvent = datesWithEvents.includes(day);

                // Add has-event class if there's an event on this day
                calendarHTML += `
                    <div class="day-cell ${isToday ? 'today' : ''} ${hasEvent ? 'has-event' : ''}" data-date="${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}">${day}</div>
                `;
            }

            document.getElementById('calendarGrid').innerHTML = calendarHTML;

            // Add click event for showing events when clicking on a day
            document.querySelectorAll('.day-cell:not(.empty)').forEach(cell => {
                cell.addEventListener('click', function() {
                    document.querySelectorAll('.day-cell').forEach(c => c.classList.remove('highlight'));
                    this.classList.add('highlight');

                    const dateStr = this.getAttribute('data-date');
                    if (dateStr) {
                        // Show events for this date if desired
                        // For now we just highlight and prefill the form date if opened
                    }
                });
            });
        }

        function prevMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar();
        }

        // Initialize calendar when page loads
        document.addEventListener('DOMContentLoaded', generateCalendar);

        // Fix the event form submission handler
        document.addEventListener('DOMContentLoaded', function() {
            const newEventBtn = document.querySelector('.new-event-btn');

            newEventBtn.addEventListener('click', function() {
                openEventForm();
            });

            function openEventForm() {
                // If modal already exists, just show it
                if (document.getElementById('eventFormModal')) {
                    document.getElementById('eventFormModal').style.display = 'block';
                    return;
                }

                // Create modal elements
                const modal = document.createElement('div');
                modal.id = 'eventFormModal';
                modal.className = 'modal';

                modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Event</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <div class="form-group">
                            <label for="eventTitle">Event Title</label>
                            <input type="text" id="eventTitle" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="eventDate">Date</label>
                            <input type="date" id="eventDate" name="date" required>
                        </div>
                        <div class="form-group">
                            <label for="startTime">Start Time</label>
                            <input type="time" id="startTime" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label for="endTime">End Time</label>
                            <input type="time" id="endTime" name="end_time" required>
                        </div>
                        <div class="form-group">
                            <label for="eventLocation">Location</label>
                            <input type="text" id="eventLocation" name="location">
                        </div>
                        <div class="form-group">
                            <label for="eventDescription">Description</label>
                            <textarea id="eventDescription" name="description" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="submit-btn">Save Event</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

                document.body.appendChild(modal);

                // Set up event handlers
                const closeBtn = modal.querySelector('.close-modal');
                closeBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });

                window.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });

                // Event form submission handler - FIX BEGINS HERE
                const eventForm = document.getElementById('eventForm');
                eventForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Get form values
                    const eventTitle = document.getElementById('eventTitle').value;
                    const eventDate = document.getElementById('eventDate').value;
                    const startTime = document.getElementById('startTime').value;
                    const endTime = document.getElementById('endTime').value;
                    const eventLocation = document.getElementById('eventLocation').value;
                    const eventDescription = document.getElementById('eventDescription').value;

                    // Create new event object
                    const newEvent = {
                        id: Date.now(), // unique id using timestamp
                        title: eventTitle,
                        date: eventDate,
                        startTime: startTime,
                        endTime: endTime,
                        location: eventLocation,
                        description: eventDescription
                    };

                    // Get existing events from localStorage
                    let savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

                    // Add new event to the list
                    savedEvents.push(newEvent);

                    // Sort events by date
                    savedEvents.sort((a, b) => new Date(a.date) - new Date(b.date));

                    // Save back to localStorage
                    localStorage.setItem('calendarEvents', JSON.stringify(savedEvents));

                    // Close the modal
                    modal.style.display = 'none';

                    // Regenerate calendar to update event dots
                    generateCalendar();

                    // Update the upcoming events display
                    displayEvents(savedEvents);

                    // Show success message
                    alert('Event created successfully!');
                });

                // If a day was clicked, pre-fill the date
                const selectedDay = document.querySelector('.day-cell.highlight');
                if (selectedDay) {
                    const day = selectedDay.textContent;
                    const month = currentDate.getMonth() + 1;
                    const year = currentDate.getFullYear();
                    document.getElementById('eventDate').value = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                } else {
                    // Default to today's date
                    const today = new Date();
                    const day = today.getDate();
                    const month = today.getMonth() + 1;
                    const year = today.getFullYear();
                    document.getElementById('eventDate').value = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                }

                // Show the modal
                modal.style.display = 'block';
            }
        });

        // Add this JavaScript to handle showing events when clicking on a day
        document.addEventListener('DOMContentLoaded', function() {
            // Make sure we create the popup elements if they don't exist
            if (!document.getElementById('eventsPopupOverlay')) {
                const overlay = document.createElement('div');
                overlay.id = 'eventsPopupOverlay';
                overlay.className = 'events-popup-overlay';
                document.body.appendChild(overlay);

                const popupContainer = document.createElement('div');
                popupContainer.id = 'eventsPopupContainer';
                popupContainer.className = 'events-popup-container';
                popupContainer.innerHTML = `
                    <div class="events-popup-header">
                        <h3 id="eventsPopupDate">Events for Today</h3>
                        <span class="events-popup-close">&times;</span>
                    </div>
                    <div class="events-popup-body">
                        <div id="eventsContent">
                            <div class="no-events">No events scheduled for this day.</div>
                        </div>
                    </div>
                `;
                document.body.appendChild(popupContainer);

                // Close popup when clicking X or overlay
                document.querySelector('.events-popup-close').addEventListener('click', closeEventsPopup);
                overlay.addEventListener('click', closeEventsPopup);
            }

            // Global function to show events for a specific date
            window.showEventsForDate = function(dateString, displayDate) {
                // Get real events from localStorage
                const savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

                // Filter events for the selected date
                const eventsForDate = savedEvents.filter(event => event.date === dateString);

                // Update popup title with selected date
                document.getElementById('eventsPopupDate').textContent = `Events for ${displayDate}`;

                // Generate events HTML
                let eventsHTML = '';
                if (eventsForDate.length > 0) {
                    eventsHTML = '<ul class="events-list">';
                    eventsForDate.forEach(event => {
                        // Format times for display
                        const startTime = event.startTime.substr(0, 5);
                        const endTime = event.endTime.substr(0, 5);

                        eventsHTML += `
                <li class="event-item" data-event-id="${event.id}">
                    <div class="event-time">${startTime} - ${endTime}</div>
                    <div class="event-details">
                        <div class="event-title">${event.title}</div>
                        <div class="event-location"><i class="fas fa-map-marker-alt"></i> ${event.location || 'No location'}</div>
                        ${event.description ? `<div class="event-description">${event.description}</div>` : ''}
                    </div>
                    <div class="event-actions">
                        <button class="edit-event-btn" onclick="editEvent(${event.id})"><i class="fas fa-edit"></i></button>
                        <button class="delete-event-btn" onclick="deleteEvent(${event.id})"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </li>
            `;
                    });
                    eventsHTML += '</ul>';
                } else {
                    eventsHTML = '<div class="no-events">No events scheduled for this day.</div>';
                }

                // Update content
                document.getElementById('eventsContent').innerHTML = eventsHTML;

                // Show popup with smooth animation
                const overlay = document.getElementById('eventsPopupOverlay');
                const container = document.getElementById('eventsPopupContainer');

                // Make sure the elements are visible before animation
                overlay.style.display = 'block';
                container.style.display = 'block';

                // Force reflow to ensure transition works
                void overlay.offsetWidth;
                void container.offsetWidth;

                // Add active class to trigger animation
                overlay.classList.add('active');
                container.classList.add('active');
            };

            // Add these functions for editing and deleting events
            window.editEvent = function(eventId) {
                // Get existing events
                const savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');
                const eventToEdit = savedEvents.find(event => event.id === eventId);

                if (!eventToEdit) return;

                // Create edit form modal if it doesn't exist
                if (!document.getElementById('editEventFormModal')) {
                    const modal = document.createElement('div');
                    modal.id = 'editEventFormModal';
                    modal.className = 'modal';

                    modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Event</h3>
                    <span class="close-modal" onclick="document.getElementById('editEventFormModal').style.display='none'">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" id="editEventId">
                        <div class="form-group">
                            <label for="editEventTitle">Event Title</label>
                            <input type="text" id="editEventTitle" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="editEventDate">Date</label>
                            <input type="date" id="editEventDate" name="date" required>
                        </div>
                        <div class="form-group">
                            <label for="editStartTime">Start Time</label>
                            <input type="time" id="editStartTime" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label for="editEndTime">End Time</label>
                            <input type="time" id="editEndTime" name="end_time" required>
                        </div>
                        <div class="form-group">
                            <label for="editEventLocation">Location</label>
                            <input type="text" id="editEventLocation" name="location">
                        </div>
                        <div class="form-group">
                            <label for="editEventDescription">Description</label>
                            <textarea id="editEventDescription" name="description" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="submit-btn">Update Event</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

                    document.body.appendChild(modal);

                    // Set up form submission handler
                    document.getElementById('editEventForm').addEventListener('submit', function(e) {
                        e.preventDefault();

                        const eventId = parseInt(document.getElementById('editEventId').value);
                        const title = document.getElementById('editEventTitle').value;
                        const date = document.getElementById('editEventDate').value;
                        const startTime = document.getElementById('editStartTime').value;
                        const endTime = document.getElementById('editEndTime').value;
                        const location = document.getElementById('editEventLocation').value;
                        const description = document.getElementById('editEventDescription').value;

                        // Get all events
                        let savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

                        // Find and update the event
                        const updatedEvents = savedEvents.map(event => {
                            if (event.id === eventId) {
                                return {
                                    ...event,
                                    title,
                                    date,
                                    startTime,
                                    endTime,
                                    location,
                                    description
                                };
                            }
                            return event;
                        });

                        // Save updated events
                        localStorage.setItem('calendarEvents', JSON.stringify(updatedEvents));

                        // Close modals
                        document.getElementById('editEventFormModal').style.display = 'none';
                        document.getElementById('eventsPopupOverlay').classList.remove('active');
                        document.getElementById('eventsPopupContainer').classList.remove('active');

                        // Regenerate calendar to update dots
                        generateCalendar();

                        // Update upcoming events
                        displayEvents(updatedEvents);

                        // Show success message
                        alert('Event updated successfully!');
                    });
                }

                // Fill the form with event data
                document.getElementById('editEventId').value = eventToEdit.id;
                document.getElementById('editEventTitle').value = eventToEdit.title;
                document.getElementById('editEventDate').value = eventToEdit.date;
                document.getElementById('editStartTime').value = eventToEdit.startTime;
                document.getElementById('editEndTime').value = eventToEdit.endTime;
                document.getElementById('editEventLocation').value = eventToEdit.location || '';
                document.getElementById('editEventDescription').value = eventToEdit.description || '';

                // Show the edit modal
                document.getElementById('editEventFormModal').style.display = 'block';
            };

            window.deleteEvent = function(eventId) {
                if (confirm('Are you sure you want to delete this event?')) {
                    // Get existing events
                    let savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

                    // Remove the event
                    const filteredEvents = savedEvents.filter(event => event.id !== eventId);

                    // Save updated events
                    localStorage.setItem('calendarEvents', JSON.stringify(filteredEvents));

                    // Close the events popup
                    closeEventsPopup();

                    // Regenerate calendar to update dots
                    generateCalendar();

                    // Update upcoming events
                    displayEvents(filteredEvents);

                    // Show success message
                    alert('Event deleted successfully!');
                }
            };
        });

        // Add this code to modify the upcoming events section with a slider
        document.addEventListener('DOMContentLoaded', function() {
            // Create the upcoming events section if it doesn't exist, but with slider capability
            if (!document.querySelector('.upcoming-events')) {
                const calendarSection = document.querySelector('.calendar-widget');
                const upcomingEvents = document.createElement('div');
                upcomingEvents.className = 'upcoming-events';
                upcomingEvents.innerHTML = `
            <div class="upcoming-header">
                <h4><i class="fas fa-calendar-check"></i> Upcoming Events</h4>
                <div class="slider-controls">
                    <button class="slider-btn prev-btn" disabled><i class="fas fa-chevron-left"></i></button>
                    <button class="slider-btn next-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="events-slider-container">
                <div id="events-slider">
                    <p class="no-events-message">No upcoming events.</p>
                </div>
            </div>
        `;
                calendarSection.appendChild(upcomingEvents);

                // Add styles for the slider
                const styleElement = document.createElement('style');
                styleElement.textContent = `
            .upcoming-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 12px;
            }
            
            .slider-controls {
                display: flex;
                gap: 8px;
            }
            
            .slider-btn {
                background: #f0f0f0;
                border: none;
                border-radius: 50%;
                width: 28px;
                height: 28px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                color: #174080;
                transition: all 0.2s ease;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            
            .slider-btn:hover:not(:disabled) {
                background: #e3f2fd;
                transform: translateY(-1px);
            }
            
            .slider-btn:disabled {
                color: #ccc;
                cursor: not-allowed;
                box-shadow: none;
            }
            
            .events-slider-container {
                overflow: hidden;
                position: relative;
            }
            
            #events-slider {
                display: flex;
                transition: transform 0.3s ease;
            }
            
            .event-slide {
                min-width: 100%;
                flex-shrink: 0;
                padding: 0 2px;
            }
            
            .event-slide .event-item {
                height: 100%;
            }
        `;
                document.head.appendChild(styleElement);
            }

            // Replace the displayEvents function with a slider version
            window.displayEvents = function(events) {
                const eventsSlider = document.getElementById('events-slider');
                if (!eventsSlider) return;

                // Filter to show only upcoming events (today and future)
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const upcomingEvents = events.filter(event => {
                    const eventDate = new Date(event.date);
                    eventDate.setHours(0, 0, 0, 0);
                    return eventDate >= today;
                });

                // Sort by date (closest first)
                upcomingEvents.sort((a, b) => new Date(a.date) - new Date(b.date));

                if (upcomingEvents.length === 0) {
                    eventsSlider.innerHTML = '<p class="no-events-message">No upcoming events.</p>';
                    document.querySelector('.prev-btn').disabled = true;
                    document.querySelector('.next-btn').disabled = true;
                    return;
                }

                let html = '';

                // Create a slide for each event
                upcomingEvents.forEach((event, index) => {
                    // Format date
                    const eventDate = new Date(event.date);
                    const formattedDate = eventDate.toLocaleDateString('en-US', {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric'
                    });

                    html += `
                <div class="event-slide" data-index="${index}">
                    <div class="event-item">
                        <div class="event-date">${formattedDate} • ${event.startTime.substr(0, 5)} - ${event.endTime.substr(0, 5)}</div>
                        <div class="event-title">${event.title}</div>
                        <div class="event-details">
                            ${event.location ? `<div class="event-location"><i class="fas fa-map-marker-alt"></i> ${event.location}</div>` : ''}
                            ${event.description ? `<div class="event-description">${event.description.substring(0, 100)}${event.description.length > 100 ? '...' : ''}</div>` : ''}
                        </div>
                    </div>
                </div>
            `;
                });

                eventsSlider.innerHTML = html;

                // Initialize slider at first item
                let currentSlide = 0;
                const totalSlides = upcomingEvents.length;

                // Update navigation buttons
                document.querySelector('.prev-btn').disabled = true;
                document.querySelector('.next-btn').disabled = totalSlides <= 1;

                // Add navigation functionality
                document.querySelector('.prev-btn').onclick = function() {
                    if (currentSlide > 0) {
                        currentSlide--;
                        updateSliderPosition();
                    }
                };

                document.querySelector('.next-btn').onclick = function() {
                    if (currentSlide < totalSlides - 1) {
                        currentSlide++;
                        updateSliderPosition();
                    }
                };

                function updateSliderPosition() {
                    eventsSlider.style.transform = `translateX(-${currentSlide * 100}%)`;
                    document.querySelector('.prev-btn').disabled = currentSlide === 0;
                    document.querySelector('.next-btn').disabled = currentSlide === totalSlides - 1;
                }
            };

            // Get existing events from localStorage and display them
            let savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');
            displayEvents(savedEvents);
        });
    </script>

    <script>
        // Add this function to properly close the events popup
        window.closeEventsPopup = function() {
            const overlay = document.getElementById('eventsPopupOverlay');
            const container = document.getElementById('eventsPopupContainer');

            // Remove active class to trigger fade-out animation
            overlay.classList.remove('active');
            container.classList.remove('active');

            // Wait for animation to complete before hiding elements
            setTimeout(() => {
                overlay.style.display = 'none';
                container.style.display = 'none';
            }, 300);
        };

        // Update the click event handler to show events popup when clicking on days with events
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener for clicks on day cells
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('day-cell') && !e.target.classList.contains('empty')) {
                    // Remove highlight from all cells
                    document.querySelectorAll('.day-cell').forEach(cell => {
                        cell.classList.remove('highlight');
                    });

                    // Add highlight to clicked cell
                    e.target.classList.add('highlight');

                    // Get the date of the clicked day
                    const day = e.target.textContent;
                    const month = currentDate.getMonth() + 1;
                    const year = currentDate.getFullYear();
                    const formattedDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

                    // Only show events popup if this day has an event (has the has-event class)
                    if (e.target.classList.contains('has-event')) {
                        showEventsForDate(formattedDate, `${months[month - 1]} ${day}, ${year}`);
                    }
                }
            });
        });
    </script>

</body>

</html>