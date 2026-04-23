document.addEventListener('DOMContentLoaded', function(){
  // Attach AJAX submit handlers to post/comment forms
  function attachAjaxFormHandlers(root){
    root = root || document;

    // helper to attach to a form only once
    function attach(form){
      if(!form) return;
      // remove previous capturing handler if present
      try{ form.removeEventListener('submit', form._ajaxHandler, true); }catch(e){}
      var handler = function(e){
        // run custom validation handler first (if available) but avoid double-run
        try{
          if(form._validateHandler && typeof form._validateHandler === 'function' && !e._validationHandled){
            try{ form._validateHandler(e); } catch(err){ console.error('validator error', err); }
            // mark handled if not already set by the validator
            if(!e._validationHandled) e._validationHandled = true;
          }
        }catch(err){ console.error('run validate', err); }

        if (e.defaultPrevented) return; // validation blocked
        e.preventDefault();
        var action = form.getAttribute('action') || window.location.href;

        // confirmation for deletes
        if(action.indexOf('delete_post') !== -1){ if(!confirm('Supprimer ce post ?')) return; }
        if(action.indexOf('delete_comment') !== -1){ if(!confirm('Supprimer ce commentaire ?')) return; }

        var fd = new FormData(form);
        var params = new URLSearchParams();
        for(var pair of fd.entries()) params.append(pair[0], pair[1]);
        // capture post_id for comment creates so we can reset the right form after fragment refresh
        var pendingCommentPostId = params.get ? params.get('post_id') : null;

        var submitBtn = form.querySelector('[type="submit"]') || form.querySelector('button[type="submit"]');
        var oldDisabled = submitBtn ? submitBtn.disabled : false;
        var oldHtml = submitBtn ? submitBtn.innerHTML : null;
        if(submitBtn){ submitBtn.disabled = true; submitBtn.innerHTML = '...'; }

        var doRefresh = function(){
          if(typeof window.refreshPosts === 'function'){
            // set pendingReset for create_post forms (reset after refresh)
            if(action.indexOf('create_post') !== -1) window._pendingResetForm = form;
            // set pending reset for comment form so textarea will be cleared after refresh
            if(action.indexOf('create_comment') !== -1 && pendingCommentPostId) window._pendingResetCommentPostId = pendingCommentPostId;
            window.refreshPosts();
          } else {
            fetch('index.php?action=fetch_posts_fragment').then(function(r){return r.text();}).then(function(html){ if(html){ var c = document.querySelector('.posts'); if(c) c.outerHTML = html; } });
          }
        };

        fetch(action, {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: params.toString(),
          credentials: 'same-origin'
        }).then(function(resp){
          // trigger refresh regardless of redirect/body
          doRefresh();
        }).catch(function(err){
          console.error('ajax submit error', err);
          alert('Erreur réseau lors de l\'envoi');
        }).finally(function(){
          if(submitBtn){ submitBtn.disabled = oldDisabled; if(oldHtml !== null) submitBtn.innerHTML = oldHtml; }
        });

        return false;
      };
      form._ajaxHandler = handler;
      // attach capturing submit handler so we can run validation then intercept navigation
      form.addEventListener('submit', handler, true);
    }

    // selectors to cover create/update/delete posts & comments
    var selectors = [
      '#create-post-form',
      'form[action*="delete_post"]',
      'form[action*="create_comment"]',
      'form[action*="delete_comment"]',
      '#update-post-form',
      '#update-comment-form',
      'form[action*="update_post"]',
      'form[action*="update_comment"]'
    ];

    selectors.forEach(function(sel){
      root.querySelectorAll(sel).forEach(function(f){ attach(f); });
    });
  }

  // When posts refreshed, reattach handlers and reset create form if needed
  window.onPostsRefreshed = function(newContainer){
    try{ attachAjaxFormHandlers(newContainer || document); } catch(e){ console.error(e); }
    // reattach comment validation handlers if available
    try{
      if(typeof window.attachCommentFormValidation === 'function') window.attachCommentFormValidation(newContainer || document);
      if(typeof window.attachUpdateCommentValidation === 'function') window.attachUpdateCommentValidation(newContainer || document);
      if(typeof window.attachPostCreateValidation === 'function') window.attachPostCreateValidation(newContainer || document);
      if(typeof window.attachPostUpdateValidation === 'function') window.attachPostUpdateValidation(newContainer || document);
    }catch(e){ console.error('re-attach validators', e); }

    if(window._pendingResetForm){ try{ window._pendingResetForm.reset(); }catch(e){} window._pendingResetForm = null; }

    // if a comment was just created, clear the matching comment textarea in the refreshed fragment
    if(window._pendingResetCommentPostId){
      try{
        var nc = newContainer || document;
        var selector = 'form.comment-form input[name="post_id"][value="' + window._pendingResetCommentPostId + '"]';
        var inp = nc.querySelector(selector);
        if(inp){ var f = inp.closest('form'); if(f){ var ta = f.querySelector('textarea[name="content"]'); if(ta) ta.value = ''; } }
      }catch(e){ console.error(e); }
      window._pendingResetCommentPostId = null;
    }
  };

  // initial attach on page load
  attachAjaxFormHandlers(document);
});
