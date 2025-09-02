// --- Navigation Bar Component Logic ---
const navBar = (function () {
    const sidebar = document.getElementById("sidebar");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    const topbar = document.getElementById("topbar");
    const content = document.getElementById("content");
    const searchOverlay = document.getElementById("searchOverlay");
    const popupSearch = document.getElementById("popupSearch");
    const topSearch = document.getElementById("topSearch");

    let lastScrollTop = 0;

    function toggleSidebar(event) {
        event.stopPropagation();
        sidebar.classList.toggle("active");
        sidebarOverlay.classList.toggle("active");
    }

    function closeSidebar() {
        sidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
    }

    function openSearch() {
        searchOverlay.classList.add("active");
        content.classList.add("blurred");
        setTimeout(() => popupSearch.focus(), 100);
    }

    function closeSearch(event) {
        // If the click is on the search overlay itself, or the close button, close it.
        // We stop propagation for clicks *inside* search-popup to prevent closing when interacting with the input.
        if (
            event.target === searchOverlay ||
            event.target.classList.contains("search-close")
        ) {
            searchOverlay.classList.remove("active");
            content.classList.remove("blurred");
            topSearch.blur(); // Remove focus from the top search bar
        }
    }


    // Keyboard escape for search
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && searchOverlay.classList.contains("active")) {
            // Simulate clicking the overlay to close
            closeSearch({
                target: searchOverlay
            });
        }
    });

    // Scroll behavior for topbar
    window.addEventListener("scroll", function () {
        let st = window.pageYOffset || document.documentElement.scrollTop;

        if (st > lastScrollTop) {
            // Scrolling down
            topbar.style.top = "-70px"; // Hide topbar (adjust if your topbar height is different)
            closeSidebar(); // Also close sidebar if open for better UX
        } else {
            // Scrolling up
            topbar.style.top = "0"; // Show topbar
        }
        lastScrollTop = st <= 0 ? 0 : st;
    });

    return {
        toggleSidebar: toggleSidebar,
        closeSidebar: closeSidebar,
        openSearch: openSearch,
        closeSearch: closeSearch,
    };
})();