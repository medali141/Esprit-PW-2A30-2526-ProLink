// ===== RÈGLES =====
function checkNom(val)       { return val.trim().length >= 2; }
function checkPrenom(val)    { return val.trim().length >= 2; }
function checkEmail(val)     { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim()); }
function checkTelephone(val) { return /^\d{8}$/.test(val.trim()); }
function checkStatut(val)    { return val !== ""; }
function checkEvent(val)     { return val !== ""; }

function displayMsg(id, message, isSuccess) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = message;
        el.className = "msg " + (isSuccess ? "success" : "error");
    }
}

// ===== SUBMIT =====
document.getElementById("participationForm").addEventListener("submit", function(e) {
    e.preventDefault();
    let isValid = true;

    const eventEl = document.getElementById("id_event");
    if (eventEl) {
        if (checkEvent(eventEl.value)) { displayMsg("msg-event", "Correct !", true); }
        else { displayMsg("msg-event", "Sélectionnez un événement.", false); isValid = false; }
    }

    const nom = document.getElementById("nom").value;
    if (checkNom(nom)) { displayMsg("msg-nom", "Correct !", true); }
    else { displayMsg("msg-nom", "Nom : au moins 2 caractères.", false); isValid = false; }

    const prenom = document.getElementById("prenom").value;
    if (checkPrenom(prenom)) { displayMsg("msg-prenom", "Correct !", true); }
    else { displayMsg("msg-prenom", "Prénom : au moins 2 caractères.", false); isValid = false; }

    const email = document.getElementById("email").value;
    if (checkEmail(email)) { displayMsg("msg-email", "Correct !", true); }
    else { displayMsg("msg-email", "Email invalide.", false); isValid = false; }

    const tel = document.getElementById("telephone").value;
    if (checkTelephone(tel)) { displayMsg("msg-telephone", "Correct !", true); }
    else { displayMsg("msg-telephone", "Téléphone : 8 chiffres requis.", false); isValid = false; }

    const statutEl = document.getElementById("statut");
    if (statutEl) {
        if (checkStatut(statutEl.value)) { displayMsg("msg-statut", "Correct !", true); }
        else { displayMsg("msg-statut", "Sélectionnez un statut.", false); isValid = false; }
    }

    if (isValid) this.submit();
});

// ===== TEMPS RÉEL =====
document.getElementById("nom").addEventListener("keyup", function() {
    displayMsg("msg-nom", checkNom(this.value) ? "Valide" : "Au moins 2 caractères.", checkNom(this.value));
});
document.getElementById("prenom").addEventListener("keyup", function() {
    displayMsg("msg-prenom", checkPrenom(this.value) ? "Valide" : "Au moins 2 caractères.", checkPrenom(this.value));
});
document.getElementById("email").addEventListener("keyup", function() {
    displayMsg("msg-email", checkEmail(this.value) ? "Email valide" : "Format invalide.", checkEmail(this.value));
});
document.getElementById("telephone").addEventListener("keyup", function() {
    displayMsg("msg-telephone", checkTelephone(this.value) ? "Valide" : "8 chiffres requis.", checkTelephone(this.value));
});

const statutEl = document.getElementById("statut");
if (statutEl) {
    statutEl.addEventListener("change", function() {
        displayMsg("msg-statut", checkStatut(this.value) ? "Valide" : "Sélectionnez un statut.", checkStatut(this.value));
    });
}
const eventEl = document.getElementById("id_event");
if (eventEl) {
    eventEl.addEventListener("change", function() {
        displayMsg("msg-event", checkEvent(this.value) ? "Valide" : "Sélectionnez un événement.", checkEvent(this.value));
    });
}