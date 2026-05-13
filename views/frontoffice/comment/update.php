<?php
// expects $comment
?>
<h2>Modifier le commentaire</h2>
<form id="update-comment-form" action="index.php?action=update_comment" method="post">
  <input type="hidden" name="id" value="<?= $comment['id'] ?>">
  <textarea name="content"><?= htmlspecialchars($comment['content']) ?></textarea>
  <button class="btn" type="submit">Enregistrer</button>
</form>
