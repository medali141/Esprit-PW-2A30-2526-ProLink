document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('update-post-form');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    var title = document.getElementById('post-title').value.trim();
    var content = document.getElementById('post-content').value.trim();
    if (title.length < 3) {
      alert('Le titre doit contenir au moins 3 caractères.');
      e.preventDefault();
      return false;
    }
    if (content.length < 5) {
      alert('Le contenu doit contenir au moins 5 caractères.');
      e.preventDefault();
      return false;
    }
  });
});
