// Shared BackOffice client-side validation
document.addEventListener('DOMContentLoaded', function(){
    const forms = document.querySelectorAll('form[data-validate="user-form"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e){
            const valid = validateUserForm(form);
            if (!valid) e.preventDefault();
        });
    });
});

function clearErrors(form){
    form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
}

function setError(field, message){
    let err = field.parentElement.querySelector('.field-error');
    if(!err){
        err = document.createElement('div');
        err.className = 'field-error';
        field.parentElement.appendChild(err);
    }
    err.textContent = message;
}

function isEmail(val){
    // simple email regex
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
}

function isAlpha(val){
    return /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/.test(val);
}

function validateUserForm(form){
    clearErrors(form);
    let ok = true;

    const nom = form.elements['nom'];
    const prenom = form.elements['prenom'];
    const email = form.elements['email'];
    const mdp = form.elements['mdp'];
    const type = form.elements['type'];
    const age = form.elements['age'];

    if(nom){
        const v = nom.value.trim();
        if(v.length < 2) { setError(nom, 'Le nom doit contenir au moins 2 caractères'); ok = false; }
        else if(!isAlpha(v)) { setError(nom, 'Nom: caractères invalides'); ok = false; }
    }

    if(prenom){
        const v = prenom.value.trim();
        if(v.length < 2) { setError(prenom, 'Le prénom doit contenir au moins 2 caractères'); ok = false; }
        else if(!isAlpha(v)) { setError(prenom, 'Prénom: caractères invalides'); ok = false; }
    }

    if(email){
        const v = email.value.trim();
        if(v.length === 0) { setError(email, 'Email requis'); ok = false; }
        else if(!isEmail(v)) { setError(email, 'Format d\'email invalide'); ok = false; }
    }

    if(mdp){
        const v = mdp.value;
        if(v.length < 6) { setError(mdp, 'Le mot de passe doit contenir au moins 6 caractères'); ok = false; }
    }

    if(type){
        const v = type.value;
        if(!['admin','candidat','entrepreneur'].includes(v)) { setError(type, 'Type invalide'); ok = false; }
    }

    if(age){
        const v = age.value.trim();
        if(v.length === 0) { setError(age, 'Âge requis'); ok = false; }
        else if(!/^\d+$/.test(v)) { setError(age, 'Âge doit être un nombre entier'); ok = false; }
        else {
            const n = parseInt(v,10);
            if(n < 13 || n > 120) { setError(age, 'Âge doit être entre 13 et 120'); ok = false; }
        }
    }

    return ok;
}

// Delete confirmation modal handling
document.addEventListener('DOMContentLoaded', function(){
    // find all delete buttons (they should have class .js-delete and data-href)
    document.querySelectorAll('.js-delete').forEach(btn => {
        btn.addEventListener('click', function(e){
            e.preventDefault();
            const href = btn.getAttribute('data-href') || btn.getAttribute('href');
            showConfirmModal('Voulez-vous vraiment supprimer cet utilisateur ?', href);
        });
    });

    // modal elements
    const modal = document.getElementById('confirmModal');
    const okBtn = document.getElementById('confirmOk');
    const cancelBtn = document.getElementById('confirmCancel');

    function closeModal(){
        if(!modal) return;
        modal.setAttribute('aria-hidden','true');
        // remove stored href
        modal.dataset.href = '';
    }

    if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if(modal) modal.addEventListener('click', function(e){
        if(e.target.classList.contains('confirm-modal-backdrop')) closeModal();
    });

    if(okBtn) okBtn.addEventListener('click', function(){
        const target = modal && modal.dataset.href;
        if(target){ window.location.href = target; }
    });

    window.showConfirmModal = function(message, href){
        if(!modal) return window.confirm(message);
        const msg = modal.querySelector('#confirmMessage');
        if(msg) msg.textContent = message;
        modal.dataset.href = href || '';
        modal.setAttribute('aria-hidden','false');
    };
});
