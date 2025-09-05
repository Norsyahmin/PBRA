// --- Scroll-to-Top Button Component Logic ---
window.scrollTopManager = (function () {
    let scrollTopBtn = null;

    function showHideButton() {
        if (!scrollTopBtn) return;
        if (window.pageYOffset > 200) {
            scrollTopBtn.style.display = "block";
        } else {
            scrollTopBtn.style.display = "none";
        }
    }

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    function init() {
        scrollTopBtn = document.getElementById("scrollTopBtn");
        if (!scrollTopBtn) {
            // No button in DOM; nothing to do.
            return;
        }
        // Ensure initial visibility is correct
        showHideButton();
        window.addEventListener("scroll", showHideButton);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    return {
        scrollToTop: scrollToTop,
    };
})();