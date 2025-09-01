function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

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

(function () {
    'use strict';

    const data = window.dashboardData || {};
    const totals = (data.totals || {});
    const charts = (data.charts || {});

    // Update stat elements (defensive)
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }
    setText('stat-total-users', totals.totalUsers ?? 0);
    setText('stat-new-users', totals.newUsers30 ?? 0);
    setText('stat-announcements', totals.totalAnnouncements ?? 0);
    setText('stat-reports', totals.totalReports ?? 0);
    setText('stat-notifications', totals.unreadNotifications ?? 0);

    // Users line chart
    const usersCtx = document.getElementById('chart-users')?.getContext('2d');
    if (usersCtx) {
        new Chart(usersCtx, {
            type: 'line',
            data: {
                labels: charts.labelsMonths || [],
                datasets: [{
                    label: 'New users (by start_date)',
                    data: charts.usersPerMonth || [],
                    borderColor: 'rgba(54,162,235,1)',
                    backgroundColor: 'rgba(54,162,235,0.12)',
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    }

    // Reports by status - pie
    const repCtx = document.getElementById('chart-reports')?.getContext('2d');
    if (repCtx) {
        const obj = charts.reportsByStatus || {};
        const labels = Object.keys(obj);
        const values = labels.map(k => obj[k]);
        const colors = labels.map((l, i) => ['#4caf50', '#ff9800', '#f44336', '#9e9e9e'][i % 4]);
        new Chart(repCtx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                }]
            },
            options: { responsive: true }
        });
    }

    // Announcements bar chart
    const annCtx = document.getElementById('chart-announcements')?.getContext('2d');
    if (annCtx) {
        new Chart(annCtx, {
            type: 'bar',
            data: {
                labels: charts.labelsMonths || [],
                datasets: [{
                    label: 'Announcements',
                    data: charts.announcementsPerMonth || [],
                    backgroundColor: 'rgba(153,102,255,0.6)',
                    borderColor: 'rgba(153,102,255,1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    }
})();

(function () {
    if (typeof window.DASHBOARD_DATA === 'undefined') {
        console.warn('DASHBOARD_DATA not available');
        return;
    }
    const data = window.DASHBOARD_DATA.charts || {};

    // Utility to extract labels/data arrays
    function extract(series) {
        series = series || [];
        return {
            labels: series.map(r => r.label || ''),
            values: series.map(r => parseInt(r.value || 0, 10))
        };
    }

    // Roles per department - horizontal bar
    const rpd = extract(data.rolesPerDept);
    const ctxRPD = document.getElementById('rolesPerDeptChart');
    if (ctxRPD) {
        new Chart(ctxRPD, {
            type: 'bar',
            data: {
                labels: rpd.labels,
                datasets: [{
                    label: 'Roles',
                    data: rpd.values,
                    backgroundColor: rpd.labels.map((_, i) => `hsl(${(i * 40) % 360} 70% 60%)`)
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });
    }

    // Users per role - bar
    const upr = extract(data.usersPerRole);
    const ctxUPR = document.getElementById('usersPerRoleChart');
    if (ctxUPR) {
        new Chart(ctxUPR, {
            type: 'bar',
            data: {
                labels: upr.labels,
                datasets: [{
                    label: 'Assigned users',
                    data: upr.values,
                    backgroundColor: upr.labels.map((_, i) => `hsl(${(i * 60) % 360} 65% 55%)`)
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Tasks by status - doughnut
    const tbs = extract(data.tasksByStatus);
    const ctxTBS = document.getElementById('tasksByStatusChart');
    if (ctxTBS) {
        new Chart(ctxTBS, {
            type: 'doughnut',
            data: {
                labels: tbs.labels,
                datasets: [{
                    label: 'Tasks',
                    data: tbs.values,
                    backgroundColor: ['#4caf50', '#ff9800', '#f44336', '#9e9e9e']
                }]
            },
            options: { maintainAspectRatio: false }
        });
    }
})();