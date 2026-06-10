document.addEventListener("DOMContentLoaded", function() {
    async function ucitajOcene() {
    const reviewsContainer = document.getElementById("reviewsContainer");

    if (!reviewsContainer) return;

    try {

        const response = await fetch("obrada.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                akcija: "ucitaj_ocene"
            })
        });

        const data = await response.json();

        if (data.status === "success") {

            reviewsContainer.innerHTML = "";

            data.ocene.forEach(ocena => {

                const card = document.createElement("div");
                card.className = "review-card";

                card.innerHTML = `
                    <div class="rev-header">
                        <h4>${ocena.ime}</h4>
                        <span class="rev-stars">${"⭐".repeat(parseInt(ocena.zvezdice))}</span>
                    </div>
                    <p>"${ocena.komentar}"</p>
                `;

                reviewsContainer.appendChild(card);
            });
        }

    } catch(err) {
        console.error(err);
    }
    ucitajOcene();
}

    // --- 1. RUTER LOGIKA ZA STRANICE (SPA EFEKAT) ---
    const navLinks = document.querySelectorAll(".nav-link, .browse-menu-btn");
    const pages = document.querySelectorAll(".page-section");

    navLinks.forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault();

            const targetPageId = this.getAttribute("data-target");
            if (!targetPageId) return;

            pages.forEach(page => {
                page.classList.remove("active-page");
                page.classList.add("hidden-page");
            });

            const activePage = document.getElementById(targetPageId);
            if (activePage) {
                activePage.classList.remove("hidden-page");
                activePage.classList.add("active-page");
            }

            document.querySelectorAll(".nav-link").forEach(nav => {
                nav.classList.remove("active");
                if (nav.getAttribute("data-target") === targetPageId) {
                    nav.classList.add("active");
                }
            });

            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    });

    function showMessage(element, message, type) {
        element.textContent = message;
        element.className = type;
        element.classList.remove("hidden");
    }

    // --- 2. LOGIKA ZA REZERVACIJE ---
    const reservationForm = document.getElementById("reservationForm");
    const formMessage = document.getElementById("formMessage");

    if (reservationForm && formMessage) {
        reservationForm.addEventListener("submit", async function(e) {
            e.preventDefault();

            const ime = document.getElementById("ime").value.trim();
            const gosti = document.getElementById("gosti").value;
            const datumVreme = document.getElementById("datum").value;

            if (!ime || !gosti || !datumVreme) {
                showMessage(formMessage, "Molimo popunite sva polja za rezervaciju.", "error-alert");
                return;
            }

            try {
                const response = await fetch("obrada.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        akcija: "nova_rezervacija",
                        ime,
                        gosti: parseInt(gosti, 10),
                        datum: datumVreme
                    })
                });

                const data = await response.json();
                showMessage(formMessage, data.message || "Došlo je do greške.", data.status === "success" ? "success-alert" : "error-alert");

                if (data.status === "success") {
                    reservationForm.reset();
                }
            } catch (error) {
                showMessage(formMessage, "Greška pri slanju rezervacije. Proverite server.", "error-alert");
            }
        });
    }

    // --- 3. LOGIKA ZA OCENE UŽIVO ---
    const reviewForm = document.getElementById("reviewForm");
    const reviewsContainer = document.getElementById("reviewsContainer");

    if (reviewForm && reviewsContainer) {
        reviewForm.addEventListener("submit", async function(e) {
            e.preventDefault();

            const imeGosta = document.getElementById("revName").value.trim();
            const brojZvezdica = document.getElementById("revStars").value;
            const tekstKomentara = document.getElementById("revText").value.trim();

            if (!imeGosta || !tekstKomentara) {
                return;
            }

            try {
                const response = await fetch("obrada.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        akcija: "nova_ocena",
                        ime: imeGosta,
                        zvezdice: parseInt(brojZvezdica, 10),
                        komentar: tekstKomentara
                    })
                });

                const data = await response.json();

                if (data.status === "success") {
                    const zvezdiceStars = "⭐".repeat(parseInt(brojZvezdica, 10));
                    const novaOcenaCard = document.createElement("div");
                    novaOcenaCard.className = "review-card";

                    novaOcenaCard.innerHTML = `
                        <div class="rev-header">
                            <h4>${imeGosta}</h4>
                            <span class="rev-stars">${zvezdiceStars}</span>
                        </div>
                        <p>"${tekstKomentara}"</p>
                    `;

                    await ucitajOcene();
                    reviewForm.reset();
                } else {
                    alert(data.message || "Došlo je do greške pri slanju utiska.");
                }
            } catch (error) {
                alert("Greška pri slanju utiska. Proverite server.");
            }
        });
    }
});