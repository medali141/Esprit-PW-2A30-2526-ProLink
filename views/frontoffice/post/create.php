<?php
// simple post create form (can be included standalone)
?>
<h2>Créer un post</h2>
<form id="create-post-form" action="index.php?action=create_post" method="post">
  <input type="text" name="title" id="post-title" placeholder="Titre" />
  <textarea name="content" id="post-content" placeholder="Contenu"></textarea>
  <button class="btn" type="submit">Publier</button>
</form>
