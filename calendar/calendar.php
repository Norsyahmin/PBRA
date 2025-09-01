<?php
session_start();
include '../mypbra_connect.php';
$user_id = $_SESSION['id'];

$page_name = $page_name ?? 'Calendar'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];


$sql_tasks = "SELECT task_name, task_date, task_time, status FROM tasks WHERE assigned_to = ?";
$stmt = $conn->prepare($sql_tasks);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$php_tasks = [];
while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'pending') {
        $php_tasks[] = $row;
    }
}

$stmt->close();
$conn->close();

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
    <link rel="stylesheet" href="calendar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Calendar</title>
</head>

<header>
<?php include '../includes/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">

    <div class="page-title">
        <h1 style="font-size: 30px;">CALENDAR</h1>
        
    <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
    Add to Favorite
</button>
    </div>

    <div class="breadcrumb">
        <ul id="breadcrumb-list">
        </ul>
    </div>
    

    <div class="main-container">
        <div class="calendar-container">
            <div class="calendar-header">
                <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                <h2 id="current-month-year">August 2023</h2>
                <button id="next-month"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="calendar-grid" id="calendar-grid">
                <div class="calendar-day">Sun</div>
                <div class="calendar-day">Mon</div>
                <div class="calendar-day">Tue</div>
                <div class="calendar-day">Wed</div>
                <div class="calendar-day">Thu</div>
                <div class="calendar-day">Fri</div>
                <div class="calendar-day">Sat</div>
            </div>
        </div>

        <div class="event-list">
            <div id="events-container">
            </div>
        </div>
    </div>

    <button class="add-event-btn" id="add-event-btn">+</button>

    <div id="event-form-scene" class="event-form-scene">
        <div class="event-form-container">
            <h2 id="event-form-title">Add Event</h2>
            <form id="event-form">
                <label for="event-date">Date:</label>
                <input type="date" id="event-date" name="event-date" required>

                <label for="event-time">Time:</label>
                <input type="time" id="event-time" name="event-time" required>

                <label for="event-title">Title:</label>
                <input type="text" id="event-title" name="event-title" required>

                <label>Type:</label>
                <div class="event-type">
                    <label><input type="radio" name="event-type" value="personal" required> Personal</label>
                    <label><input type="radio" name="event-type" value="urgent"> Urgent</label>
                    <label><input type="radio" name="event-type" value="neutral"> Neutral</label>
                </div>

                <button type="submit">Save</button>
                <button type="button" id="delete-event-btn" style="display: none;">Delete</button>
                <button type="button" id="cancel-event-btn">Cancel</button>
            </form>
        </div>
    </div>

    <script>
const phpEvents = <?php echo json_encode($php_tasks); ?>;
let events = [];

// Add DB tasks (source: db)
phpEvents.forEach(task => {
    events.push({
        date: task.task_date,
        time: task.task_time,
        title: task.task_name,
        type: 'urgent',
        source: 'db'
    });
});

// Add manual events (source: manual)
const localEvents = JSON.parse(localStorage.getItem('events')) || [];
localEvents.forEach(event => {
    events.push({
        date: event.date,
        time: event.time,
        title: event.title,
        type: event.type || 'neutral',
        source: 'manual'
    });
});


