// ==================== FONCTIONS DE VALIDATION ====================

function checkTitre(val) {
    return val.trim().length >= 3;
}

function checkDescription(val) {
    return val.trim().length >= 10;
}

function checkType(val) {
    return val !== "";
}

function checkDateDebut(val) {
    if (!val) return false;
    const inputDate = new Date(val);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return inputDate > today;
}

function checkDateFin(dateDebut, dateFin) {
    if (!dateDebut || !dateFin) return false;
    return new Date(dateFin) > new Date(dateDebut);
}

function checkLieu(val) {
    return val.trim().length >= 3;
}

function checkCapacite(val) {
    return !isNaN(val) && parseInt(val) >= 1;
}

function checkStatut(val) {
    return val !== "";
}

function checkIdOrg(val) {
    return !isNaN(val) && parseInt(val) >= 1;
}

function displayMsg(id, message, isSuccess) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = message;
        el.className = "msg " + (isSuccess ? "success" : "error");
    }
}


// ==================== PARTIE 1 : VALIDATION GLOBALE (alert) ====================

function validerFormulaire() {
    let errors = [];

    const titre       = document.getElementById("titre_event").value;
    const description = document.getElementById("description_event").value;
    const type        = document.getElementById("type_event").value;
    const dateDebut   = document.getElementById("date_debut").value;
    const dateFin     = document.getElementById("date_fin").value;
    const lieu        = document.getElementById("lieu_event").value;
    const capacite    = document.getElementById("capacite_max").value;
    const statut      = document.getElementById("statut").value;
    const idOrg       = document.getElementById("id_org").value;

    if (!checkTitre(titre))
        errors.push("Le titre doit contenir au moins 3 caractères.");

    if (!checkDescription(description))
        errors.push("La description doit contenir au moins 10 caractères.");

    if (!checkType(type))
        errors.push("Le type d'événement est obligatoire.");

    if (!checkDateDebut(dateDebut))
        errors.push("La date de début doit être une date future.");

    if (!checkDateFin(dateDebut, dateFin))
        errors.push("La date de fin doit être postérieure à la date de début.");

    if (!checkLieu(lieu))
        errors.push("Le lieu doit contenir au moins 3 caractères.");

    if (!checkCapacite(capacite))
        errors.push("La capacité maximale doit être un entier positif.");

    if (!checkStatut(statut))
        errors.push("Le statut est obligatoire.");

    if (!checkIdOrg(idOrg))
        errors.push("L'ID organisateur doit être un entier positif.");

    if (errors.length > 0) {
        alert("Erreurs :\n" + errors.join("\n"));
    } else {
        alert("Formulaire valide !");
    }
}


// ==================== PARTIE 2 : VALIDATION AU SUBMIT (messages inline) ====================

