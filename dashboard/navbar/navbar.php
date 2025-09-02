<!-- Navigation Bar Component -->
<div id="navBarComponent">
    <!-- Top bar -->
    <div id="topbar" class="topbar">
        <div class="topbar-inner">
            <!-- Left: Hamburger -->
            <div class="topbar-left">
                <div class="hamburger-container" onclick="navBar.toggleSidebar(event)">
                    <span class="hamburger">&#9776;</span>
                </div>
            </div>
            <!-- Center: Search -->
            <div class="topbar-center">
                <input type="text" id="topSearch" class="search-bar" placeholder="Search..." onfocus="navBar.openSearch()" />
            </div>
            <!-- Right: Placeholder -->
            <div class="topbar-right"></div>
        </div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <span class="closebtn" onclick="navBar.closeSidebar()">&times;</span>
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
    </div>

    <!-- Overlay background -->
    <div id="sidebarOverlay" class="overlay" onclick="navBar.closeSidebar()"></div>

    <!-- Search overlay -->
    <div id="searchOverlay" class="search-overlay" onclick="navBar.closeSearch(event)">
        <div class="search-popup" onclick="event.stopPropagation()">
            <input type="text" id="popupSearch" placeholder="Type to search..." />
            <span class="search-close" onclick="navBar.closeSearch(event)">&times;</span>
        </div>
    </div>
</div>
<!-- End Navigation Bar Component -->