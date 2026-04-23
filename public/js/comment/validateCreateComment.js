function attachCommentFormValidation(root){
  root = root || document;
  root.querySelectorAll('.comment-form').forEach(function (form) {
    // remove previous listener if present
    if(form._validateHandler) form.removeEventListener('submit', form._validateHandler);
    var handler = function (e) {
      if(e && e._validationHandled) return;
      if(e) e._validationHandled = true;
      clearFormErrors(form);
      var textarea = form.querySelector('textarea[name="content"]');
      var text = textarea ? textarea.value.trim() : '';
      if (text.length < 1) {
        setFieldError(textarea || 'textarea[name="content"]', 'Le commentaire ne peut pas être vide.');
        e.preventDefault();
        if (typeof focusFirstInvalid === 'function') focusFirstInvalid(form);
      }
    };
    form._validateHandler = handler;
    form.addEventListener('submit', handler);
  });
}

document.addEventListener('DOMContentLoaded', function(){ attachCommentFormValidation(document); });

// expose for fragment refresh
window.attachCommentFormValidation = attachCommentFormValidation;
