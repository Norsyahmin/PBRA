// --- Scroll-to-Top Button Component Logic ---
const scrollTopManager = (function () {
    const scrollTopBtn = document.getElementById("scrollTopBtn");

    function showHideButton() {
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

    // Listen for scroll events to show/hide the button
    window.addEventListener("scroll", showHideButton);

    return {
        scrollToTop: scrollToTop,
    };
})();