document.getElementById("eventForm").addEventListener("submit", function(e) {
    e.preventDefault();
    let isValid = true;

    const titreVal = document.getElementById("titre_event").value;
    if (checkTitre(titreVal)) {
        displayMsg("msg-titre", "Correct !", true);
    } else {
        displayMsg("msg-titre", "Le titre doit contenir au moins 3 caractères.", false);
        isValid = false;
    }

    const descVal = document.getElementById("description_event").value;
    if (checkDescription(descVal)) {
        displayMsg("msg-description", "Correct !", true);
    } else {
        displayMsg("msg-description", "La description doit contenir au moins 10 caractères.", false);
        isValid = false;
    }

    const typeVal = document.getElementById("type_event").value;
    if (checkType(typeVal)) {
        displayMsg("msg-type", "Correct !", true);
    } else {
        displayMsg("msg-type", "Sélectionnez un type d'événement.", false);
        isValid = false;
    }

    const dateDebutVal = document.getElementById("date_debut").value;
    if (checkDateDebut(dateDebutVal)) {
        displayMsg("msg-date-debut", "Correct !", true);
    } else {
        displayMsg("msg-date-debut", "La date de début doit être une date future.", false);
        isValid = false;
    }

    const dateFinVal = document.getElementById("date_fin").value;
    if (checkDateFin(dateDebutVal, dateFinVal)) {
        displayMsg("msg-date-fin", "Correct !", true);
    } else {
        displayMsg("msg-date-fin", "La date de fin doit être postérieure à la date de début.", false);
        isValid = false;
    }

    const lieuVal = document.getElementById("lieu_event").value;
    if (checkLieu(lieuVal)) {
        displayMsg("msg-lieu", "Correct !", true);
    } else {
        displayMsg("msg-lieu", "Le lieu doit contenir au moins 3 caractères.", false);
        isValid = false;
    }

    const capaciteVal = document.getElementById("capacite_max").value;
    if (checkCapacite(capaciteVal)) {
        displayMsg("msg-capacite", "Correct !", true);
    } else {
        displayMsg("msg-capacite", "La capacité maximale doit être un entier positif.", false);
        isValid = false;
    }

    const statutVal = document.getElementById("statut").value;
    if (checkStatut(statutVal)) {
        displayMsg("msg-statut", "Correct !", true);
    } else {
        displayMsg("msg-statut", "Sélectionnez un statut.", false);
        isValid = false;
    }

    const idOrgVal = document.getElementById("id_org").value;
    if (checkIdOrg(idOrgVal)) {
        displayMsg("msg-idorg", "Correct !", true);
    } else {
        displayMsg("msg-idorg", "L'ID organisateur doit être un entier positif.", false);
        isValid = false;
    }

    if (isValid) {
        alert("Événement ajouté avec succès !");
        document.getElementById("eventForm").submit();
    }
});


// ==================== PARTIE 3 : VALIDATION EN TEMPS RÉEL ====================

document.getElementById("titre_event").addEventListener("keyup", function() {
    if (checkTitre(this.value)) {
        displayMsg("msg-titre", "Titre valide", true);
    } else {
        displayMsg("msg-titre", "Erreur : moins de 3 caractères", false);
    }
});

document.getElementById("description_event").addEventListener("keyup", function() {
    if (checkDescription(this.value)) {
        displayMsg("msg-description", "Description valide", true);
    } else {
        displayMsg("msg-description", "Erreur : moins de 10 caractères", false);
    }
});

document.getElementById("type_event").addEventListener("change", function() {
    if (checkType(this.value)) {
        displayMsg("msg-type", "Type valide", true);
    } else {
        displayMsg("msg-type", "Sélectionnez un type.", false);
    }
});

document.getElementById("date_debut").addEventListener("change", function() {
    if (checkDateDebut(this.value)) {
        displayMsg("msg-date-debut", "Date de début valide", true);
    } else {
        displayMsg("msg-date-debut", "La date de début doit être future.", false);
    }
});

document.getElementById("date_fin").addEventListener("change", function() {
    const dateDebut = document.getElementById("date_debut").value;
    if (checkDateFin(dateDebut, this.value)) {
        displayMsg("msg-date-fin", "Date de fin valide", true);
    } else {
        displayMsg("msg-date-fin", "La date de fin doit être après la date de début.", false);
    }
});

document.getElementById("lieu_event").addEventListener("blur", function() {
    if (checkLieu(this.value)) {
        displayMsg("msg-lieu", "Lieu valide", true);
    } else {
        displayMsg("msg-lieu", "Erreur : au moins 3 caractères requis.", false);
    }
});

document.getElementById("capacite_max").addEventListener("keyup", function() {
    if (checkCapacite(this.value)) {
        displayMsg("msg-capacite", "Capacité valide", true);
    } else {
        displayMsg("msg-capacite", "Entier positif requis.", false);
    }
});

document.getElementById("statut").addEventListener("change", function() {
    if (checkStatut(this.value)) {
        displayMsg("msg-statut", "Statut valide", true);
    } else {
        displayMsg("msg-statut", "Sélectionnez un statut.", false);
    }
});

document.getElementById("id_org").addEventListener("keyup", function() {
    if (checkIdOrg(this.value)) {
        displayMsg("msg-idorg", "ID organisateur valide", true);
    } else {
        displayMsg("msg-idorg", "Entier positif requis.", false);
    }
});