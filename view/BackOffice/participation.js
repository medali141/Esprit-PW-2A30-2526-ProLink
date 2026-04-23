// ==================== FONCTIONS DE VALIDATION ====================

function checkNom(val) {
    return val.trim().length > 0;
}

function checkPrenom(val) {
    return val.trim().length > 0;
}

function checkEmail(val) {
    // doit contenir @ et .
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim());
}

function checkTelephone(val) {
    // exactement 8 chiffres
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

// ==================== VALIDATION AU SUBMIT ====================

document.getElementById("participationForm").addEventListener("submit", function(e) {
    e.preventDefault();
    let isValid = true;

    // Événement (select) — présent seulement dans ajout backoffice
    const eventEl = document.getElementById("id_event");
    if (eventEl && eventEl.tagName === "SELECT") {
        if (checkEvent(eventEl.value)) {
            displayMsg("msg-event", "Correct !", true);
        } else {
            displayMsg("msg-event", "Sélectionnez un événement.", false);
            isValid = false;
        }
    }

    // Nom
    const nomVal = document.getElementById("nom").value;
    if (checkNom(nomVal)) {
        displayMsg("msg-nom", "Correct !", true);
    } else {
        displayMsg("msg-nom", "Le nom ne peut pas être vide.", false);
        isValid = false;
    }

    // Prénom
    const prenomVal = document.getElementById("prenom").value;
    if (checkPrenom(prenomVal)) {
        displayMsg("msg-prenom", "Correct !", true);
    } else {
        displayMsg("msg-prenom", "Le prénom ne peut pas être vide.", false);
        isValid = false;
    }

    // Email
    const emailVal = document.getElementById("email").value;
    if (checkEmail(emailVal)) {
        displayMsg("msg-email", "Correct !", true);
    } else {
        displayMsg("msg-email", "Email invalide (doit contenir @ et .).", false);
        isValid = false;
    }

    // Téléphone
    const telVal = document.getElementById("telephone").value;
    if (checkTelephone(telVal)) {
        displayMsg("msg-telephone", "Correct !", true);
    } else {
        displayMsg("msg-telephone", "Téléphone invalide (8 chiffres requis).", false);
        isValid = false;
    }

    // Statut — présent seulement dans le backoffice
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
        this.submit();
    }
});

// ==================== VALIDATION EN TEMPS RÉEL ====================

document.getElementById("nom").addEventListener("keyup", function() {
    const ok = checkNom(this.value);
    displayMsg("msg-nom", ok ? "Valide" : "Le nom ne peut pas être vide.", ok);
});

document.getElementById("prenom").addEventListener("keyup", function() {
    const ok = checkPrenom(this.value);
    displayMsg("msg-prenom", ok ? "Valide" : "Le prénom ne peut pas être vide.", ok);
});

document.getElementById("email").addEventListener("keyup", function() {
    const ok = checkEmail(this.value);
    displayMsg("msg-email", ok ? "Email valide" : "Doit contenir @ et .", ok);
});

document.getElementById("telephone").addEventListener("keyup", function() {
    const ok = checkTelephone(this.value);
    displayMsg("msg-telephone", ok ? "Valide" : "8 chiffres requis.", ok);
});

const statutEl = document.getElementById("statut");
if (statutEl) {
    statutEl.addEventListener("change", function() {
        const ok = checkStatut(this.value);
        displayMsg("msg-statut", ok ? "Statut valide" : "Sélectionnez un statut.", ok);
    });
}

const eventEl = document.getElementById("id_event");
if (eventEl && eventEl.tagName === "SELECT") {
    eventEl.addEventListener("change", function() {
        const ok = checkEvent(this.value);
        displayMsg("msg-event", ok ? "Événement valide" : "Sélectionnez un événement.", ok);
    });
}