document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.comment-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var text = form.querySelector('textarea[name="content"]').value.trim();
      if (text.length < 1) {
        alert('Le commentaire ne peut pas être vide.');
        e.preventDefault();
      }
    });
  });
});
