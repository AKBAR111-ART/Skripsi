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
document.addEventListener("DOMContentLoaded", () => {

    const container = document.querySelector(".animated-bg");

    // ======================
    // BUAT PAKAN
    // ======================
    function createFeed() {
        const feed = document.createElement("div");
        feed.classList.add("feed");

        feed.x = Math.random() * window.innerWidth;
        feed.y = 0;

        feed.style.left = feed.x + "px";
        feed.style.top = feed.y + "px";

        container.appendChild(feed);

        // jatuh
        let fall = setInterval(() => {
            feed.y += 2;
            feed.style.top = feed.y + "px";

            if (feed.y > window.innerHeight) {
                feed.remove();
                clearInterval(fall);
            }
        }, 20);
    }

    setInterval(createFeed, 800);


    // ======================
    // INIT UDANG
    // ======================
    const shrimps = document.querySelectorAll(".shrimp");

    shrimps.forEach(shrimp => {
        shrimp.x = Math.random() * window.innerWidth;
        shrimp.y = Math.random() * window.innerHeight;

        shrimp.style.left = shrimp.x + "px";
        shrimp.style.top = shrimp.y + "px";
    });


    // ======================
    // AI LOOP 🔥
    // ======================
    setInterval(() => {

        const feeds = document.querySelectorAll(".feed");

        shrimps.forEach(shrimp => {

            if (feeds.length === 0) return;

            let closest = null;
            let minDist = 999999;

            feeds.forEach(feed => {
                const dx = feed.x - shrimp.x;
                const dy = feed.y - shrimp.y;
                const dist = Math.sqrt(dx*dx + dy*dy);

                if (dist < minDist) {
                    minDist = dist;
                    closest = feed;
                }
            });

            if (!closest) return;

            // GERAK KE TARGET
            let dx = closest.x - shrimp.x;
            let dy = closest.y - shrimp.y;

            shrimp.x += dx * 0.02;
            shrimp.y += dy * 0.02;

            shrimp.style.left = shrimp.x + "px";
            shrimp.style.top = shrimp.y + "px";

            // ROTASI ARAH
            let angle = Math.atan2(dy, dx) * 180 / Math.PI;
            shrimp.style.transform = `rotate(${angle}deg)`;

            // MAKAN 🔥
            if (minDist < 20) {
                closest.remove();
            }

        });

    }, 50);

});
// load pertama
const active = document.querySelector(".footbar a.active");
if (active) moveIndicator(active);