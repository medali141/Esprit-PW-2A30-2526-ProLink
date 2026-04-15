/**
 * Contrôles de saisie côté client (Back-office + Front-office).
 * Attacher data-validate="…" et novalidate sur les <form>.
 */
(function () {
    'use strict';

    function clearErrors(form) {
        form.querySelectorAll('.field-error').forEach(function (el) {
            el.remove();
        });
    }

    function setError(field, message) {
        if (!field) return;
        var err = field.nextElementSibling;
        if (!err || !err.classList.contains('field-error')) {
            err = document.createElement('div');
            err.className = 'field-error';
            err.style.cssText = 'color:#e74c3c;font-size:13px;margin:4px 0 8px;';
            field.insertAdjacentElement('afterend', err);
        }
        err.textContent = message;
    }

    function isEmail(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    }

    function isAlpha(val) {
        return /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/.test(val);
    }

    function parseMoneyInput(val) {
        if (val === undefined || val === null) return NaN;
        var s = String(val).trim().replace(/\s/g, '').replace(',', '.');
        if (s === '') return NaN;
        return parseFloat(s);
    }

    function validateUserForm(form) {
        clearErrors(form);
        var ok = true;
        var nom = form.elements['nom'];
        var prenom = form.elements['prenom'];
        var email = form.elements['email'];
        var mdp = form.elements['mdp'];
        var type = form.elements['type'];
        var age = form.elements['age'];

        if (nom) {
            var vn = nom.value.trim();
            if (vn.length < 2) {
                setError(nom, 'Le nom doit contenir au moins 2 caractères');
                ok = false;
            } else if (!isAlpha(vn)) {
                setError(nom, 'Nom : caractères invalides');
                ok = false;
            }
        }
        if (prenom) {
            var vp = prenom.value.trim();
            if (vp.length < 2) {
                setError(prenom, 'Le prénom doit contenir au moins 2 caractères');
                ok = false;
            } else if (!isAlpha(vp)) {
                setError(prenom, 'Prénom : caractères invalides');
                ok = false;
            }
        }
        if (email) {
            var ve = email.value.trim();
            if (ve.length === 0) {
                setError(email, 'Email requis');
                ok = false;
            } else if (!isEmail(ve)) {
                setError(email, "Format d'email invalide");
                ok = false;
            }
        }
        if (mdp) {
            if (mdp.value.length < 6) {
                setError(mdp, 'Le mot de passe doit contenir au moins 6 caractères');
                ok = false;
            }
        }
        if (type) {
            if (!['admin', 'candidat', 'entrepreneur'].includes(type.value)) {
                setError(type, 'Type invalide');
                ok = false;
            }
        }
        if (age) {
            var va = age.value.trim();
            if (va.length === 0) {
                setError(age, 'Âge requis');
                ok = false;
            } else if (!/^\d+$/.test(va)) {
                setError(age, 'Âge : nombre entier');
                ok = false;
            } else {
                var n = parseInt(va, 10);
                if (n < 13 || n > 120) {
                    setError(age, 'Âge entre 13 et 120');
                    ok = false;
                }
            }
        }
        return ok;
    }

    function validateProduitForm(form) {
        clearErrors(form);
        var ok = true;
        var ref = form.elements['reference'];
        if (ref) {
            var vr = ref.value.trim();
            if (vr.length < 2) {
                setError(ref, 'Référence : au moins 2 caractères');
                ok = false;
            } else if (vr.length > 50) {
                setError(ref, 'Référence : maximum 50 caractères');
                ok = false;
            } else if (!/^[A-Za-z0-9._-]+$/.test(vr)) {
                setError(ref, 'Référence : lettres, chiffres, point, tiret et _ uniquement');
                ok = false;
            }
        }
        var des = form.elements['designation'];
        if (des) {
            var vd = des.value.trim();
            if (vd.length < 2) {
                setError(des, 'Désignation : au moins 2 caractères');
                ok = false;
            } else if (vd.length > 200) {
                setError(des, 'Désignation : maximum 200 caractères');
                ok = false;
            }
        }
        var prixEl = form.elements['prix_unitaire'];
        if (prixEl) {
            var pn = parseMoneyInput(prixEl.value);
            if (!isFinite(pn) || pn < 0) {
                setError(prixEl, 'Prix invalide (nombre ≥ 0, virgule ou point acceptés)');
                ok = false;
            }
        }
        var stockEl = form.elements['stock'];
        if (stockEl) {
            var vs = stockEl.value.trim();
            if (!/^\d+$/.test(vs)) {
                setError(stockEl, 'Stock : entier positif uniquement');
                ok = false;
            } else if (parseInt(vs, 10) > 9999999) {
                setError(stockEl, 'Stock trop élevé');
                ok = false;
            }
        }
        var vendeur = form.elements['id_vendeur'];
        if (vendeur && vendeur.tagName === 'SELECT') {
            if (!vendeur.value || vendeur.value === '') {
                setError(vendeur, 'Choisissez un vendeur');
                ok = false;
            }
        }
        var desc = form.elements['description'];
        if (desc && desc.value.length > 65535) {
            setError(desc, 'Description trop longue');
            ok = false;
        }
        return ok;
    }

    function validateCommandeForm(form) {
        clearErrors(form);
        var ok = true;
        var notes = form.elements['notes'];
        if (notes && notes.value.length > 500) {
            setError(notes, 'Notes : maximum 500 caractères');
            ok = false;
        }
        var dprev = form.elements['date_livraison_prevue'];
        if (dprev && dprev.value && !/^\d{4}-\d{2}-\d{2}$/.test(dprev.value)) {
            setError(dprev, 'Date prévue invalide');
            ok = false;
        }
        var deff = form.elements['date_livraison_effective'];
        if (deff && deff.value && !/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(deff.value)) {
            setError(deff, 'Date/heure invalide');
            ok = false;
        }
        return ok;
    }

    function validateCheckoutForm(form) {
        clearErrors(form);
        var ok = true;
        var adr = form.elements['adresse_livraison'];
        if (adr) {
            var a = adr.value.trim();
            if (a.length < 5) {
                setError(adr, 'Adresse : au moins 5 caractères');
                ok = false;
            } else if (a.length > 300) {
                setError(adr, 'Adresse : maximum 300 caractères');
                ok = false;
            }
        }
        var cp = form.elements['code_postal'];
        if (cp) {
            var c = cp.value.trim();
            if (c.length < 2 || c.length > 20) {
                setError(cp, 'Code postal : entre 2 et 20 caractères');
                ok = false;
            } else if (!/^[\w\s-]+$/.test(c)) {
                setError(cp, 'Code postal : caractères invalides');
                ok = false;
            }
        }
        var ville = form.elements['ville'];
        if (ville) {
            var vi = ville.value.trim();
            if (vi.length < 2) {
                setError(ville, 'Ville : au moins 2 caractères');
                ok = false;
            } else if (vi.length > 100) {
                setError(ville, 'Ville : maximum 100 caractères');
                ok = false;
            }
        }
        var pays = form.elements['pays'];
        if (pays && pays.value.trim().length > 100) {
            setError(pays, 'Pays : maximum 100 caractères');
            ok = false;
        }
        var notes = form.elements['notes'];
        if (notes && notes.value.length > 500) {
            setError(notes, 'Notes : maximum 500 caractères');
            ok = false;
        }
        return ok;
    }

    function validateLoginForm(form) {
        clearErrors(form);
        var ok = true;
        var email = form.elements['email'];
        var mdp = form.elements['mdp'];
        if (email) {
            var ve = email.value.trim();
            if (!ve) {
                setError(email, 'Email requis');
                ok = false;
            } else if (!isEmail(ve)) {
                setError(email, 'Format email invalide');
                ok = false;
            }
        }
        if (mdp) {
            if (!mdp.value || mdp.value.length === 0) {
                setError(mdp, 'Mot de passe requis');
                ok = false;
            } else if (mdp.value.length < 6) {
                setError(mdp, 'Mot de passe : au moins 6 caractères');
                ok = false;
            }
        }
        return ok;
    }

    function validatePanierForm(form) {
        clearErrors(form);
        var ok = true;
        form.querySelectorAll('input[name^="qty["]').forEach(function (inp) {
            var v = inp.value.trim();
            if (!/^\d+$/.test(v)) {
                setError(inp, 'Quantité : entier positif');
                ok = false;
                return;
            }
            var n = parseInt(v, 10);
            var max = parseInt(inp.getAttribute('max'), 10);
            var min = parseInt(inp.getAttribute('min'), 10);
            if (isNaN(min)) min = 0;
            if (n < min || n > max) {
                setError(inp, 'Quantité entre ' + min + ' et ' + max);
                ok = false;
            }
        });
        return ok;
    }

    function bindForm(selector, validator) {
        document.querySelectorAll(selector).forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!validator(form)) e.preventDefault();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindForm('form[data-validate="user-form"]', validateUserForm);
        bindForm('form[data-validate="produit-form"]', validateProduitForm);
        bindForm('form[data-validate="commande-form"]', validateCommandeForm);
        bindForm('form[data-validate="checkout-form"]', validateCheckoutForm);
        bindForm('form[data-validate="panier-form"]', validatePanierForm);
        bindForm('form[data-validate="login-form"]', validateLoginForm);
        bindForm('form[data-validate="forgot-form"]', validateLoginForm);
    });
})();
