
const userData = {
    username: "Muhamad Ali bin Haji Ahmad",
    profilePicture: "", 
};

const profilePicElement = document.getElementById("profile-pic");
if (userData.profilePicture) {
    profilePicElement.src = userData.profilePicture;
} else {
    profilePicElement.src = "images/default-profile.jpg"; 
}

document.getElementById("username").textContent = userData.username;


// Sidebar
const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const closeSidebar = document.getElementById('close-sidebar');

        // Function to toggle the sidebar and the button
        menuToggle.addEventListener('click', () => {
            console.log("Menu button clicked!");  // Debugging message
            sidebar.classList.toggle('active');
            // Change the icon based on the sidebar state
            if (sidebar.classList.contains('active')) {
                menuToggle.innerHTML = '<i class="material-icons">close</i>';
            } else {
                menuToggle.innerHTML = '<i class="material-icons">menu</i>';
            }
        });

        closeSidebar.addEventListener('click', () => {
            console.log("Close button clicked!");  // Debugging message
            sidebar.classList.remove('active');
            // Reset the menu button icon when sidebar is closed
            menuToggle.innerHTML = '<i class="material-icons">menu</i>';
        });
