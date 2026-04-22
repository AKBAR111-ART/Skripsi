document.addEventListener("DOMContentLoaded", () => {

    const cards = document.querySelectorAll('.card-week');
    const nextBtn = document.getElementById("nextBtn");
    const prevBtn = document.getElementById("prevBtn");

    const detailSheet = document.getElementById("detailSheet");
    const closeSheet = document.getElementById("closeSheet");

    const dayTitle = document.getElementById("dayTitle");
    const dataTable = document.getElementById("dataTable");
    const feedList = document.getElementById("feedList");

    let currentIndex = 0;

    /* =========================
       SLIDER BUTTON
    ========================= */
    function scrollToCard(index) {
        cards[index].scrollIntoView({
            behavior: "smooth",
            inline: "center"
        });
    }

    nextBtn.onclick = () => {
        if (currentIndex < cards.length - 1) {
            currentIndex++;
            scrollToCard(currentIndex);
        }
    };

    prevBtn.onclick = () => {
        if (currentIndex > 0) {
            currentIndex--;
            scrollToCard(currentIndex);
        }
    };

    /* =========================
       TOGGLE WEEK (ANTI BUG)
    ========================= */
    document.querySelectorAll('.week-header').forEach(header => {

        header.addEventListener('click', function () {

            const parent = this.closest('.card-week');
            const content = parent.querySelector('.week-days');

            // tutup semua card lain (biar clean)
            document.querySelectorAll('.week-days').forEach(el => {
                if (el !== content) el.style.height = "0px";
            });

            // toggle
            if (content.style.height && content.style.height !== "0px") {
                content.style.height = content.scrollHeight + "px";
                requestAnimationFrame(() => {
                    content.style.height = "0px";
                });
            } else {
                content.style.height = content.scrollHeight + "px";
            }

        });

    });

    /* =========================
       OPEN DETAIL (PER MENIT)
    ========================= */
    document.querySelectorAll('.card-day').forEach(day => {

        day.onclick = function () {

            let dayName = this.dataset.day;
            let week = this.dataset.week;

            detailSheet.classList.add('active');

            // sembunyikan tombol
            nextBtn.style.display = "none";
            prevBtn.style.display = "none";

            dayTitle.innerText = `Minggu ${week} - ${dayName}`;

            /* =========================
               GENERATE DATA 10 DETIK
            ========================= */
            let rawData = [];

            for (let i = 0; i < 360; i++) {
                rawData.push({
                    time: i * 10,
                    ph: 7 + Math.random(),
                    turb: 10 + Math.random() * 3
                });
            }

            /* =========================
               RATA-RATA PER MENIT
            ========================= */
            let perMinuteData = [];

            for (let i = 0; i < rawData.length; i += 6) {

                let slice = rawData.slice(i, i + 6);

                let avgPH = slice.reduce((sum, d) => sum + d.ph, 0) / slice.length;
                let avgTurb = slice.reduce((sum, d) => sum + d.turb, 0) / slice.length;

                let minute = Math.floor(i / 6);
                let hour = Math.floor(minute / 60);
                let min = minute % 60;

                let timeLabel =
                    String(hour).padStart(2, '0') + ":" +
                    String(min).padStart(2, '0');

                perMinuteData.push({
                    time: timeLabel,
                    ph: avgPH.toFixed(2),
                    turb: avgTurb.toFixed(2)
                });
            }

            /* =========================
               RENDER TABLE
            ========================= */
            let html = "";

            perMinuteData.forEach(d => {
                html += `
                    <tr>
                        <td>${d.time}</td>
                        <td>${d.ph}</td>
                        <td>${d.turb}</td>
                    </tr>
                `;
            });

            dataTable.innerHTML = html;

            /* =========================
               FEED DATA
            ========================= */
            feedList.innerHTML = `
                <div class="feed-item">08:00 - 0.2 kg</div>
                <div class="feed-item">12:00 - 0.3 kg</div>
                <div class="feed-item">16:00 - 0.25 kg</div>
                <div class="feed-item">20:00 - 0.2 kg</div>
            `;
        };

    });

    /* =========================
       CLOSE
    ========================= */
    closeSheet.onclick = () => {
        detailSheet.classList.remove('active');

        nextBtn.style.display = "flex";
        prevBtn.style.display = "flex";
    };

});
feather.replace();