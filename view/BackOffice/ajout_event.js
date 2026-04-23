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

function checkTypeAutre(val) {
    return val.trim().length >= 3;
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
    return val !== "";
}

function checkCapacite(val) {
    return val !== "" && !isNaN(val) && Number.isInteger(Number(val)) && parseInt(val) >= 1;
}

function checkStatut(val) {
    return val !== "";
}

function displayMsg(id, message, isSuccess) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = message;
        el.className = "msg " + (isSuccess ? "success" : "error");
    }
}


// ==================== AFFICHAGE CONDITIONNEL "AUTRE" ====================

document.getElementById("type_event").addEventListener("change", function () {
    const wrap = document.getElementById("type_autre_wrap");
    if (this.value === "autre") {
        wrap.style.display = "block";
        displayMsg("msg-type", "", true);
    } else {
        wrap.style.display = "none";
        document.getElementById("type_autre").value = "";
        displayMsg("msg-type-autre", "", true);
        if (checkType(this.value)) {
            displayMsg("msg-type", "Type valide", true);
        } else {
            displayMsg("msg-type", "Sélectionnez un type.", false);
        }
    }
});

document.getElementById("type_autre").addEventListener("keyup", function () {
    if (checkTypeAutre(this.value)) {
        displayMsg("msg-type-autre", "Type valide", true);
    } else {
        displayMsg("msg-type-autre", "Erreur : au moins 3 caractères requis.", false);
    }
});


// ==================== VALIDATION AU SUBMIT ====================

document.getElementById("eventForm").addEventListener("submit", function (e) {
    e.preventDefault();
    let isValid = true;

    // Titre
    const titreVal = document.getElementById("titre_event").value;
    if (checkTitre(titreVal)) {
        displayMsg("msg-titre", "Correct !", true);
    } else {
        displayMsg("msg-titre", "Le titre doit contenir au moins 3 caractères.", false);
        isValid = false;
    }

    // Description
    const descVal = document.getElementById("description_event").value;
    if (checkDescription(descVal)) {
        displayMsg("msg-description", "Correct !", true);
    } else {
        displayMsg("msg-description", "La description doit contenir au moins 10 caractères.", false);
        isValid = false;
    }

    // Type
    const typeVal = document.getElementById("type_event").value;
    if (!checkType(typeVal)) {
        displayMsg("msg-type", "Sélectionnez un type d'événement.", false);
        isValid = false;
    } else if (typeVal === "autre") {
        const typeAutreVal = document.getElementById("type_autre").value;
        if (checkTypeAutre(typeAutreVal)) {
            displayMsg("msg-type-autre", "Correct !", true);
            displayMsg("msg-type", "", true);
        } else {
            displayMsg("msg-type-autre", "Précisez le type (au moins 3 caractères).", false);
            isValid = false;
        }
    } else {
        displayMsg("msg-type", "Correct !", true);
    }

    // Lieu
    const lieuVal = document.getElementById("lieu_event").value;
    if (checkLieu(lieuVal)) {
        displayMsg("msg-lieu", "Correct !", true);
    } else {
        displayMsg("msg-lieu", "Sélectionnez un gouvernorat.", false);
        isValid = false;
    }

    // Statut
    const statutVal = document.getElementById("statut").value;
    if (checkStatut(statutVal)) {
        displayMsg("msg-statut", "Correct !", true);
    } else {
        displayMsg("msg-statut", "Sélectionnez un statut.", false);
        isValid = false;
    }

    // Date début
    const dateDebutVal = document.getElementById("date_debut").value;
    if (checkDateDebut(dateDebutVal)) {
        displayMsg("msg-date-debut", "Correct !", true);
    } else {
        displayMsg("msg-date-debut", "La date de début doit être une date future.", false);
        isValid = false;
    }

    // Date fin
    const dateFinVal = document.getElementById("date_fin").value;
    if (checkDateFin(dateDebutVal, dateFinVal)) {
        displayMsg("msg-date-fin", "Correct !", true);
    } else {
        displayMsg("msg-date-fin", "La date de fin doit être postérieure à la date de début.", false);
        isValid = false;
    }

    // Capacité
    const capaciteVal = document.getElementById("capacite_max").value;
    if (checkCapacite(capaciteVal)) {
        displayMsg("msg-capacite", "Correct !", true);
    } else {
        displayMsg("msg-capacite", "La capacité maximale doit être un entier positif.", false);
        isValid = false;
    }

    if (isValid) {
        alert("Événement ajouté avec succès !");
        document.getElementById("eventForm").submit();
    }
});


// ==================== VALIDATION EN TEMPS RÉEL ====================

document.getElementById("titre_event").addEventListener("keyup", function () {
    displayMsg("msg-titre",
        checkTitre(this.value) ? "Titre valide" : "Erreur : moins de 3 caractères", checkTitre(this.value));
});

document.getElementById("description_event").addEventListener("keyup", function () {
    displayMsg("msg-description",
        checkDescription(this.value) ? "Description valide" : "Erreur : moins de 10 caractères", checkDescription(this.value));
});

document.getElementById("lieu_event").addEventListener("change", function () {
    displayMsg("msg-lieu",
        checkLieu(this.value) ? "Gouvernorat valide" : "Sélectionnez un gouvernorat.", checkLieu(this.value));
});

document.getElementById("statut").addEventListener("change", function () {
    displayMsg("msg-statut",
        checkStatut(this.value) ? "Statut valide" : "Sélectionnez un statut.", checkStatut(this.value));
});

document.getElementById("date_debut").addEventListener("change", function () {
    displayMsg("msg-date-debut",
        checkDateDebut(this.value) ? "Date de début valide" : "La date de début doit être future.", checkDateDebut(this.value));
});

document.getElementById("date_fin").addEventListener("change", function () {
    const dateDebut = document.getElementById("date_debut").value;
    const ok = checkDateFin(dateDebut, this.value);
    displayMsg("msg-date-fin",
        ok ? "Date de fin valide" : "La date de fin doit être après la date de début.", ok);
});

document.getElementById("capacite_max").addEventListener("keyup", function () {
    displayMsg("msg-capacite",
        checkCapacite(this.value) ? "Capacité valide" : "Entier positif requis.", checkCapacite(this.value));
});