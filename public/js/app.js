const links = document.querySelectorAll(".footbar a");
const indicator = document.querySelector(".indicator");

function moveIndicator(el) {
    const rect = el.getBoundingClientRect();
    const parent = el.parentElement.getBoundingClientRect();

    indicator.style.left =
        rect.left - parent.left + rect.width / 2 - 27 + "px";
}

links.forEach(link => {
    link.addEventListener("click", function () {
        links.forEach(l => l.classList.remove("active"));
        this.classList.add("active");

        moveIndicator(this);
    });
});

// load pertama
const active = document.querySelector(".footbar a.active");
if (active) moveIndicator(active);