function attachPostUpdateValidation(root){
  root = root || document;
  var form = root.querySelector('#update-post-form');
  if (!form) return;
  if(form._validateHandler) form.removeEventListener('submit', form._validateHandler);
  var handler = function (e) {
    if(e && e._validationHandled) return;
    if(e) e._validationHandled = true;
    clearFormErrors(form);
    var titleEl = document.getElementById('post-title');
    var contentEl = document.getElementById('post-content');
    var title = titleEl ? titleEl.value.trim() : '';
    var content = contentEl ? contentEl.value.trim() : '';
    var ok = true;
    if (title.length < 3) { setFieldError(titleEl || '#post-title', 'Le titre doit contenir au moins 3 caractères.'); ok = false; }
    if (content.length < 5) { setFieldError(contentEl || '#post-content', 'Le contenu doit contenir au moins 5 caractères.'); ok = false; }
    if (!ok) { e.preventDefault(); if (typeof focusFirstInvalid === 'function') focusFirstInvalid(form); return false; }
  };
  form._validateHandler = handler;
  form.addEventListener('submit', handler);
}

document.addEventListener('DOMContentLoaded', function(){ attachPostUpdateValidation(document); });
window.attachPostUpdateValidation = attachPostUpdateValidation;
