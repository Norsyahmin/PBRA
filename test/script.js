// Toggle sidebar collapse
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

// Toggle profile dropdown
function toggleDropdown() {
    document.getElementById("dropdownMenu").classList.toggle("show");
}

// Close dropdown if clicked outside
window.addEventListener("click", function (e) {
    const profilePic = document.querySelector(".profile-pic");
    const dropdown = document.getElementById("dropdownMenu");

    if (!profilePic.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove("show");
    }
});

// Toggle collapse/expand for a single chart card
function toggleCard(header) {
    const card = header.parentElement;
    card.classList.toggle("collapsed");
}

// Collapse/Expand all chart cards
function toggleAllCards() {
    const cards = document.querySelectorAll(".chart-card");
    const btn = document.getElementById("toggleAllBtn");

    // Check if any card is expanded
    const anyExpanded = Array.from(cards).some(
        (card) => !card.classList.contains("collapsed")
    );

    if (anyExpanded) {
        // Collapse all
        cards.forEach((card) => card.classList.add("collapsed"));
        btn.textContent = "Expand All";
    } else {
        // Expand all
        cards.forEach((card) => card.classList.remove("collapsed"));
        btn.textContent = "Collapse All";
    }
}

// ✅ Dynamic chart height adjustment
function adjustChartHeight(chartId, labelsCount, minHeight = 300, perLabel = 30) {
    const canvas = document.getElementById(chartId);
    if (canvas) {
        // Calculate height: at least minHeight, otherwise scale by labels
        canvas.height = Math.max(minHeight, labelsCount * perLabel);
    }
}

// ================== Dashboard Charts ==================
(function () {
    if (typeof window.DASHBOARD_DATA === "undefined") {
        console.warn("DASHBOARD_DATA not available");
        return;
    }
    const data = window.DASHBOARD_DATA.charts || {};

    // Utility to extract labels/data arrays
    function extract(series) {
        series = series || [];
        return {
            labels: series.map((r) => r.label || ""),
            values: series.map((r) => parseInt(r.value || 0, 10)),
        };
    }

    // Roles per department - horizontal bar
    const rpd = extract(data.rolesPerDept);
    const ctxRPD = document.getElementById("rolesPerDeptChart");
    if (ctxRPD) {
        adjustChartHeight("rolesPerDeptChart", rpd.labels.length);

        new Chart(ctxRPD, {
            type: "bar",
            data: {
                labels: rpd.labels,
                datasets: [
                    {
                        label: "Roles",
                        data: rpd.values,
                        backgroundColor: rpd.labels.map(
                            (_, i) => `hsl(${(i * 40) % 360} 70% 60%)`
                        ),
                    },
                ],
            },
            options: {
                indexAxis: "y",
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } },
            },
        });
    }

    // Users per role - bar
    const upr = extract(data.usersPerRole);
    const ctxUPR = document.getElementById("usersPerRoleChart");
    if (ctxUPR) {
        adjustChartHeight("usersPerRoleChart", upr.labels.length);

        new Chart(ctxUPR, {
            type: "bar",
            data: {
                labels: upr.labels,
                datasets: [
                    {
                        label: "Assigned users",
                        data: upr.values,
                        backgroundColor: upr.labels.map(
                            (_, i) => `hsl(${(i * 60) % 360} 65% 55%)`
                        ),
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    // Tasks by status - doughnut
    const tbs = extract(data.tasksByStatus);
    const ctxTBS = document.getElementById("tasksByStatusChart");
    if (ctxTBS) {
        adjustChartHeight("tasksByStatusChart", tbs.labels.length, 250, 40);

        new Chart(ctxTBS, {
            type: "doughnut",
            data: {
                labels: tbs.labels,
                datasets: [
                    {
                        label: "Tasks",
                        data: tbs.values,
                        backgroundColor: ["#4caf50", "#ff9800", "#f44336", "#9e9e9e"],
                    },
                ],
            },
            options: { maintainAspectRatio: false },
        });
    }
})();