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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="event.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Events</title>
</head>

<body onload="fetchNotifications()">

    <?php include '../navbar/navbar.php'; ?>

    <div class="page-title">
        <h1 style="font-size: 30px;">EVENT</h1>
    </div>

    <div class="breadcrumb">
        <ul id="breadcrumb-list">
        </ul>
    </div>

    <div class="content">
        <ul>
            <li>

                <div class="container">
                    <a href="#">
                        <div class="folder-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>

                        <div class="text" onclick="window.location.href='../calendar/calendar.php';"
                            style="cursor: pointer;">
                            <h1>Calendar</h1>
                            <p>The calendar displays tasks assigned to you, and you can also add your own events or
                                reminders</p>
                        </div>
                    </a>
                </div>
            </li>

            <li>
                <div class="container">
                    <a href="#">
                        <div class="folder-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>

                        <div class="text" onclick="window.location.href='../schedule/schedule.php';"
                            style="cursor: pointer;">
                            <h1>Schedule</h1>
                            <p>Here, you can view the schedule assigned to you by Politeknik Brunei</p>
                        </div>
                    </a>
                </div>
            </li>
        </ul>
    </div>

    <script>
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