</script>


    <script>

        const calendarGrid = document.getElementById('calendar-grid');
        const currentMonthYear = document.getElementById('current-month-year');
        const prevMonthButton = document.getElementById('prev-month');
        const nextMonthButton = document.getElementById('next-month');
        const eventFormScene = document.getElementById('event-form-scene');
        const eventForm = document.getElementById('event-form');
        const cancelEventBtn = document.getElementById('cancel-event-btn');
        const addEventBtn = document.getElementById('add-event-btn');
        const doneConfirmationScene = document.getElementById('done-confirmation-scene');
        const closeDoneConfirmation = document.getElementById('close-done-confirmation');
        const eventFormTitle = document.getElementById('event-form-title');
        const eventsContainer = document.getElementById('events-container');

        let currentDate = new Date();
        let editingEventIndex = null;

        function formatDateToInputValue(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;     
    }


        document.addEventListener("DOMContentLoaded", () => {
            renderCalendar(currentDate);
            renderEvents();
        });

        function renderCalendar(date) {
            const year = date.getFullYear();
            const month = date.getMonth();
            const firstDayOfMonth = new Date(year, month, 1);
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const startingDay = firstDayOfMonth.getDay();

            currentMonthYear.textContent = `${date.toLocaleString('default', { month: 'long' })} ${year}`;
            calendarGrid.innerHTML = '';

            const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            daysOfWeek.forEach(day => {
                const dayElement = document.createElement('div');
                dayElement.classList.add('calendar-day');
                dayElement.textContent = day;
                calendarGrid.appendChild(dayElement);
            });

            for (let i = 0; i < startingDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.classList.add('calendar-date', 'empty');
                calendarGrid.appendChild(emptyDay);
            }

            for (let i = 1; i <= daysInMonth; i++) {
                const dateElement = document.createElement('div');
                dateElement.classList.add('calendar-date');
                dateElement.textContent = i;

                const event = events.find(event => {
                    const eventDate = new Date(event.date);
                    return eventDate.getFullYear() === year && eventDate.getMonth() === month && eventDate.getDate() === i;
                });

                if (event) dateElement.classList.add(event.type);

                if (!event || event.source === 'manual') {
    dateElement.addEventListener('click', () => openEventForm(new Date(year, month, i)));
}
                calendarGrid.appendChild(dateElement);
            }
        }

        function openEventForm(date, eventIndex = null) {
    eventFormScene.style.display = 'flex';
    editingEventIndex = eventIndex;

    let displayDate = date;
    let inputDate = formatDateToInputValue(date);

    // If editing an existing event
    if (eventIndex !== null && events[eventIndex]) {
        const event = events[eventIndex];

        displayDate = new Date(event.date);
        inputDate = formatDateToInputValue(displayDate);

        document.getElementById('event-date').value = event.date;
        document.getElementById('event-time').value = event.time;
        document.getElementById('event-title').value = event.title;
        const typeRadio = document.querySelector(`input[name="event-type"][value="${event.type}"]`);
        if (typeRadio) typeRadio.checked = true;

        eventFormTitle.textContent = 'Edit Event';
        document.getElementById('delete-event-btn').style.display = 'inline-block';

    } else {
        eventForm.reset();
        document.getElementById('event-date').value = inputDate;
        document.getElementById('event-time').value = '';
        document.getElementById('event-title').value = '';
        const radios = document.querySelectorAll('input[name="event-type"]');
        radios.forEach(r => r.checked = false);

        eventFormTitle.textContent = 'Add Event';
        document.getElementById('delete-event-btn').style.display = 'none';

    }

    // Format and show the selected date as dd/mm/yyyy
    const day = String(displayDate.getDate()).padStart(2, '0');
    const month = String(displayDate.getMonth() + 1).padStart(2, '0');
    const year = displayDate.getFullYear();
    const formattedDisplay = `${day}/${month}/${year}`;
    document.getElementById('formatted-date-display').textContent = `Selected Date: ${formattedDisplay}`;
}

function renderEvents() {
    eventsContainer.innerHTML = '';
    events.forEach((event, index) => {
        const eventElement = document.createElement('div');
        eventElement.classList.add('event');
        eventElement.setAttribute('data-type', event.type);

        const dateObj = new Date(event.date);
        const dayOfWeek = dateObj.toLocaleString('default', { weekday: 'short' });
        const monthName = dateObj.toLocaleString('default', { month: 'short' });

        eventElement.innerHTML = `
            <span class="event-date">${dayOfWeek}, ${dateObj.getDate()} ${monthName} ${dateObj.getFullYear()}</span>
            <span class="event-title">${event.title}</span>
            <span class="event-time">${event.time}</span>
        `;

        if (event.source === 'manual') {
    const editBtn = document.createElement('button');
    editBtn.classList.add('done-btn');
    editBtn.textContent = 'Edit';
    editBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        openEventForm(new Date(event.date), index);
    });
    eventElement.appendChild(editBtn);
}

        eventsContainer.appendChild(eventElement);
    });
}

function saveEvents() {
    localStorage.setItem('events', JSON.stringify(events.filter(e => e.source === 'manual')));
}


        prevMonthButton.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        });

        nextMonthButton.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        });

        addEventBtn.addEventListener('click', () => openEventForm(new Date()));

        cancelEventBtn.addEventListener('click', () => {
            eventFormScene.style.display = 'none';
            editingEventIndex = null;
        });

        const deleteEventBtn = document.getElementById('delete-event-btn');

        deleteEventBtn.addEventListener('click', () => {
    if (editingEventIndex !== null) {
        events.splice(editingEventIndex, 1);
        saveEvents();
        renderCalendar(currentDate);
        renderEvents();
        eventFormScene.style.display = 'none';
        editingEventIndex = null;
    }
});


        eventForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const eventDate = document.getElementById('event-date').value;
            const eventTime = document.getElementById('event-time').value;
            const eventTitle = document.getElementById('event-title').value;
            const eventType = document.querySelector('input[name="event-type"]:checked').value;

            if (eventDate && eventTime && eventTitle && eventType) {
                const newEvent = {
  date: eventDate,
  time: eventTime,
  title: eventTitle,
  type: eventType,
  source: 'manual' // ✅ add this
};
                if (editingEventIndex !== null) {
                    events[editingEventIndex] = newEvent;
                } else {
                    events.push(newEvent);
                }
                saveEvents();
                renderCalendar(currentDate);
                renderEvents();
                eventFormScene.style.display = 'none';
                editingEventIndex = null;
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