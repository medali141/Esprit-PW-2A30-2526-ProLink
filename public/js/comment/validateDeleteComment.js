document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[action="index.php?action=delete_comment"]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!confirm('Confirmer la suppression du commentaire ?')) e.preventDefault();
    });
  });
});
