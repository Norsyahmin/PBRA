document.addEventListener("DOMContentLoaded", function () {
    fetchNotifications();
    initializeSearch();

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

    // Hide notifications when clicking outside
    document.addEventListener("click", function (event) {
        const container = document.getElementById("notification-container");
        const button = document.querySelector(".notification-btn");
        if (container && button && !container.contains(event.target) && !button.contains(event.target)) {
            container.style.display = "none";
        }
    });
});

// --- NOTIFICATIONS ---
function fetchNotifications() {
    fetch("../homepage/process_notification.php")
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById("notification-list");
            const dot = document.getElementById("notification-dot");

            // Update dot
            if (dot) {
                if (data.unreadCount > 0) {
                    dot.style.display = "block";
                    dot.innerText = data.unreadCount;
                } else {
                    dot.style.display = "none";
                }
            }

            // Update list
            if (list) {
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
            }
        })
        .catch(e => console.error("Notification fetch error:", e));
}

function markNotificationsRead() {
    fetch("../homepage/process_notification.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "mark_read=true"
    }).then(() => {
        const dot = document.getElementById("notification-dot");
        if (dot) dot.style.display = "none";
    });
}

function toggleNotifications() {
    const container = document.getElementById("notification-container");
    if (!container) return;
    const isVisible = container.style.display === "block";
    container.style.display = isVisible ? "none" : "block";

    if (!isVisible) {
        markNotificationsRead();
        fetchNotifications();
    }
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
        if (resultsBox && !resultsBox.contains(event.target) && event.target !== searchInput) {
            resultsBox.style.display = "none";
        }
    });
}

function liveSearch() {
    const searchInput = document.getElementById("search");
    const resultBox = document.getElementById("search-results");
    if (!searchInput || !resultBox) return;

    const input = searchInput.value.toLowerCase();
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
        noResult.style.color = "black";
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