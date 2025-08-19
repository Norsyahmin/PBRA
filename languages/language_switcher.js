document.addEventListener('DOMContentLoaded', function () {
    const languageTrigger = document.getElementById('languageTrigger');
    const languageDropdown = document.getElementById('languageDropdown');

    if (languageTrigger && languageDropdown) {
        languageTrigger.addEventListener('click', function (event) {
            languageDropdown.classList.toggle('show');
            event.stopPropagation(); // Prevent click from bubbling up and closing immediately
        });

        // Close dropdown if clicked outside
        document.addEventListener('click', function (event) {
            if (!languageDropdown.contains(event.target) && !languageTrigger.contains(event.target)) {
                languageDropdown.classList.remove('show');
            }
        });
    }
});