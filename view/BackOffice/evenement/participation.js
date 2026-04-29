// ==================== FONCTIONS DE VALIDATION ====================

function checkNom(val) {
    return val.trim().length > 0;
}

function checkPrenom(val) {
    return val.trim().length > 0;
}

function checkEmail(val) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim());
}

function checkTelephone(val) {
    return /^\d{8}$/.test(val.trim());
}

function checkStatut(val) {
    return val !== "";
}

function checkEvent(val) {
    return val !== "";
}

function displayMsg(id, message, isSuccess) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = message;
        el.className = "msg " + (isSuccess ? "success" : "error");
    }
}

(function () {
    const participationForm = document.getElementById("participationForm");
    if (!participationForm) {
        return;
    }

    participationForm.addEventListener("submit", function (e) {
        e.preventDefault();
        let isValid = true;

        const eventEl = document.getElementById("id_event");
        if (eventEl && eventEl.tagName === "SELECT") {
            if (checkEvent(eventEl.value)) {
                displayMsg("msg-event", "Correct !", true);
            } else {
                displayMsg("msg-event", "Sélectionnez un événement.", false);
                isValid = false;
            }
        }

        const nomVal = document.getElementById("nom").value;
        if (checkNom(nomVal)) {
            displayMsg("msg-nom", "Correct !", true);
        } else {
            displayMsg("msg-nom", "Le nom ne peut pas être vide.", false);
            isValid = false;
        }

        const prenomVal = document.getElementById("prenom").value;
        if (checkPrenom(prenomVal)) {
            displayMsg("msg-prenom", "Correct !", true);
        } else {
            displayMsg("msg-prenom", "Le prénom ne peut pas être vide.", false);
            isValid = false;
        }

        const emailVal = document.getElementById("email").value;
        if (checkEmail(emailVal)) {
            displayMsg("msg-email", "Correct !", true);
        } else {
            displayMsg("msg-email", "Email invalide (doit contenir @ et .).", false);
            isValid = false;
        }

        const telVal = document.getElementById("telephone").value;
        if (checkTelephone(telVal)) {
            displayMsg("msg-telephone", "Correct !", true);
        } else {
            displayMsg("msg-telephone", "Téléphone invalide (8 chiffres requis).", false);
            isValid = false;
        }

        const statutEl = document.getElementById("statut");
        if (statutEl) {
            if (checkStatut(statutEl.value)) {
                displayMsg("msg-statut", "Correct !", true);
            } else {
                displayMsg("msg-statut", "Sélectionnez un statut.", false);
                isValid = false;
            }
        }

        if (isValid) {
            participationForm.submit();
        }
    });

    const nomI = document.getElementById("nom");
    const prenomI = document.getElementById("prenom");
    const emailI = document.getElementById("email");
    const telI = document.getElementById("telephone");
    if (nomI) nomI.addEventListener("keyup", function () {
        const ok = checkNom(this.value);
        displayMsg("msg-nom", ok ? "Valide" : "Le nom ne peut pas être vide.", ok);
    });
    if (prenomI) prenomI.addEventListener("keyup", function () {
        const ok = checkPrenom(this.value);
        displayMsg("msg-prenom", ok ? "Valide" : "Le prénom ne peut pas être vide.", ok);
    });
    if (emailI) emailI.addEventListener("keyup", function () {
        const ok = checkEmail(this.value);
        displayMsg("msg-email", ok ? "Email valide" : "Doit contenir @ et .", ok);
    });
    if (telI) telI.addEventListener("keyup", function () {
        const ok = checkTelephone(this.value);
        displayMsg("msg-telephone", ok ? "Valide" : "8 chiffres requis.", ok);
    });

    const st = document.getElementById("statut");
    if (st) {
        st.addEventListener("change", function () {
            const ok = checkStatut(this.value);
            displayMsg("msg-statut", ok ? "Statut valide" : "Sélectionnez un statut.", ok);
        });
    }

    const eventSel = document.getElementById("id_event");
    if (eventSel && eventSel.tagName === "SELECT") {
        eventSel.addEventListener("change", function () {
            const ok = checkEvent(this.value);
            displayMsg("msg-event", ok ? "Événement valide" : "Sélectionnez un événement.", ok);
        });
    }
})();
