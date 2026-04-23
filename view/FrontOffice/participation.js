// ==================== FONCTIONS DE VALIDATION (FrontOffice) ====================

function checkNom(val)       { return val.trim().length > 0; }
function checkPrenom(val)    { return val.trim().length > 0; }
function checkEmail(val)     { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim()); }
function checkTelephone(val) { return /^\d{8}$/.test(val.trim()); }

function displayMsg(id, message, isSuccess) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = message;
        el.className = "msg " + (isSuccess ? "success" : "error");
    }
}

// ==================== VÉRIFICATION EXISTENCE (via serveur) ====================

/**
 * Vérifie si une valeur existe déjà en base de données.
 * @param {string} champ  - "email" ou "telephone"
 * @param {string} valeur - la valeur à vérifier
 * @returns {Promise<boolean>} - true si elle existe déjà
 */
async function checkExistence(champ, valeur) {
    try {
        const response = await fetch("check_existence.php", {   // ← adapte l'URL ici
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ champ, valeur })
        });
        const data = await response.json();
        return data.exists === true;   // le serveur doit renvoyer { "exists": true/false }
    } catch (err) {
        console.error("Erreur vérification existence :", err);
        return false;  // en cas d'erreur réseau, on laisse passer
    }
}

// ==================== VALIDATION AU SUBMIT ====================

document.getElementById("participationForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    let isValid = true;

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

    // --- Email : format + existence ---
    const emailVal = document.getElementById("email").value;
    if (!checkEmail(emailVal)) {
        displayMsg("msg-email", "Email invalide (doit contenir @ et .).", false);
        isValid = false;
    } else {
        displayMsg("msg-email", "Vérification en cours...", true);
        const emailExiste = await checkExistence("email", emailVal.trim());
        if (emailExiste) {
            displayMsg("msg-email", "Cet email est déjà utilisé.", false);
            isValid = false;
        } else {
            displayMsg("msg-email", "Correct !", true);
        }
    }

    // --- Téléphone : format + existence ---
    const telVal = document.getElementById("telephone").value;
    if (!checkTelephone(telVal)) {
        displayMsg("msg-telephone", "Téléphone invalide (8 chiffres requis).", false);
        isValid = false;
    } else {
        displayMsg("msg-telephone", "Vérification en cours...", true);
        const telExiste = await checkExistence("telephone", telVal.trim());
        if (telExiste) {
            displayMsg("msg-telephone", "Ce numéro est déjà utilisé.", false);
            isValid = false;
        } else {
            displayMsg("msg-telephone", "Correct !", true);
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

// Vérification existence en temps réel sur email (après que le champ perd le focus)
document.getElementById("email").addEventListener("blur", async function() {
    const val = this.value;
    if (!checkEmail(val)) {
        displayMsg("msg-email", "Doit contenir @ et .", false);
        return;
    }
    displayMsg("msg-email", "Vérification...", true);
    const existe = await checkExistence("email", val.trim());
    displayMsg("msg-email", existe ? "Cet email est déjà utilisé." : "Email valide", !existe);
});

document.getElementById("email").addEventListener("keyup", function() {
    const ok = checkEmail(this.value);
    if (!ok) displayMsg("msg-email", "Doit contenir @ et .", false);
});

// Vérification existence en temps réel sur téléphone (après que le champ perd le focus)
document.getElementById("telephone").addEventListener("blur", async function() {
    const val = this.value;
    if (!checkTelephone(val)) {
        displayMsg("msg-telephone", "8 chiffres requis.", false);
        return;
    }
    displayMsg("msg-telephone", "Vérification...", true);
    const existe = await checkExistence("telephone", val.trim());
    displayMsg("msg-telephone", existe ? "Ce numéro est déjà utilisé." : "Valide", !existe);
});

document.getElementById("telephone").addEventListener("keyup", function() {
    const ok = checkTelephone(this.value);
    if (!ok) displayMsg("msg-telephone", "8 chiffres requis.", false);
});