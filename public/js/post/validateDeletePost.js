document.addEventListener('DOMContentLoaded', function () {
  var forms = document.querySelectorAll('form[action="index.php?action=delete_post"]');
  forms.forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!confirm('Confirmer la suppression du post ?')) e.preventDefault();
    });
  });
});
