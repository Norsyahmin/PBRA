<?php
// PHP website skeleton with hamburger left, centered search bar, overlay sidebar, popup search with centered close button, and scroll-to-top button
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PHP Website with Sidebar</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Top bar container */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background-color: #1e3a8a;
            display: flex;
            align-items: center;
            z-index: 1000;
        }

        /* Flex layout for topbar */
        .topbar-inner {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Left section (hamburger) */
        .topbar-left {
            display: flex;
            align-items: center;
        }

        /* Hamburger container */
        .hamburger-container {
            background-color: #1e3a8a;
            border-radius: 0;
            padding: 12px 16px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .hamburger-container:hover {
            background-color: #0d47a1;
        }

        /* Hamburger icon */
        .hamburger {
            font-size: 24px;
            color: #fff;
            line-height: 1;
        }

        /* Centered search bar */
        .topbar-center {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .search-bar {
            width: 50%;
            max-width: 400px;
            padding: 8px 12px;
            border-radius: 20px;
            border: none;
            outline: none;
            font-size: 16px;
        }

        /* Right section (optional future links) */
        .topbar-right {
            min-width: 50px;
        }

        /* Sidebar (overlay style) */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #1e3a8a;
            overflow-x: hidden;
            transition: 0.3s;
            padding-top: 60px;
            z-index: 1100;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #f1f1f1;
            display: block;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .closebtn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 30px;
            color: #f1f1f1;
            cursor: pointer;
        }

        .content {
            margin-left: 0;
            padding: 80px 20px 20px;
            transition: filter 0.3s;
        }

        .blurred {
            filter: blur(5px);
        }

        .sidebar.active {
            left: 0;
        }

        #scrollTopBtn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1200;
            background-color: #1e3a8a;
            color: white;
            border: none;
            outline: none;
            width: 50px;
            height: 50px;
            border-radius: 8px;
            font-size: 22px;
            cursor: pointer;
            display: none;
            transition: background-color 0.3s, opacity 0.3s;
        }

        #scrollTopBtn:hover {
            background-color: #0d47a1;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1050;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        /* Search popup overlay (glass blur) */
        .search-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1300;
            display: none;
        }

        .search-overlay.active {
            display: flex;
        }

        /* Popup container */
        .search-popup {
            width: 80%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            padding: 10px 15px;
            display: flex;
            align-items: center;
            /* vertical center */
            justify-content: space-between;
            /* input left, close right */
        }

        .search-popup input {
            flex: 1;
            padding: 14px 18px;
            font-size: 20px;
            border-radius: 8px;
            border: none;
            outline: none;
            background: white;
        }

        /* Close button centered with input */
        .search-close {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            line-height: 1;
            margin-left: 10px;
            user-select: none;
            transition: color 0.2s;
        }

        .search-close:hover {
            color: #000;
        }
    </style>
</head>

<body>
    <!-- Top bar -->
    <div id="topbar" class="topbar">
        <div class="topbar-inner">
            <!-- Left: Hamburger -->
            <div class="topbar-left">
                <div class="hamburger-container" onclick="toggleSidebar(event)">
                    <span class="hamburger">&#9776;</span>
                </div>
            </div>
            <!-- Center: Search -->
            <div class="topbar-center">
                <input type="text" id="topSearch" class="search-bar" placeholder="Search..." onfocus="openSearch()" />
            </div>
            <!-- Right: Placeholder -->
            <div class="topbar-right"></div>
        </div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <span class="closebtn" onclick="closeSidebar()">&times;</span>
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
    </div>

    <!-- Overlay background -->
    <div id="overlay" class="overlay" onclick="closeSidebar()"></div>

    <!-- Search overlay -->
    <div id="searchOverlay" class="search-overlay" onclick="closeSearch(event)">
        <div class="search-popup" onclick="event.stopPropagation()">
            <input type="text" id="popupSearch" placeholder="Type to search..." />
            <span class="search-close" onclick="closeSearch(event)">&times;</span>
        </div>
    </div>

    <!-- Main content -->
    <div id="content" class="content">
        <h1>Welcome to My PHP Website</h1>
        <p>Now the popup search bar has the close button vertically centered with the input field.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nunc at
            facilisis tincidunt, justo erat tincidunt nulla, nec ultricies libero nulla
            nec lorem. Curabitur nec lorem vel sapien fermentum dictum. Donec vel
            tincidunt lorem. Suspendisse potenti.</p>
        <p style="height: 2000px;">Keep scrolling...</p>
    </div>

    <!-- Scroll to top button -->
    <button id="scrollTopBtn" onclick="scrollToTop()">↑</button>

    <script>
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");
        const topbar = document.getElementById("topbar");
        const scrollTopBtn = document.getElementById("scrollTopBtn");
        const content = document.getElementById("content");
        const searchOverlay = document.getElementById("searchOverlay");
        const popupSearch = document.getElementById("popupSearch");
        const topSearch = document.getElementById("topSearch");

        function toggleSidebar(event) {
            event.stopPropagation();
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
        }

        function closeSidebar() {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        }

        function openSearch() {
            searchOverlay.classList.add("active");
            content.classList.add("blurred");
            setTimeout(() => popupSearch.focus(), 100);
        }

        function closeSearch(event) {
            if (
                event.target === searchOverlay ||
                event.target.classList.contains("search-close")
            ) {
                searchOverlay.classList.remove("active");
                content.classList.remove("blurred");
                topSearch.blur();
            }
        }

        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                searchOverlay.classList.remove("active");
                content.classList.remove("blurred");
                topSearch.blur();
            }
        });

        let lastScrollTop = 0;
        window.addEventListener("scroll", function() {
            let st = window.pageYOffset || document.documentElement.scrollTop;

            if (st > lastScrollTop) {
                topbar.style.top = "-70px";
                closeSidebar();
            } else {
                topbar.style.top = "0";
            }
            lastScrollTop = st <= 0 ? 0 : st;

            if (st > 200) {
                scrollTopBtn.style.display = "block";
            } else {
                scrollTopBtn.style.display = "none";
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }
    </script>
</body>

</html>