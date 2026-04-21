/**
 * Validation du formulaire d'ajout de formation
 */

(function() {
    document.addEventListener('DOMContentLoaded', function() {
        
        const form = document.getElementById('formationForm');
        if(!form) return;
        
        const submitBtn = document.getElementById('submitBtn');
        
        // Éléments du formulaire
        const categorie = document.getElementById('id_categorie');
        const titre = document.getElementById('titre');
        const type = document.getElementById('type');
        const dateDebut = document.getElementById('date_debut');
        const dateFin = document.getElementById('date_fin');
        const placesMax = document.getElementById('places_max');
        const statut = document.getElementById('statut');
        
        // Messages d'erreur
        const errorCategorie = document.getElementById('error-categorie');
        const errorTitre = document.getElementById('error-titre');
        const errorType = document.getElementById('error-type');
        const errorDateDebut = document.getElementById('error-date_debut');
        const errorDateFin = document.getElementById('error-date_fin');
        const errorPlacesMax = document.getElementById('error-places_max');
        const errorStatut = document.getElementById('error-statut');
        
        // Compteur
        const titreCounter = document.getElementById('titre-counter');
        
        // Date du jour
        const today = new Date().toISOString().split('T')[0];
        if(dateDebut) dateDebut.setAttribute('min', today);
        
        // Validation catégorie
        function validateCategorie() {
            if(!categorie) return true;
            if(!categorie.value) {
                categorie.classList.add('error');
                if(errorCategorie) errorCategorie.classList.add('show');
                return false;
            } else {
                categorie.classList.remove('error');
                if(errorCategorie) errorCategorie.classList.remove('show');
                return true;
            }
        }
        
        // Validation titre
        function validateTitre() {
            if(!titre) return true;
            const value = titre.value.trim();
            if(!value || value.length < 3 || value.length > 150) {
                titre.classList.add('error');
                if(errorTitre) errorTitre.classList.add('show');
                return false;
            } else {
                titre.classList.remove('error');
                if(errorTitre) errorTitre.classList.remove('show');
                return true;
            }
        }
        
        // Compteur titre
        function updateTitreCounter() {
            if(!titreCounter || !titre) return;
            const length = titre.value.length;
            titreCounter.textContent = length + '/150 caractères';
            if(length > 140) titreCounter.classList.add('warning');
            else titreCounter.classList.remove('warning');
            if(length > 150) titreCounter.classList.add('danger');
            else titreCounter.classList.remove('danger');
        }
        
        // Validation type
        function validateType() {
            if(!type) return true;
            if(!type.value) {
                type.classList.add('error');
                if(errorType) errorType.classList.add('show');
                return false;
            } else {
                type.classList.remove('error');
                if(errorType) errorType.classList.remove('show');
                return true;
            }
        }
        
        // Validation date début
        function validateDateDebut() {
            if(!dateDebut) return true;
            if(!dateDebut.value) {
                dateDebut.classList.add('error');
                if(errorDateDebut) errorDateDebut.classList.add('show');
                return false;
            } else {
                dateDebut.classList.remove('error');
                if(errorDateDebut) errorDateDebut.classList.remove('show');
                return true;
            }
        }
        
        // Validation date fin
        function validateDateFin() {
            if(!dateFin) return true;
            if(!dateFin.value) {
                dateFin.classList.add('error');
                if(errorDateFin) errorDateFin.classList.add('show');
                return false;
            } else if(dateDebut && dateDebut.value && dateFin.value < dateDebut.value) {
                dateFin.classList.add('error');
                if(errorDateFin) errorDateFin.classList.add('show');
                return false;
            } else {
                dateFin.classList.remove('error');
                if(errorDateFin) errorDateFin.classList.remove('show');
                return true;
            }
        }
        
        // Validation places max
        function validatePlacesMax() {
            if(!placesMax) return true;
            const value = parseInt(placesMax.value);
            if(!placesMax.value || isNaN(value) || value < 1 || value > 999) {
                placesMax.classList.add('error');
                if(errorPlacesMax) errorPlacesMax.classList.add('show');
                return false;
            } else {
                placesMax.classList.remove('error');
                if(errorPlacesMax) errorPlacesMax.classList.remove('show');
                return true;
            }
        }
        
        // Validation statut
        function validateStatut() {
            if(!statut) return true;
            if(!statut.value) {
                statut.classList.add('error');
                if(errorStatut) errorStatut.classList.add('show');
                return false;
            } else {
                statut.classList.remove('error');
                if(errorStatut) errorStatut.classList.remove('show');
                return true;
            }
        }
        
        // Validation globale
        function validateForm() {
            const isValid = validateCategorie() && validateTitre() && validateType() && 
                           validateDateDebut() && validateDateFin() && 
                           validatePlacesMax() && validateStatut();
            if(submitBtn) submitBtn.disabled = !isValid;
            return isValid;
        }
        
        // Formatage places max
        function formatPlacesMax() {
            if(!placesMax) return;
            let value = placesMax.value.replace(/[^0-9]/g, '');
            if(parseInt(value) > 999) value = 999;
            placesMax.value = value;
            validatePlacesMax();
        }
        
        // Mise à jour date min
        function updateDateFinMin() {
            if(dateFin && dateDebut && dateDebut.value) {
                dateFin.setAttribute('min', dateDebut.value);
                if(dateFin.value && dateFin.value < dateDebut.value) {
                    dateFin.value = dateDebut.value;
                }
                validateDateFin();
            }
        }
        
        // Écouteurs d'événements
        if(categorie) categorie.addEventListener('change', validateForm);
        
        if(titre) {
            titre.addEventListener('input', function() {
                updateTitreCounter();
                validateTitre();
                validateForm();
            });
            titre.addEventListener('blur', validateTitre);
        }
        
        if(type) type.addEventListener('change', validateForm);
        
        if(dateDebut) {
            dateDebut.addEventListener('change', function() {
                validateDateDebut();
                updateDateFinMin();
                validateForm();
            });
        }
        
        if(dateFin) dateFin.addEventListener('change', function() {
            validateDateFin();
            validateForm();
        });
        
        if(placesMax) {
            placesMax.addEventListener('input', formatPlacesMax);
            placesMax.addEventListener('blur', validatePlacesMax);
            placesMax.addEventListener('input', validateForm);
        }
        
        if(statut) statut.addEventListener('change', validateForm);
        
        // Soumission
        if(form) {
            form.addEventListener('submit', function(e) {
                if(!validateForm()) {
                    e.preventDefault();
                    let msg = "Veuillez corriger :\n";
                    if(!validateCategorie()) msg += "- Catégorie\n";
                    if(!validateTitre()) msg += "- Titre (3-150 caractères)\n";
                    if(!validateType()) msg += "- Type\n";
                    if(!validateDateDebut()) msg += "- Date début\n";
                    if(!validateDateFin()) msg += "- Date fin\n";
                    if(!validatePlacesMax()) msg += "- Places (1-999)\n";
                    if(!validateStatut()) msg += "- Statut\n";
                    alert(msg);
                }
            });
        }
        
        // Réinitialisation des erreurs au focus
        const fields = [categorie, titre, type, dateDebut, dateFin, placesMax, statut];
        fields.forEach(field => {
            if(field) {
                field.addEventListener('focus', function() {
                    this.classList.remove('error');
                    const errorId = 'error-' + this.id;
                    const errorEl = document.getElementById(errorId);
                    if(errorEl) errorEl.classList.remove('show');
                });
            }
        });
        
        // Initialisation
        if(titreCounter) updateTitreCounter();
        validateForm();
    });
})();