document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('update-comment-form');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    var text = form.querySelector('textarea[name="content"]').value.trim();
    if (text.length < 1) {
      alert('Le commentaire ne peut pas être vide.');
      e.preventDefault();
    }
  });
});
