document.addEventListener("DOMContentLoaded", () => {

    // animasi hover card
    const cards = document.querySelectorAll(".card");

    cards.forEach(card => {
        card.addEventListener("mouseenter", () => {
            card.style.transform = "translateY(-5px)";
            card.style.transition = "0.3s";
        });

        card.addEventListener("mouseleave", () => {
            card.style.transform = "translateY(0)";
        });
    });

    // tombol save
    document.querySelector(".btn-save").addEventListener("click", () => {
        alert("Pengaturan berhasil disimpan!");
    });

});