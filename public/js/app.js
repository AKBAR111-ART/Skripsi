// ======================
// FOOTBAR NAVIGATION
// ======================
const links = document.querySelectorAll(".footbar a");
const indicator = document.querySelector(".indicator");

function moveIndicator(el) {
    if (!el || !indicator) return; // Cegah error jika element tidak ada
    
    const rect = el.getBoundingClientRect();
    const parent = el.parentElement.getBoundingClientRect();

    indicator.style.left = rect.left - parent.left + rect.width / 2 - 27 + "px";
}

// Event listener untuk link footbar
if (links.length > 0) {
    links.forEach(link => {
        link.addEventListener("click", function (e) {
            // e.preventDefault();
            links.forEach(l => l.classList.remove("active"));
            this.classList.add("active");
            moveIndicator(this);
        });
    });
}

// Set indicator awal jika ada active link
document.addEventListener("DOMContentLoaded", () => {
    const active = document.querySelector(".footbar a.active");
    if (active && indicator) moveIndicator(active);
});

// ======================
// ANIMASI PAKAN JATUH & UDANG
// ======================
document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".animated-bg");
    
    // Jika container tidak ada, hentikan eksekusi (biar tidak error)
    if (!container) {
        console.warn("Element .animated-bg tidak ditemukan di halaman ini");
        return;
    }

    // ======================
    // BUAT PAKAN (FEED)
    // ======================
    let feedInterval = null;
    
    function createFeed() {
        const feed = document.createElement("div");
        feed.classList.add("feed");

        feed.x = Math.random() * window.innerWidth;
        feed.y = 0;

        feed.style.left = feed.x + "px";
        feed.style.top = feed.y + "px";

        container.appendChild(feed);

        // Animasi jatuh
        let fall = setInterval(() => {
            feed.y += 2;
            feed.style.top = feed.y + "px";

            if (feed.y > window.innerHeight) {
                if (feed.remove) feed.remove();
                clearInterval(fall);
            }
        }, 20);
    }

    // Jalankan feed interval (hanya jika container ada)
    feedInterval = setInterval(createFeed, 800);

    // ======================
    // INIT UDANG (SHRIMP)
    // ======================
    const shrimps = document.querySelectorAll(".shrimp");

    if (shrimps.length > 0) {
        shrimps.forEach(shrimp => {
            shrimp.x = Math.random() * window.innerWidth;
            shrimp.y = Math.random() * window.innerHeight;

            shrimp.style.left = shrimp.x + "px";
            shrimp.style.top = shrimp.y + "px";
        });

        // ======================
        // AI LOOP - UDANG MAKAN
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
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < minDist) {
                        minDist = dist;
                        closest = feed;
                    }
                });

                if (!closest) return;

                // Gerak ke target
                let dx = closest.x - shrimp.x;
                let dy = closest.y - shrimp.y;

                shrimp.x += dx * 0.02;
                shrimp.y += dy * 0.02;

                shrimp.style.left = shrimp.x + "px";
                shrimp.style.top = shrimp.y + "px";

                // Rotasi arah
                let angle = Math.atan2(dy, dx) * 180 / Math.PI;
                shrimp.style.transform = `rotate(${angle}deg)`;

                // Makan (jika jarak cukup dekat)
                if (minDist < 20) {
                    if (closest.remove) closest.remove();
                }
            });
        }, 50);
    }
});
