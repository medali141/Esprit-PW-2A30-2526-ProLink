/* Validation helper functions to show errors inline below fields */
(function(){
  function createErrorEl(message){
    var d = document.createElement('div');
    d.className = 'field-error';
    d.textContent = message || '';
    d.setAttribute('role','alert');
    d.setAttribute('aria-live','polite');
    // inline style fallback to ensure visibility if CSS is missing/overridden
    d.style.color = '#e74c3c';
    d.style.display = 'block';
    d.style.marginTop = '8px';
    return d;
  }

  function ensureElement(field){
    if(!field) return null;
    if(typeof field === 'string') field = document.querySelector(field);
    return field || null;
  }

  window.setFieldError = function(field, message){
    field = ensureElement(field);
    if(!field){ console.debug('setFieldError: field not found for', field, message); return; }
    window.clearFieldError(field);
    var err = createErrorEl(message || '');
    // ensure color even if external CSS overridden
    try{ err.style.color = '#e74c3c'; }catch(e){}
    // prefer Element.after when available to place immediately after the field
    if(typeof field.after === 'function'){
      try{ field.after(err); }
      catch(e){
        if(field.nextSibling){ field.parentNode.insertBefore(err, field.nextSibling); }
        else { field.parentNode.appendChild(err); }
      }
    } else {
      if(field.nextSibling){ field.parentNode.insertBefore(err, field.nextSibling); }
      else { field.parentNode.appendChild(err); }
    }
    field.classList.add('is-invalid');
    console.debug('setFieldError inserted for', field, err);
  };

  window.clearFieldError = function(field){
    field = ensureElement(field);
    if(!field) return;
    field.classList.remove('is-invalid');
    var next = field.nextElementSibling;
    if(next && next.classList && next.classList.contains('field-error')) next.remove();
  };

  window.clearFormErrors = function(form){
    if(!form) form = document;
    var errors = form.querySelectorAll('.field-error');
    errors.forEach(function(el){ el.remove(); });
    var invalids = form.querySelectorAll('.is-invalid');
    invalids.forEach(function(f){ f.classList.remove('is-invalid'); });
  };

  window.focusFirstInvalid = function(form){
    if(!form) form = document;
    var f = form.querySelector('.is-invalid');
    if(f && typeof f.focus === 'function') f.focus();
  };

  // Fallback: override window.alert to avoid blocking popups from older scripts.
  (function(){
    if(typeof window.alert !== 'function') return;
    var _orig = window.alert;
    function showToast(message){
      try{
        var id = 'validation-toast-container';
        var container = document.getElementById(id);
        if(!container){
          container = document.createElement('div');
          container.id = id;
          container.style.position = 'fixed';
          container.style.top = '18px';
          container.style.left = '50%';
          container.style.transform = 'translateX(-50%)';
          container.style.zIndex = 99999;
          container.style.pointerEvents = 'none';
          document.body.appendChild(container);
        }
        var t = document.createElement('div');
        t.className = 'validation-toast';
        t.style.pointerEvents = 'auto';
        t.style.background = 'rgba(11,22,38,0.95)';
        t.style.color = '#fff';
        t.style.padding = '12px 18px';
        t.style.marginTop = '8px';
        t.style.borderRadius = '10px';
        t.style.minWidth = '220px';
        t.style.boxShadow = '0 10px 30px rgba(11,22,38,0.25)';
        t.style.fontSize = '14px';
        t.textContent = message;
        container.appendChild(t);
        setTimeout(function(){ t.style.opacity = '0'; t.style.transition = 'opacity .35s ease'; setTimeout(function(){ t.remove(); if(!container.children.length) container.remove(); },360); }, 3600);
      }catch(e){ /* ignore */ }
    }

    window.alert = function(msg){
      try{
        var text = (typeof msg === 'string') ? msg : String(msg || '');
        var low = text.toLowerCase();
        var mapped = false;

        // simple keyword -> selector mapping
        var map = [
          ['titre','#post-title'],
          ['contenu','#post-content'],
          ['commentaire','textarea[name="content"]'],
          ['email','input[name="email"]'],
          ['mot de passe','input[name="mdp"]'],
          ['nom','input[name="nom"]'],
          ['prénom','input[name="prenom"]'],
          ['âge','input[name="age"]'],
          ['age','input[name="age"]']
        ];

        for(var i=0;i<map.length;i++){
          if(low.indexOf(map[i][0]) !== -1){
            try{ setFieldError(map[i][1], text); mapped = true; break; } catch(e){}
          }
        }

        if(!mapped){
          // try to attach to first empty required field in the active form
          var active = document.activeElement;
          var form = (active && active.closest) ? active.closest('form') : null;
          if(!form) form = document.querySelector('form');
          if(form){
            var fields = form.querySelectorAll('input,textarea,select');
            for(var j=0;j<fields.length;j++){
              var f = fields[j];
              if(f.disabled) continue;
              var val = (f.value||'').toString().trim();
              var type = (f.getAttribute('type')||'').toLowerCase();
              if(f.hasAttribute('required') && (val === '' || (type==='number' && isNaN(Number(val))))){
                try{ setFieldError(f, text); mapped = true; break; } catch(e){}
              }
            }
          }
        }

        if(!mapped){
          showToast(text);
        }
      }catch(e){ try{ _orig(msg); }catch(_){} }
    };
  })();

})();
