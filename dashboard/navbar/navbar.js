// --- Navigation Bar Component Logic ---
const navBar = (function () {
    const sidebar = document.getElementById("sidebar");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    const topbar = document.getElementById("topbar");
    const content = document.getElementById("content");
    const searchOverlay = document.getElementById("searchOverlay");
    const popupSearch = document.getElementById("popupSearch");
    const topSearch = document.getElementById("topSearch");

    // user menu elements (may be null if component not present)
    const userMenu = document.getElementById("userMenu");
    const userDropdown = userMenu ? userMenu.querySelector(".user-dropdown") : null;

    let lastScrollTop = 0;

    function toggleSidebar(event) {
        event && event.stopPropagation && event.stopPropagation();
        if (!sidebar || !sidebarOverlay) return;
        sidebar.classList.toggle("active");
        sidebarOverlay.classList.toggle("active");
    }

    function closeSidebar() {
        if (!sidebar || !sidebarOverlay) return;
        sidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
    }

    function openSearch() {
        if (!searchOverlay || !content || !popupSearch) return;
        searchOverlay.classList.add("active");
        content.classList.add("blurred");
        setTimeout(() => popupSearch.focus(), 100);
    }

    function closeSearch(event) {
        if (!searchOverlay) return;
        // If the click is on the search overlay itself, or the close button, close it.
        if (
            event.target === searchOverlay ||
            (event.target && event.target.classList && event.target.classList.contains("search-close"))
        ) {
            searchOverlay.classList.remove("active");
            if (content) content.classList.remove("blurred");
            if (topSearch) topSearch.blur();
        }
    }

    // Toggle user dropdown menu
    function toggleUserMenu(event) {
        if (!userMenu) return;
        event && event.stopPropagation && event.stopPropagation();
        const isOpen = userMenu.classList.toggle("open");
        try { userMenu.setAttribute("aria-expanded", isOpen ? "true" : "false"); } catch (e) { }
    }

    // Close user menu
    function closeUserMenu() {
        if (!userMenu) return;
        userMenu.classList.remove("open");
        try { userMenu.setAttribute("aria-expanded", "false"); } catch (e) { }
    }

    // Close user menu on outside click
    document.addEventListener("click", function (e) {
        if (!userMenu) return;
        if (!userMenu.contains(e.target)) {
            closeUserMenu();
        }
    });

    // Keyboard escape for search and user menu
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            if (searchOverlay && searchOverlay.classList.contains("active")) {
                closeSearch({ target: searchOverlay });
            }
            if (userMenu && userMenu.classList.contains("open")) {
                closeUserMenu();
            }
        }
    });

    // Scroll behavior for topbar
    if (topbar) {
        window.addEventListener("scroll", function () {
            let st = window.pageYOffset || document.documentElement.scrollTop;
            if (st > lastScrollTop) {
                topbar.style.top = "-70px";
                closeSidebar();
            } else {
                topbar.style.top = "0";
            }
            lastScrollTop = st <= 0 ? 0 : st;
        });
    }

    return {
        toggleSidebar: toggleSidebar,
        closeSidebar: closeSidebar,
        openSearch: openSearch,
        closeSearch: closeSearch,
        toggleUserMenu: toggleUserMenu,
        closeUserMenu: closeUserMenu
    };
})();