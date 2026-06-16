document.addEventListener("DOMContentLoaded", function() {
    // TEST: Check if server is responding
    async function testServer() {
        try {
            const response = await fetch("obrada.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ akcija: "test" })
            });
            const text = await response.text();
            console.log("SERVER TEST:", text);
        } catch (e) {
            console.error("SERVER TEST FAILED:", e);
        }
    }
    
    // Run test immediately
    testServer();

    async function ucitajOcene() {
        const reviewsContainer = document.getElementById("reviewsContainer");
        if (!reviewsContainer) return;

        try {
            const response = await fetch("obrada.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ akcija: "ucitaj_ocene" })
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
    }

    // Postavi minimalnu vrednost datuma na danasnji dan
    const datumInput = document.getElementById("datum");
    if (datumInput) {
        const today = new Date();
        today.setMinutes(today.getMinutes() - today.getTimezoneOffset());
        datumInput.min = today.toISOString().slice(0, 16);
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

            if (targetPageId === "ocene") {
                ucitajOcene();
            }

            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    });

    function showMessage(element, message, type) {
        element.textContent = message;
        element.className = "";
        element.classList.add(type);
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
            const dateWarning = document.getElementById("dateWarning");

            if (!ime || !gosti || !datumVreme) {
                showMessage(formMessage, "Molimo popunite sva polja za rezervaciju.", "error-alert");
                return;
            }

            const selectedDate = new Date(datumVreme);
            const now = new Date();
            if (selectedDate <= now) {
                dateWarning.style.display = "block";
                showMessage(formMessage, "Molimo izaberite budući datum i vreme.", "error-alert");
                return;
            }
            dateWarning.style.display = "none";

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

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const responseText = await response.text();
                const data = JSON.parse(responseText);
                
                showMessage(formMessage, data.message || "Došlo je do greške.", data.status === "success" ? "success-alert" : "error-alert");

                if (data.status === "success") {
                    reservationForm.reset();
                }
            } catch (error) {
                console.error("Full error:", error);
                showMessage(formMessage, "Greška pri slanju rezervacije: " + error.message, "error-alert");
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

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.status === "success") {
                    await ucitajOcene();
                    reviewForm.reset();
                } else {
                    alert(data.message || "Došlo je do greške pri slanju utiska.");
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Greška pri slanju utiska. Proverite server: " + error.message);
            }
        });
    }

    // Učitaj ocene iz baze kada se stranica učita
    ucitajOcene();
});