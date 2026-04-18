document.addEventListener("DOMContentLoaded", function () {

    // OPEN PANEL
    window.openProfile = function () {
        document.getElementById("profilePanel").classList.add("active");
        document.getElementById("overlay").classList.add("active");
    }

    // CLOSE PANEL
    window.closeProfile = function () {
        document.getElementById("profilePanel").classList.remove("active");
        document.getElementById("overlay").classList.remove("active");
    }

    // THEME
    window.changeTheme = function (color) {
        document.documentElement.style.setProperty('--main-color', color);
        localStorage.setItem('theme', color);
    }

    // DARK MODE
    window.toggleDarkMode = function () {
        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {
            localStorage.setItem("mode", "dark");
        } else {
            localStorage.setItem("mode", "light");
        }
    }

    // LOAD SETTING
    let savedTheme = localStorage.getItem('theme');
    let savedMode = localStorage.getItem('mode');

    if (savedTheme) {
        document.documentElement.style.setProperty('--main-color', savedTheme);
    }

    if (savedMode === "dark") {
        document.body.classList.add("dark-mode");
    }

    // FIX CLICK AVATAR
    let avatar = document.querySelector(".profile-trigger");
    if (avatar) {
        avatar.addEventListener("click", function () {
            openProfile();
        });
    }

});