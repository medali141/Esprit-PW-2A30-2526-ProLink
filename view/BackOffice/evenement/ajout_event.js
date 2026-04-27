// ==================== DATES (AAAA-MM-JJ, heure locale) ====================

function parseYMD(s) {
    const t = String(s).trim();
    if (!/^\d{4}-\d{2}-\d{2}$/.test(t)) {
        return null;
    }
    const p = t.split("-");
    return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
}

function formatYMD(d) {
    const m = d.getMonth() + 1;
    const day = d.getDate();
    return d.getFullYear() + "-" + (m < 10 ? "0" : "") + m + "-" + (day < 10 ? "0" : "") + day;
}

function ymdAddOneDay(ymd) {
    const d = parseYMD(ymd);
    if (!d) {
        return ymd;
    }
    d.setDate(d.getDate() + 1);
    return formatYMD(d);
}

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
    if (typeof window !== "undefined" && window.PROLINK_EVENT_EDIT) {
        return /^\d{4}-\d{2}-\d{2}$/.test(String(val).trim());
    }
    const inputDate = parseYMD(val);
    if (!inputDate) {
        return false;
    }
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return inputDate.getTime() > today.getTime();
}

function checkDateFin(dateDebut, dateFin) {
    if (!dateDebut || !dateFin) return false;
    const d0 = parseYMD(dateDebut);
    const d1 = parseYMD(dateFin);
    if (!d0 || !d1) {
        return false;
    }
    return d1.getTime() > d0.getTime();
}

function checkLieu(val) {
    return val !== "";
}

function checkCapacite(val) {
    return val !== "" && !isNaN(val) && Number.isInteger(Number(val)) && parseInt(val, 10) >= 1;
}

function displayMsg(id, message, isSuccess) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = message;
        el.className = "msg " + (isSuccess ? "success" : "error");
    }
}

const eventForm = document.getElementById("eventForm");
const typeEvent = document.getElementById("type_event");
const typeAutre = document.getElementById("type_autre");
const typeAutreWrap = document.getElementById("type_autre_wrap");
const titreEvent = document.getElementById("titre_event");
const descriptionEvent = document.getElementById("description_event");
const lieuEvent = document.getElementById("lieu_event");
const dateDebut = document.getElementById("date_debut");
const dateFin = document.getElementById("date_fin");
const capaciteMax = document.getElementById("capacite_max");

if (typeEvent && typeAutre && typeAutreWrap) {
    typeEvent.addEventListener("change", function () {
        if (this.value === "autre") {
            typeAutreWrap.style.display = "block";
            displayMsg("msg-type", "", true);
        } else {
            typeAutreWrap.style.display = "none";
            typeAutre.value = "";
            displayMsg("msg-type-autre", "", true);
            if (checkType(this.value)) {
                displayMsg("msg-type", "Type valide", true);
            } else {
                displayMsg("msg-type", "Sélectionnez un type.", false);
            }
        }
    });

    typeAutre.addEventListener("keyup", function () {
        if (checkTypeAutre(this.value)) {
            displayMsg("msg-type-autre", "Type valide", true);
        } else {
            displayMsg("msg-type-autre", "Erreur : au moins 3 caractères requis.", false);
        }
    });
}

if (eventForm && typeEvent && titreEvent && descriptionEvent && lieuEvent && dateDebut && dateFin && capaciteMax) {
    eventForm.addEventListener("submit", function (e) {
        e.preventDefault();
        let isValid = true;

        const titreVal = titreEvent.value;
        if (checkTitre(titreVal)) {
            displayMsg("msg-titre", "Correct !", true);
        } else {
            displayMsg("msg-titre", "Le titre doit contenir au moins 3 caractères.", false);
            isValid = false;
        }

        const descVal = descriptionEvent.value;
        if (checkDescription(descVal)) {
            displayMsg("msg-description", "Correct !", true);
        } else {
            displayMsg("msg-description", "La description doit contenir au moins 10 caractères.", false);
            isValid = false;
        }

        const typeVal = typeEvent.value;
        if (!checkType(typeVal)) {
            displayMsg("msg-type", "Sélectionnez un type d'événement.", false);
            isValid = false;
        } else if (typeVal === "autre") {
            const typeAutreVal = typeAutre ? typeAutre.value : "";
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

        const lieuVal = lieuEvent.value;
        if (checkLieu(lieuVal)) {
            displayMsg("msg-lieu", "Correct !", true);
        } else {
            displayMsg("msg-lieu", "Sélectionnez un gouvernorat.", false);
            isValid = false;
        }

        const dateDebutVal = dateDebut.value;
        if (checkDateDebut(dateDebutVal)) {
            displayMsg("msg-date-debut", "Correct !", true);
        } else {
            displayMsg("msg-date-debut", (typeof window !== "undefined" && window.PROLINK_EVENT_EDIT) ? "Sélectionnez une date de début valide." : "La date de début doit être future (après aujourd’hui).", false);
            isValid = false;
        }

        const dateFinVal = dateFin.value;
        if (checkDateFin(dateDebutVal, dateFinVal)) {
            displayMsg("msg-date-fin", "Correct !", true);
        } else {
            displayMsg("msg-date-fin", "La date de fin doit être postérieure à la date de début.", false);
            isValid = false;
        }

        const capaciteVal = capaciteMax.value;
        if (checkCapacite(capaciteVal)) {
            displayMsg("msg-capacite", "Correct !", true);
        } else {
            displayMsg("msg-capacite", "La capacité maximale doit être un entier positif.", false);
            isValid = false;
        }

        if (isValid) {
            eventForm.submit();
        }
    });

    titreEvent.addEventListener("keyup", function () {
        displayMsg("msg-titre",
            checkTitre(this.value) ? "Titre valide" : "Erreur : moins de 3 caractères", checkTitre(this.value));
    });

    descriptionEvent.addEventListener("keyup", function () {
        displayMsg("msg-description",
            checkDescription(this.value) ? "Description valide" : "Erreur : moins de 10 caractères", checkDescription(this.value));
    });

    lieuEvent.addEventListener("change", function () {
        displayMsg("msg-lieu",
            checkLieu(this.value) ? "Gouvernorat valide" : "Sélectionnez un gouvernorat.", checkLieu(this.value));
    });

    function syncFinMinFromDebut() {
        const a = dateDebut.value;
        if (!/^\d{4}-\d{2}-\d{2}$/.test(a)) {
            return;
        }
        const minFin = ymdAddOneDay(a);
        dateFin.setAttribute("min", minFin);
        if (dateFin.value && dateFin.value.length >= 8 && checkDateFin(a, dateFin.value) === false) {
            dateFin.value = minFin;
        }
    }

    syncFinMinFromDebut();

    dateDebut.addEventListener("change", function () {
        const ok = checkDateDebut(this.value);
        displayMsg("msg-date-debut",
            ok ? "Date de début valide" : ((typeof window !== "undefined" && window.PROLINK_EVENT_EDIT) ? "Sélectionnez une date (AAAA-MM-JJ)." : "La date de début doit être future."), ok);
        if (ok) {
            syncFinMinFromDebut();
        }
    });

    dateFin.addEventListener("change", function () {
        const dateDebutValue = dateDebut.value;
        const ok = checkDateFin(dateDebutValue, this.value);
        displayMsg("msg-date-fin",
            ok ? "Date de fin valide" : "La date de fin doit être après la date de début.", ok);
    });

    capaciteMax.addEventListener("keyup", function () {
        displayMsg("msg-capacite",
            checkCapacite(this.value) ? "Capacité valide" : "Entier positif requis.", checkCapacite(this.value));
    });
}
