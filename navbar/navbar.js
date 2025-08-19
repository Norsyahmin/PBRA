document.addEventListener("DOMContentLoaded", function () {
    fetchNotifications();
    initializeSearch();
});

// Sidebar toggle
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.getElementById('sidebar');
const closeSidebar = document.getElementById('close-sidebar');

menuToggle?.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    menuToggle.innerHTML = sidebar.classList.contains('active')
        ? '<i class="material-icons">close</i>'
        : '<i class="material-icons">menu</i>';
});

closeSidebar?.addEventListener('click', () => {
    sidebar.classList.remove('active');
    menuToggle.innerHTML = '<i class="material-icons">menu</i>';
});

// Notifications
function fetchNotifications() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "../homepage/process_notification.php", true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                console.error("Invalid JSON response", e);
                return;
            }

            const notificationList = document.getElementById("notification-list");
            const notificationDot = document.getElementById("notification-dot");

            notificationList.innerHTML = "";
            if (response.unreadCount > 0) {
                notificationDot.style.display = "block";
                notificationDot.innerText = response.unreadCount;
            } else {
                notificationDot.style.display = "none";
            }

            if (response.notifications?.length > 0) {
                response.notifications.forEach(notif => {
                    const li = document.createElement("li");
                    li.innerHTML = `${notif.message} <br><small>${notif.time}</small>`;
                    notificationList.appendChild(li);
                });
            } else {
                notificationList.innerHTML = "<li>No new notifications</li>";
            }
        }
    };
    xhr.send();
}

function markNotificationsRead() {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../homepage/process_notification.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById("notification-dot").style.display = "none";
        }
    };
    xhr.send("mark_read=true");
}

document.addEventListener("click", function (event) {
    const container = document.getElementById("notification-container");
    const button = document.querySelector(".notification-btn");
    if (!container.contains(event.target) && !button.contains(event.target)) {
        container.style.display = "none";
    }
});

function toggleNotifications() {
    const container = document.getElementById("notification-container");
    container.style.display = container.style.display === "block" ? "none" : "block";
}

// --- SEARCH FUNCTIONALITY ---
let searchData = [];

function initializeSearch() {
    fetch("../search/search_data.php")
        .then(res => res.json())
        .then(data => {
            searchData = [...data.users, ...data.features];
        });

    const searchInput = document.getElementById("search");
    searchInput?.addEventListener("input", liveSearch);

    document.addEventListener("click", function (event) {
        const resultsBox = document.getElementById("search-results");
        if (!resultsBox.contains(event.target) && event.target !== searchInput) {
            resultsBox.style.display = "none";
        }
    });
}

function liveSearch() {
    const input = document.getElementById("search").value.toLowerCase();
    const resultBox = document.getElementById("search-results");
    resultBox.innerHTML = "";

    if (input.length === 0) {
        resultBox.style.display = "none";
        return;
    }

    const filtered = searchData.filter(item => item.name.toLowerCase().includes(input));

    if (filtered.length === 0) {
        const noResult = document.createElement("div");
        noResult.classList.add("no-result");
        noResult.textContent = "No results found";
        noResult.style.color = "black"; // Make text visible
        resultBox.appendChild(noResult);
    } else {
        filtered.forEach(item => {
            const div = document.createElement("div");
            div.classList.add("result-item");

            const icon = document.createElement("i");
            icon.className = item.type === "user" ? "fas fa-user" : `fas fa-${item.icon}`;
            icon.style.color = "#174080";

            const text = document.createElement("span");
            text.textContent = item.name;
            text.style.marginLeft = "10px";
            text.style.color = "#333";
            text.style.textDecoration = "none";
            text.style.cursor = "pointer";

            div.appendChild(icon);
            div.appendChild(text);

            div.addEventListener("click", () => {
                if (item.type === "user") {
                    window.location.href = `../profile/profile.php?id=${item.id}`;
                } else {
                    window.location.href = item.url;
                }
            });

            resultBox.appendChild(div);
        });
    }

    resultBox.style.display = "block";
}

function toggleNotifications() {
    const container = document.getElementById("notification-container");
    const isVisible = container.style.display === "block";
    container.style.display = isVisible ? "none" : "block";

    if (!isVisible) {
        markNotificationsRead();
        fetchNotifications();
    }
}

function fetchNotifications() {
    fetch("../homepage/process_notification.php")
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById("notification-list");
            const dot = document.getElementById("notification-dot");

            // Update dot
            if (data.unreadCount > 0) {
                dot.style.display = "block";
                dot.innerText = data.unreadCount;
            } else {
                dot.style.display = "none";
            }

            // Update list
            list.innerHTML = "";
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(n => {
                    const li = document.createElement("li");
                    li.innerHTML = `${n.message}<br><small>${n.time}</small>`;
                    list.appendChild(li);
                });
            } else {
                list.innerHTML = "<li>No new notifications</li>";
            }
        });
}

function markNotificationsRead() {
    fetch("../homepage/process_notification.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "mark_read=true"
    });
}
